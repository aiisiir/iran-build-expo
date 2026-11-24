<?php

namespace Tests\Feature;

use App\Models\Gallery;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsSite;
use Tests\TestCase;

class GalleryPageTest extends TestCase
{
    use BuildsSite, RefreshDatabase;

    public function test_gallery_page_lists_images_and_module_tiles_link_to_it(): void
    {
        $this->seedLanguages();
        $this->seed(DemoContentSeeder::class);
        $gallery = Gallery::with('language', 'items')->firstOrFail();

        $this->get($gallery->path())->assertOk()->assertSee($gallery->title)->assertSee('data-lightbox', false)
            ->assertSee('<link rel="canonical" href="'.\App\Support\PublicUrl::absolute($gallery->path()).'">', false);
        $this->assertSame($gallery->items->count(), substr_count($this->get($gallery->path())->getContent(), 'data-caption='));

        // Home tiles open the lightbox on the photo itself instead of linking to the gallery page.
        $this->get('/'.$gallery->language->code)->assertSee('data-lightbox', false);
        $this->get('/fa/gallery/999999')->assertNotFound();
        $this->app['env'] = 'production';
        config(['app.indexable' => true]);
        $this->get('/sitemap.xml')->assertOk()->assertSee($gallery->path());
    }

    public function test_inactive_gallery_and_other_language_are_not_public(): void
    {
        $this->seedLanguages();
        $this->seed(DemoContentSeeder::class);
        $gallery = Gallery::with('language')->firstOrFail();
        $other = $gallery->language->code === 'fa' ? 'en' : 'fa';

        $this->get("/{$other}/gallery/{$gallery->id}")->assertNotFound();
        $gallery->update(['is_active' => false]);
        $this->get($gallery->path())->assertNotFound();
    }
}
