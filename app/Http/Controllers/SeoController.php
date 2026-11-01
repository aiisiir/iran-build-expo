<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Language;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Support\PublicUrl;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class SeoController
{
    public function robots(): Response
    {
        if (! app()->environment('production')) {
            return response("User-agent: *\nDisallow: /\n", 200, [
                'Content-Type' => 'text/plain; charset=UTF-8',
                'X-Robots-Tag' => 'noindex, nofollow, noarchive',
            ]);
        }

        $content = implode("\n", ['User-agent: *', 'Disallow: /admin', 'Sitemap: '.rtrim((string) config('app.url'), '/').'/sitemap.xml', '']);

        return response($content, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    /** Only real, currently public records; language alternates come from the shared translation_key. */
    public function sitemap(): Response
    {
        if (! app()->environment('production')) {
            return response('', 404)->header('X-Robots-Tag', 'noindex, nofollow');
        }

        $languages = Language::active();
        $default = Language::default()?->code;

        $records = collect()
            ->merge(Page::published()->where('noindex', false)->with('language')->get())
            ->merge(Post::live()->with(['language', 'page'])->get())
            ->merge(Company::where('is_active', true)->with(['language', 'page'])->get())
            ->filter(fn ($r) => $languages->has($r->language->code));

        $entries = $records->map(function ($record) use ($records, $default) {
            $group = $record->translation_key
                ? $records->filter(fn ($r) => $r::class === $record::class && $r->translation_key === $record->translation_key)
                : collect([$record]);
            $alternates = $group->count() > 1
                ? $group->mapWithKeys(fn ($r) => [$r->language->htmlLang() => PublicUrl::absolute($r->path())])->all()
                : [];

            if ($alternates && ($original = $group->first(fn ($r) => $r->language->code === $default))) {
                $alternates['x-default'] = PublicUrl::absolute($original->path());
            }

            return ['loc' => PublicUrl::absolute($record->path()), 'lastmod' => $record->updated_at?->toAtomString(), 'alternates' => $alternates];
        })->merge($this->products($languages));

        return response()->view('seo.sitemap', ['entries' => $entries], 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    private function products(Collection $languages): Collection
    {
        return Product::where('is_active', true)->with('company.page', 'company.language')
            ->whereHas('company', fn ($q) => $q->where('is_active', true))->get()
            ->filter(fn ($p) => $languages->has($p->company->language->code))
            ->map(fn ($p) => ['loc' => PublicUrl::absolute($p->path()), 'lastmod' => $p->updated_at?->toAtomString(), 'alternates' => []]);
    }
}
