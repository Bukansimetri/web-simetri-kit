<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\MenuItemResource\Pages\CreateMenuItem;
use App\Filament\Resources\MenuItemResource\Pages\EditMenuItem;
use App\Filament\Resources\MenuItemResource\Pages\ListMenuItems;
use App\Filament\Resources\MenuItemResource\RelationManagers\ChildrenRelationManager;
use App\Models\CustomPage;
use App\Models\MenuItem;
use App\Models\MenuLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MenuItemResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_render_list_page(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ListMenuItems::class)
            ->assertOk();
    }

    public function test_admin_can_create_menu_item_without_link(): void
    {
        $location = MenuLocation::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(CreateMenuItem::class)
            ->fillForm([
                'menu_location_id' => $location->id,
                'label' => 'Grup Layanan',
                'link_type' => MenuItem::LINK_TYPE_NONE,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('menu_items', [
            'label' => 'Grup Layanan',
            'link_type' => MenuItem::LINK_TYPE_NONE,
        ]);
    }

    public function test_admin_can_create_menu_item_with_external_link(): void
    {
        $location = MenuLocation::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(CreateMenuItem::class)
            ->fillForm([
                'menu_location_id' => $location->id,
                'label' => 'Promo',
                'link_type' => MenuItem::LINK_TYPE_EXTERNAL,
                'external_url' => 'https://example.com/promo',
                'open_in_new_tab' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('menu_items', [
            'label' => 'Promo',
            'external_url' => 'https://example.com/promo',
            'open_in_new_tab' => true,
        ]);
    }

    public function test_external_url_is_required_when_link_type_is_external(): void
    {
        $location = MenuLocation::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(CreateMenuItem::class)
            ->fillForm([
                'menu_location_id' => $location->id,
                'label' => 'Promo',
                'link_type' => MenuItem::LINK_TYPE_EXTERNAL,
                'external_url' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['external_url']);
    }

    public function test_admin_can_create_menu_item_with_internal_link(): void
    {
        $location = MenuLocation::factory()->create();
        $page = CustomPage::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(CreateMenuItem::class)
            ->fillForm([
                'menu_location_id' => $location->id,
                'label' => 'Tentang',
                'link_type' => MenuItem::LINK_TYPE_INTERNAL,
                'linkable_type' => 'custom-page',
                'linkable_id' => $page->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('menu_items', [
            'label' => 'Tentang',
            'linkable_type' => 'custom-page',
            'linkable_id' => $page->id,
        ]);
    }

    public function test_admin_can_edit_menu_item(): void
    {
        $item = MenuItem::factory()->create(['label' => 'Lama']);

        Livewire::actingAs(User::factory()->create())
            ->test(EditMenuItem::class, ['record' => $item->getRouteKey()])
            ->fillForm(['label' => 'Baru'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Baru', $item->fresh()->label);
    }

    public function test_admin_can_delete_menu_item(): void
    {
        $item = MenuItem::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(ListMenuItems::class)
            ->callTableAction('delete', $item);

        $this->assertDatabaseMissing('menu_items', ['id' => $item->id]);
    }

    public function test_admin_can_deactivate_menu_item_without_deleting_it(): void
    {
        $item = MenuItem::factory()->create(['is_active' => true]);

        Livewire::actingAs(User::factory()->create())
            ->test(ListMenuItems::class)
            ->call('updateTableColumnState', 'is_active', $item->getKey(), false);

        $this->assertDatabaseHas('menu_items', ['id' => $item->id, 'is_active' => false]);
    }

    public function test_broken_internal_link_shows_warning_in_table(): void
    {
        $page = CustomPage::factory()->create();
        $item = MenuItem::factory()->internal($page)->create(['label' => 'Rusak']);
        $page->delete();

        Livewire::actingAs(User::factory()->create())
            ->test(ListMenuItems::class)
            ->assertTableColumnStateSet('link_summary', 'Tautan tidak valid', record: $item->fresh());
    }

    public function test_admin_can_add_sub_menu_item_via_relation_manager(): void
    {
        $parent = MenuItem::factory()->create(['label' => 'Layanan', 'link_type' => MenuItem::LINK_TYPE_NONE]);

        Livewire::actingAs(User::factory()->create())
            ->test(ChildrenRelationManager::class, [
                'ownerRecord' => $parent,
                'pageClass' => EditMenuItem::class,
            ])
            ->callTableAction('create', data: [
                'label' => 'Panel Residensial',
                'link_type' => MenuItem::LINK_TYPE_NONE,
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('menu_items', [
            'label' => 'Panel Residensial',
            'parent_id' => $parent->id,
            'menu_location_id' => $parent->menu_location_id,
        ]);
    }
}
