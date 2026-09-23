<?php

namespace Tests\Feature\Public;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Instalasi bersih (belum pernah disentuh admin) MUST tidak menampilkan
 * data identitas klien mana pun (FR-009, SC-002).
 */
class NoHardcodedClientDataTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array<int, string>
     */
    private const CLIENT_SPECIFIC_MARKERS = [
        'Jl. Jend. Sudirman Kav. 52-53',
        'hello@suoer.id',
        '(021) 555-0123',
    ];

    public function test_fresh_install_homepage_has_no_client_specific_contact_data(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        foreach (self::CLIENT_SPECIFIC_MARKERS as $marker) {
            $response->assertDontSee($marker, escape: false);
        }
    }

    public function test_fresh_install_contact_page_has_no_client_specific_contact_data(): void
    {
        $response = $this->get('/kontak');

        $response->assertOk();

        foreach (self::CLIENT_SPECIFIC_MARKERS as $marker) {
            $response->assertDontSee($marker, escape: false);
        }
    }
}
