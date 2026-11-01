<?php

namespace Tests\Feature;

use App\Models\AuditEvent;
use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\BuildsSite;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use BuildsSite, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedLanguages();
    }

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/r/posts')->assertRedirect('/admin/login');
    }

    public function test_login_logout_and_password_is_stored_salted(): void
    {
        $user = $this->admin(['password' => 'abc123']);
        $this->assertTrue(Hash::check('abc123', $user->password));
        $this->assertNotSame('abc123', $user->password);
        $this->assertNotSame(User::create(['name' => 'B', 'email' => 'b@test.test', 'password' => 'abc123', 'role' => 'editor'])->password, $user->password);

        $this->post('/admin/login', ['email' => 'admin@test.test', 'password' => 'abc123'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_six_character_passwords_are_accepted_when_creating_users(): void
    {
        $this->actingAs($this->admin())->post('/admin/r/users', ['name' => 'N', 'email' => 'n@test.test', 'password' => '123456', 'role' => 'editor', 'is_active' => 1])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->post('/admin/r/users', ['name' => 'M', 'email' => 'm@test.test', 'password' => '12345', 'role' => 'editor'])->assertSessionHasErrors('password');
    }

    public function test_five_wrong_passwords_lock_the_account_for_that_ip(): void
    {
        $this->admin();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => 'admin@test.test', 'password' => 'wrong'])->assertSessionHasErrors('email');
        }

        $this->post('/admin/login', ['email' => 'admin@test.test', 'password' => 'secret1'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertDatabaseHas('audit_events', ['event' => 'login_failed']);
    }

    public function test_inactive_users_cannot_log_in(): void
    {
        $this->admin(['is_active' => false]);

        $this->post('/admin/login', ['email' => 'admin@test.test', 'password' => 'secret1'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_editors_cannot_open_admin_only_areas(): void
    {
        $this->actingAs($this->editor());

        foreach (['/admin/r/users', '/admin/r/languages', '/admin/r/audit', '/admin/r/editions', '/admin/settings'] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->get('/admin/r/pages')->assertOk();
        $this->get('/admin/media')->assertOk();
    }

    public function test_admin_pages_have_a_csp_that_allows_only_same_origin_scripts(): void
    {
        $csp = $this->actingAs($this->admin())->get('/admin')->assertOk()->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("script-src 'self'", $csp);
        $this->assertStringContainsString("style-src 'self' 'unsafe-inline'", $csp);
    }

    public function test_editor_creates_a_page_and_a_post_through_the_generic_form(): void
    {
        $this->actingAs($this->editor());
        $fa = $this->lang('fa');

        $this->post('/admin/r/pages', ['language_id' => $fa->id, 'title' => 'اخبار', 'status' => 'published', 'body' => '<p>x</p><script>1</script>'])->assertRedirect()->assertSessionHasNoErrors();
        $page = Page::firstOrFail();
        $this->assertSame('اخبار', $page->slug);
        $this->assertStringNotContainsString('script', $page->body);
        $this->assertNotNull($page->created_by);

        $category = Category::create(['language_id' => $fa->id, 'type' => 'post', 'name' => 'اخبار']);
        $this->post('/admin/r/posts', ['language_id' => $fa->id, 'page_id' => $page->id, 'category_id' => $category->id, 'title' => 'خبر یک', 'status' => 'published'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(1, Post::count());
    }

    public function test_a_post_cannot_mix_languages(): void
    {
        $this->actingAs($this->editor());
        $enPage = $this->makePage('en', ['title' => 'News']);
        $faCategory = Category::create(['language_id' => $this->lang('fa')->id, 'type' => 'post', 'name' => 'اخبار']);

        $this->post('/admin/r/posts', ['language_id' => $this->lang('fa')->id, 'page_id' => $enPage->id, 'category_id' => $faCategory->id, 'title' => 'x', 'status' => 'draft'])
            ->assertSessionHasErrors('page_id');
    }

    public function test_javascript_urls_are_rejected_in_links(): void
    {
        $this->actingAs($this->editor());
        $fa = $this->lang('fa');
        $page = $this->makePage('fa');
        $category = Category::create(['language_id' => $fa->id, 'type' => 'post', 'name' => 'اخبار']);
        $base = ['language_id' => $fa->id, 'page_id' => $page->id, 'category_id' => $category->id, 'title' => 'x', 'status' => 'draft'];

        $this->post('/admin/r/posts', $base + ['link_url' => 'javascript:alert(1)'])->assertSessionHasErrors('link_url');
        $this->post('/admin/r/posts', $base + ['link_url' => '//evil.test/x'])->assertSessionHasErrors('link_url');
        $this->post('/admin/r/posts', $base + ['link_url' => 'https://example.com/x'])->assertSessionHasNoErrors();
    }

    public function test_deleting_is_a_soft_delete_and_is_audited(): void
    {
        $this->actingAs($this->admin());
        $page = $this->makePage('fa', ['title' => 'حذف شدنی']);

        $this->delete("/admin/r/pages/{$page->id}")->assertRedirect();

        $this->assertSoftDeleted('pages', ['id' => $page->id]);
        $this->assertTrue(AuditEvent::where('event', 'deleted')->where('subject_type', 'Page')->exists());
    }

    public function test_a_user_cannot_delete_their_own_account(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->delete("/admin/r/users/{$admin->id}")->assertForbidden();
    }

    public function test_languages_cannot_be_deleted(): void
    {
        $this->actingAs($this->admin())->delete('/admin/r/languages/'.$this->lang('en')->id)->assertForbidden();
    }
}
