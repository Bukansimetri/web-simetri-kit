<?php

namespace Tests\Feature\Settings;

use App\Filament\Pages\AppearanceSettingsPage;
use App\Models\User;
use App\Settings\AppearanceSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cakupan setara tests/Feature/Settings/BrandSettingsTest.php sebelum
 * Brand Settings dibubarkan (spec 023-site-settings FR-063, FR-070).
 */
class AppearanceSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_primary_color(): void
    {
        config(['app.env' => 'local']);
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(AppearanceSettingsPage::class)
            ->fillForm([
                'primary_color' => '#112233',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $settings = app(AppearanceSettings::class);

        $this->assertSame('#112233', $settings->primary_color);
    }

    public function test_admin_panel_falls_back_to_default_branding_when_unconfigured(): void
    {
        config(['app.env' => 'local']);
        Role::create(['name' => 'super_admin']);

        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $response = $this->actingAs($user)->get('/admin');

        $response->assertOk();
        $response->assertSee(config('app.name'));
    }

    public function test_theme_settings_default_to_luminous_azure_when_unconfigured(): void
    {
        config(['app.env' => 'local']);
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(AppearanceSettingsPage::class)
            ->assertSet('data.secondary_color', AppearanceSettings::DEFAULT_SECONDARY_COLOR)
            ->assertSet('data.font_heading', AppearanceSettings::DEFAULT_FONT_HEADING)
            ->assertSet('data.font_body', AppearanceSettings::DEFAULT_FONT_BODY);
    }

    public function test_admin_can_save_secondary_color_and_fonts(): void
    {
        config(['app.env' => 'local']);
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(AppearanceSettingsPage::class)
            ->fillForm([
                'secondary_color' => '#ab12cd',
                'font_heading' => 'Inter',
                'font_body' => 'Poppins',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $settings = app(AppearanceSettings::class);

        $this->assertSame('#ab12cd', $settings->secondary_color);
        $this->assertSame('Inter', $settings->font_heading);
        $this->assertSame('Poppins', $settings->font_body);
    }

    public function test_admin_cannot_save_font_outside_curated_list(): void
    {
        config(['app.env' => 'local']);
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(AppearanceSettingsPage::class)
            ->fillForm([
                'font_heading' => 'Comic Sans MS',
            ])
            ->call('save')
            ->assertHasFormErrors(['font_heading']);

        $this->assertNull(app(AppearanceSettings::class)->font_heading);
    }

    public function test_theme_settings_fall_back_to_default_when_cleared(): void
    {
        config(['app.env' => 'local']);
        $user = User::factory()->create();

        $settings = app(AppearanceSettings::class);
        $settings->secondary_color = '#ab12cd';
        $settings->font_heading = 'Inter';
        $settings->save();

        Livewire::actingAs($user)
            ->test(AppearanceSettingsPage::class)
            ->fillForm([
                'secondary_color' => null,
                'font_heading' => null,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        app()->forgetInstance(AppearanceSettings::class);
        $settings = app(AppearanceSettings::class);

        $this->assertNull($settings->secondary_color);
        $this->assertNull($settings->font_heading);
    }
}
