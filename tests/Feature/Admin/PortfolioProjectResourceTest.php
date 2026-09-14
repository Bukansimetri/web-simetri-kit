<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\PortfolioProjectResource\Pages\CreatePortfolioProject;
use App\Filament\Resources\PortfolioProjectResource\Pages\ListPortfolioProjects;
use App\Models\PortfolioCategory;
use App\Models\PortfolioProject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PortfolioProjectResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_render_list_page(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ListPortfolioProjects::class)
            ->assertOk();
    }

    public function test_admin_can_create_project_with_gallery_and_auto_slug(): void
    {
        Storage::fake('public');
        $category = PortfolioCategory::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(CreatePortfolioProject::class)
            ->fillForm([
                'title' => 'Atap Rumah 5 kWp Bandung',
                'portfolio_category_id' => $category->id,
                'description' => '<p>Instalasi rapi.</p>',
                'images' => [
                    UploadedFile::fake()->image('a.jpg', 800, 600),
                    UploadedFile::fake()->image('b.jpg', 800, 600),
                ],
                'order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $project = PortfolioProject::where('title', 'Atap Rumah 5 kWp Bandung')->first();

        $this->assertNotNull($project);
        $this->assertSame('atap-rumah-5-kwp-bandung', $project->slug);
        $this->assertCount(2, $project->images);
        foreach ($project->images as $path) {
            $this->assertSame('webp', pathinfo($path, PATHINFO_EXTENSION));
            Storage::disk('public')->assertExists($path);
        }
    }

    public function test_wide_gallery_image_is_downscaled_to_1200(): void
    {
        Storage::fake('public');
        $category = PortfolioCategory::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(CreatePortfolioProject::class)
            ->fillForm([
                'title' => 'Proyek Lebar',
                'portfolio_category_id' => $category->id,
                'description' => 'x',
                'images' => [UploadedFile::fake()->image('wide.jpg', 2000, 1000)],
                'order' => 0,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $path = PortfolioProject::where('title', 'Proyek Lebar')->first()->images[0];
        $info = getimagesizefromstring(Storage::disk('public')->get($path));

        $this->assertSame(1200, $info[0]);
        $this->assertSame(600, $info[1]);
    }

    public function test_required_fields_are_validated(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(CreatePortfolioProject::class)
            ->fillForm([
                'title' => '',
                'portfolio_category_id' => null,
                'description' => '',
                'images' => [],
            ])
            ->call('create')
            ->assertHasFormErrors(['title', 'portfolio_category_id', 'description', 'images']);
    }

    public function test_duplicate_slug_is_rejected(): void
    {
        Storage::fake('public');
        $category = PortfolioCategory::factory()->create();
        PortfolioProject::factory()->create(['slug' => 'proyek-a', 'portfolio_category_id' => $category->id]);

        Livewire::actingAs(User::factory()->create())
            ->test(CreatePortfolioProject::class)
            ->fillForm([
                'title' => 'Proyek B',
                'slug' => 'proyek-a',
                'portfolio_category_id' => $category->id,
                'description' => 'x',
                'images' => [UploadedFile::fake()->image('x.jpg', 400, 300)],
            ])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }

    public function test_project_url_validation(): void
    {
        Storage::fake('public');
        $category = PortfolioCategory::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(CreatePortfolioProject::class)
            ->fillForm([
                'title' => 'Proyek URL',
                'portfolio_category_id' => $category->id,
                'description' => 'x',
                'images' => [UploadedFile::fake()->image('x.jpg', 400, 300)],
                'project_url' => 'contoh.com',
            ])
            ->call('create')
            ->assertHasFormErrors(['project_url']);
    }

    public function test_optional_fields_can_be_blank(): void
    {
        Storage::fake('public');
        $category = PortfolioCategory::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(CreatePortfolioProject::class)
            ->fillForm([
                'title' => 'Proyek Minimal',
                'portfolio_category_id' => $category->id,
                'description' => 'x',
                'images' => [UploadedFile::fake()->image('x.jpg', 400, 300)],
                'project_url' => null,
                'client_name' => null,
                'completed_at' => null,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('portfolio_projects', [
            'title' => 'Proyek Minimal',
            'client_name' => null,
            'project_url' => null,
            'completed_at' => null,
        ]);
    }

    public function test_admin_can_delete_project(): void
    {
        $project = PortfolioProject::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(ListPortfolioProjects::class)
            ->callTableAction('delete', $project);

        $this->assertDatabaseMissing('portfolio_projects', ['id' => $project->id]);
    }

    public function test_admin_can_fill_seo_fields_and_they_are_stored(): void
    {
        Storage::fake('public');
        $category = PortfolioCategory::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(CreatePortfolioProject::class)
            ->fillForm([
                'title' => 'Proyek dengan SEO',
                'portfolio_category_id' => $category->id,
                'description' => '<p>Instalasi rapi.</p>',
                'images' => [UploadedFile::fake()->image('a.jpg', 800, 600)],
                'meta_title' => 'Judul SEO Kustom',
                'meta_description' => 'Deskripsi SEO kustom.',
                'meta_image_path' => UploadedFile::fake()->image('seo.jpg'),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $project = PortfolioProject::where('title', 'Proyek dengan SEO')->first();

        $this->assertSame('Judul SEO Kustom', $project->meta_title);
        $this->assertSame('Deskripsi SEO kustom.', $project->meta_description);
        $this->assertStringEndsWith('.webp', $project->meta_image_path);
    }
}
