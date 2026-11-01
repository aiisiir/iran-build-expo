<?php

namespace Tests\Concerns;

use App\Enums\PublishStatus;
use App\Models\Category;
use App\Models\Language;
use App\Models\Page;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\LanguageSeeder;

trait BuildsSite
{
    protected function seedLanguages(): void
    {
        $this->seed(LanguageSeeder::class);
    }

    protected function lang(string $code): Language
    {
        return Language::where('code', $code)->firstOrFail();
    }

    protected function makePage(string $code = 'fa', array $overrides = []): Page
    {
        return Page::create($overrides + [
            'language_id' => $this->lang($code)->id,
            'title' => 'درباره ما',
            'status' => PublishStatus::Published,
        ]);
    }

    protected function makePost(Page $page, array $overrides = []): Post
    {
        $category = Category::firstOrCreate(
            ['language_id' => $page->language_id, 'type' => 'post', 'slug' => 'news'],
            ['name' => 'اخبار'],
        );

        return Post::create($overrides + [
            'language_id' => $page->language_id,
            'page_id' => $page->id,
            'category_id' => $category->id,
            'title' => 'خبر آزمایشی',
            'status' => PublishStatus::Published,
        ]);
    }

    protected function admin(array $overrides = []): User
    {
        return User::create($overrides + ['name' => 'Admin', 'email' => 'admin@test.test', 'password' => 'secret1', 'role' => 'admin']);
    }

    protected function editor(): User
    {
        return User::create(['name' => 'Editor', 'email' => 'editor@test.test', 'password' => 'secret1', 'role' => 'editor']);
    }
}
