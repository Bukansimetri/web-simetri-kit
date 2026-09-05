<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\ContactSubmissionResource\Pages\EditContactSubmission;
use App\Filament\Resources\ContactSubmissionResource\Pages\ListContactSubmissions;
use App\Models\ContactSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ContactSubmissionResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_submission_list_with_full_detail(): void
    {
        $user = User::factory()->create();
        $submission = ContactSubmission::factory()->create([
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'topic' => 'residensial',
            'message' => 'Saya tertarik dengan panel surya.',
        ]);

        Livewire::actingAs($user)
            ->test(ListContactSubmissions::class)
            ->assertCanSeeTableRecords([$submission])
            ->assertSee('Budi Santoso')
            ->assertSee('081234567890');
    }

    public function test_admin_can_change_submission_status(): void
    {
        $user = User::factory()->create();
        $submission = ContactSubmission::factory()->create(['status' => ContactSubmission::STATUS_NEW]);

        Livewire::actingAs($user)
            ->test(EditContactSubmission::class, ['record' => $submission->getKey()])
            ->fillForm(['status' => ContactSubmission::STATUS_CONTACTED])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(ContactSubmission::STATUS_CONTACTED, $submission->fresh()->status);
    }

    public function test_admin_can_filter_submissions_by_status(): void
    {
        $user = User::factory()->create();
        $new = ContactSubmission::factory()->create(['status' => ContactSubmission::STATUS_NEW]);
        $closed = ContactSubmission::factory()->create(['status' => ContactSubmission::STATUS_CLOSED]);

        Livewire::actingAs($user)
            ->test(ListContactSubmissions::class)
            ->filterTable('status', ContactSubmission::STATUS_NEW)
            ->assertCanSeeTableRecords([$new])
            ->assertCanNotSeeTableRecords([$closed]);
    }

    public function test_admin_can_delete_submission(): void
    {
        $user = User::factory()->create();
        $submission = ContactSubmission::factory()->create();

        Livewire::actingAs($user)
            ->test(ListContactSubmissions::class)
            ->callTableAction('delete', $submission);

        $this->assertDatabaseMissing('contact_submissions', ['id' => $submission->id]);
    }
}
