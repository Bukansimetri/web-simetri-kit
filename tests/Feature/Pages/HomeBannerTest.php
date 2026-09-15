<?php

namespace Tests\Feature\Pages;

use App\Models\Banner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomeBannerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Judul hero BAWAAN (komponen `x-sections.hero` statis, fallback nol-banner).
     * Bukan judul apa pun yang mungkin tersimpan di record Banner — begitu ada
     * banner tayang, `hero-slider` dirender sebagai gantinya, sehingga teks
     * hardcoded ini TIDAK PERNAH ikut tampil (T026).
     */
    private const HERO_HEADLINE = 'Nyalakan Rumah &amp; Bisnis Anda dengan Energi Matahari';

    protected function makeBanner(array $attributes = []): Banner
    {
        $banner = Banner::factory()->create($attributes);
        Storage::disk('public')->put($banner->image_path, 'fake-bytes');

        return $banner;
    }

    protected function persistFile(Banner $banner): Banner
    {
        Storage::disk('public')->put($banner->image_path, 'fake-bytes');

        return $banner;
    }

    public function test_live_banners_render_in_hero_slot_instead_of_static_hero(): void
    {
        Storage::fake('public');
        $this->makeBanner(['alt_text' => 'Banner Kedua', 'order' => 2]);
        $this->makeBanner(['alt_text' => 'Banner Pertama', 'order' => 1]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeInOrder(['Banner Pertama', 'Banner Kedua'], escape: false);
        $response->assertDontSee(self::HERO_HEADLINE, escape: false);
    }

    public function test_banner_with_link_is_wrapped_in_anchor(): void
    {
        Storage::fake('public');
        $this->makeBanner(['alt_text' => 'Promo', 'link_url' => 'https://x.test/promo']);

        $this->get('/')
            ->assertOk()
            ->assertSee('href="https://x.test/promo"', escape: false);
    }

    public function test_scheduled_expired_and_inactive_banners_do_not_appear(): void
    {
        Storage::fake('public');
        $this->persistFile(Banner::factory()->scheduled()->create(['alt_text' => 'Terjadwal']));
        $this->persistFile(Banner::factory()->expired()->create(['alt_text' => 'Kedaluwarsa']));
        $this->persistFile(Banner::factory()->create(['alt_text' => 'Nonaktif', 'is_active' => false]));

        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('Terjadwal', escape: false);
        $response->assertDontSee('Kedaluwarsa', escape: false);
        $response->assertDontSee('Nonaktif', escape: false);
        $response->assertSee(self::HERO_HEADLINE, escape: false);
    }

    public function test_static_hero_is_shown_when_no_live_banner(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(self::HERO_HEADLINE, escape: false);
    }

    public function test_other_pages_do_not_show_banner(): void
    {
        Storage::fake('public');
        $this->makeBanner(['alt_text' => 'Banner Beranda Saja']);

        $this->get('/produk')
            ->assertOk()
            ->assertDontSee('Banner Beranda Saja', escape: false);
    }

    /**
     * T014: banner dengan heading, subheading, dan CTA merender seluruh
     * elemen tsb di beranda (contracts/public-render.md §2).
     */
    public function test_banner_with_full_content_renders_all_hero_elements(): void
    {
        Storage::fake('public');
        $this->makeBanner([
            'alt_text' => 'Banner Konten Lengkap',
            'badge_text' => 'Badge Unik Sekali',
            'heading' => 'Judul Slide Unik Sekali',
            'subheading' => 'Subjudul slide unik sekali untuk pengujian.',
            'cta_primary_label' => 'Konsultasi Gratis Unik',
            'cta_primary_url' => '/kontak',
            'cta_secondary_label' => 'Pelajari Lagi Unik',
            'cta_secondary_url' => '/#kalkulator',
        ]);

        $response = $this->get('/')->assertOk();

        $response->assertSee('Badge Unik Sekali', escape: false);
        $response->assertSee('Judul Slide Unik Sekali', escape: false);
        $response->assertSee('Subjudul slide unik sekali untuk pengujian.', escape: false);
        $response->assertSee('Konsultasi Gratis Unik', escape: false);
        $response->assertSee('Pelajari Lagi Unik', escape: false);
        $response->assertSee('href="'.url('/kontak').'"', escape: false);
        $response->assertSee('href="'.url('/#kalkulator').'"', escape: false);
    }

    /**
     * T015a: banner tanpa konten apa pun merender gambar tanpa blok teks.
     */
    public function test_banner_without_content_renders_image_only(): void
    {
        Storage::fake('public');
        $this->makeBanner(['alt_text' => 'Banner Polos Unik']);

        $content = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Banner Polos Unik', $content);
        $this->assertStringNotContainsString('cta_primary_label', $content);
    }

    /**
     * T015b: banner satu CTA hanya merender satu tombol.
     */
    public function test_banner_with_single_cta_renders_one_button_only(): void
    {
        Storage::fake('public');
        $this->makeBanner([
            'alt_text' => 'Banner Satu Tombol',
            'heading' => 'Judul Satu Tombol',
            'cta_primary_label' => 'Tombol Tunggal Unik',
            'cta_primary_url' => '/kontak',
        ]);

        $response = $this->get('/')->assertOk();

        $response->assertSee('Tombol Tunggal Unik', escape: false);
        $response->assertDontSee('cta_secondary_label', escape: false);
    }

    /**
     * T028: satu banner tayang merender tanpa kontrol navigasi; tiga banner
     * merender panah, tiga titik, dan penanda posisi (contracts/public-render.md §1/§5).
     */
    public function test_single_banner_renders_without_slider_controls(): void
    {
        Storage::fake('public');
        $this->makeBanner(['alt_text' => 'Banner Tunggal Unik']);

        $content = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Banner Tunggal Unik', $content);
        $this->assertStringNotContainsString('aria-roledescription="carousel"', $content);
    }

    public function test_three_banners_render_slider_controls(): void
    {
        Storage::fake('public');
        $this->makeBanner(['alt_text' => 'Banner Slider Satu', 'order' => 1]);
        $this->makeBanner(['alt_text' => 'Banner Slider Dua', 'order' => 2]);
        $this->makeBanner(['alt_text' => 'Banner Slider Tiga', 'order' => 3]);

        $content = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('aria-roledescription="carousel"', $content);
        $this->assertSame(3, substr_count($content, 'aria-roledescription="slide"'));
        $this->assertStringContainsString('1 / 3', $content);
    }

    /**
     * T029: urutan slide mengikuti kolom `order` menaik, pemecah seri `id`.
     */
    public function test_slide_order_follows_order_column_ascending(): void
    {
        Storage::fake('public');
        $this->makeBanner(['alt_text' => 'Urutan Ketiga', 'order' => 3]);
        $this->makeBanner(['alt_text' => 'Urutan Pertama', 'order' => 1]);
        $this->makeBanner(['alt_text' => 'Urutan Kedua', 'order' => 2]);

        $this->get('/')
            ->assertOk()
            ->assertSeeInOrder(['Urutan Pertama', 'Urutan Kedua', 'Urutan Ketiga'], escape: false);
    }

    /**
     * T030: banner yang berkas gambarnya hilang dilewati tanpa menggagalkan
     * render, dan jumlah titik navigasi menyesuaikan (FR-020).
     */
    public function test_banner_with_missing_image_file_is_skipped(): void
    {
        Storage::fake('public');
        $this->makeBanner(['alt_text' => 'Banner Ada Berkas Satu', 'order' => 1]);
        $this->makeBanner(['alt_text' => 'Banner Ada Berkas Dua', 'order' => 2]);
        // Tanpa persistFile -- berkasnya sengaja tidak pernah dibuat.
        Banner::factory()->create(['alt_text' => 'Banner Berkas Hilang', 'order' => 3]);

        $response = $this->get('/')->assertOk();

        $response->assertSee('Banner Ada Berkas Satu', escape: false);
        $response->assertSee('Banner Ada Berkas Dua', escape: false);
        $response->assertDontSee('Banner Berkas Hilang', escape: false);
        $this->assertSame(2, substr_count($response->getContent(), 'aria-roledescription="slide"'));
    }

    /**
     * T041: preset overlay_style/text_position terpilih menghasilkan kelas
     * lapisan dan perataan yang sesuai di markup (contracts/public-render.md §4).
     */
    public function test_overlay_and_text_position_presets_render_expected_classes(): void
    {
        Storage::fake('public');
        $this->makeBanner([
            'alt_text' => 'Banner Preset Terang',
            'heading' => 'Judul Preset Terang',
            'overlay_style' => 'light',
            'text_position' => 'center',
        ]);

        $content = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('from-white/90', $content);
        $this->assertStringContainsString('mx-auto text-center', $content);
    }

    public function test_none_overlay_renders_without_gradient_layer(): void
    {
        Storage::fake('public');
        $this->makeBanner([
            'alt_text' => 'Banner Tanpa Lapisan',
            'heading' => 'Judul Tanpa Lapisan',
            'overlay_style' => 'none',
        ]);

        $content = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('bg-gradient-to-r from-on-surface', $content);
        $this->assertStringNotContainsString('bg-gradient-to-r from-white', $content);
    }

    /**
     * T045: `trust_html` dirender pada slide terkait, tidak dirender saat
     * kosong, dan elemen terlarang sudah dibuang saat sampai di halaman.
     */
    public function test_trust_bar_renders_on_its_own_slide_only_and_is_sanitized(): void
    {
        Storage::fake('public');
        $this->makeBanner([
            'alt_text' => 'Banner Trust Bar',
            'heading' => 'Judul Trust Bar',
            'trust_html' => '<p>Dipercaya <strong>500+</strong> pelanggan</p><script>alert(1)</script>',
            'order' => 1,
        ]);
        $this->makeBanner([
            'alt_text' => 'Banner Tanpa Trust Bar',
            'heading' => 'Judul Tanpa Trust Bar',
            'order' => 2,
        ]);

        $content = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('Dipercaya <strong>500+</strong> pelanggan', $content);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $content);
    }

    public function test_trust_bar_area_is_absent_when_blank(): void
    {
        Storage::fake('public');
        $this->makeBanner([
            'alt_text' => 'Banner Trust Bar Kosong',
            'heading' => 'Judul Trust Bar Kosong',
            'trust_html' => null,
        ]);

        $content = $this->get('/')->assertOk()->getContent();

        $this->assertStringNotContainsString('border-current/15', $content);
    }
}
