<?php

namespace Tests\Feature;

use App\Enums\PublishStatus;
use App\Models\Company;
use App\Models\Link;
use App\Models\LinkGroup;
use App\Models\PageModule;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsSite;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use BuildsSite, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedLanguages();
    }

    public function test_fresh_install_shows_the_fallback_home_with_hreflang(): void
    {
        $this->get('/fa')->assertOk()->assertSee('hreflang="en"', false)->assertSee('hreflang="x-default"', false);
        $this->get('/en')->assertOk()->assertSee('<html lang="en" dir="ltr">', false);
        $this->get('/de')->assertNotFound();
    }

    public function test_root_redirects_to_the_default_language(): void
    {
        $this->get('/')->assertRedirect('/fa')->assertStatus(301);
    }

    public function test_home_page_and_its_modules_render_server_side(): void
    {
        $home = $this->makePage('fa', ['title' => 'خانه']);
        $home->claimFlag('is_home', ['language_id' => $home->language_id]);
        $news = $this->makePage('fa', ['title' => 'اخبار']);
        $this->makePost($news, ['title' => 'افتتاح نمایشگاه']);
        PageModule::create(['page_id' => $home->id, 'type' => 'posts', 'title' => 'آخرین اخبار', 'source_page_id' => $news->id, 'display' => 'list']);

        $this->get('/fa')->assertOk()->assertSee('آخرین اخبار')->assertSee('افتتاح نمایشگاه')
            ->assertSee('rel="canonical" href="http://localhost/fa"', false);
        $this->get('/fa/خانه')->assertRedirect('/fa');
    }

    public function test_draft_and_future_pages_are_not_public(): void
    {
        $this->makePage('fa', ['title' => 'پیش‌نویس', 'status' => PublishStatus::Draft]);
        $this->makePage('fa', ['title' => 'آینده', 'published_at' => now()->addDay()]);

        $this->get('/fa/'.rawurlencode('پیش‌نویس'))->assertNotFound();
        $this->get('/fa/'.rawurlencode('آینده'))->assertNotFound();
    }

    public function test_a_page_only_exists_in_its_own_language(): void
    {
        $this->makePage('fa', ['title' => 'درباره ما']);

        $this->get('/fa/'.rawurlencode('درباره-ما'))->assertOk();
        $this->get('/en/'.rawurlencode('درباره-ما'))->assertNotFound();
    }

    public function test_translations_produce_hreflang_alternates_and_the_language_switch(): void
    {
        $this->makePage('fa', ['title' => 'درباره ما', 'slug' => 'about-fa', 'translation_key' => 'about']);
        $this->makePage('en', ['title' => 'About us', 'slug' => 'about', 'translation_key' => 'about']);

        $this->get('/fa/about-fa')->assertOk()
            ->assertSee('hreflang="fa-IR" href="http://localhost/fa/about-fa"', false)
            ->assertSee('hreflang="en" href="http://localhost/en/about"', false)
            ->assertSee('hreflang="x-default" href="http://localhost/fa/about-fa"', false)
            ->assertSee('href="/en/about"', false);
    }

    public function test_news_item_is_reachable_under_its_page_and_has_article_data(): void
    {
        $news = $this->makePage('fa', ['title' => 'اخبار']);
        $post = $this->makePost($news, ['title' => 'خبر مهم', 'body' => '<p>متن خبر</p>']);

        $this->get('/fa/'.rawurlencode('اخبار').'/'.rawurlencode('خبر-مهم'))
            ->assertOk()->assertSee('متن خبر')->assertSee('NewsArticle', false)->assertSee('BreadcrumbList', false);

        $this->assertSame(1, $post->fresh()->view_count);
        $this->get('/fa/'.rawurlencode('اخبار').'/'.rawurlencode('خبر-مهم'), ['User-Agent' => 'Googlebot'])->assertOk();
        $this->assertSame(1, $post->fresh()->view_count);
    }

    public function test_company_and_product_have_their_own_pages(): void
    {
        $list = $this->makePage('fa', ['title' => 'شرکت‌کنندگان', 'slug' => 'participants']);
        $company = Company::create(['language_id' => $list->language_id, 'page_id' => $list->id, 'name' => 'آجر پارس', 'industry' => 'مصالح']);
        Product::create(['company_id' => $company->id, 'name' => 'آجر نسوز']);
        Company::create(['language_id' => $list->language_id, 'page_id' => $list->id, 'name' => 'غیرفعال', 'is_active' => false]);

        $this->get('/fa/participants/'.rawurlencode('آجر-پارس'))->assertOk()->assertSee('مصالح')->assertSee('آجر نسوز');
        $this->get('/fa/participants/'.rawurlencode('آجر-پارس').'/'.rawurlencode('آجر-نسوز'))->assertOk()->assertSee('"@type":"Product"', false);
        $this->get('/fa/participants/'.rawurlencode('غیرفعال'))->assertNotFound();
    }

    public function test_public_pages_send_no_cookies(): void
    {
        $this->makePage('fa', ['title' => 'تماس']);

        $this->get('/fa/'.rawurlencode('تماس'))->assertOk()->assertCookieMissing(config('session.cookie'));
    }

    public function test_menu_comes_from_link_groups_and_supports_sub_items(): void
    {
        $page = $this->makePage('fa', ['title' => 'تماس']);
        $group = LinkGroup::create(['language_id' => $page->language_id, 'placement' => 'menu']);
        $parent = Link::create(['link_group_id' => $group->id, 'label' => 'درباره', 'type' => 'url', 'url' => '/fa']);
        Link::create(['link_group_id' => $group->id, 'parent_id' => $parent->id, 'label' => 'تماس با ما', 'type' => 'page', 'page_id' => $page->id]);

        $this->get('/fa')->assertSee('درباره')->assertSee('تماس با ما')->assertSee('class="menu depth-1"', false);
    }

    public function test_iframe_is_allowed_for_the_tour_origin_on_the_tour_page_only(): void
    {
        config(['services.tour3d.url' => 'https://tour.example.com/expo']);
        $tour = $this->makePage('fa', ['title' => 'تور مجازی']);
        PageModule::create(['page_id' => $tour->id, 'type' => 'embed', 'title' => 'تور']);
        $plain = $this->makePage('fa', ['title' => 'ساده']);

        $csp = fn ($r) => $r->headers->get('Content-Security-Policy');
        $this->assertStringContainsString('frame-src https://tour.example.com', $csp($this->get('/fa/'.rawurlencode('تور-مجازی'))));
        $this->assertStringContainsString("frame-src 'none'", $csp($this->get('/fa/'.rawurlencode('ساده'))));
        $this->get('/fa/'.rawurlencode('تور-مجازی'))->assertSee('<iframe src="https://tour.example.com/expo"', false)->assertHeader('X-Frame-Options', 'SAMEORIGIN');
    }

    public function test_sitemap_lists_only_public_records_with_alternates(): void
    {
        app()->detectEnvironment(fn () => 'production');
        $fa = $this->makePage('fa', ['title' => 'درباره', 'slug' => 'about-fa', 'translation_key' => 'about']);
        $this->makePage('en', ['title' => 'About', 'slug' => 'about', 'translation_key' => 'about']);
        $this->makePage('fa', ['title' => 'مخفی', 'status' => PublishStatus::Draft]);
        $this->makePage('fa', ['title' => 'بدون ایندکس', 'noindex' => true]);

        $this->get('/sitemap.xml')->assertOk()
            ->assertSee('<loc>http://localhost/fa/about-fa</loc>', false)
            ->assertSee('hreflang="en" href="http://localhost/en/about"', false)
            ->assertDontSee(rawurlencode('مخفی'))->assertDontSee(rawurlencode('بدون-ایندکس'));
    }
}
