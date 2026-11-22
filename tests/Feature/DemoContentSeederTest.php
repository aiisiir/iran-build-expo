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

            $this->assertSame(1, substr_count($html, 'aria-roledescription="slide"'), "hero slides ({$code})");
            foreach (['class="hero"', 'quick-actions', 'about-panel', 'resources-section', 'news-section', 'registration-section', 'gallery-section', 'site-footer', 'id="primary-nav"'] as $marker) {
                $this->assertStringContainsString($marker, $html, "{$marker} missing ({$code})");
            }
            $this->assertStringContainsString("/{$code}/badge", $html);
            $this->assertStringContainsString('class="brand-logo"', $html); // logo is a panel record
            $this->assertStringNotContainsString('Iran Build Expo', $html);
            $lang = $this->lang($code);
            $this->assertSame(4, \App\Models\Post::where('language_id', $lang->id)->whereHas('category', fn ($q) => $q->where('name', $this->news($code)))->count());
            $this->assertSame(6, \App\Models\Link::whereHas('group', fn ($q) => $q->where('language_id', $lang->id)->where('placement', 'supporters'))->whereNotNull('icon_media_id')->count());
            $this->assertSame(4, \App\Models\Gallery::where('language_id', $lang->id)->has('items', '=', 10)->count());
        }

        foreach (['اخبار', 'شرکت-ها', 'درباره-ما', 'تماس-با-ما'] as $slug) {
            $this->get('/fa/'.rawurlencode($slug))->assertOk();
        }
        $post = \App\Models\Post::where('language_id', $this->lang('fa')->id)->firstOrFail();
        $this->get(\App\Support\PublicUrl::path($post->path()))->assertOk();
    }

    private function news(string $code): string
    {
        return $code === 'fa' ? 'اخبار' : 'News';
    }

    public function test_it_never_touches_a_database_that_already_has_pages(): void
    {
        $this->seedLanguages();
        $this->makePage('fa', ['title' => 'موجود']);

        $this->seed(DemoContentSeeder::class);

        $this->assertSame(1, Page::count());
    }
}
