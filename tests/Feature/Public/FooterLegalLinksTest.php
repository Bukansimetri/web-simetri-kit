<?php

namespace Tests\Feature\Public;

use App\Models\CustomPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FooterLegalLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_footer_legal_links_point_to_custom_pages(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(url('/halaman/kebijakan-privasi'), escape: false);
        $response->assertSee(url('/halaman/syarat-ketentuan'), escape: false);
        $response->assertDontSee('href="'.url('/tentang-kami').'">Kebijakan Privasi', escape: false);
    }

    public function test_legal_page_returns_404_before_admin_creates_it(): void
    {
        $this->get('/halaman/kebijakan-privasi')->assertNotFound();
    }

    public function test_legal_page_returns_content_once_created(): void
    {
        CustomPage::factory()->create([
            'title' => 'Kebijakan Privasi',
            'slug' => 'kebijakan-privasi',
            'content' => '<p>Konten kebijakan.</p>',
        ]);

        $this->get('/halaman/kebijakan-privasi')
            ->assertOk()
            ->assertSee('Konten kebijakan.', escape: false);
    }

    public function test_tentang_kami_page_is_unchanged(): void
    {
        $this->get('/tentang-kami')->assertOk()->assertSee('Tentang Kami', escape: false);
    }
}
