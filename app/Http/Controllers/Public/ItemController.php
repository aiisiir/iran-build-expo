<?php

namespace App\Http\Controllers\Public;

use App\Models\Company;
use App\Models\Post;
use App\Support\PublicRenderer;
use Illuminate\Contracts\View\View;

/** /{locale}/news/{id} and /{locale}/company/{id}: the object type is the fixed segment, the id is the record. */
class ItemController
{
    public function __construct(private readonly PublicRenderer $renderer) {}

    public function post(string $locale, int $id): View
    {
        $post = Post::live()->where('language_id', app('site.language')->id)->findOrFail($id);

        // Bots would inflate the counter; real readers are already bounded by the 120/min limit.
        if (! preg_match('/bot|crawl|spider|slurp|preview/i', (string) request()->userAgent())) {
            Post::whereKey($post->id)->increment('view_count');
        }

        return $this->renderer->post($post);
    }

    public function company(string $locale, int $id): View
    {
        $company = Company::where('language_id', app('site.language')->id)->where('is_active', true)->findOrFail($id);

        return $this->renderer->company($company);
    }
}
