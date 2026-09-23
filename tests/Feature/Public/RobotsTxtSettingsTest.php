<?php

namespace Tests\Feature\Public;

use App\Settings\SeoSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Isi robots.txt berasal dari pengaturan, bukan lagi string mati di
 * SitemapController (FR-035, FR-036, FR-037, contracts §5).
 */
class RobotsTxtSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_txt_content_matches_configured_value(): void
    {
        $seo = app(SeoSettings::class);
        $seo->robots_txt_content = "User-agent: *\nDisallow: /admin\n";
        $seo->save();

        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertSee("User-agent: *\nDisallow: /admin", escape: false);
    }

    public function test_site_url_placeholder_is_replaced_with_active_site_url(): void
    {
        $seo = app(SeoSettings::class);
        $seo->robots_txt_content = "User-agent: *\nAllow: /\n\nSitemap: {site_url}/sitemap.xml\n";
        $seo->save();

        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertSee('Sitemap: '.url('/sitemap.xml'), escape: false);
        $response->assertDontSee('{site_url}', escape: false);
    }

    public function test_safe_default_is_served_when_content_is_empty(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertSee('User-agent: *', escape: false);
    }
}
