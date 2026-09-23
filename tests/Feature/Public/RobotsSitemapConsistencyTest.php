<?php

namespace Tests\Feature\Public;

use App\Settings\SeoSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * robots.txt tidak boleh menunjuk ke sitemap.xml yang sedang dimatikan,
 * meski admin menuliskan barisnya sendiri (FR-040, contracts §5).
 */
class RobotsSitemapConsistencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_line_is_stripped_from_robots_txt_when_sitemap_disabled(): void
    {
        $seo = app(SeoSettings::class);
        $seo->sitemap_enabled = false;
        $seo->robots_txt_content = "User-agent: *\nAllow: /\n\nSitemap: {site_url}/sitemap.xml\n";
        $seo->save();

        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertDontSee('Sitemap:', escape: false);
    }

    public function test_sitemap_line_present_when_sitemap_enabled(): void
    {
        $seo = app(SeoSettings::class);
        $seo->sitemap_enabled = true;
        $seo->robots_txt_content = "User-agent: *\nAllow: /\n\nSitemap: {site_url}/sitemap.xml\n";
        $seo->save();

        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertSee('Sitemap: '.url('/sitemap.xml'), escape: false);
    }
}
