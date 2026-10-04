<?php

namespace Tests\Feature\Public;

use App\Filament\Pages\SiteSettingsPage;
use App\Models\User;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Teks deskripsi footer bisa diedit dari Pengaturan Umum (spec 031 FR-012, FR-013).
 */
class FooterDescriptionTest extends TestCase
{
    use RefreshDatabase;

    private const DEFAULT_TEXT = 'Menginspirasi masa depan berkelanjutan melalui inovasi tenaga surya yang elegan dan presisi tinggi untuk masyarakat Indonesia.';

    public function test_default_text_is_shown_after_install(): void
    {
        $this->get('/')->assertOk()->assertSee(self::DEFAULT_TEXT);
    }

    public function test_configured_text_replaces_the_default_and_keeps_line_breaks(): void
    {
        $settings = app(SiteSettings::class);
        $settings->footer_description = "Baris satu.\nBaris dua.";
        $settings->save();

        $response = $this->get('/')->assertOk();

        $response->assertDontSee(self::DEFAULT_TEXT);
        $this->assertStringContainsString('Baris satu.<br />', $response->getContent());
        $response->assertSee('Baris dua.');
    }

    public function test_html_in_the_text_is_escaped(): void
    {
        $settings = app(SiteSettings::class);
        $settings->footer_description = '<script>alert(1)</script> Teks';
        $settings->save();

        $this->get('/')->assertOk()->assertDontSee('<script>alert(1)</script>', escape: false);
    }

    public function test_empty_text_hides_the_paragraph(): void
    {
        $settings = app(SiteSettings::class);
        $settings->footer_description = null;
        $settings->save();

        $response = $this->get('/')->assertOk();

        $response->assertDontSee(self::DEFAULT_TEXT);
        $this->assertStringNotContainsString('<p class="text-white/60 text-sm leading-relaxed"></p>', $response->getContent());
    }

    public function test_admin_can_edit_the_footer_description_in_general_settings(): void
    {
        config(['app.env' => 'local']);

        Livewire::actingAs(User::factory()->create())
            ->test(SiteSettingsPage::class)
            ->assertFormFieldExists('footer_description')
            ->fillForm([
                'footer_description' => 'Deskripsi footer baru.',
                'default_language' => 'id',
                'timezone' => 'Asia/Jakarta',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Deskripsi footer baru.', app(SiteSettings::class)->footer_description);
    }

    public function test_description_longer_than_500_characters_is_rejected(): void
    {
        config(['app.env' => 'local']);

        Livewire::actingAs(User::factory()->create())
            ->test(SiteSettingsPage::class)
            ->fillForm([
                'footer_description' => str_repeat('a', 501),
                'default_language' => 'id',
                'timezone' => 'Asia/Jakarta',
            ])
            ->call('save')
            ->assertHasFormErrors(['footer_description' => 'max']);
    }
}
