<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_category(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateCategory::class)
            ->fillForm(['name' => 'Aksesoris', 'order' => 4])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('categories', ['name' => 'Aksesoris']);
    }

    public function test_admin_can_edit_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['name' => 'Lama']);

        Livewire::actingAs($user)
            ->test(EditCategory::class, ['record' => $category->getKey()])
            ->fillForm(['name' => 'Baru'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Baru', $category->fresh()->name);
    }

    public function test_duplicate_category_name_is_rejected(): void
    {
        $user = User::factory()->create();
        Category::factory()->create(['name' => 'Residensial']);

        Livewire::actingAs($user)
            ->test(CreateCategory::class)
            ->fillForm(['name' => 'Residensial'])
            ->call('create')
            ->assertHasFormErrors(['name']);

        $this->assertSame(1, Category::where('name', 'Residensial')->count());
    }

    public function test_category_still_in_use_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id]);

        Livewire::actingAs($user)
            ->test(ListCategories::class)
            ->callTableAction('delete', $category);

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_unused_category_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Livewire::actingAs($user)
            ->test(ListCategories::class)
            ->callTableAction('delete', $category);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
