<?php

namespace Tests\Feature\Pages;

use App\Models\CustomPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomPagePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_custom_page_show_displays_title_and_content(): void
    {
        $page = CustomPage::factory()->create([
            'title' => 'Kebijakan Privasi',
            'slug' => 'kebijakan-privasi',
            'content' => '<h2>Pendahuluan</h2><p>Kami menghargai privasi Anda.</p>',
        ]);

        $response = $this->get('/halaman/kebijakan-privasi');

        $response->assertOk();
        $response->assertSee('Kebijakan Privasi', escape: false);
        $response->assertSee('Kami menghargai privasi Anda.', escape: false);
        $response->assertSee('<h2>Pendahuluan</h2>', escape: false);
    }

    public function test_custom_page_show_returns_404_for_unknown_slug(): void
    {
        $this->get('/halaman/tidak-ada')->assertNotFound();
    }

    public function test_deleted_custom_page_slug_returns_404(): void
    {
        $page = CustomPage::factory()->create(['slug' => 'sementara']);
        $this->get('/halaman/sementara')->assertOk();

        $page->delete();

        $this->get('/halaman/sementara')->assertNotFound();
    }
}
