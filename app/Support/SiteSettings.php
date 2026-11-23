<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/** Per-language site texts (footer, site name, …) as key => value. Plain text only; always escaped on output. */
final class SiteSettings
{
    /** Bump the version (v2 -> v3) whenever settings are changed directly in the DB. */
    public const CACHE_PREFIX = 'settings.v2.';

    public const KEYS = [
        'site_name' => 'نام سایت',
        'tagline' => 'شعار (زیر نام سایت در هدر)',
        'brand_latin' => 'نام لاتین برند (فوتر، مثلاً Benis Fairs)',
        'brand_caption' => 'زیرنویس لوگو (انگلیسی، مثلاً INTERNATIONAL EXHIBITIONS)',
        'meta_description' => 'توضیح سئوی پیش‌فرض',
        'footer_about' => 'متن فوتر',
        'footer_email' => 'ایمیل',
        'copyright' => 'متن کپی‌رایت',
    ];

    /** @return array<string, string> */
    public static function for(int $languageId): array
    {
        $cacheKey = self::CACHE_PREFIX.$languageId;

        $values = Cache::get($cacheKey);
        if (is_array($values)) {
            return $values;
        }

        $values = Setting::where('language_id', $languageId)->pluck('value', 'key')->all();

        // Never cache an empty result, otherwise a not-yet-seeded table gets stuck forever.
        if ($values !== []) {
            Cache::forever($cacheKey, $values);
        }

        return $values;
    }

    public static function save(int $languageId, array $values): void
    {
        foreach (array_keys(self::KEYS) as $key) {
            $value = trim((string) ($values[$key] ?? ''));

            $value === ''
                ? Setting::where(['language_id' => $languageId, 'key' => $key])->get()->each->delete()
                : Setting::updateOrCreate(['language_id' => $languageId, 'key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_PREFIX.$languageId);
    }
}