<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicHomeSeoTest extends TestCase
{
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
}
