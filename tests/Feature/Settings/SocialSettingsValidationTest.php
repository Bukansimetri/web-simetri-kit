<?php

namespace Tests\Feature\Settings;

use App\Filament\Pages\SocialSettingsPage;
use App\Models\User;
use App\Settings\SocialSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Alamat profil yang bukan URL sah ditolak dengan pesan yang menyebut
 * platform bersangkutan (FR-021).
 */
class SocialSettingsValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_facebook_url_is_rejected(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(SocialSettingsPage::class)
            ->fillForm(['facebook_url' => 'bukan alamat web'])
            ->call('save')
            ->assertHasFormErrors(['facebook_url']);
    }

    public function test_valid_social_urls_are_saved(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(SocialSettingsPage::class)
            ->fillForm([
                'instagram_url' => 'https://instagram.com/klienuji',
                'linkedin_url' => 'https://linkedin.com/company/klienuji',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $settings = app(SocialSettings::class);

        $this->assertSame('https://instagram.com/klienuji', $settings->instagram_url);
        $this->assertSame('https://linkedin.com/company/klienuji', $settings->linkedin_url);
    }
}
