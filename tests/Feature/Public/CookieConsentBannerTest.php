<?php

namespace Tests\Feature\Public;

use App\Settings\ScriptSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Bilah persetujuan cookie tampil hanya saat diaktifkan, memuat tombol
 * terima/tolak setara, dan tautan pengaturannya hanya ada saat aktif
 * (FR-051, FR-057, FR-073 sampai FR-075, contracts/consent-gating-contract.md
 * §2 dan §3).
 */
class CookieConsentBannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_banner_markup_present_when_consent_enabled(): void
    {
        $settings = app(ScriptSettings::class);
        $settings->cookie_consent_enabled = true;
        $settings->save();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('data-cookie-consent-banner', escape: false);
    }

    public function test_banner_markup_absent_when_consent_disabled(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('data-cookie-consent-banner', escape: false);
    }

    public function test_footer_cookie_settings_link_absent_when_consent_disabled(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('Pengaturan Cookie', escape: false);
    }

    public function test_footer_cookie_settings_link_present_when_consent_enabled(): void
    {
        $settings = app(ScriptSettings::class);
        $settings->cookie_consent_enabled = true;
        $settings->save();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Pengaturan Cookie', escape: false);
    }

    public function test_custom_banner_message_is_rendered(): void
    {
        $settings = app(ScriptSettings::class);
        $settings->cookie_consent_enabled = true;
        $settings->cookie_banner_message = 'Pesan persetujuan cookie khusus klien uji.';
        $settings->save();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Pesan persetujuan cookie khusus klien uji.', escape: false);
    }
}
