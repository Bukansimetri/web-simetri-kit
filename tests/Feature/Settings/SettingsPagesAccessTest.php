<?php

namespace Tests\Feature\Settings;

use App\Filament\Pages\AppearanceSettingsPage;
use App\Filament\Pages\ScriptSettingsPage;
use App\Filament\Pages\SeoSettingsPage;
use App\Filament\Pages\SiteSettingsPage;
use App\Filament\Pages\SocialSettingsPage;
use App\Models\User;
use App\Settings\AppearanceSettings;
use App\Settings\ScriptSettings;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Membuktikan FR-070 (lima halaman pengaturan, masing-masing dapat disimpan
 * sendiri) dan FR-045 (Scripts & Analytics hanya untuk super_admin).
 */
class SettingsPagesAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_all_five_settings_pages(): void
    {
        Role::create(['name' => 'super_admin']);
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        foreach ([SiteSettingsPage::class, AppearanceSettingsPage::class, SeoSettingsPage::class, SocialSettingsPage::class, ScriptSettingsPage::class] as $page) {
            Livewire::actingAs($user)->test($page)->assertSuccessful();
        }
    }

    public function test_saving_one_settings_page_does_not_change_another(): void
    {
        Role::create(['name' => 'super_admin']);
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        Livewire::actingAs($user)
            ->test(SiteSettingsPage::class)
            ->fillForm(['site_name' => 'Nama Situs Awal'])
            ->call('save')
            ->assertHasNoFormErrors();

        Livewire::actingAs($user)
            ->test(AppearanceSettingsPage::class)
            ->fillForm(['primary_color' => '#010101'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Nama Situs Awal', app(SiteSettings::class)->site_name);
        $this->assertSame('#010101', app(AppearanceSettings::class)->primary_color);
    }

    public function test_regular_admin_cannot_see_scripts_and_analytics_menu(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->assertFalse(ScriptSettingsPage::shouldRegisterNavigation());
    }

    public function test_super_admin_can_see_scripts_and_analytics_menu(): void
    {
        Role::create(['name' => 'super_admin']);
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        $this->actingAs($user);

        $this->assertTrue(ScriptSettingsPage::shouldRegisterNavigation());
    }

    public function test_regular_admin_is_denied_opening_scripts_and_analytics_page_directly(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(ScriptSettingsPage::class)->assertForbidden();
    }

    public function test_super_admin_can_save_scripts_and_analytics(): void
    {
        Role::create(['name' => 'super_admin']);
        $user = User::factory()->create();
        $user->assignRole('super_admin');

        Livewire::actingAs($user)
            ->test(ScriptSettingsPage::class)
            ->fillForm([
                'head_scripts' => '<!-- test head script -->',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('<!-- test head script -->', app(ScriptSettings::class)->head_scripts);
    }
}
