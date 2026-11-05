<?php

namespace App\Admin;

use App\Admin\Resources as R;

final class Registry
{
    /** Sidebar groups in display order. */
    private const GROUPS = [
        'محتوا' => [R\PageResource::class, R\PageModuleResource::class, R\PostResource::class, R\CategoryResource::class, R\FaqResource::class],
        'لینک‌ها و منو' => [R\LinkGroupResource::class, R\LinkResource::class],
        'گالری' => [R\GalleryResource::class, R\GalleryItemResource::class],
        'شرکت‌ها' => [R\CompanyResource::class, R\ProductResource::class, R\ProductImageResource::class],
        'مدیریت' => [R\LanguageResource::class, R\EditionResource::class, R\UserResource::class, R\BadgeTypeResource::class, R\AuditEventResource::class],
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
