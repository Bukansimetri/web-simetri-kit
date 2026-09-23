<?php

namespace Tests\Feature\Public;

use App\Models\Product;
use App\Settings\SeoSettings;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Judul halaman mengikuti pola default/per-jenis-halaman, dan judul SEO per
 * konten tetap menang atas keduanya (FR-025, FR-026, FR-028, contracts §1).
 */
class SeoTitleFormatTest extends TestCase
{
    use RefreshDatabase;

    public function test_static_page_title_follows_default_pattern(): void
    {
        $site = app(SiteSettings::class);
        $site->site_name = 'Situs Uji';
        $site->save();

        $seo = app(SeoSettings::class);
        $seo->title_separator = '::';
        $seo->default_title_format = '{page_title} {separator} {site_name}';
        $seo->save();

        $response = $this->get('/kontak');

        $response->assertOk();
        $response->assertSee('<title>Kontak :: Situs Uji</title>', escape: false);
    }

    public function test_page_type_specific_pattern_is_used_for_product_detail(): void
    {
        $site = app(SiteSettings::class);
        $site->site_name = 'Situs Uji';
        $site->save();

        $seo = app(SeoSettings::class);
        $seo->page_title_formats = ['produk_show' => 'Beli {page_title} di {site_name}'];
        $seo->save();

        $product = Product::factory()->create(['name' => 'Panel Surya 500W', 'meta_title' => null]);

        $response = $this->get('/produk/'.$product->slug);

        $response->assertOk();
        $response->assertSee('<title>Beli Panel Surya 500W di Situs Uji</title>', escape: false);
    }

    public function test_content_seo_title_wins_over_any_pattern(): void
    {
        $seo = app(SeoSettings::class);
        $seo->page_title_formats = ['produk_show' => 'Beli {page_title} di {site_name}'];
        $seo->save();

        $product = Product::factory()->create([
            'name' => 'Panel Surya 500W',
            'meta_title' => 'Judul SEO Kustom Admin',
        ]);

        $response = $this->get('/produk/'.$product->slug);

        $response->assertOk();
        $response->assertSee('<title>Judul SEO Kustom Admin</title>', escape: false);
    }
}
