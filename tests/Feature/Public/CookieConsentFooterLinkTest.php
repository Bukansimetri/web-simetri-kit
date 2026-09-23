<?php

namespace Tests\Feature\Public;

use App\Settings\ScriptSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tautan pengaturan cookie di footer hadir sebaris tautan legal hanya saat
 * persetujuan diaktifkan (FR-056, FR-075, contracts §3).
 */
class CookieConsentFooterLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_cookie_settings_link_sits_alongside_legal_links(): void
    {
        $settings = app(ScriptSettings::class);
        $settings->cookie_consent_enabled = true;
        $settings->save();

        $content = $this->get('/')->assertOk()->getContent();

        $footerStart = strpos($content, '<footer');
        $this->assertNotFalse($footerStart);

        $legalLinksArea = substr($content, $footerStart);
        $this->assertStringContainsString('Kebijakan Privasi', $legalLinksArea);
        $this->assertStringContainsString('Pengaturan Cookie', $legalLinksArea);
    }
}
