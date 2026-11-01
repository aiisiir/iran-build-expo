<?php

namespace App\Support;

use App\Models\Language;
use App\Models\MediaAsset;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/** Builds the array consumed by components/seo/meta.blade.php. Structured data only describes what the page shows. */
final class Seo
{
    /**
     * @param  Collection<int, array{language: Language, path: string}>  $versions  every public language version incl. this one
     * @param  list<array{name: string, path: string}>  $breadcrumbs
     */
    public static function make(Language $language, string $title, ?string $description, string $path, Collection $versions, string $type = 'website', bool $noindex = false, ?MediaAsset $image = null, array $jsonld = [], array $breadcrumbs = []): array
    {
        $alternates = $versions->count() > 1
            ? $versions->mapWithKeys(fn ($v) => [$v['language']->htmlLang() => PublicUrl::absolute($v['path'])])->all()
            : [];
        $default = Language::default();
        $defaultVersion = $versions->first(fn ($v) => $v['language']->code === $default?->code);

        $graph = $jsonld ? [$jsonld] : [];
        if (count($breadcrumbs) > 1) {
            $graph[] = [
                '@type' => 'BreadcrumbList',
                'itemListElement' => collect($breadcrumbs)->values()->map(fn ($c, $i) => [
                    '@type' => 'ListItem', 'position' => $i + 1, 'name' => $c['name'], 'item' => PublicUrl::absolute($c['path']),
                ])->all(),
            ];
        }

        return [
            'title' => $title,
            'description' => $description ? Str::limit(trim(preg_replace('/\s+/u', ' ', strip_tags($description))), 160, '…') : '',
            'canonical' => PublicUrl::absolute($path),
            'alternates' => $alternates,
            'x_default' => $alternates && $defaultVersion ? PublicUrl::absolute($defaultVersion['path']) : null,
            'robots' => app()->environment('production') && ! $noindex ? 'index,follow' : 'noindex,nofollow',
            'type' => $type,
            'image' => $image ? $image->url() : null,
            'jsonld' => $graph ? ['@context' => 'https://schema.org', '@graph' => $graph] : null,
        ];
    }
}
