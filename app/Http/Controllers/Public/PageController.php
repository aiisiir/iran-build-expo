<?php

namespace App\Http\Controllers\Public;

use App\Models\Language;
use App\Models\Page;
use App\Support\PublicRenderer;
use App\Support\PublicUrl;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class PageController
{
    public function __construct(private readonly PublicRenderer $renderer) {}

    public function home(string $locale): View
    {
        $page = Page::published()->where('language_id', app('site.language')->id)->where('is_home', 1)->first();

        return $page ? $this->renderer->page($page) : view('pages.home', ['seo' => $this->fallbackSeo($locale)]);
    }

    public function show(string $locale, string $page): View|RedirectResponse
    {
        $record = Page::published()->where('language_id', app('site.language')->id)->where('slug', $page)->firstOrFail();

        // The home page has exactly one URL: /{locale}.
        return $record->is_home ? redirect(route('public.home', ['locale' => $locale]), 301) : $this->renderer->page($record);
    }

    /** Shown until a home page exists in this language (fresh install). */
    private function fallbackSeo(string $locale): array
    {
        $languages = Language::active();
        $alternates = $languages->mapWithKeys(fn ($l) => [$l->htmlLang() => PublicUrl::absolute("/{$l->code}")])->all();

        return [
            'title' => __('seo.home_title'),
            'description' => __('seo.home_description'),
            'canonical' => PublicUrl::absolute("/{$locale}"),
            'alternates' => $alternates,
            'x_default' => PublicUrl::absolute('/'.(Language::default()?->code ?? $locale)),
            'robots' => app()->environment('production') ? 'index,follow' : 'noindex,nofollow',
            'type' => 'website',
            'jsonld' => ['@context' => 'https://schema.org', '@type' => 'WebSite', 'name' => config('app.name'), 'url' => PublicUrl::absolute("/{$locale}"), 'inLanguage' => $languages->get($locale)->htmlLang()],
        ];
    }
}
