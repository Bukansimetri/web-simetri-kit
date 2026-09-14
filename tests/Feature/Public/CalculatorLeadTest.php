<?php

namespace Tests\Feature\Public;

use App\Mail\CalculatorLeadThankYou;
use App\Models\CalculatorLead;
use App\Notifications\NewCalculatorLead;
use App\Settings\BrandSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CalculatorLeadTest extends TestCase
{
    use RefreshDatabase;

    private function billPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'email' => 'budi@example.com',
            'area' => 'Jakarta Selatan',
            'category' => 'residential',
            'method' => 'bill',
            'monthly_bill' => 1_000_000,
            'va_capacity' => '2200',
        ], $overrides);
    }

    public function test_lead_is_stored_with_server_calculated_result(): void
    {
        $response = $this->postJson('/kalkulator/lead', $this->billPayload());

        $response->assertCreated()->assertJsonStructure([
            'message', 'whatsapp_url',
            'result' => ['savings_year1', 'total_savings_25y', 'estimated_investment', 'breakeven_years', 'annual_kwh'],
            'chart' => ['investment', 'points'],
        ]);

        $this->assertDatabaseHas('calculator_leads', [
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'email' => 'budi@example.com',
            'area' => 'Jakarta Selatan',
            'category' => 'residential',
            'method' => 'bill',
            'monthly_bill' => 1_000_000,
            'estimated_monthly_bill' => 1_000_000,
            'savings_year1' => 8_400_000, // 1jt * 12 * 0.7 (default settings)
            'status' => 'new',
        ]);

        $lead = CalculatorLead::first();
        $this->assertCount(25, $response->json('chart.points'));
        $this->assertSame(1500, $lead->assumptions['tariff_per_kwh']);
    }

    public function test_appliance_method_ignores_client_supplied_watt_and_uses_server_catalog(): void
    {
        Mail::fake();
        Notification::fake();

        // Sengaja TIDAK mengirim field watt sama sekali -- server harus
        // mengambil watt dari katalog sendiri (App\Services\SavingsEstimator).
        $response = $this->postJson('/kalkulator/lead', $this->billPayload([
            'method' => 'appliance',
            'monthly_bill' => null,
            'appliances' => [
                ['key' => 'ac', 'qty' => 2],
                ['key' => 'kulkas', 'qty' => 1],
            ],
        ]));

        $response->assertCreated();

        $this->assertDatabaseHas('calculator_leads', [
            'method' => 'appliance',
            'total_watt' => 2200, // 2*1000 + 1*200
        ]);
    }

    public function test_email_is_optional_and_thank_you_mail_only_sent_when_provided(): void
    {
        Mail::fake();
        Notification::fake();

        $this->postJson('/kalkulator/lead', $this->billPayload(['email' => null]))->assertCreated();

        $this->assertDatabaseHas('calculator_leads', ['name' => 'Budi Santoso', 'email' => null]);
        Mail::assertNothingQueued();

        $this->postJson('/kalkulator/lead', $this->billPayload(['phone' => '081298765432', 'email' => 'ada@example.com']))
            ->assertCreated();

        Mail::assertQueued(CalculatorLeadThankYou::class);
    }

    public function test_required_fields_are_validated(): void
    {
        $this->postJson('/kalkulator/lead', $this->billPayload([
            'name' => '', 'phone' => 'x', 'category' => 'nope', 'method' => 'bill', 'monthly_bill' => null,
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'phone', 'category', 'monthly_bill']);

        $this->assertDatabaseCount('calculator_leads', 0);
    }

    public function test_admin_notification_is_sent_when_configured(): void
    {
        Notification::fake();
        $settings = app(BrandSettings::class);
        $settings->contact_notification_email = 'sales@example.test';
        $settings->save();

        $this->postJson('/kalkulator/lead', $this->billPayload())->assertCreated();

        Notification::assertSentOnDemand(NewCalculatorLead::class);
    }
}
