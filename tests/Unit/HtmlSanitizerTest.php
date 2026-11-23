<?php

namespace Tests\Unit;

use App\Support\HtmlSanitizer;
use Tests\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public function test_scripts_handlers_iframes_and_bad_urls_are_removed(): void
    {
        $clean = HtmlSanitizer::clean('<p onclick="x()">سلام</p><script>alert(1)</script><iframe src="https://evil.test"></iframe>'
            .'<a href="javascript:alert(1)">bad</a><img src="https://evil.test/p.png" onerror="x()"><svg onload="x()"></svg>');

        $this->assertStringContainsString('سلام', $clean);
        foreach (['script', 'onclick', 'iframe', 'javascript:', 'evil.test', 'onerror', '<svg'] as $needle) {
            $this->assertStringNotContainsString($needle, $clean);
        }
    }

    public function test_safe_formatting_links_and_local_images_survive(): void
    {
        $clean = HtmlSanitizer::clean('<h2>تیتر</h2><p><strong>پررنگ</strong> <a href="https://example.com" target="_blank">لینک</a></p><img src="/uploads/a.jpg" alt="عکس">');

        $this->assertStringContainsString('<h2>تیتر</h2>', $clean);
        $this->assertStringContainsString('<strong>پررنگ</strong>', $clean);
        $this->assertStringContainsString('rel="noreferrer noopener"', $clean);
        $this->assertStringContainsString('src="/uploads/a.jpg"', $clean);
    }

    public function test_text_align_style_becomes_a_class_because_csp_blocks_inline_styles(): void
    {
        $clean = HtmlSanitizer::clean('<p style="text-align: center; color: red">x</p>');

        $this->assertStringContainsString('class="ta-center"', $clean);
        $this->assertStringNotContainsString('style=', $clean);
        $this->assertStringNotContainsString('red', $clean);
    }

    public function test_empty_input_stays_null(): void
    {
        $this->assertNull(HtmlSanitizer::clean('  '));
        $this->assertNull(HtmlSanitizer::clean(null));
    }
}
