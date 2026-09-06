<?php

namespace Tests\Feature\Pages;

use App\Models\ClientLogo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AboutPageClientLogosTest extends TestCase
{
    use RefreshDatabase;

    protected function makeLogo(array $attributes = []): ClientLogo
    {
        $logo = ClientLogo::factory()->create($attributes);
        Storage::disk('public')->put($logo->logo_path, 'fake-bytes');

        return $logo;
    }

    public function test_active_logo_is_shown_on_about_page(): void
    {
        Storage::fake('public');
        $logo = $this->makeLogo(['company_name' => 'PT Sinar Mas']);

        $response = $this->get('/tentang-kami');

        $response->assertOk();
        $response->assertSee('PT Sinar Mas', escape: false);
        $response->assertSee($logo->logo_path, escape: false);
    }

    public function test_inactive_logo_is_not_shown(): void
    {
        Storage::fake('public');
        $this->makeLogo(['company_name' => 'Klien Nonaktif', 'is_active' => false]);

        $this->get('/tentang-kami')
            ->assertOk()
            ->assertDontSee('Klien Nonaktif', escape: false);
    }

    public function test_logos_render_in_order(): void
    {
        Storage::fake('public');
        $this->makeLogo(['company_name' => 'Kedua', 'order' => 2]);
        $this->makeLogo(['company_name' => 'Pertama', 'order' => 1]);

        $this->get('/tentang-kami')
            ->assertOk()
            ->assertSeeInOrder(['Pertama', 'Kedua'], escape: false);
    }

    public function test_logo_with_link_is_a_new_tab_anchor(): void
    {
        Storage::fake('public');
        $this->makeLogo(['company_name' => 'Tokopedia', 'link_url' => 'https://example.com']);

        $response = $this->get('/tentang-kami');

        $response->assertSee('href="https://example.com"', escape: false);
        $response->assertSee('target="_blank"', escape: false);
    }

    public function test_logo_without_link_has_no_anchor_to_it(): void
    {
        Storage::fake('public');
        $logo = $this->makeLogo(['company_name' => 'TanpaTautan', 'link_url' => null]);

        $response = $this->get('/tentang-kami');

        $response->assertOk();
        $response->assertSee('TanpaTautan', escape: false);
        // Logo tanpa link dirender sebagai <img> polos, tidak dibungkus <a target="_blank">.
        $response->assertDontSee('rel="noopener noreferrer nofollow"', escape: false);
    }

    public function test_about_page_renders_without_logo_strip_when_none_active(): void
    {
        Storage::fake('public');
        $this->makeLogo(['is_active' => false]);

        $response = $this->get('/tentang-kami');

        $response->assertOk();
        $response->assertSee('Nilai-Nilai Kami', escape: false);
        $response->assertDontSee('Dipercaya oleh', escape: false);
    }

    public function test_home_page_does_not_show_logo_strip(): void
    {
        Storage::fake('public');
        $this->makeLogo(['company_name' => 'Logo Beranda']);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Logo Beranda', escape: false);
    }
}
