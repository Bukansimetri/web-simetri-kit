<?php

namespace Tests\Feature\Pages;

use App\Models\Product;
use App\Models\Testimonial;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\LegacyMarkup;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }

    public function test_home_page_displays_key_sections(): void
    {
        Product::factory()->create(['name' => 'SUOER Mono X-Pro 550W']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Hitung Estimasi Penghematan', escape: false);
        $response->assertSee('SUOER Mono X-Pro 550W', escape: false);
    }

    public function test_testimonials_show_label_and_partner_heading(): void
    {
        Testimonial::factory()->count(3)->create();

        $this->get('/')
            ->assertOk()
            ->assertSee('Testimoni')
            ->assertSee('Partner Kami')
            ->assertDontSee('Apa Kata Mereka Tentang', escape: false);
    }

    public function test_testimonial_cards_are_uniform_without_highlight(): void
    {
        Testimonial::factory()->count(3)->create();

        $html = $this->get('/')->assertOk()->getContent();
        $section = LegacyMarkup::extract($html, "//section[.//h2[contains(., 'Partner Kami')]]");

        $this->assertSame(3, substr_count($section, 'shadow-sm hover:shadow-lg'));
        $this->assertStringNotContainsString('bg-surface-container-low', $section);
        $this->assertStringNotContainsString('border-primary/30', $section);
    }

    public function test_testimonial_stars_follow_rating_with_outline_for_the_rest(): void
    {
        Testimonial::factory()->create(['rating' => 3]);

        $html = $this->get('/')->assertOk()->getContent();
        $section = LegacyMarkup::extract($html, "//section[.//h2[contains(., 'Partner Kami')]]");

        $this->assertSame(3, substr_count($section, '>star<'));
        $this->assertSame(2, substr_count($section, '>star_border<'));
    }

    public function test_testimonial_without_photo_shows_initials_and_without_attribution_hides_line(): void
    {
        Testimonial::factory()->create(['name' => 'Bapak Michael', 'attribution' => null, 'photo_path' => null]);

        $html = $this->get('/')->assertOk()->getContent();
        $section = LegacyMarkup::extract($html, "//section[.//h2[contains(., 'Partner Kami')]]");

        $this->assertStringContainsString('BM </div>', $section);
        $this->assertStringNotContainsString('text-outline font-medium', $section);
    }

    public function test_testimonial_section_is_hidden_without_active_testimonials(): void
    {
        Testimonial::factory()->inactive()->create();

        $this->get('/')->assertOk()->assertDontSee('Partner Kami');
    }

    public function test_featured_products_section_keeps_popular_badge_button_and_all_products_link(): void
    {
        Product::factory()->count(3)->create();

        $html = $this->get('/')->assertOk()->getContent();
        $section = LegacyMarkup::extract($html, "//section[.//h2[contains(., 'Solusi Untuk Setiap Kebutuhan')]]");

        $this->assertSame(1, substr_count($section, 'Terpopuler'));
        $this->assertSame(1, substr_count($section, 'Lihat Detail Produk'));
        $this->assertSame(2, substr_count($section, 'Pelajari lebih lanjut'));
        $this->assertStringContainsString('Lihat semua produk', $section);
    }

    public function test_footer_contact_column_shows_address_email_and_phone_with_icons(): void
    {
        $site = app(SiteSettings::class);
        $site->company_address = 'Jl. Jend. Sudirman Kav. 52-53, Jakarta Selatan 12190';
        $site->company_email = 'hello@suoer.id';
        $site->company_phone = '(021) 555-0123';
        $site->save();

        $this->get('/')
            ->assertOk()
            ->assertSee('Jl. Jend. Sudirman Kav. 52-53')
            ->assertSee('hello@suoer.id')
            ->assertSee('(021) 555-0123')
            ->assertSee('location_on')
            ->assertSee('>mail<', escape: false)
            ->assertSee('>call<', escape: false);
    }

    public function test_footer_hides_empty_contact_rows(): void
    {
        $site = app(SiteSettings::class);
        $site->company_address = null;
        $site->company_email = 'hello@suoer.id';
        $site->company_phone = null;
        $site->save();

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('location_on', $html);
        $this->assertStringNotContainsString('>call<', $html);
        $this->assertStringContainsString('hello@suoer.id', $html);
    }

    public function test_footer_omits_contact_column_when_all_contact_data_is_empty(): void
    {
        $site = app(SiteSettings::class);
        $site->company_name = null;
        $site->company_address = null;
        $site->company_email = null;
        $site->company_phone = null;
        $site->save();

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('<h4 class="font-bold text-white mb-6">Kontak</h4>', $html);
    }
}
