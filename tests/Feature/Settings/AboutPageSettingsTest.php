<?php

namespace Tests\Feature\Settings;

use App\Filament\Pages\AboutPageSettingsPage;
use App\Models\User;
use App\Settings\AboutPageSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AboutPageSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_about_page_text_fields(): void
    {
        config(['app.env' => 'local']);
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(AboutPageSettingsPage::class)
            ->fillForm([
                'hero_subtitle' => 'Subjudul hero baru.',
                'visi_heading' => 'Visi baru kami.',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $settings = app(AboutPageSettings::class);

        $this->assertSame('Subjudul hero baru.', $settings->hero_subtitle);
        $this->assertSame('Visi baru kami.', $settings->visi_heading);
    }

    public function test_admin_can_save_misi_nilai_and_trust_items(): void
    {
        config(['app.env' => 'local']);
        $user = User::factory()->create();

        $misiItems = array_fill(0, 5, ['title' => 'Judul Misi', 'description' => 'Deskripsi misi.']);
        $nilaiItems = array_fill(0, 3, ['icon' => 'eco', 'title' => 'Judul Nilai', 'description' => 'Deskripsi nilai.']);
        $trustItems = array_fill(0, 3, ['icon' => 'group', 'value' => '1.000+', 'label' => 'Label']);

        Livewire::actingAs($user)
            ->test(AboutPageSettingsPage::class)
            ->fillForm([
                'misi_items' => $misiItems,
                'nilai_items' => $nilaiItems,
                'trust_items' => $trustItems,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        app()->forgetInstance(AboutPageSettings::class);
        $settings = app(AboutPageSettings::class);

        $this->assertCount(5, $settings->misiItems());
        $this->assertCount(3, $settings->nilaiItems());
        $this->assertCount(3, $settings->trustItems());
        $this->assertSame('Judul Misi', $settings->misiItems()[0]['title']);
    }

    public function test_about_page_renders_updated_hero_subtitle_without_stale_cache(): void
    {
        config(['app.env' => 'local']);
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(AboutPageSettingsPage::class)
            ->fillForm(['hero_subtitle' => 'Teks hero yang sudah diperbarui.'])
            ->call('save')
            ->assertHasNoFormErrors();

        $response = $this->get('/tentang-kami');

        $response->assertOk();
        $response->assertSee('Teks hero yang sudah diperbarui.');
    }
}
