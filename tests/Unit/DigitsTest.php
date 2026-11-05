<?php

namespace Tests\Unit;

use App\Support\Digits;
use PHPUnit\Framework\TestCase;

class DigitsTest extends TestCase
{
    public function test_converts_both_ways(): void
    {
        $this->assertSame('۱۲۳۴۵ abc ۰', Digits::toPersian('12345 abc 0'));
        $this->assertSame('09121234567', Digits::toLatin('۰۹۱۲۱۲۳۴۵۶۷'));
        $this->assertSame('0123', Digits::toLatin('٠١٢٣'));
    }

    public function test_locale_aware(): void
    {
        $this->assertSame('۱۲', Digits::forLocale('12', 'fa'));
        $this->assertSame('12', Digits::forLocale('12', 'en'));
    }
}
