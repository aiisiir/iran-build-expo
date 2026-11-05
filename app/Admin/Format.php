<?php

namespace App\Admin;

use App\Support\Jalali;
use BackedEnum;
use DateTimeInterface;
use Illuminate\Support\Str;

/** Renders model values for admin tables (dates are shown in Jalali, stored in Gregorian). */
final class Format
{
    public static function cell(mixed $value): string
    {
        return match (true) {
            $value === null || $value === '' => '—',
            is_bool($value) => $value ? '✓' : '—',
            $value instanceof BackedEnum => (string) $value->value,
            $value instanceof DateTimeInterface => Jalali::format($value, 'Y/m/d').' '.$value->format('H:i'),
            default => Str::limit(strip_tags((string) $value), 60),
        };
    }
}
