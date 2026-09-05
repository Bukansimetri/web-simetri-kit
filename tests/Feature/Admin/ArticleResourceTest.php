<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\ArticleResource\Pages\CreateArticle;
use App\Filament\Resources\ArticleResource\Pages\EditArticle;
use App\Filament\Resources\ArticleResource\Pages\ListArticles;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Tags\Tag;
use Tests\TestCase;

class ArticleResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_article_and_it_appears_on_public_page(): void
    {
        $user = User::factory()->create();
        $category = ArticleCategory::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateArticle::class)
            ->fillForm([
                'title' => 'Artikel Tes Panel Surya',
                'article_category_id' => $category->id,
                'excerpt' => 'Ringkasan singkat.',
                'content' => '<p>Isi lengkap artikel.</p>',
                'redaksi' => 'Tim Redaksi SUOER',
                'publish_status' => 'now',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $article = Article::where('title', 'Artikel Tes Panel Surya')->first();

        $this->assertNotNull($article);
        $this->assertSame('artikel-tes-panel-surya', $article->slug);
        $this->assertSame('Tim Redaksi SUOER', $article->redaksi);

        $this->get('/artikel')->assertOk()->assertSee('Artikel Tes Panel Surya', escape: false);
    }

    public function test_admin_can_edit_article_and_change_is_reflected_publicly(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['title' => 'Judul Lama', 'published_at' => now()]);

        Livewire::actingAs($user)
            ->test(EditArticle::class, ['record' => $article->getRouteKey()])
            ->fillForm(['title' => 'Judul Baru'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Judul Baru', $article->fresh()->title);
        $this->get('/artikel/'.$article->fresh()->slug)->assertOk()->assertSee('Judul Baru', escape: false);
    }

    public function test_slug_auto_generates_from_title_when_blank(): void
    {
        $user = User::factory()->create();
        $category = ArticleCategory::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateArticle::class)
            ->fillForm([
                'title' => 'Judul Otomatis Slug',
                'article_category_id' => $category->id,
                'excerpt' => 'x',
                'content' => 'x',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('articles', ['slug' => 'judul-otomatis-slug']);
    }

    public function test_duplicate_slug_is_rejected(): void
    {
        $user = User::factory()->create();
        $category = ArticleCategory::factory()->create();
        Article::factory()->create(['slug' => 'artikel-a']);

        Livewire::actingAs($user)
            ->test(CreateArticle::class)
            ->fillForm([
                'title' => 'Artikel B',
                'slug' => 'artikel-a',
                'article_category_id' => $category->id,
                'excerpt' => 'x',
                'content' => 'x',
            ])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }

    public function test_required_fields_are_validated(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateArticle::class)
            ->fillForm([
                'title' => '',
                'article_category_id' => null,
                'excerpt' => '',
                'content' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['title', 'article_category_id', 'excerpt', 'content']);
    }

    public function test_admin_can_attach_new_and_existing_tags_without_duplicates(): void
    {
        $user = User::factory()->create();
        $category = ArticleCategory::factory()->create();
        $existing = Article::factory()->create();
        $existing->syncTags(['panel-surya']);

        Livewire::actingAs($user)
            ->test(CreateArticle::class)
            ->fillForm([
                'title' => 'Artikel Bertag',
                'article_category_id' => $category->id,
                'excerpt' => 'x',
                'content' => 'x',
                'tags' => ['panel-surya', 'hemat-listrik'],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $article = Article::where('title', 'Artikel Bertag')->first();

        $this->assertSame(['hemat-listrik', 'panel-surya'], $article->tags->pluck('name')->sort()->values()->all());
        $this->assertSame(1, Tag::get()->pluck('name')->filter(fn ($name) => $name === 'panel-surya')->count());
    }

    public function test_detaching_tag_from_one_article_keeps_it_available_for_others(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();
        $article->syncTags(['panel-surya', 'hemat-listrik']);

        Livewire::actingAs($user)
            ->test(EditArticle::class, ['record' => $article->getRouteKey()])
            ->fillForm(['tags' => ['panel-surya']])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(['panel-surya'], $article->fresh()->tags->pluck('name')->all());
        $this->assertSame(1, Tag::get()->pluck('name')->filter(fn ($name) => $name === 'hemat-listrik')->count());
    }

    public function test_uploaded_featured_image_is_converted_to_webp(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $category = ArticleCategory::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateArticle::class)
            ->fillForm([
                'title' => 'Artikel Bergambar',
                'article_category_id' => $category->id,
                'excerpt' => 'x',
                'content' => 'x',
                'image_path' => UploadedFile::fake()->image('sampul.jpg'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $article = Article::where('title', 'Artikel Bergambar')->first();

        $this->assertNotNull($article->image_path);
        $this->assertSame('webp', pathinfo($article->image_path, PATHINFO_EXTENSION));
        Storage::disk('public')->assertExists($article->image_path);
    }

    public function test_article_without_featured_image_saves_successfully(): void
    {
        $user = User::factory()->create();
        $category = ArticleCategory::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateArticle::class)
            ->fillForm([
                'title' => 'Artikel Tanpa Gambar',
                'article_category_id' => $category->id,
                'excerpt' => 'x',
                'content' => 'x',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('articles', ['title' => 'Artikel Tanpa Gambar', 'image_path' => null]);
    }

    public function test_admin_can_delete_article_and_old_slug_returns_404(): void
    {
        $article = Article::factory()->create(['published_at' => now()]);
        $slug = $article->slug;

        $user = User::factory()->create();
        Livewire::actingAs($user)
            ->test(ListArticles::class)
            ->callTableAction('delete', $article);

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
        $this->get('/artikel/'.$slug)->assertNotFound();
        $this->get('/artikel')->assertOk()->assertDontSee($article->title, escape: false);
    }
}
