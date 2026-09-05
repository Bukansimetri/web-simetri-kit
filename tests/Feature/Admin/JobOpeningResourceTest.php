<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\JobOpeningResource\Pages\CreateJobOpening;
use App\Filament\Resources\JobOpeningResource\Pages\EditJobOpening;
use App\Filament\Resources\JobOpeningResource\Pages\ListJobOpenings;
use App\Models\JobOpening;
use App\Models\User;
use App\Settings\BrandSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JobOpeningResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_job_opening_and_it_appears_on_public_page(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateJobOpening::class)
            ->fillForm([
                'title' => 'QA Engineer',
                'location' => 'Jakarta',
                'employment_type' => 'full-time',
                'description' => 'Bertanggung jawab atas quality assurance produk.',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('job_openings', ['title' => 'QA Engineer']);

        $this->get('/karir')->assertOk()->assertSee('QA Engineer', escape: false);
    }

    public function test_admin_can_edit_job_opening_and_change_is_reflected_publicly(): void
    {
        $user = User::factory()->create();
        $job = JobOpening::factory()->create(['title' => 'Judul Lama', 'is_active' => true]);

        Livewire::actingAs($user)
            ->test(EditJobOpening::class, ['record' => $job->getKey()])
            ->fillForm(['title' => 'Judul Baru'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Judul Baru', $job->fresh()->title);
        $this->get('/karir')->assertOk()->assertSee('Judul Baru', escape: false);
    }

    public function test_required_fields_are_validated(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateJobOpening::class)
            ->fillForm([
                'title' => '',
                'location' => '',
                'employment_type' => null,
                'description' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['title', 'location', 'employment_type', 'description']);
    }

    public function test_employment_type_outside_fixed_list_is_rejected(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateJobOpening::class)
            ->fillForm([
                'title' => 'Posisi Tes',
                'location' => 'Jakarta',
                'employment_type' => 'freelance',
                'description' => 'x',
            ])
            ->call('create')
            ->assertHasFormErrors(['employment_type']);
    }

    public function test_deactivating_job_opening_hides_it_from_public_without_deleting(): void
    {
        $user = User::factory()->create();
        $job = JobOpening::factory()->create(['title' => 'Lowongan Aktif', 'is_active' => true]);

        Livewire::actingAs($user)
            ->test(EditJobOpening::class, ['record' => $job->getKey()])
            ->fillForm(['is_active' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('job_openings', ['id' => $job->id, 'is_active' => false]);
        $this->get('/karir')->assertOk()->assertDontSee('Lowongan Aktif', escape: false);
    }

    public function test_admin_crud_remains_accessible_when_career_module_disabled(): void
    {
        $settings = app(BrandSettings::class);
        $settings->career_module_enabled = false;
        $settings->save();

        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(ListJobOpenings::class)
            ->assertSuccessful();

        Livewire::actingAs($user)
            ->test(CreateJobOpening::class)
            ->fillForm([
                'title' => 'Lowongan Saat Modul Nonaktif',
                'location' => 'Jakarta',
                'employment_type' => 'full-time',
                'description' => 'x',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('job_openings', ['title' => 'Lowongan Saat Modul Nonaktif']);
    }

    public function test_admin_can_delete_job_opening_and_it_disappears_from_public_page(): void
    {
        $user = User::factory()->create();
        $job = JobOpening::factory()->create(['title' => 'Lowongan Dihapus', 'is_active' => true]);

        Livewire::actingAs($user)
            ->test(ListJobOpenings::class)
            ->callTableAction('delete', $job);

        $this->assertDatabaseMissing('job_openings', ['id' => $job->id]);
        $this->get('/karir')->assertOk()->assertDontSee('Lowongan Dihapus', escape: false);
    }
}
