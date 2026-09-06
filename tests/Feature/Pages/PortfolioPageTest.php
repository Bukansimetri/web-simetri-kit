<?php

namespace Tests\Feature\Pages;

use App\Models\PortfolioCategory;
use App\Models\PortfolioProject;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_shows_active_projects_and_hides_inactive(): void
    {
        $cat = PortfolioCategory::factory()->create();
        PortfolioProject::factory()->create(['title' => 'Proyek Aktif', 'portfolio_category_id' => $cat->id]);
        PortfolioProject::factory()->inactive()->create(['title' => 'Proyek Nonaktif', 'portfolio_category_id' => $cat->id]);

        $response = $this->get('/portfolio');

        $response->assertOk();
        $response->assertSee('Proyek Aktif', escape: false);
        $response->assertSee($cat->name, escape: false);
        $response->assertDontSee('Proyek Nonaktif', escape: false);
    }

    public function test_index_orders_by_order_then_id(): void
    {
        $cat = PortfolioCategory::factory()->create();
        PortfolioProject::factory()->create(['title' => 'Kedua', 'order' => 2, 'portfolio_category_id' => $cat->id]);
        PortfolioProject::factory()->create(['title' => 'Pertama', 'order' => 1, 'portfolio_category_id' => $cat->id]);

        $this->get('/portfolio')
            ->assertOk()
            ->assertSeeInOrder(['Pertama', 'Kedua'], escape: false);
    }

    public function test_category_filter_narrows_and_is_shareable(): void
    {
        $atap = PortfolioCategory::factory()->create(['name' => 'Instalasi Atap', 'slug' => 'instalasi-atap']);
        $plts = PortfolioCategory::factory()->create(['name' => 'PLTS Industri', 'slug' => 'plts-industri']);
        PortfolioProject::factory()->create(['title' => 'Rumah A', 'portfolio_category_id' => $atap->id]);
        PortfolioProject::factory()->create(['title' => 'Pabrik B', 'portfolio_category_id' => $plts->id]);

        $response = $this->get('/portfolio?kategori=plts-industri');

        $response->assertOk();
        $response->assertSee('Pabrik B', escape: false);
        $response->assertDontSee('Rumah A', escape: false);
    }

    public function test_unknown_category_filter_shows_all(): void
    {
        $cat = PortfolioCategory::factory()->create();
        PortfolioProject::factory()->create(['title' => 'Proyek X', 'portfolio_category_id' => $cat->id]);

        $this->get('/portfolio?kategori=tidak-ada')
            ->assertOk()
            ->assertSee('Proyek X', escape: false);
    }

    public function test_index_empty_state_is_200_not_404(): void
    {
        $response = $this->get('/portfolio');

        $response->assertOk();
        $response->assertSee('Belum ada proyek', escape: false);
    }

    public function test_detail_shows_active_project_with_optional_metadata(): void
    {
        $cat = PortfolioCategory::factory()->create();
        $project = PortfolioProject::factory()->create([
            'title' => 'Atap Rumah Bandung',
            'slug' => 'atap-rumah-bandung',
            'description' => '<p>Detail lengkap proyek.</p>',
            'client_name' => 'PT ABC',
            'project_url' => 'https://example.com/proyek',
            'completed_at' => '2026-03-15',
            'portfolio_category_id' => $cat->id,
        ]);

        $response = $this->get('/portfolio/atap-rumah-bandung');

        $response->assertOk();
        $response->assertSee('Atap Rumah Bandung', escape: false);
        $response->assertSee('Detail lengkap proyek.', escape: false);
        $response->assertSee('PT ABC', escape: false);
        $response->assertSee('href="https://example.com/proyek"', escape: false);
        $response->assertSee('target="_blank"', escape: false);
    }

    public function test_detail_hides_empty_optional_labels(): void
    {
        $cat = PortfolioCategory::factory()->create();
        PortfolioProject::factory()->create([
            'slug' => 'proyek-minimal',
            'client_name' => null,
            'project_url' => null,
            'completed_at' => null,
            'portfolio_category_id' => $cat->id,
        ]);

        $response = $this->get('/portfolio/proyek-minimal');

        $response->assertOk();
        $response->assertDontSee('Klien:', escape: false);
        $response->assertDontSee('Kunjungi proyek', escape: false);
    }

    public function test_detail_404_for_inactive_and_unknown(): void
    {
        $cat = PortfolioCategory::factory()->create();
        PortfolioProject::factory()->inactive()->create(['slug' => 'nonaktif', 'portfolio_category_id' => $cat->id]);

        $this->get('/portfolio/nonaktif')->assertNotFound();
        $this->get('/portfolio/ngawur')->assertNotFound();
    }
}
