<?php

namespace Tests\Feature\Public;

use App\Settings\SocialSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Ikon media sosial header memakai URL dari SocialSettings, menggantikan
 * tautan mati href="#" yang sebelumnya ditulis di kode (FR-019, FR-020,
 * SC-005, contracts §2).
 */
class SocialProfileLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_header_shows_only_configured_platforms(): void
    {
        $settings = app(SocialSettings::class);
        $settings->instagram_url = 'https://instagram.com/klienuji';
        $settings->facebook_url = 'https://facebook.com/klienuji';
        $settings->save();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('https://instagram.com/klienuji', escape: false);
        $response->assertSee('https://facebook.com/klienuji', escape: false);
        $response->assertDontSee('aria-label="YouTube"', escape: false);
        $response->assertDontSee('aria-label="LinkedIn"', escape: false);
        $response->assertDontSee('aria-label="Twitter/X"', escape: false);
        $response->assertDontSee('aria-label="Pinterest"', escape: false);
        $response->assertDontSee('aria-label="TikTok"', escape: false);
    }

    public function test_header_shows_new_platform_not_previously_supported(): void
    {
        $settings = app(SocialSettings::class);
        $settings->linkedin_url = 'https://linkedin.com/company/klienuji';
        $settings->save();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('aria-label="LinkedIn"', escape: false);
        $response->assertSee('https://linkedin.com/company/klienuji', escape: false);
    }

    public function test_no_social_icon_group_rendered_when_nothing_configured(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('href="#" aria-label=', escape: false);
    }
}
