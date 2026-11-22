<?php

namespace Tests\Feature;

use App\Models\CustomSnippet;
use App\Models\Page;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsSite;
use Tests\TestCase;

class CustomCodeTest extends TestCase
{
    use BuildsSite, RefreshDatabase;

    public function test_only_superadmin_can_see_or_edit_custom_code(): void
    {
        $this->seedLanguages();
        $this->actingAs($this->admin());
        $this->get('/admin/r/custom-code')->assertForbidden();
        $this->post('/admin/r/custom-code', ['title' => 'x', 'type' => 'js', 'code' => 'alert(1)'])->assertForbidden();
        $this->get('/admin')->assertDontSee('استایل و اسکریپت اختصاصی');
    }

    public function test_superadmin_manages_custom_code(): void
    {
        $this->seedLanguages();
        $this->actingAs($this->superAdmin());
        $this->get('/admin')->assertSee('استایل و اسکریپت اختصاصی');
        $this->post('/admin/r/custom-code', ['title' => 'x', 'type' => 'js', 'code' => 'window.x=1', 'is_active' => '1'])->assertRedirect();
        $this->assertSame(1, CustomSnippet::count());
    }

    public function test_active_snippets_are_served_as_files_linked_with_a_version_and_inactive_ones_vanish(): void
    {
        $this->seedLanguages();
        $this->seed(DemoContentSeeder::class);
        CustomSnippet::create(['title' => 'Brand', 'type' => 'css', 'code' => 'body{--x:1}']);
        $js = CustomSnippet::create(['title' => 'Hello', 'type' => 'js', 'code' => 'window.hello=1']);

        $this->get('/assets/custom.css')->assertOk()->assertHeader('Content-Type', 'text/css; charset=utf-8')->assertSee('--x:1', false);
        $this->get('/assets/custom.js')->assertOk()->assertSee('window.hello=1', false);
        $this->get('/fa')->assertOk()->assertSee('/assets/custom.js?v=', false)->assertSee('/assets/custom.css?v=', false);
        $this->get('/fa')->assertHeader('Content-Security-Policy'); // still strict: files, no inline code

        $js->update(['is_active' => false]);
        $this->get('/assets/custom.js')->assertNotFound();
        $this->get('/fa')->assertDontSee('/assets/custom.js', false);
    }

    public function test_the_panel_never_grants_changes_or_deletes_a_superadmin(): void
    {
        $this->seedLanguages();
        $editor = $this->editor();
        $super = $this->superAdmin();
        $admin = $this->admin();

        foreach ([$admin, $super] as $actor) {
            $this->actingAs($actor);
            $this->put("/admin/r/users/{$editor->id}", ['name' => 'E', 'email' => $editor->email, 'role' => 'superadmin', 'is_active' => '1'])->assertSessionHasErrors('role');
            $this->put("/admin/r/users/{$super->id}", ['name' => 'S', 'email' => $super->email, 'role' => 'editor', 'is_active' => '1'])->assertSessionHasErrors('role');
        }

        $this->actingAs($admin);
        $this->delete("/admin/r/users/{$super->id}")->assertForbidden();
        $this->assertSame('editor', $editor->fresh()->role->value);
        $this->assertSame('superadmin', $super->fresh()->role->value);
        $this->assertNull($super->fresh()->deleted_at);
    }

    public function test_block_class_and_id_are_rendered_and_validated(): void
    {
        $this->seedLanguages();
        $this->seed(DemoContentSeeder::class);
        $module = Page::where('is_home', 1)->firstOrFail()->modules()->where('type', 'faq')->firstOrFail();
        $module->update(['css_class' => 'my-hero dark', 'css_id' => 'top-banner']);

        $this->get('/fa')->assertSee('id="top-banner"', false)->assertSee('my-hero dark', false);

        $this->actingAs($this->admin());
        $this->put("/admin/r/page-modules/{$module->id}", [
            'page_id' => $module->page_id, 'type' => $module->type->value, 'display' => $module->display->value, 'css_id' => '1 bad"id', 'css_class' => 'a"><script>',
        ])->assertSessionHasErrors(['css_id', 'css_class']);
    }
}
