<?php

namespace Tests\Feature\Public;

use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Halaman 404/500 menampilkan pesan dari pengaturan, dengan pesan bawaan
 * yang wajar saat kosong (FR-015 sampai FR-017, contracts §8).
 */
class CustomErrorPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_404_page_shows_configured_message(): void
    {
        $settings = app(SiteSettings::class);
        $settings->error_404_message = 'Pesan 404 khusus klien uji.';
        $settings->save();

        $response = $this->get('/alamat-yang-tidak-ada');

        $response->assertStatus(404);
        $response->assertSee('Pesan 404 khusus klien uji.', escape: false);
    }

    public function test_404_page_shows_default_message_when_not_configured(): void
    {
        $response = $this->get('/alamat-yang-tidak-ada');

        $response->assertStatus(404);
        $response->assertSee('tidak ditemukan', escape: false);
    }

    public function test_404_page_shows_site_identity_and_link_home(): void
    {
        $settings = app(SiteSettings::class);
        $settings->site_name = 'Situs Uji';
        $settings->save();

        $response = $this->get('/alamat-yang-tidak-ada');

        $response->assertStatus(404);
        $response->assertSee('Situs Uji', escape: false);
        $response->assertSee('href="'.url('/').'"', escape: false);
    }

    /**
     * Halaman 500 dirender saat aplikasi sedang bermasalah — diuji dengan
     * merender view-nya langsung, bukan memaksakan error 500 sungguhan yang
     * rapuh untuk disimulasikan (tidak ada rute yang sengaja error di app
     * ini). Membuktikan view tersebut valid dan memakai pesan pengaturan.
     */
    public function test_500_view_renders_configured_message(): void
    {
        $settings = app(SiteSettings::class);
        $settings->error_500_message = 'Pesan 500 khusus klien uji.';
        $settings->save();

        $html = view('errors.500')->render();

        $this->assertStringContainsString('Pesan 500 khusus klien uji.', $html);
    }

    public function test_500_view_renders_default_message_when_not_configured(): void
    {
        $html = view('errors.500')->render();

        $this->assertStringContainsString('kendala', $html);
    }
}
