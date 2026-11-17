<?php

namespace Tests\Feature;

use App\Admin\Registry;
use App\Models\User;
use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsSite;
use Tests\TestCase;

/** With real rows in every table, no admin list, create or edit page may fail (empty tables hide missing relations). */
class AdminPagesSmokeTest extends TestCase
{
    use BuildsSite, RefreshDatabase;

    public function test_every_admin_page_renders_with_data(): void
    {
        $this->seedLanguages();
        $this->seed(DemoContentSeeder::class);
        $this->actingAs($this->admin());

        foreach (['/admin', '/admin/media', '/admin/badges', '/admin/settings?language_id=1'] as $url) {
            $this->get($url)->assertOk();
        }

        foreach (Registry::groups() as $resources) {
            foreach ($resources as $resource) {
                $base = '/admin/r/'.$resource->key();
                $this->get($base)->assertOk();
                if ($resource->readOnly) {
                    continue; // the audit log has no create/edit pages
                }
                $this->get("$base/create")->assertOk();

                $model = $resource->model()::query()->first();
                if ($model) {
                    $this->get("$base/{$model->getKey()}/edit")->assertOk();
                }
            }
        }
    }

    public function test_language_defaults_audit_names_and_settings_tabs(): void
    {
        $this->seedLanguages();
        $this->actingAs($admin = $this->admin());
        $default = \App\Models\Language::default();

        $zh = \App\Models\Language::create(['code' => 'zh', 'name' => 'Chinese', 'native_name' => '中文', 'direction' => 'ltr', 'is_active' => true]);
        $this->assertSame((int) \App\Models\Language::max('sort_order'), (int) $zh->sort_order, 'a new language goes to the end');

        $this->get('/admin/r/faqs/create')->assertOk()->assertSee('value="'.$default->id.'" selected', false);

        $this->get('/admin')->assertOk()->assertSee($admin->name);
        $this->get('/admin/r/audit')->assertOk()->assertSee($admin->name);

        $this->get('/admin/settings?lang=zh')->assertOk()->assertSee('name="settings['.$zh->id.'][', false)->assertDontSee('name="settings['.$default->id.'][', false);
        $this->put('/admin/settings', ['language_id' => $zh->id, 'settings' => [$zh->id => ['site_name' => 'نمونه']]])->assertRedirect();
        $this->assertSame('نمونه', \App\Support\SiteSettings::for($zh->id)['site_name']);
    }
}
