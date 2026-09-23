<?php

namespace Tests\Feature\Public;

use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Teks hak cipta dan tautan legal footer memakai pengaturan, bukan lagi
 * ditulis mati (FR-007, contracts §3).
 */
class FooterLegalSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_footer_uses_configured_copyright_and_legal_links(): void
    {
        $settings = app(SiteSettings::class);
        $settings->copyright_text = 'Hak cipta khusus klien uji.';
        $settings->terms_url = 'https://klien.test/syarat';
        $settings->privacy_url = 'https://klien.test/privasi';
        $settings->cookie_policy_url = 'https://klien.test/cookie';
        $settings->save();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Hak cipta khusus klien uji.', escape: false);
        $response->assertSee('https://klien.test/syarat', escape: false);
        $response->assertSee('https://klien.test/privasi', escape: false);
        $response->assertSee('https://klien.test/cookie', escape: false);
    }

    public function test_cookie_policy_link_is_hidden_when_not_configured(): void
    {
        $settings = app(SiteSettings::class);
        $settings->cookie_policy_url = null;
        $settings->save();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('Kebijakan Cookie', escape: false);
    }
}
