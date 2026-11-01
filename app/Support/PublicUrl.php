<?php

namespace App\Support;

/** Persian slugs are valid in URLs but must be percent-encoded in href, canonical and sitemap values. */
final class PublicUrl
{
    public static function path(string $path): string
    {
        return implode('/', array_map('rawurlencode', explode('/', $path)));
    }

    public static function absolute(string $path): string
    {
        return rtrim((string) config('app.url'), '/').self::path($path);
    }
}
