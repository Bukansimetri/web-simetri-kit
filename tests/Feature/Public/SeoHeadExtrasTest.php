<?php

namespace Tests\Feature\Public;

use App\Settings\SeoSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kata kunci, kanonik default, handle Twitter/X, dan meta tag tambahan
 * hadir sesuai pengaturan (FR-029, FR-031, FR-033, contracts §1).
 */
class SeoHeadExtrasTest extends TestCase
{
    use RefreshDatabase;

    public function test_meta_keywords_are_rendered_when_configured(): void
    {
        $seo = app(SeoSettings::class);
        $seo->meta_keywords = ['panel surya', 'energi terbarukan'];
        $seo->save();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('name="keywords" content="panel surya, energi terbarukan"', escape: false);
    }

    public function test_meta_keywords_absent_when_empty(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('name="keywords"', escape: false);
    }

    public function test_default_canonical_url_overrides_current_url(): void
    {
        $seo = app(SeoSettings::class);
        $seo->default_canonical_url = 'https://kanonik.test/beranda';
        $seo->save();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('rel="canonical" href="https://kanonik.test/beranda"', escape: false);
    }

    public function test_twitter_handle_is_rendered_when_configured(): void
    {
        $seo = app(SeoSettings::class);
        $seo->twitter_handle = '@klienuji';
        $seo->save();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('name="twitter:site" content="@klienuji"', escape: false);
    }

    public function test_additional_head_meta_is_rendered_as_is(): void
    {
        $seo = app(SeoSettings::class);
        $seo->additional_head_meta = '<meta name="penanda-tambahan" content="unik">';
        $seo->save();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('<meta name="penanda-tambahan" content="unik">', escape: false);
    }
}
