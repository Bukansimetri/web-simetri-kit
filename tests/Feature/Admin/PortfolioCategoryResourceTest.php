<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\PortfolioCategoryResource\Pages\CreatePortfolioCategory;
use App\Filament\Resources\PortfolioCategoryResource\Pages\EditPortfolioCategory;
use App\Filament\Resources\PortfolioCategoryResource\Pages\ListPortfolioCategories;
use App\Models\PortfolioCategory;
use App\Models\PortfolioProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PortfolioCategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_render_list_page(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ListPortfolioCategories::class)
            ->assertOk();
    }

    public function test_admin_can_create_category_with_auto_slug(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(CreatePortfolioCategory::class)
            ->fillForm(['name' => 'Instalasi Atap', 'order' => 1])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('portfolio_categories', [
            'name' => 'Instalasi Atap',
            'slug' => 'instalasi-atap',
        ]);
    }

    public function test_duplicate_name_is_rejected(): void
    {
        PortfolioCategory::factory()->create(['name' => 'PLTS Industri']);

        Livewire::actingAs(User::factory()->create())
            ->test(CreatePortfolioCategory::class)
            ->fillForm(['name' => 'PLTS Industri', 'order' => 0])
            ->call('create')
            ->assertHasFormErrors(['name']);
    }

    public function test_admin_can_edit_category(): void
    {
        $category = PortfolioCategory::factory()->create(['name' => 'Lama']);

        Livewire::actingAs(User::factory()->create())
            ->test(EditPortfolioCategory::class, ['record' => $category->getRouteKey()])
            ->fillForm(['name' => 'Baru'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Baru', $category->fresh()->name);
    }

    public function test_admin_can_delete_unused_category(): void
    {
        $category = PortfolioCategory::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(ListPortfolioCategories::class)
            ->callTableAction('delete', $category);

        $this->assertDatabaseMissing('portfolio_categories', ['id' => $category->id]);
    }

    public function test_category_in_use_cannot_be_deleted(): void
    {
        $category = PortfolioCategory::factory()->create();
        PortfolioProject::factory()->create(['portfolio_category_id' => $category->id]);

        Livewire::actingAs(User::factory()->create())
            ->test(ListPortfolioCategories::class)
            ->callTableAction('delete', $category);

        $this->assertDatabaseHas('portfolio_categories', ['id' => $category->id]);
    }
}
