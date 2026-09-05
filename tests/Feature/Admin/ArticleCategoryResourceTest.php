<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\ArticleCategoryResource\Pages\CreateArticleCategory;
use App\Filament\Resources\ArticleCategoryResource\Pages\EditArticleCategory;
use App\Filament\Resources\ArticleCategoryResource\Pages\ListArticleCategories;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ArticleCategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_category(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateArticleCategory::class)
            ->fillForm(['name' => 'Studi Kasus', 'order' => 4])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('article_categories', ['name' => 'Studi Kasus']);
    }

    public function test_admin_can_edit_category(): void
    {
        $user = User::factory()->create();
        $category = ArticleCategory::factory()->create(['name' => 'Lama']);

        Livewire::actingAs($user)
            ->test(EditArticleCategory::class, ['record' => $category->getKey()])
            ->fillForm(['name' => 'Baru'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Baru', $category->fresh()->name);
    }

    public function test_duplicate_category_name_is_rejected(): void
    {
        $user = User::factory()->create();
        ArticleCategory::factory()->create(['name' => 'Tips']);

        Livewire::actingAs($user)
            ->test(CreateArticleCategory::class)
            ->fillForm(['name' => 'Tips'])
            ->call('create')
            ->assertHasFormErrors(['name']);

        $this->assertSame(1, ArticleCategory::where('name', 'Tips')->count());
    }

    public function test_category_still_in_use_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $category = ArticleCategory::factory()->create();
        Article::factory()->create(['article_category_id' => $category->id]);

        Livewire::actingAs($user)
            ->test(ListArticleCategories::class)
            ->callTableAction('delete', $category);

        $this->assertDatabaseHas('article_categories', ['id' => $category->id]);
    }

    public function test_unused_category_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $category = ArticleCategory::factory()->create();

        Livewire::actingAs($user)
            ->test(ListArticleCategories::class)
            ->callTableAction('delete', $category);

        $this->assertDatabaseMissing('article_categories', ['id' => $category->id]);
    }
}
