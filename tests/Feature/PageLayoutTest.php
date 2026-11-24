<?php

namespace Tests\Feature;

use App\Models\Page;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsSite;
use Tests\TestCase;

class PageLayoutTest extends TestCase
{
    use BuildsSite, RefreshDatabase;

    public function test_layout_editor_saves_order_switches_titles_and_item_count(): void
    {
        $this->seedLanguages();
        $this->seed(DemoContentSeeder::class);
        $this->actingAs($this->admin());
        $home = Page::where('is_home', 1)->firstOrFail();
        $modules = $home->modules()->get();
        $posts = $modules->first(fn ($m) => $m->type->value === 'posts');
        $first = $modules->first();

        $this->get("/admin/pages/{$home->id}/layout")->assertOk()->assertSee('رکوردها')->assertSee('/admin/r/');

        // Reverse the order, switch the first block off, hide the posts block's title and set its count to 3.
        $ids = $modules->pluck('id')->reverse()->values()->all();
        $this->put("/admin/pages/{$home->id}/layout", [
            'order' => $ids,
            'active' => $modules->where('id', '!=', $first->id)->mapWithKeys(fn ($m) => [$m->id => '1'])->all(),
            'titles' => $modules->where('id', '!=', $posts->id)->mapWithKeys(fn ($m) => [$m->id => '1'])->all(),
            'limit' => [$posts->id => 3],
        ])->assertRedirect("/admin/pages/{$home->id}/layout");

        $this->assertSame($ids, $home->modules()->pluck('id')->all());
        $this->assertFalse($first->fresh()->is_active);
        $this->assertFalse($posts->fresh()->show_title);
        $this->assertSame(3, $posts->fresh()->item_limit);
    }

    public function test_blocks_of_other_pages_are_ignored_and_bad_counts_rejected(): void
    {
        $this->seedLanguages();
        $this->seed(DemoContentSeeder::class);
        $this->actingAs($this->admin());
        $home = Page::where('is_home', 1)->firstOrFail();
        $foreign = Page::where('is_home', null)->has('modules')->first()?->modules()->first();
        $own = $home->modules()->first();

        if ($foreign) {
            $before = $foreign->sort_order;
            $this->put("/admin/pages/{$home->id}/layout", ['order' => [$foreign->id, $own->id]])->assertRedirect();
            $this->assertSame($before, $foreign->fresh()->sort_order);
        }

        $this->put("/admin/pages/{$home->id}/layout", ['order' => [$own->id], 'limit' => [$own->id => 500]])->assertSessionHasErrors('limit.*');
    }
}
