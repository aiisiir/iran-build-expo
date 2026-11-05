<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsSite;
use Tests\TestCase;

class DemoContentSeederTest extends TestCase
{
    use BuildsSite, RefreshDatabase;

    public function test_demo_content_fills_every_home_section_in_both_languages(): void
    {
        $this->seedLanguages();
        $this->seed(DemoContentSeeder::class);

        foreach (['fa' => 'آخرین اخبار', 'en' => 'Latest news'] as $code => $latest) {
            $html = $this->get("/{$code}")->assertOk()->assertSee($latest)->getContent();

            $this->assertSame(4, substr_count($html, 'aria-roledescription="slide"'), "hero slides ({$code})");
            foreach (['class="pillars"', 'class="cards', 'class="link-list', 'class="companies"', 'class="gallery"', 'class="embed"', 'class="faq"', 'data-atmosphere', 'site-footer', 'id="primary-nav"'] as $marker) {
                $this->assertStringContainsString($marker, $html, "{$marker} missing ({$code})");
            }
            $this->assertStringContainsString("/{$code}/badge", $html);
        }

        foreach (['اخبار', 'شرکت-ها', 'درباره-ما', 'تماس-با-ما'] as $slug) {
            $this->get('/fa/'.rawurlencode($slug))->assertOk();
        }
        $post = \App\Models\Post::where('language_id', $this->lang('fa')->id)->firstOrFail();
        $this->get(\App\Support\PublicUrl::path($post->path()))->assertOk();
    }

    public function test_it_never_touches_a_database_that_already_has_pages(): void
    {
        $this->seedLanguages();
        $this->makePage('fa', ['title' => 'موجود']);

        $this->seed(DemoContentSeeder::class);

        $this->assertSame(1, Page::count());
    }
}
