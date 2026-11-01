<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;
use Illuminate\Support\Facades\Cache;

/**
 * Single allow-list sanitizer for every rich-text field. Used when saving (admin input) and
 * when rendering (public output). iframes, scripts, forms, inline event handlers and
 * javascript:/data: URLs are never allowed; embeds are a separate, single-source module.
 */
final class HtmlSanitizer
{
    private const ALLOWED = 'p[dir|style],br,strong,b,em,i,u,s,sub,sup,span[dir],ul,ol,li,h2,h3,h4,blockquote,hr,'
        .'a[href|title|target],img[src|alt|title|width|height],'
        .'table,thead,tbody,tr,th[colspan|rowspan|scope],td[colspan|rowspan]';

    private static ?HTMLPurifier $purifier = null;

    public static function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        // Output is cached by content hash, so purifying on every render costs one cache read.
        return Cache::rememberForever('purify.'.sha1($html), function () use ($html): string {
            $clean = self::purifier()->purify($html);

            // CSP forbids inline style attributes, so the only allowed style (text-align) becomes a class.
            return (string) preg_replace('/\sstyle="text-align:\s*(left|right|center|justify);?"/i', ' class="ta-$1"', $clean);
        });
    }

    private static function purifier(): HTMLPurifier
    {
        if (self::$purifier) {
            return self::$purifier;
        }

        $cache = storage_path('framework/purifier');
        is_dir($cache) || mkdir($cache, 0775, true);

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('Cache.SerializerPath', $cache);
        $config->set('HTML.Allowed', self::ALLOWED);
        $config->set('CSS.AllowedProperties', ['text-align']);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('HTML.TargetBlank', true);          // adds rel="noopener noreferrer"
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);
        $config->set('URI.DisableExternalResources', true); // no hot-linked images (privacy + CSP)
        $config->set('AutoFormat.RemoveEmpty', true);

        return self::$purifier = new HTMLPurifier($config);
    }
}
