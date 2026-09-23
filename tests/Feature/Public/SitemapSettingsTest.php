<?php

namespace Tests\Feature\Public;

use App\Models\Article;
use App\Settings\SeoSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Komposisi sitemap.xml mengikuti sakelar per jenis konten, dan peta situs
 * dapat dimatikan sepenuhnya (FR-038, FR-039, contracts §6).
 */
class SitemapSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_articles_excluded_when_switch_disabled(): void
    {
        Article::factory()->create(['slug' => 'artikel-uji', 'published_at' => now()->subDay()]);

        $seo = app(SeoSettings::class);
        $seo->sitemap_include_articles = false;
        $seo->save();

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertDontSee('/artikel/artikel-uji', escape: false);
    }

    public function test_products_still_included_when_only_articles_disabled(): void
    {
        $seo = app(SeoSettings::class);
        $seo->sitemap_include_articles = false;
        $seo->save();

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertSee('<loc>'.url('/produk').'</loc>', escape: false);
    }

    public function test_sitemap_returns_not_found_when_disabled(): void
    {
        $seo = app(SeoSettings::class);
        $seo->sitemap_enabled = false;
        $seo->save();

        $this->get('/sitemap.xml')->assertNotFound();
    }

    public function test_entries_include_configured_changefreq_and_priority(): void
    {
        $seo = app(SeoSettings::class);
        $seo->sitemap_changefreq = 'daily';
        $seo->sitemap_priority = '0.5';
        $seo->save();

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertSee('<changefreq>daily</changefreq>', escape: false);
        $response->assertSee('<priority>0.5</priority>', escape: false);
    }
}
