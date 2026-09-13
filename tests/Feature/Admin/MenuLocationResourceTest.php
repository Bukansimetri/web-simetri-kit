<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\MenuLocationResource\Pages\CreateMenuLocation;
use App\Filament\Resources\MenuLocationResource\Pages\EditMenuLocation;
use App\Filament\Resources\MenuLocationResource\Pages\ListMenuLocations;
use App\Models\MenuLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MenuLocationResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_render_list_page(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ListMenuLocations::class)
            ->assertOk();
    }

    public function test_admin_can_create_menu_location_without_touching_code(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(CreateMenuLocation::class)
            ->fillForm([
                'name' => 'Menu Mobile',
                'slug' => 'menu-mobile',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('menu_locations', ['name' => 'Menu Mobile', 'slug' => 'menu-mobile']);
    }

    public function test_slug_must_be_unique(): void
    {
        MenuLocation::factory()->create(['slug' => 'footer']);

        Livewire::actingAs(User::factory()->create())
            ->test(CreateMenuLocation::class)
            ->fillForm([
                'name' => 'Footer Duplikat',
                'slug' => 'footer',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }

    public function test_admin_can_edit_menu_location(): void
    {
        $location = MenuLocation::factory()->create(['name' => 'Lama']);

        Livewire::actingAs(User::factory()->create())
            ->test(EditMenuLocation::class, ['record' => $location->getRouteKey()])
            ->fillForm(['name' => 'Baru'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Baru', $location->fresh()->name);
    }
}
