<?php

namespace Tests\Feature\Pages;

use App\Models\CustomPage;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LegalPageRenderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @param  array<string, mixed>  $legal
     */
    private function legalPage(array $legal = [], string $slug = 'dokumen-uji'): CustomPage
    {
        return CustomPage::factory()->legal(array_merge([
            'subtitle' => 'Subjudul dokumen uji.',
            'intro' => '<p>Paragraf pembuka uji.</p>',
            'highlight_title' => 'Sorotan Uji',
            'highlight_body' => 'Isi sorotan uji.',
            'sections' => [
                [
                    'label' => 'Pendahuluan',
                    'title' => 'Ketentuan Pertama',
                    'body' => '<p>Isi bagian pertama.</p><ul><li>Butir satu</li></ul>',
                    'cards' => [
                        ['icon' => 'bolt', 'title' => 'Kartu A', 'text' => 'Teks kartu A.'],
                        ['icon' => 'lock', 'title' => 'Kartu B', 'text' => 'Teks kartu B.'],
                        ['icon' => 'eco', 'title' => 'Kartu C', 'text' => 'Teks kartu C.'],
                    ],
                    'note' => '*Catatan kecil uji.',
                ],
                ['label' => null, 'title' => 'Ketentuan Kedua', 'body' => '<p>Isi bagian kedua.</p>', 'cards' => [], 'note' => null],
            ],
            'contact_title' => 'Ada pertanyaan?',
            'contact_text' => 'Hubungi kami.',
            'contact_whatsapp_label' => 'Chat WhatsApp',
            'contact_whatsapp_message' => 'Halo uji',
            'contact_email' => 'uji@contoh.test',
            'pdf_path' => null,
            'pdf_label' => null,
            'cta_title' => 'Siap Bertanya?',
            'cta_body' => 'Kami siap membantu.',
            'cta_button_label' => 'Hubungi Kami',
            'cta_button_url' => null,
        ], $legal))->create(['title' => 'Dokumen Uji', 'slug' => $slug]);
    }

    private function setWhatsapp(?string $number): void
    {
        $site = app(SiteSettings::class);
        $site->whatsapp_number = $number;
        $site->save();
    }

    public function test_legal_page_renders_hero_toc_sections_cards_note_and_cta(): void
    {
        $this->setWhatsapp('6281200001111');
        $this->legalPage();

        $response = $this->get('/halaman/dokumen-uji');

        $response->assertOk();
        $response->assertSee('Dokumen Uji');
        $response->assertSee('Subjudul dokumen uji.');
        $response->assertSee('Paragraf pembuka uji.', escape: false);
        $response->assertSee('Sorotan Uji');
        $response->assertSeeInOrder(['href="#bagian-1"', 'href="#bagian-2"'], escape: false);
        $response->assertSee('id="bagian-1"', escape: false);
        $response->assertSee('id="bagian-2"', escape: false);
        $response->assertSee('Pasal 01 · Pendahuluan', escape: false);
        $response->assertDontSee('Pasal 02', escape: false);
        $response->assertSeeInOrder(['Kartu A', 'Kartu B', 'Kartu C']);
        $response->assertSee('lg:grid-cols-3', escape: false);
        $response->assertSee('*Catatan kecil uji.');
        $response->assertSee('Siap Bertanya?');
        $response->assertSee('href="'.url('/kontak').'"', escape: false);
    }

    public function test_legal_page_has_no_date_label(): void
    {
        $this->legalPage();

        $this->get('/halaman/dokumen-uji')
            ->assertOk()
            ->assertDontSee('Berlaku Efektif')
            ->assertDontSee('Terakhir Diperbarui');
    }

    public function test_optional_blocks_are_hidden_when_empty(): void
    {
        $this->legalPage([
            'intro' => null,
            'highlight_title' => null,
            'highlight_body' => null,
            'contact_title' => null,
            'contact_text' => null,
            'cta_title' => null,
            'sections' => [],
        ]);

        $this->get('/halaman/dokumen-uji')
            ->assertOk()
            ->assertDontSee('Daftar Isi')
            ->assertDontSee('Sorotan Uji')
            ->assertDontSee('Ada pertanyaan?')
            ->assertDontSee('Siap Bertanya?');
    }

    public function test_section_without_cards_has_no_card_grid_and_cards_without_multiple_of_three_use_two_columns(): void
    {
        $this->legalPage(['sections' => [[
            'label' => null,
            'title' => 'Dua Kartu',
            'body' => null,
            'cards' => [
                ['icon' => 'bolt', 'title' => 'Kartu X', 'text' => null],
                ['icon' => null, 'title' => 'Kartu Y', 'text' => null],
            ],
            'note' => null,
        ]]]);

        $this->get('/halaman/dokumen-uji')
            ->assertOk()
            ->assertSee('Kartu X')
            ->assertDontSee('lg:grid-cols-3', escape: false);
    }

    public function test_whatsapp_button_uses_site_number_and_hides_when_number_missing(): void
    {
        $this->legalPage();
        $this->setWhatsapp('6281200001111');

        $this->get('/halaman/dokumen-uji')
            ->assertSee('https://wa.me/6281200001111?text=Halo%20uji', escape: false)
            ->assertSee('Chat WhatsApp')
            ->assertSee('mailto:uji@contoh.test', escape: false);

        $this->setWhatsapp(null);

        $this->get('/halaman/dokumen-uji')
            ->assertDontSee('wa.me')
            ->assertDontSee('Chat WhatsApp')
            ->assertSee('mailto:uji@contoh.test', escape: false);
    }

    public function test_rich_text_is_sanitized(): void
    {
        $this->legalPage([
            'intro' => '<p>Aman</p><script>alert(1)</script><img src="x" onerror="alert(2)">',
            'sections' => [[
                'label' => null,
                'title' => 'Bagian Berbahaya',
                'body' => '<p onclick="alert(3)">Teks</p><a href="javascript:alert(4)">klik</a>',
                'cards' => [],
                'note' => null,
            ]],
        ]);

        $html = $this->get('/halaman/dokumen-uji')->assertOk()->getContent();

        $this->assertStringNotContainsString('<script>alert', $html);
        $this->assertStringNotContainsString('onerror', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('javascript:alert', $html);
        $this->assertStringContainsString('Aman', $html);
    }

    public function test_cta_button_rejects_unsafe_urls(): void
    {
        $this->legalPage(['cta_button_url' => 'javascript:alert(1)']);

        $html = $this->get('/halaman/dokumen-uji')->assertOk()->getContent();

        $this->assertStringNotContainsString('javascript:alert', $html);
        $this->assertStringContainsString('href="'.url('/kontak').'"', $html);
    }

    public function test_pdf_button_shows_only_when_the_file_exists(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('legal-pages/pdf/dokumen.pdf', '%PDF-1.4');

        $this->legalPage(['pdf_path' => 'legal-pages/pdf/dokumen.pdf']);

        $this->get('/halaman/dokumen-uji')
            ->assertOk()
            ->assertSee('Unduh Dokumen (PDF)')
            ->assertSee(Storage::disk('public')->url('legal-pages/pdf/dokumen.pdf'), escape: false)
            ->assertSee('download', escape: false);
    }

    public function test_pdf_button_uses_custom_label(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('legal-pages/pdf/dokumen.pdf', '%PDF-1.4');

        $this->legalPage(['pdf_path' => 'legal-pages/pdf/dokumen.pdf', 'pdf_label' => 'Unduh Salinan']);

        $this->get('/halaman/dokumen-uji')->assertSee('Unduh Salinan');
    }

    public function test_pdf_button_is_hidden_without_pdf_or_when_file_is_missing(): void
    {
        Storage::fake('public');

        $this->legalPage(['pdf_path' => null, 'pdf_label' => 'Unduh Salinan']);
        $this->get('/halaman/dokumen-uji')->assertOk()->assertDontSee('Unduh Salinan');

        CustomPage::query()->where('slug', 'dokumen-uji')->delete();
        $this->legalPage(['pdf_path' => 'legal-pages/pdf/hilang.pdf', 'pdf_label' => 'Unduh Salinan']);
        $this->get('/halaman/dokumen-uji')->assertOk()->assertDontSee('Unduh Salinan');
    }

    public function test_standard_template_is_unchanged(): void
    {
        CustomPage::factory()->create([
            'slug' => 'biasa',
            'content' => '<h2>Judul Biasa</h2><p>Isi biasa.</p>',
        ]);

        $this->get('/halaman/biasa')
            ->assertOk()
            ->assertSee('<h2>Judul Biasa</h2>', escape: false)
            ->assertDontSee('Daftar Isi');
    }

    public function test_installed_default_pages_render_with_toc(): void
    {
        $this->get('/halaman/kebijakan-privasi')
            ->assertOk()
            ->assertSee('Daftar Isi')
            ->assertSee('Informasi yang Kami Kumpulkan')
            ->assertSee('id="bagian-7"', escape: false)
            ->assertDontSee('Terakhir Diperbarui');

        $this->get('/halaman/syarat-ketentuan')
            ->assertOk()
            ->assertSee('Pasal 05 · Jaminan Kualitas', escape: false)
            ->assertSee('Garansi Performa Panel')
            ->assertDontSee('Berlaku Efektif');
    }
}
