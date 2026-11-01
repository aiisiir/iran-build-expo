<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityFoundationTest extends TestCase
{
    public function test_security_headers_are_sent_on_pages(): void
    {
        $this->get('/fa')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy')
            ->assertHeader('Content-Security-Policy');
        $this->assertStringContainsString("default-src 'self'", $this->get('/fa')->headers->get('Content-Security-Policy'));
    }

    public function test_csp_is_report_only_while_debugging(): void
    {
        config(['app.debug' => true]);

        $this->get('/fa')->assertHeader('Content-Security-Policy-Report-Only')->assertHeaderMissing('Content-Security-Policy');
    }

    public function test_404_page_is_custom_noindex_and_has_security_headers(): void
    {
        $this->get('/no-such-page')
            ->assertNotFound()
            ->assertSee('صفحه پیدا نشد', false)
            ->assertSee('Page not found')
            ->assertSee('name="robots" content="noindex,nofollow"', false)
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('Content-Security-Policy');
    }

    public function test_public_routes_are_rate_limited(): void
    {
        for ($i = 0; $i < 120; $i++) {
            $this->get('/fa')->assertOk();
        }

        $this->get('/fa')->assertStatus(429)->assertSee('Too many requests');
    }
}
