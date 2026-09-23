<?php

namespace Tests\Feature\Public;

use App\Settings\SeoSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kode verifikasi mesin pencari yang diisi hadir sebagai meta tag; yang
 * kosong tidak hadir (FR-034, contracts §1).
 */
class SeoVerificationTagTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_meta_tags_present_when_configured(): void
    {
        $seo = app(SeoSettings::class);
        $seo->verification_google = 'kode-google-123';
        $seo->verification_bing = 'kode-bing-456';
        $seo->save();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('name="google-site-verification" content="kode-google-123"', escape: false);
        $response->assertSee('name="msvalidate.01" content="kode-bing-456"', escape: false);
    }

    public function test_verification_meta_tags_absent_when_not_configured(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('google-site-verification', escape: false);
        $response->assertDontSee('msvalidate.01', escape: false);
        $response->assertDontSee('yandex-verification', escape: false);
        $response->assertDontSee('baidu-site-verification', escape: false);
    }
}
