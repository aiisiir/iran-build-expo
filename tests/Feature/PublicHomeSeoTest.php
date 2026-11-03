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

    public function test_fallback_home_has_exactly_four_hero_slides_and_a_prioritised_first_image(): void
    {
        $html = $this->get('/en')->assertOk()->getContent();

        $this->assertSame(4, substr_count($html, 'aria-roledescription="slide"'));
        $this->assertSame(1, substr_count($html, 'fetchpriority="high"'));
        $this->assertMatchesRegularExpression('/<img class="hero-image"[^>]*fetchpriority="high"/', $html);
        $this->assertStringNotContainsString('class="btn btn-gold"', $html); // no destination, no call to action
    }

    public function test_hero_module_posts_become_slides_with_a_call_to_action(): void
    {
        $home = $this->makePage('fa', ['title' => 'خانه']);
        $home->claimFlag('is_home', ['language_id' => $home->language_id]);
        $news = $this->makePage('fa', ['title' => 'اخبار']);
        $this->makePost($news, ['title' => 'اسلاید یک']);
        PageModule::create(['page_id' => $home->id, 'type' => 'posts', 'title' => 'هیرو', 'source_page_id' => $news->id, 'display' => 'hero']);

        $this->get('/fa')->assertOk()->assertSee('اسلاید یک')->assertSee('class="btn btn-gold"', false)
            ->assertDontSee('معماری در مقیاس یادمانی') // built-in placeholder copy is replaced
            ->assertDontSee('class="section module module-posts"', false);
    }

    public function test_home_has_a_single_h1_and_a_video_section_with_a_poster(): void
    {
        $html = $this->get('/fa')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<h1'));
        $this->assertStringContainsString('preload="none"', $html);
        $this->assertStringContainsString('atmosphere-poster.jpg', $html);
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
}
