<?php

namespace Tests\Feature\Pages;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\LegacyMarkup;
use Tests\TestCase;

/**
 * Sekali jalan, pada kode SEBELUM refactor: `CAPTURE_LEGACY_FIXTURES=1 php artisan test --filter=CaptureLegacyPageContentFixturesTest`.
 * Hanya menulis fixture yang belum ada; menimpa butuh `CAPTURE_LEGACY_OVERWRITE=1`.
 */
class CaptureLegacyPageContentFixturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_capture_legacy_page_content_fixtures(): void
    {
        if (! env('CAPTURE_LEGACY_FIXTURES')) {
            $this->markTestSkipped('Set CAPTURE_LEGACY_FIXTURES=1 untuk menulis ulang fixture HTML lama.');
        }

        LegacyMarkup::seedDeterministicState();

        foreach (LegacyMarkup::FRAGMENTS as $name => [$path, $xpath]) {
            if (file_exists(LegacyMarkup::fixturePath($name)) && ! env('CAPTURE_LEGACY_OVERWRITE')) {
                continue;
            }

            $response = $this->get($path);
            $response->assertOk();

            file_put_contents(
                LegacyMarkup::fixturePath($name),
                LegacyMarkup::extract($response->getContent(), $xpath).PHP_EOL
            );
        }

        $this->assertCount(count(LegacyMarkup::FRAGMENTS), glob(base_path('tests/Fixtures/legacy-page-content/*.html')));
    }
}
