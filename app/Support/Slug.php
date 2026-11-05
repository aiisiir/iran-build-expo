<?php

namespace App\Support;

/** URL slugs keep Persian letters (UTF-8 URLs are valid and indexable); they are never transliterated. */
final class Slug
{
    public const RESERVED = ['badge', 'admin', 'api', 'up'];

    public static function make(string $text, int $max = 160): string
    {
        $text = Digits::toLatin($text);
        $text = strtr($text, ['ي' => 'ی', 'ك' => 'ک', "\u{200C}" => ' ', "\u{200D}" => ' ']);
        $text = (string) preg_replace('/\p{Mn}+/u', '', $text); // diacritics
        $text = mb_strtolower($text);
        $text = trim((string) preg_replace('/[^\p{L}\p{N}]+/u', '-', $text), '-');
        $text = trim(mb_substr($text, 0, $max), '-');

        return $text === '' ? 'item' : $text;
    }
}
