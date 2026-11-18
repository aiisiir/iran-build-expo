<?php

namespace App\Http\Controllers\Public;

use App\Models\Company;
use App\Models\Page;
use App\Models\Post;
use App\Support\PublicRenderer;
use Illuminate\Contracts\View\View;

/** /{locale}/{page}/{slug}: a news item or a company that belongs to that page. */
class ItemController
{
    public function __construct(private readonly PublicRenderer $renderer) {}

    public function show(string $locale, string $page, string $item): View
    {
        $language = app('site.language');
        $parent = Page::published()->where('language_id', $language->id)->where('slug', $page)->firstOrFail();

        // A record is also reachable under the extra pages it is placed in; its canonical URL stays the main page's.
        $post = Post::live()->where('language_id', $language->id)->where('slug', $item)->onPage($parent->id)
            ->orderByRaw('posts.page_id = ? desc', [$parent->id])->first();

        if ($post) {
            // Bots would inflate the counter; real readers are already bounded by the 120/min limit.
            if (! preg_match('/bot|crawl|spider|slurp|preview/i', (string) request()->userAgent())) {
                Post::whereKey($post->id)->increment('view_count');
            }

            return $this->renderer->post($post);
        }

        $company = Company::where('language_id', $language->id)->where('slug', $item)->onPage($parent->id)->where('is_active', true)
            ->orderByRaw('companies.page_id = ? desc', [$parent->id])->firstOrFail();

        return $this->renderer->company($company);
    }
}
