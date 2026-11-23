<?php

namespace Tests\Feature;

use App\Models\PageModule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsSite;
use Tests\TestCase;

class PublicHomeSeoTest extends TestCase
{
    use BuildsSite, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedLanguages();
    }

    public function test_root_permanently_redirects_to_the_default_farsi_locale(): void
    {
        $this->get('/')->assertStatus(301)->assertRedirect('/fa');
    }

    public function test_farsi_home_is_server_rendered_with_canonical_and_language_alternates(): void
    {
        $this->get('/fa')
            ->assertOk()
            ->assertSee('<meta charset="utf-8">', false)
            ->assertSee('<html lang="fa-IR" dir="rtl">', false)
            ->assertSee('rel="canonical" href="http://localhost/fa"', false)
            ->assertSee('hreflang="fa-IR"', false)
            ->assertSee('hreflang="en"', false)
            ->assertSee('hreflang="x-default"', false)
            ->assertSee('name="description"', false)
            ->assertSee('og:image', false)
            ->assertSee('application/ld+json', false);
    }

    public function test_english_home_is_server_rendered_and_uses_english_direction(): void
    {
        $this->get('/en')
            ->assertOk()
            ->assertSee('<html lang="en" dir="ltr">', false)
            ->assertSee('rel="canonical" href="http://localhost/en"', false);
    }

    public function test_unknown_locale_is_not_found(): void
    {
        $this->get('/de')->assertNotFound();
    }

    public function test_non_production_responses_are_marked_noindex(): void
    {
        $this->get('/fa')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')
            ->assertSee('name="robots" content="noindex,nofollow"', false);
    }

    public function test_sitemap_is_not_exposed_in_non_production(): void
    {
        $this->get('/sitemap.xml')->assertNotFound();
    }

    public function test_robots_blocks_everything_in_non_production(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /');
    }

    public function test_home_without_records_shows_no_built_in_content(): void
    {
        $html = $this->get('/en')->assertOk()->getContent();

        $this->assertStringNotContainsString('aria-roledescription="slide"', $html);
        $this->assertStringNotContainsString('data-atmosphere', $html);
        $this->assertSame(1, substr_count($html, '<h1'));
    }

    public function test_hero_module_posts_become_slides_with_a_call_to_action(): void
    {
        $home = $this->makePage('fa', ['title' => 'خانه']);
        $home->claimFlag('is_home', ['language_id' => $home->language_id]);
        $news = $this->makePage('fa', ['title' => 'اخبار']);
        $this->makePost($news, ['title' => 'اسلاید یک']);
        PageModule::create(['page_id' => $home->id, 'type' => 'posts', 'title' => 'هیرو', 'source_page_id' => $news->id, 'display' => 'hero']);

        $this->get('/fa')->assertOk()->assertSee('اسلاید یک')->assertSee('class="button button--green hero-cta"', false)
            ->assertDontSee('معماری در مقیاس یادمانی') // built-in placeholder copy is replaced
            ->assertDontSee('class="section module module-posts"', false);
    }

    public function test_text_module_keeps_images_of_this_site(): void
    {
        $home = $this->makePage('fa', ['title' => 'خانه']);
        $home->claimFlag('is_home', ['language_id' => $home->language_id]);
        $src = url('uploads/inline/a.jpg');
        PageModule::create(['page_id' => $home->id, 'type' => 'about', 'title' => 'متن', 'show_title' => true, 'display' => 'list',
            'body' => '<p>متن آزمایشی</p><img src="'.$src.'" alt="x"><img src="https://evil.example/x.jpg" alt="y">']);

        $html = $this->get('/fa')->assertOk()->getContent();

        $this->assertStringContainsString('متن آزمایشی', $html);
        $this->assertStringContainsString($src, $html);
        $this->assertStringNotContainsString('evil.example', $html);
    }

    public function test_supporters_become_a_slider_above_four(): void
    {
        $home = $this->makePage('fa', ['title' => 'خانه']);
        $home->claimFlag('is_home', ['language_id' => $home->language_id]);
        $make = function (string $placement, int $count) use ($home) {
            $group = \App\Models\LinkGroup::create(['language_id' => $home->language_id, 'placement' => $placement, 'title' => $placement, 'is_active' => true]);
            foreach (range(1, $count) as $i) {
                \App\Models\Link::create(['link_group_id' => $group->id, 'label' => "نام {$placement} {$i}", 'type' => 'url', 'url' => '/fa/x', 'is_active' => true, 'sort_order' => $i]);
            }

            return $group;
        };
        PageModule::create(['page_id' => $home->id, 'type' => 'links', 'display' => 'auto', 'link_group_id' => $make('supporters', 5)->id]);

        $html = $this->get('/fa')->assertOk()->getContent();

        $this->assertStringContainsString('supporters-section', $html);
        $this->assertStringContainsString('data-slider', $html);
    }

    public function test_language_switcher_lists_only_the_other_languages(): void
    {
        $html = $this->get('/en')->assertOk()->getContent();
        preg_match('#<nav class="lang-switch".*?</nav>#s', $html, $m);

        $this->assertStringContainsString('hreflang="fa-IR"', $m[0]);
        $this->assertStringNotContainsString('hreflang="en"', $m[0]);
    }

    public function test_production_urls_ignore_a_spoofed_host_header(): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => 'https://iranbuildexpo.test']);
        $this->app->make(\App\Providers\AppServiceProvider::class, ['app' => $this->app])->boot();

        $this->get('/fa/badge', ['Host' => 'evil.test'])->assertOk()->assertSee('https://iranbuildexpo.test/fa/badge', false)->assertDontSee('evil.test');
    }

    public function test_site_indexable_switch_keeps_a_production_draft_out_of_search_engines(): void
    {
        $this->app['env'] = 'production';

        config(['app.indexable' => false]);
        $this->get('/fa')->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive')->assertSee('name="robots" content="noindex,nofollow"', false);
        $this->get('/robots.txt')->assertSee('Disallow: /');
        $this->get('/sitemap.xml')->assertNotFound();

        config(['app.indexable' => true]);
        $this->get('/fa')->assertHeaderMissing('X-Robots-Tag')->assertSee('name="robots" content="index,follow"', false);
        $this->get('/sitemap.xml')->assertOk();
    }
}
