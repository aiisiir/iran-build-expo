<?php

namespace App\Http\Controllers\Public;

use App\Models\Gallery;
use App\Support\PublicUrl;
use App\Support\Seo;
use App\Support\SiteSettings;
use Illuminate\Contracts\View\View;

/** A gallery's own page: every image, each opening in the modal viewer (site.js). */
class GalleryController
{
    public function show(string $locale, int $id): View
    {
        $language = app('site.language');
        $gallery = Gallery::where('is_active', true)->where('language_id', $language->id)->with(['language', 'items.media'])->findOrFail($id);
        $items = $gallery->items->filter(fn ($i) => $i->media)->values();
        $path = $gallery->path();
        $home = ['name' => SiteSettings::for($language->id)['site_name'] ?? config('app.name'), 'path' => "/{$language->code}"];
        $breadcrumbs = [$home, ['name' => $gallery->title, 'path' => $path]];

        return view('public.gallery', [
            'gallery' => $gallery,
            'items' => $items,
            'breadcrumbs' => $breadcrumbs,
            'translations' => [],
            'seo' => Seo::make($language, $gallery->title.' | '.$home['name'], $gallery->description, $path, collect([['language' => $language, 'path' => $path]]), 'website', false, $items->first()?->media,
                ['@type' => 'ImageGallery', 'name' => $gallery->title, 'url' => PublicUrl::absolute($path), 'inLanguage' => $language->htmlLang()], $breadcrumbs),
        ]);
    }
}
