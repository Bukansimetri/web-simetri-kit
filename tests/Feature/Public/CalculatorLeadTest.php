<?php

namespace Tests\Feature\Public;

use App\Mail\CalculatorLeadThankYou;
use App\Models\CalculatorLead;
use App\Notifications\NewCalculatorLead;
use App\Services\SubmissionGuard;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CalculatorLeadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // RateLimiter menyimpan hitungannya di cache (array store saat testing)
        // yang hidup sepanjang proses — dibersihkan supaya tiap test mulai dari nol.
        Cache::flush();
    }

    /**
     * Token waktu yang sah: dibuat 10 detik lalu supaya lolos ambang minimal
     * pengisian (SubmissionGuard::MIN_FILL_SECONDS).
     */
    private function validToken(): string
    {
        return Crypt::encryptString((string) now()->subSeconds(10)->timestamp);
    }

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
            'form_token' => $this->validToken(),
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
            'phone_normalized' => '6281234567890',
            'email' => 'budi@example.com',
            'category' => 'residential',
            'method' => 'bill',
            'monthly_bill' => 1_000_000,
            'savings_year1' => 8_400_000, // 1jt * 12 * 0.7 (asumsi default)
            'status' => 'new',
        ]);

        $this->assertCount(25, $response->json('chart.points'));
        $this->assertSame(1500, CalculatorLead::first()->assumptions['tariff_per_kwh']);
    }

    public function test_appliance_method_ignores_client_supplied_watt_and_uses_server_catalog(): void
    {
        Mail::fake();
        Notification::fake();

        // Sengaja TIDAK mengirim field watt sama sekali -- server harus
        // mengambil watt dari katalog sendiri (App\Services\SavingsEstimator).
        $this->postJson('/kalkulator/lead', $this->billPayload([
            'method' => 'appliance',
            'monthly_bill' => null,
            'appliances' => [
                ['key' => 'ac', 'qty' => 2],
                ['key' => 'kulkas', 'qty' => 1],
            ],
        ]))->assertCreated();

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
        $settings = app(SiteSettings::class);
        $settings->contact_notification_email = 'sales@example.test';
        $settings->save();

        $this->postJson('/kalkulator/lead', $this->billPayload())->assertCreated();

        Notification::assertSentOnDemand(NewCalculatorLead::class);
    }

    // --- Proteksi anti-bot -------------------------------------------------

    public function test_filled_honeypot_is_silently_rejected(): void
    {
        Mail::fake();
        Notification::fake();

        // Dibalas seolah sukses supaya bot tidak tahu dia terdeteksi...
        $this->postJson('/kalkulator/lead', $this->billPayload([
            SubmissionGuard::HONEYPOT_FIELD => 'http://spam.example.com',
        ]))->assertCreated();

        // ...tapi tidak ada yang tersimpan & tidak ada email terkirim.
        $this->assertDatabaseCount('calculator_leads', 0);
        Mail::assertNothingQueued();
        Notification::assertNothingSent();
    }

    public function test_submission_that_is_too_fast_is_silently_rejected(): void
    {
        Mail::fake();

        // Token dibuat "sekarang" = form disubmit < 3 detik setelah dirender.
        $this->postJson('/kalkulator/lead', $this->billPayload([
            'form_token' => SubmissionGuard::issueToken(),
        ]))->assertCreated();

        $this->assertDatabaseCount('calculator_leads', 0);
        Mail::assertNothingQueued();
    }

    public function test_missing_or_invalid_token_is_silently_rejected(): void
    {
        // Request langsung ke endpoint (curl/Postman) tanpa pernah membuka form.
        $this->postJson('/kalkulator/lead', $this->billPayload(['form_token' => null]))
            ->assertCreated();

        $this->postJson('/kalkulator/lead', $this->billPayload(['form_token' => 'bukan-token-asli']))
            ->assertCreated();

        $this->assertDatabaseCount('calculator_leads', 0);
    }

    public function test_old_token_is_still_accepted(): void
    {
        // Pengunjung yang membiarkan tab terbuka semalaman tetap pelanggan asli
        // dan tidak boleh ditolak.
        $this->postJson('/kalkulator/lead', $this->billPayload([
            'form_token' => Crypt::encryptString((string) now()->subDays(2)->timestamp),
        ]))->assertCreated();

        $this->assertDatabaseCount('calculator_leads', 1);
    }

    // --- Deteksi duplikat --------------------------------------------------

    public function test_resubmit_from_same_phone_updates_existing_lead(): void
    {
        Notification::fake();
        $settings = app(SiteSettings::class);
        $settings->contact_notification_email = 'sales@example.test';
        $settings->save();

        $this->postJson('/kalkulator/lead', $this->billPayload())->assertCreated();
        $this->postJson('/kalkulator/lead', $this->billPayload(['monthly_bill' => 2_000_000]))->assertCreated();

        // Satu baris saja, dengan angka terbaru.
        $this->assertDatabaseCount('calculator_leads', 1);
        $this->assertDatabaseHas('calculator_leads', [
            'monthly_bill' => 2_000_000,
            'savings_year1' => 16_800_000, // 2jt * 12 * 0.7
        ]);

        // Admin tidak dikirimi email dua kali untuk orang yang sama.
        Notification::assertSentOnDemandTimes(NewCalculatorLead::class, 1);
    }

    public function test_dedupe_recognises_same_person_across_phone_formats(): void
    {
        $this->postJson('/kalkulator/lead', $this->billPayload(['phone' => '081234567890']))->assertCreated();
        $this->postJson('/kalkulator/lead', $this->billPayload(['phone' => '+6281234567890']))->assertCreated();

        $this->assertDatabaseCount('calculator_leads', 1);
    }

    public function test_resubmit_does_not_reset_follow_up_progress(): void
    {
        $this->postJson('/kalkulator/lead', $this->billPayload())->assertCreated();

        $lead = CalculatorLead::first();
        $lead->update([
            'status' => CalculatorLead::STATUS_CONTACTED,
            'follow_up_notes' => 'Sudah ditelepon, minta survei Sabtu.',
        ]);

        $this->postJson('/kalkulator/lead', $this->billPayload(['monthly_bill' => 3_000_000]))->assertCreated();

        $lead->refresh();
        $this->assertSame(CalculatorLead::STATUS_CONTACTED, $lead->status);
        $this->assertSame('Sudah ditelepon, minta survei Sabtu.', $lead->follow_up_notes);
        $this->assertSame(3_000_000, $lead->monthly_bill);
    }

    public function test_different_phone_creates_separate_lead(): void
    {
        $this->postJson('/kalkulator/lead', $this->billPayload(['phone' => '081234567890']))->assertCreated();
        $this->postJson('/kalkulator/lead', $this->billPayload(['phone' => '081200000000']))->assertCreated();

        $this->assertDatabaseCount('calculator_leads', 2);
    }

    // --- Rate limit per nomor ----------------------------------------------

    public function test_too_many_submissions_from_same_phone_are_blocked(): void
    {
        // Throttle per IP (routes/web.php) dimatikan supaya yang diuji di sini
        // benar-benar batas per nomor telepon.
        $this->withoutMiddleware(ThrottleRequests::class);

        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/kalkulator/lead', $this->billPayload())->assertCreated();
        }

        $this->postJson('/kalkulator/lead', $this->billPayload())->assertStatus(429);
    }
}
