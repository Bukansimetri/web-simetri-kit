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

class DemoSeedCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeds_all_five_entities_and_records_manifest(): void
    {
        $this->artisan('demo:seed')->assertExitCode(0);

        $this->assertGreaterThan(0, Product::count());
        $this->assertGreaterThan(0, TeamMember::count());
        $this->assertGreaterThan(0, Testimonial::count());
        $this->assertGreaterThan(0, PortfolioCategory::count());
        $this->assertGreaterThan(0, PortfolioProject::count());

        $expected = Product::count() + TeamMember::count() + Testimonial::count()
            + PortfolioCategory::count() + PortfolioProject::count();

        $this->assertSame($expected, DemoSeedRecord::count());
    }

    public function test_rerunning_does_not_duplicate_any_entity(): void
    {
        $this->artisan('demo:seed')->assertExitCode(0);

        $countsAfterFirstRun = [
            Product::count(),
            TeamMember::count(),
            Testimonial::count(),
            PortfolioCategory::count(),
            PortfolioProject::count(),
            DemoSeedRecord::count(),
        ];

        $this->artisan('demo:seed')->assertExitCode(0);

        $this->assertSame($countsAfterFirstRun, [
            Product::count(),
            TeamMember::count(),
            Testimonial::count(),
            PortfolioCategory::count(),
            PortfolioProject::count(),
            DemoSeedRecord::count(),
        ]);
    }
}
