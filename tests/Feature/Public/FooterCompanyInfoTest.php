<?php

namespace Tests\Feature\Public;

use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Blok kontak footer memakai informasi perusahaan dari pengaturan, bukan
 * lagi ditulis mati di kode (FR-003, FR-004, contracts §3).
 */
class FooterCompanyInfoTest extends TestCase
{
    use RefreshDatabase;

    public function test_footer_shows_company_info_when_configured(): void
    {
        $settings = app(SiteSettings::class);
        $settings->company_name = 'PT Solar Nusantara';
        $settings->company_email = 'kontak@solarnusantara.test';
        $settings->company_phone = '(022) 999-8888';
        $settings->company_address = 'Jl. Contoh No. 1, Bandung';
        $settings->save();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('PT Solar Nusantara', escape: false);
        $response->assertSee('kontak@solarnusantara.test', escape: false);
        $response->assertSee('(022) 999-8888', escape: false);
        $response->assertSee('Jl. Contoh No. 1, Bandung', escape: false);
    }

    public function test_footer_hides_empty_company_fields_without_leaving_gaps(): void
    {
        $settings = app(SiteSettings::class);
        $settings->company_email = 'kontak@solarnusantara.test';
        $settings->company_phone = null;
        $settings->company_address = null;
        $settings->save();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('kontak@solarnusantara.test', escape: false);

        // Ikon telepon/alamat hanya dirender berpasangan dengan barisnya —
        // ketiadaan ikon ini membuktikan seluruh baris hilang, bukan cuma teksnya.
        $response->assertDontSee('material-symbols-outlined text-primary-container text-lg mr-2">call', escape: false);
        $response->assertDontSee('material-symbols-outlined text-primary-container text-lg mr-2">location_on', escape: false);
    }
}
