<?php

namespace Tests\Feature\Settings;

use App\Filament\Pages\SiteSettingsPage;
use App\Models\User;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Cakupan setara bagian identitas tests/Feature/Settings/BrandSettingsTest.php
 * sebelum Brand Settings dibubarkan (spec 023-site-settings FR-063, FR-070).
 * Pembuktian FR-003/FR-007 dsb. yang lebih lengkap ada di test khusus US1.
 */
class SiteSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_site_name(): void
    {
        config(['app.env' => 'local']);
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(SiteSettingsPage::class)
            ->fillForm([
                'site_name' => 'Klien Baru',
                'default_language' => 'id',
                'timezone' => 'Asia/Jakarta',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $settings = app(SiteSettings::class);

        $this->assertSame('Klien Baru', $settings->site_name);
    }

    public function test_admin_cannot_save_language_outside_curated_list(): void
    {
        config(['app.env' => 'local']);
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(SiteSettingsPage::class)
            ->fillForm([
                'default_language' => 'fr',
                'timezone' => 'Asia/Jakarta',
            ])
            ->call('save')
            ->assertHasFormErrors(['default_language']);
    }
}
