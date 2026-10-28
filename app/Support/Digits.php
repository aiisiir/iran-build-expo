<?php

namespace App\Support;

/** Persian digits are a display concern (server side); inputs are normalised to Latin digits. */
final class Digits
{
    private const FA = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];

    private const AR = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];

    public static function toPersian(string $value): string
    {
        return strtr($value, array_combine(range('0', '9'), self::FA));
    }

    public static function toLatin(string $value): string
    {
        return strtr($value, array_merge(
            array_combine(self::FA, range('0', '9')),
            array_combine(self::AR, range('0', '9')),
        ));
    }

    /** Locale-aware: Persian digits for "fa", untouched otherwise. */
    public static function forLocale(string $value, string $locale): string
    {
        return $locale === 'fa' ? self::toPersian($value) : $value;
    }
}
