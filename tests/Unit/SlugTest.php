<?php

namespace Tests\Unit;

use App\Support\Slug;
use PHPUnit\Framework\TestCase;

class SlugTest extends TestCase
{
    public function test_persian_text_keeps_its_letters_and_digits_become_latin(): void
    {
        $this->assertSame('نمایشگاه-ساختمان-1405', Slug::make('نمایشگاه ساختمان ۱۴۰۵'));
    }

    public function test_zwnj_and_arabic_letters_are_normalised(): void
    {
        $this->assertSame('می-شود', Slug::make("می\u{200C}شود"));
        $this->assertSame('کتاب-ی', Slug::make('كتاب ي'));
    }

    public function test_punctuation_is_removed_and_empty_input_gets_a_fallback(): void
    {
        $this->assertSame('hello-world', Slug::make('  Hello,  World!! '));
        $this->assertSame('item', Slug::make('!!!'));
    }
}
