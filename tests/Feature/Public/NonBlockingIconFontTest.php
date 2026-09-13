<?php

namespace Tests\Feature\Public;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NonBlockingIconFontTest extends TestCase
{
    use RefreshDatabase;

    public function test_material_symbols_stylesheet_is_loaded_non_blocking_with_noscript_fallback(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('media="print"', escape: false);
        $response->assertSee('onload="this.media=\'all\'"', escape: false);
        $response->assertSee('<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined', escape: false);
        $response->assertSee('material-symbols-outlined', escape: false);
    }
}
