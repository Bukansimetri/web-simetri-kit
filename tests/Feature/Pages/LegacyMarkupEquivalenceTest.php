<?php

namespace Tests\Feature\Pages;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\LegacyMarkup;
use Tests\TestCase;

/**
 * Markup section & CTA harus identik dengan HTML lama (FR-012a, SC-001).
 * Jika gagal, perbaiki Blade — jangan membuat ulang fixture.
 */
class LegacyMarkupEquivalenceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string}>
     */
    public static function fragments(): array
    {
        return array_combine(
            array_keys(LegacyMarkup::FRAGMENTS),
            array_map(fn (string $name): array => [$name], array_keys(LegacyMarkup::FRAGMENTS)),
        );
    }

    #[DataProvider('fragments')]
    public function test_fragment_markup_is_identical_to_legacy(string $name): void
    {
        [$path, $xpath] = LegacyMarkup::FRAGMENTS[$name];

        LegacyMarkup::seedDeterministicState();

        $response = $this->get($path);
        $response->assertOk();

        $this->assertSame(
            trim(file_get_contents(LegacyMarkup::fixturePath($name))),
            LegacyMarkup::extract($response->getContent(), $xpath),
        );
    }
}
