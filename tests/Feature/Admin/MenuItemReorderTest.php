<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\MenuItemResource\Pages\ListMenuItems;
use App\Models\MenuItem;
use App\Models\MenuLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MenuItemReorderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_reorder_menu_items_via_drag_and_drop(): void
    {
        $location = MenuLocation::factory()->create();

        $first = MenuItem::factory()->create(['menu_location_id' => $location->id, 'label' => 'A', 'order_column' => 0]);
        $second = MenuItem::factory()->create(['menu_location_id' => $location->id, 'label' => 'B', 'order_column' => 1]);
        $third = MenuItem::factory()->create(['menu_location_id' => $location->id, 'label' => 'C', 'order_column' => 2]);

        Livewire::actingAs(User::factory()->create())
            ->test(ListMenuItems::class, ['tableFilters' => ['menu_location_id' => ['value' => $location->id]]])
            ->call('reorderTable', [$third->id, $first->id, $second->id]);

        $ordered = MenuItem::where('menu_location_id', $location->id)
            ->orderBy('order_column')
            ->pluck('id')
            ->all();

        $this->assertSame([$third->id, $first->id, $second->id], $ordered);
    }

    public function test_reordering_one_location_does_not_affect_another(): void
    {
        $locationA = MenuLocation::factory()->create();
        $locationB = MenuLocation::factory()->create();

        $a1 = MenuItem::factory()->create(['menu_location_id' => $locationA->id, 'order_column' => 0]);
        $a2 = MenuItem::factory()->create(['menu_location_id' => $locationA->id, 'order_column' => 1]);
        $b1 = MenuItem::factory()->create(['menu_location_id' => $locationB->id, 'order_column' => 0]);
        $b2 = MenuItem::factory()->create(['menu_location_id' => $locationB->id, 'order_column' => 1]);

        Livewire::actingAs(User::factory()->create())
            ->test(ListMenuItems::class, ['tableFilters' => ['menu_location_id' => ['value' => $locationA->id]]])
            ->call('reorderTable', [$a2->id, $a1->id]);

        $this->assertSame([$b1->id, $b2->id], MenuItem::where('menu_location_id', $locationB->id)->orderBy('order_column')->pluck('id')->all());
        $this->assertSame([$a2->id, $a1->id], MenuItem::where('menu_location_id', $locationA->id)->orderBy('order_column')->pluck('id')->all());
    }

    public function test_frontend_navbar_reflects_updated_order(): void
    {
        $location = MenuLocation::factory()->create(['slug' => 'navbar-utama']);
        $first = MenuItem::factory()->external('https://example.com/a')->create(['menu_location_id' => $location->id, 'label' => 'Alpha', 'order_column' => 0]);
        $second = MenuItem::factory()->external('https://example.com/b')->create(['menu_location_id' => $location->id, 'label' => 'Beta', 'order_column' => 1]);

        Livewire::actingAs(User::factory()->create())
            ->test(ListMenuItems::class, ['tableFilters' => ['menu_location_id' => ['value' => $location->id]]])
            ->call('reorderTable', [$second->id, $first->id]);

        $content = $this->get('/')->getContent();

        $this->assertLessThan(
            strpos($content, 'Alpha'),
            strpos($content, 'Beta'),
        );
    }
}
