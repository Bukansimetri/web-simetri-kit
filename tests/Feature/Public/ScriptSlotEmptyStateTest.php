<?php

namespace Tests\Feature\Public;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Slot yang dikosongkan admin tidak meninggalkan elemen kosong pada halaman
 * (FR-047, contracts §4).
 */
class ScriptSlotEmptyStateTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_empty_style_or_script_tags_when_slots_are_empty(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertDontSee('<style></style>', escape: false);
        $response->assertDontSee('<script></script>', escape: false);
    }
}
