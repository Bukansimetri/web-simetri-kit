<?php

namespace Tests\Unit\Seo;

use App\Settings\SeoSettings;
use App\Settings\SiteSettings;
use App\Support\Seo\PageTitle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Membuktikan penggantian placeholder, pembuangan penanda tak dikenal, dan
 * kemenangan judul SEO per konten (FR-025, FR-026, FR-027, FR-028).
 */
class PageTitleTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_format_replaces_all_known_placeholders(): void
    {
        $site = app(SiteSettings::class);
        $site->site_name = 'Situs Uji';
        $site->save();

        $result = PageTitle::forStatic('kontak', 'Kontak');

        $this->assertSame('Kontak | Situs Uji', $result);
    }

    public function test_custom_separator_is_used(): void
    {
        $site = app(SiteSettings::class);
        $site->site_name = 'Situs Uji';
        $site->save();

        $seo = app(SeoSettings::class);
        $seo->title_separator = '—';
        $seo->save();

        $result = PageTitle::forStatic('kontak', 'Kontak');

        $this->assertSame('Kontak — Situs Uji', $result);
    }

    public function test_unknown_placeholder_is_stripped_without_dangling_separator(): void
    {
        $seo = app(SeoSettings::class);
        $seo->default_title_format = '{page_title} {separator} {tidak_dikenal} {separator} {site_name}';
        $seo->save();

        $site = app(SiteSettings::class);
        $site->site_name = 'Situs Uji';
        $site->save();

        $result = PageTitle::forStatic('kontak', 'Kontak');

        $this->assertSame('Kontak | Situs Uji', $result);
        $this->assertStringNotContainsString('tidak_dikenal', $result);
        $this->assertStringNotContainsString('{', $result);
    }

    public function test_page_type_specific_format_wins_over_default(): void
    {
        $seo = app(SeoSettings::class);
        $seo->page_title_formats = ['artikel_show' => '{page_title} · Blog {site_name}'];
        $seo->save();

        $site = app(SiteSettings::class);
        $site->site_name = 'Situs Uji';
        $site->save();

        $resultArtikel = PageTitle::forStatic('artikel_show', 'Judul Artikel');
        $resultLain = PageTitle::forStatic('kontak', 'Kontak');

        $this->assertSame('Judul Artikel · Blog Situs Uji', $resultArtikel);
        $this->assertSame('Kontak | Situs Uji', $resultLain);
    }

    public function test_content_meta_title_wins_completely_over_pattern(): void
    {
        $site = app(SiteSettings::class);
        $site->site_name = 'Situs Uji';
        $site->save();

        $result = PageTitle::forContent('produk_show', 'Judul SEO Kustom Admin', 'Nama Produk Asli');

        $this->assertSame('Judul SEO Kustom Admin', $result);
    }

    public function test_content_falls_back_to_pattern_when_meta_title_is_empty(): void
    {
        $site = app(SiteSettings::class);
        $site->site_name = 'Situs Uji';
        $site->save();

        $result = PageTitle::forContent('produk_show', null, 'Nama Produk Asli');

        $this->assertSame('Nama Produk Asli | Situs Uji', $result);
    }
}
