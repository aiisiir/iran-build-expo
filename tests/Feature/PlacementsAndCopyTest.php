<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Page;
use App\Models\PageModule;
use App\Models\Post;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsSite;
use Tests\TestCase;

class PlacementsAndCopyTest extends TestCase
{
    use BuildsSite, RefreshDatabase;

    private function demoPost(): array
    {
        $this->seedLanguages();
        $this->seed(DemoContentSeeder::class);
        $post = Post::live()->with('page', 'language')->firstOrFail();
        $other = Page::published()->where('language_id', $post->language_id)->whereKeyNot($post->page_id)->where('is_home', null)->firstOrFail();

        return [$post, $other];
    }

    public function test_record_is_reachable_under_extra_page_with_canonical_to_main_page(): void
    {
        [$post, $other] = $this->demoPost();
        $post->placedPages()->attach($other->id);

        $main = url($post->path());
        $this->get($post->path())->assertOk();
        $this->get("/{$post->language->code}/{$other->slug}/{$post->slug}")->assertOk()
            ->assertSee('<link rel="canonical" href="'.\App\Support\PublicUrl::absolute($post->path()).'">', false);
        $this->assertStringContainsString($post->page->slug, $main);
    }

    public function test_modules_show_records_placed_on_their_page_and_in_extra_categories(): void
    {
        [$post, $other] = $this->demoPost();
        $post->placedPages()->attach($other->id);
        $module = PageModule::create(['page_id' => $other->id, 'type' => 'posts', 'title' => 'x', 'display' => 'list', 'item_limit' => 50]);

        $this->get("/{$post->language->code}/{$other->slug}")->assertOk()->assertSee($post->title);

        $category = \App\Models\Category::where('language_id', $post->language_id)->whereKeyNot($post->category_id)->where('type', 'post')->firstOrFail();
        $post->extraCategories()->attach($category->id);
        $module->update(['category_id' => $category->id]);
        $this->get("/{$post->language->code}/{$other->slug}")->assertOk()->assertSee($post->title);
    }

    public function test_extra_pages_must_match_the_record_language(): void
    {
        [$post] = $this->demoPost();
        $en = Language::where('code', 'en')->firstOrFail();
        $foreign = Page::where('language_id', $en->id)->firstOrFail();
        $this->actingAs($this->admin());

        $this->put("/admin/r/posts/{$post->id}", [
            'language_id' => $post->language_id, 'page_id' => $post->page_id, 'category_id' => $post->category_id, 'title' => $post->title,
            'slug' => $post->slug, 'status' => 'published', 'placed_pages' => [$foreign->id],
        ])->assertSessionHasErrors('placed_pages');
    }

    public function test_copy_duplicates_record_with_extra_pages_as_draft(): void
    {
        [$post, $other] = $this->demoPost();
        $post->placedPages()->attach($other->id);
        $this->actingAs($this->admin());

        $response = $this->post("/admin/r/posts/{$post->id}/replicate");
        $copy = Post::latest('id')->firstOrFail();
        $response->assertRedirect("/admin/r/posts/{$copy->id}/edit");

        $this->assertNotSame($post->id, $copy->id);
        $this->assertSame($post->title.' (کپی)', $copy->title);
        $this->assertNotSame($post->slug, $copy->slug);
        $this->assertSame('draft', $copy->status->value);
        $this->assertSame([$other->id], $copy->placedPages()->pluck('pages.id')->all());
    }

    public function test_copying_a_page_copies_its_blocks_and_unique_records_cannot_be_copied(): void
    {
        $this->seedLanguages();
        $this->seed(DemoContentSeeder::class);
        $this->actingAs($this->admin());
        $home = Page::where('is_home', 1)->firstOrFail();

        $this->post("/admin/r/pages/{$home->id}/replicate")->assertRedirect();
        $copy = Page::latest('id')->firstOrFail();
        $this->assertNull($copy->is_home);
        $this->assertSame($home->modules()->count(), $copy->modules()->count());

        $this->post('/admin/r/languages/1/replicate')->assertNotFound();
    }

    public function test_forms_are_split_into_tabs(): void
    {
        [$post] = $this->demoPost();
        $this->actingAs($this->admin());

        $this->get("/admin/r/posts/{$post->id}/edit")->assertOk()
            ->assertSee('role="tablist"', false)->assertSee('سئو')->assertSee('تصاویر و فایل‌ها')->assertSee('دسته‌بندی، صفحه و زبان');
    }
}
