<?php

namespace Tests\Feature\Public;

use App\Models\ContactSubmission;
use App\Notifications\NewContactSubmission;
use App\Settings\BrandSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CalculatorLeadTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'email' => 'budi@example.com',
            'area' => 'Jakarta Selatan',
            'category' => 'residential',
            'summary' => "Kategori: Residential\nEstimasi hemat tahun 1: Rp 12.000.000",
        ], $overrides);
    }

    public function test_lead_is_stored_as_contact_submission(): void
    {
        $response = $this->postJson('/kalkulator/lead', $this->payload());

        $response->assertCreated()->assertJsonStructure(['message', 'whatsapp_url']);

        $this->assertDatabaseHas('contact_submissions', [
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'email' => 'budi@example.com',
            'area' => 'Jakarta Selatan',
            'topic' => 'kalkulator',
            'status' => 'new',
        ]);

        $this->assertStringContainsString('Residential', ContactSubmission::first()->message);
    }

    public function test_email_and_area_are_optional(): void
    {
        $this->postJson('/kalkulator/lead', $this->payload(['email' => null, 'area' => null]))
            ->assertCreated();

        $this->assertDatabaseHas('contact_submissions', ['name' => 'Budi Santoso', 'email' => null, 'area' => null]);
    }

    public function test_required_fields_are_validated(): void
    {
        $this->postJson('/kalkulator/lead', $this->payload(['name' => '', 'phone' => 'x', 'category' => 'nope', 'summary' => '']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'phone', 'category', 'summary']);

        $this->assertDatabaseCount('contact_submissions', 0);
    }

    public function test_admin_notification_is_sent_when_configured(): void
    {
        Notification::fake();
        $settings = app(BrandSettings::class);
        $settings->contact_notification_email = 'sales@suoer.id';
        $settings->save();

        $this->postJson('/kalkulator/lead', $this->payload())->assertCreated();

        Notification::assertSentOnDemand(NewContactSubmission::class);
    }
}
