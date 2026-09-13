<?php

namespace Tests\Feature\Console;

use App\Models\PortfolioCategory;
use App\Models\PortfolioProject;
use App\Models\Product;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederDemoIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_standard_database_seeder_never_creates_demo_content(): void
    {
        $this->artisan('db:seed')->assertExitCode(0);

        $this->assertSame(0, Product::count());
        $this->assertSame(0, TeamMember::count());
        $this->assertSame(0, Testimonial::count());
        $this->assertSame(0, PortfolioCategory::count());
        $this->assertSame(0, PortfolioProject::count());
    }
}
