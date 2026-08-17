<?php

namespace Tests\Feature\Settings;

use App\Filament\Pages\BrandSettingsPage;
use App\Models\User;
use App\Settings\BrandSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BrandSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_brand_name_and_primary_color(): void
    {
        config(['app.env' => 'local']);
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(BrandSettingsPage::class)
            ->fillForm([
                'app_name' => 'Klien Baru',
                'primary_color' => '#112233',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $settings = app(BrandSettings::class);

        $this->assertSame('Klien Baru', $settings->app_name);
        $this->assertSame('#112233', $settings->primary_color);
    }

    public function test_admin_panel_falls_back_to_default_branding_when_unconfigured(): void
    {
        config(['app.env' => 'local']);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertOk();
        $response->assertSee(config('app.name'));
    }
}
