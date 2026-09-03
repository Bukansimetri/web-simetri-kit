<?php

namespace Tests\Feature\Pages;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders_successfully(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }

    public function test_home_page_displays_key_sections(): void
    {
        Product::factory()->create(['name' => 'SUOER Mono X-Pro 550W']);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Hitung Estimasi Penghematan', escape: false);
        $response->assertSee('SUOER Mono X-Pro 550W', escape: false);
    }
}
