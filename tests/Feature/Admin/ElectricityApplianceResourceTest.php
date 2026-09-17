<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\ElectricityApplianceResource\Pages\ManageElectricityAppliances;
use App\Models\ElectricityAppliance;
use App\Models\User;
use Filament\Tables\Actions\EditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ElectricityApplianceResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_render_page(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ManageElectricityAppliances::class)
            ->assertOk();
    }

    public function test_admin_can_create_appliance(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ManageElectricityAppliances::class)
            ->mountAction('create')
            ->setActionData([
                'name' => 'Mesin Cuci',
                'slug' => 'mesin-cuci',
                'icon' => 'local_laundry_service',
                'watt' => 350,
                'order' => 7,
                'is_active' => true,
            ])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('electricity_appliances', [
            'slug' => 'mesin-cuci',
            'name' => 'Mesin Cuci',
            'watt' => 350,
        ]);
    }

    public function test_slug_must_be_unique(): void
    {
        ElectricityAppliance::factory()->create(['slug' => 'tv-baru']);

        Livewire::actingAs(User::factory()->create())
            ->test(ManageElectricityAppliances::class)
            ->mountAction('create')
            ->setActionData([
                'name' => 'TV Baru',
                'slug' => 'tv-baru',
                'icon' => 'tv',
                'watt' => 100,
            ])
            ->callMountedAction()
            ->assertHasActionErrors(['slug']);
    }

    public function test_required_fields_are_validated(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ManageElectricityAppliances::class)
            ->mountAction('create')
            ->setActionData([
                'name' => '',
                'slug' => '',
                'watt' => null,
            ])
            ->callMountedAction()
            ->assertHasActionErrors(['name', 'slug', 'watt']);
    }

    public function test_admin_can_edit_appliance(): void
    {
        $appliance = ElectricityAppliance::factory()->create(['watt' => 100]);

        Livewire::actingAs(User::factory()->create())
            ->test(ManageElectricityAppliances::class)
            ->callTableAction(EditAction::class, $appliance, data: [
                'watt' => 250,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertSame(250, $appliance->fresh()->watt);
    }

    public function test_admin_can_delete_appliance(): void
    {
        $appliance = ElectricityAppliance::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(ManageElectricityAppliances::class)
            ->callTableAction('delete', $appliance);

        $this->assertDatabaseMissing('electricity_appliances', ['id' => $appliance->id]);
    }

    public function test_inactive_appliance_is_excluded_from_active_catalog(): void
    {
        ElectricityAppliance::factory()->create(['slug' => 'aktif', 'is_active' => true]);
        ElectricityAppliance::factory()->create(['slug' => 'nonaktif', 'is_active' => false]);

        $catalog = ElectricityAppliance::activeCatalog();

        $this->assertTrue($catalog->contains('slug', 'aktif'));
        $this->assertFalse($catalog->contains('slug', 'nonaktif'));
    }
}
