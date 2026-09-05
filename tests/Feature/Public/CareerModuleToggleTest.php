<?php

namespace Tests\Feature\Public;

use App\Models\JobOpening;
use App\Settings\BrandSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CareerModuleToggleTest extends TestCase
{
    use RefreshDatabase;

    public function test_career_page_and_footer_link_visible_when_module_enabled(): void
    {
        $this->get('/karir')->assertOk();
        $this->get('/')->assertSee('Karir', escape: false);
    }

    public function test_career_page_404s_and_footer_link_hidden_when_module_disabled(): void
    {
        $settings = app(BrandSettings::class);
        $settings->career_module_enabled = false;
        $settings->save();

        $this->get('/karir')->assertNotFound();
        $this->get('/')->assertDontSee('Karir', escape: false);
    }

    public function test_disabling_and_reenabling_module_preserves_job_opening_data(): void
    {
        $job = JobOpening::factory()->create(['title' => 'Lowongan Tetap Ada', 'is_active' => true]);

        $settings = app(BrandSettings::class);
        $settings->career_module_enabled = false;
        $settings->save();

        $this->get('/karir')->assertNotFound();
        $this->assertDatabaseHas('job_openings', ['id' => $job->id]);

        $settings->career_module_enabled = true;
        $settings->save();

        $this->get('/karir')->assertOk()->assertSee('Lowongan Tetap Ada', escape: false);
    }
}
