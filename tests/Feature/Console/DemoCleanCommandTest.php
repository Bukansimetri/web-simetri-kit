<?php

namespace Tests\Feature\Console;

use App\Models\DemoSeedRecord;
use App\Models\PortfolioCategory;
use App\Models\PortfolioProject;
use App\Models\Product;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DemoCleanCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_removes_all_demo_content_and_manifest(): void
    {
        $this->artisan('demo:seed')->assertExitCode(0);

        $this->artisan('demo:clean')->assertExitCode(0);

        $this->assertSame(0, Product::count());
        $this->assertSame(0, TeamMember::count());
        $this->assertSame(0, Testimonial::count());
        $this->assertSame(0, PortfolioCategory::count());
        $this->assertSame(0, PortfolioProject::count());
        $this->assertSame(0, DemoSeedRecord::count());
    }

    public function test_does_not_delete_entries_added_manually_by_admin(): void
    {
        $this->artisan('demo:seed')->assertExitCode(0);

        $manualMember = TeamMember::create([
            'name' => 'Admin Manual',
            'position' => 'Operator',
            'photo_path' => 'images/mockup/tentang-kami-2.jpg',
            'bio' => 'Ditambahkan langsung oleh admin, bukan bagian dari data demo.',
            'order' => 99,
            'is_active' => true,
        ]);

        $this->artisan('demo:clean')->assertExitCode(0);

        $this->assertDatabaseHas('team_members', ['id' => $manualMember->id]);
        $this->assertSame(1, TeamMember::count());
    }

    public function test_is_a_no_op_when_nothing_was_ever_seeded(): void
    {
        $this->artisan('demo:clean')->assertExitCode(0);

        $this->assertSame(0, Product::count());
        $this->assertSame(0, DemoSeedRecord::count());
    }

    public function test_keeps_portfolio_category_still_used_by_a_non_demo_project(): void
    {
        $this->artisan('demo:seed')->assertExitCode(0);

        $demoCategoryId = DemoSeedRecord::query()
            ->where('seedable_type', (new PortfolioCategory)->getMorphClass())
            ->value('seedable_id');

        PortfolioProject::create([
            'portfolio_category_id' => $demoCategoryId,
            'title' => 'Proyek Asli Admin',
            'slug' => 'proyek-asli-admin',
            'description' => '<p>Proyek nyata, bukan demo.</p>',
            'images' => ['images/mockup/home-1.jpg'],
            'order' => 99,
            'is_active' => true,
        ]);

        $this->artisan('demo:clean')->assertExitCode(0);

        $this->assertDatabaseHas('portfolio_categories', ['id' => $demoCategoryId]);
        $this->assertDatabaseHas('portfolio_projects', ['title' => 'Proyek Asli Admin']);

        // Kategori yang masih dipakai TIDAK dihapus, sehingga baris
        // manifest-nya juga tetap ada (belum "dilupakan") — konsisten
        // dengan aturan "hanya hapus manifest saat record-nya benar-benar
        // dihapus" (research.md #5).
        $this->assertDatabaseHas('demo_seed_records', [
            'seedable_type' => (new PortfolioCategory)->getMorphClass(),
            'seedable_id' => $demoCategoryId,
        ]);
        $this->assertSame(1, DemoSeedRecord::count());
    }
}
