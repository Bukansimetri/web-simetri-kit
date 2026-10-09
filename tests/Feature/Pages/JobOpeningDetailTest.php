<?php

namespace Tests\Feature\Pages;

use App\Models\JobOpening;
use App\Settings\SiteSettings;
use App\Support\PageContent\JobDescriptionConverter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobOpeningDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setCareerModule(true);
    }

    private function setCareerModule(bool $enabled): void
    {
        $site = app(SiteSettings::class);
        $site->career_module_enabled = $enabled;
        $site->save();
    }

    public function test_detail_shows_full_description_location_type_and_apply_button(): void
    {
        $description = JobDescriptionConverter::toHtml("Baris pertama tugas.\nBaris kedua tanggung jawab.\n\n".str_repeat('Kualifikasi panjang. ', 60));
        $job = JobOpening::factory()->create([
            'title' => 'Teknisi Instalasi',
            'location' => 'Bandung',
            'employment_type' => 'full-time',
            'description' => $description,
        ]);

        $response = $this->get(route('karir.show', $job))->assertOk();

        $response->assertSee('Teknisi Instalasi', escape: false)
            ->assertSee('Bandung')
            ->assertSee('Baris pertama tugas.')
            ->assertSee('Baris kedua tanggung jawab.')
            ->assertSee(trim(str_repeat('Kualifikasi panjang. ', 60)), escape: false)
            ->assertSee('Lamar Sekarang')
            ->assertSee(url('/kontak'))
            ->assertSee('Kembali ke Karir');
        $this->assertStringContainsString('<br>', $response->getContent());
    }

    public function test_description_is_escaped(): void
    {
        $job = JobOpening::factory()->create(['description' => '<script>alert(1)</script> Syarat']);

        $this->get(route('karir.show', $job))->assertOk()
            ->assertDontSee('<script>alert(1)</script>', escape: false);
    }

    public function test_inactive_unknown_and_disabled_module_return_404(): void
    {
        $inactive = JobOpening::factory()->create(['is_active' => false]);
        $active = JobOpening::factory()->create();

        $this->get(route('karir.show', $inactive))->assertNotFound();
        $this->get('/karir/999999')->assertNotFound();

        $this->setCareerModule(false);

        $this->get(route('karir.show', $active))->assertNotFound();
    }

    public function test_page_has_title_and_meta_description_from_the_job(): void
    {
        $job = JobOpening::factory()->create([
            'title' => 'Admin Proyek',
            'description' => '<p>Mengelola dokumen proyek PLTS.</p>',
        ]);

        $this->get(route('karir.show', $job))->assertOk()
            ->assertSee('<title>Admin Proyek', escape: false)
            ->assertSee('content="Mengelola dokumen proyek PLTS."', escape: false);
    }

    public function test_career_list_cards_link_to_the_detail_page(): void
    {
        $job = JobOpening::factory()->create(['title' => 'Surveyor']);

        $this->get('/karir')->assertOk()
            ->assertSee(route('karir.show', $job))
            ->assertSee('Lihat Detail')
            ->assertSee('Lamar Sekarang');
    }

    public function test_open_positions_heading_is_centered_like_other_section_headings(): void
    {
        $html = $this->get('/karir')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<div class="text-center mb-12">\s*<h2 class="font-headline-xl text-3xl md:text-4xl [^"]*text-primary-container">Posisi Terbuka</h2>#', $html);
        $this->assertMatchesRegularExpression('#<div class="text-center mb-12">\s*<h2 class="font-headline-xl text-3xl md:text-4xl [^"]*text-primary-container">Mengapa Bergabung#', $html);
    }
}
