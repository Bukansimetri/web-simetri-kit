<?php

namespace Tests\Feature\Pages;

use App\Models\JobOpening;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_career_page_only_shows_active_job_openings(): void
    {
        JobOpening::factory()->create(['title' => 'Solar Panel Technician', 'is_active' => true]);
        JobOpening::factory()->create(['title' => 'Lowongan Nonaktif', 'is_active' => false]);

        $response = $this->get('/karir');

        $response->assertOk();
        $response->assertSee('Solar Panel Technician', escape: false);
        $response->assertDontSee('Lowongan Nonaktif', escape: false);
    }
}
