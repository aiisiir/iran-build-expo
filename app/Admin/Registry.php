<?php

namespace App\Admin;

use App\Admin\Resources as R;

final class Registry
{
    /** Sidebar groups in display order: page structure and modules, then the data they show, then the system. */
    private const GROUPS = [
        'ساختار سایت و ماژول‌ها' => [R\PageResource::class, R\PageModuleResource::class, R\LinkGroupResource::class, R\LinkResource::class],
        'مدیریت داده‌ها' => [R\PostResource::class, R\CategoryResource::class, R\FaqResource::class, R\GalleryResource::class, R\GalleryItemResource::class, R\CompanyResource::class, R\ProductResource::class, R\ProductImageResource::class],
        'سیستم' => [R\LanguageResource::class, R\EditionResource::class, R\UserResource::class, R\BadgeTypeResource::class, R\AuditEventResource::class, R\CustomSnippetResource::class],
    ];

    /** @return array<string, list<Resource>> */
    public static function groups(): array
    {
        return array_map(fn (array $classes) => array_map(fn (string $c) => new $c, $classes), self::GROUPS);
    }

    public static function find(string $key): ?Resource
    {
        foreach (self::groups() as $resources) {
            foreach ($resources as $resource) {
                if ($resource->key() === $key) {
                    return $resource;
                }
            }
        }

        return null;
    }
}
