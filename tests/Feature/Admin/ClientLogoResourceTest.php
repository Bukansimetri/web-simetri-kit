<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\ClientLogoResource\Pages\CreateClientLogo;
use App\Filament\Resources\ClientLogoResource\Pages\EditClientLogo;
use App\Filament\Resources\ClientLogoResource\Pages\ListClientLogos;
use App\Models\ClientLogo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ClientLogoResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_render_list_page(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ListClientLogos::class)
            ->assertOk();
    }

    public function test_admin_can_create_logo_without_link(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())
            ->test(CreateClientLogo::class)
            ->fillForm([
                'company_name' => 'PT Sinar Mas',
                'logo_path' => UploadedFile::fake()->image('logo.png'),
                'order' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $logo = ClientLogo::where('company_name', 'PT Sinar Mas')->first();

        $this->assertNotNull($logo);
        $this->assertNull($logo->link_url);
        $this->assertSame('webp', pathinfo($logo->logo_path, PATHINFO_EXTENSION));
        Storage::disk('public')->assertExists($logo->logo_path);
    }

    public function test_valid_link_url_is_accepted(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())
            ->test(CreateClientLogo::class)
            ->fillForm([
                'company_name' => 'Tokopedia',
                'logo_path' => UploadedFile::fake()->image('logo.png'),
                'link_url' => 'https://tokopedia.com',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('client_logos', [
            'company_name' => 'Tokopedia',
            'link_url' => 'https://tokopedia.com',
        ]);
    }

    public function test_link_url_without_scheme_is_rejected(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())
            ->test(CreateClientLogo::class)
            ->fillForm([
                'company_name' => 'Tokopedia',
                'logo_path' => UploadedFile::fake()->image('logo.png'),
                'link_url' => 'tokopedia.com',
            ])
            ->call('create')
            ->assertHasFormErrors(['link_url']);
    }

    public function test_required_fields_are_validated(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(CreateClientLogo::class)
            ->fillForm([
                'company_name' => '',
                'logo_path' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['company_name', 'logo_path']);
    }

    public function test_admin_can_edit_logo(): void
    {
        Storage::fake('public');
        $logo = ClientLogo::factory()->create(['order' => 5]);
        Storage::disk('public')->put($logo->logo_path, 'fake-bytes');

        Livewire::actingAs(User::factory()->create())
            ->test(EditClientLogo::class, ['record' => $logo->getRouteKey()])
            ->fillForm(['order' => 2])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(2, $logo->fresh()->order);
    }

    public function test_admin_can_delete_logo(): void
    {
        $logo = ClientLogo::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(ListClientLogos::class)
            ->callTableAction('delete', $logo);

        $this->assertDatabaseMissing('client_logos', ['id' => $logo->id]);
    }
}
