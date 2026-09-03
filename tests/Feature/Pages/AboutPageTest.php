<?php

namespace Tests\Feature\Pages;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AboutPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_about_page_renders_successfully(): void
    {
        $response = $this->get('/tentang-kami');

        $response->assertOk();
        $response->assertSee('Tentang Kami', escape: false);
    }
}
