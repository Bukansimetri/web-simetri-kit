<?php

namespace Tests\Feature\Public;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RobotsTxtTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_txt_still_allows_crawling_everything(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $this->assertStringContainsString('text/plain', $response->headers->get('Content-Type'));
        $response->assertSee('User-agent: *', escape: false);
        $response->assertSee('Disallow:', escape: false);
    }

    public function test_robots_txt_points_to_sitemap(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertSee('Sitemap: '.url('/sitemap.xml'), escape: false);
    }
}
