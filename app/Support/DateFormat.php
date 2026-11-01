<?php

namespace App\Support;

use App\Models\Language;
use DateTimeInterface;

/** Dates are stored in Gregorian and shown in the reader's calendar: Jalali for Persian, Gregorian otherwise. */
final class DateFormat
{
    public static function show(?DateTimeInterface $date, Language $language): string
    {
        if ($date === null) {
            return '';
        }

        return $language->code === 'fa'
            ? Digits::toPersian(Jalali::format($date, 'j F Y'))
            : $date->format('j F Y');
    }
}
