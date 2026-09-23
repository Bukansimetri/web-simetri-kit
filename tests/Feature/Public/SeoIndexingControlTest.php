<?php

namespace Tests\Feature\Public;

use App\Settings\SeoSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Kontrol pengindeksan dinyatakan sebagai meta robots pada setiap halaman
 * publik (FR-030, contracts §1).
 */
class SeoIndexingControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_indexing_allowed_by_default(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('name="robots" content="noindex', escape: false);
    }

    public function test_indexing_disabled_adds_noindex_directive(): void
    {
        $seo = app(SeoSettings::class);
        $seo->allow_indexing = false;
        $seo->save();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('name="robots" content="noindex', escape: false);
    }

    public function test_following_disabled_adds_nofollow_directive(): void
    {
        $seo = app(SeoSettings::class);
        $seo->allow_following = false;
        $seo->save();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('nofollow', escape: false);
    }
}
