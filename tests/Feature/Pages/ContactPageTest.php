<?php

namespace Tests\Feature\Pages;

use App\Mail\ContactSubmissionThankYou;
use App\Models\ContactSubmission;
use App\Notifications\NewContactSubmission;
use App\Services\SubmissionGuard;
use App\Settings\BrandSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ContactPageTest extends TestCase
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

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'nama' => 'Budi Santoso',
            'phone' => '081234567890',
            'email' => 'budi@example.com',
            'kebutuhan' => 'residensial',
            'pesan' => 'Saya tertarik dengan panel surya untuk rumah.',
            'form_token' => $this->validToken(),
        ], $overrides);
    }

    public function test_contact_page_renders_form_fields(): void
    {
        $response = $this->get('/kontak');

        $response->assertOk();
        $response->assertSee('name="nama"', escape: false);
        $response->assertSee('name="phone"', escape: false);
        $response->assertSee('name="email"', escape: false);
        $response->assertSee('name="pesan"', escape: false);
    }

    public function test_submitting_valid_data_saves_the_submission(): void
    {
        Mail::fake();

        $response = $this->postJson('/kontak', $this->payload());

        $response->assertCreated();
        $this->assertDatabaseHas('contact_submissions', [
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'email' => 'budi@example.com',
            'topic' => 'residensial',
            'message' => 'Saya tertarik dengan panel surya untuk rumah.',
            'status' => ContactSubmission::STATUS_NEW,
        ]);
    }

    public function test_submitting_without_required_fields_is_rejected_and_not_saved(): void
    {
        $response = $this->postJson('/kontak', $this->payload([
            'nama' => '',
            'phone' => 'bukan-nomor',
            'email' => '',
            'pesan' => '',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nama', 'phone', 'email', 'pesan']);
        $this->assertSame(0, ContactSubmission::count());
    }

    public function test_thank_you_email_is_queued_for_customer(): void
    {
        Mail::fake();

        $this->postJson('/kontak', $this->payload())->assertCreated();

        Mail::assertQueued(ContactSubmissionThankYou::class, fn ($mail) => $mail->submission->email === 'budi@example.com');
    }

    public function test_response_includes_whatsapp_url_when_configured(): void
    {
        Mail::fake();

        $settings = app(BrandSettings::class);
        $settings->whatsapp_number = '6281234567890';
        $settings->save();

        $response = $this->postJson('/kontak', $this->payload(['kebutuhan' => 'umum']));

        $response->assertCreated();
        $response->assertJsonStructure(['message', 'whatsapp_url']);
        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $response->json('whatsapp_url'));
    }

    public function test_response_whatsapp_url_is_null_when_not_configured(): void
    {
        Mail::fake();

        $response = $this->postJson('/kontak', $this->payload());

        $response->assertCreated();
        $this->assertNull($response->json('whatsapp_url'));
    }

    public function test_submission_is_rate_limited_per_ip(): void
    {
        Mail::fake();

        $payload = $this->payload();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/kontak', $this->payload(['phone' => '08123456'.str_pad((string) $i, 4, '0'), 'email' => "budi{$i}@example.com"]))->assertCreated();
        }

        $this->postJson('/kontak', $payload)->assertStatus(429);
    }

    public function test_notification_is_dispatched_when_admin_email_is_configured(): void
    {
        Mail::fake();
        Notification::fake();

        $settings = app(BrandSettings::class);
        $settings->contact_notification_email = 'admin@example.com';
        $settings->save();

        $this->postJson('/kontak', $this->payload())->assertCreated();

        Notification::assertSentOnDemand(
            NewContactSubmission::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === ['admin@example.com']
        );
    }

    public function test_notification_is_not_dispatched_when_admin_email_is_not_configured(): void
    {
        Mail::fake();
        Notification::fake();

        $this->postJson('/kontak', $this->payload())->assertCreated();

        Notification::assertNothingSent();
    }

    // --- Proteksi anti-bot -------------------------------------------------

    public function test_filled_honeypot_is_silently_rejected(): void
    {
        Mail::fake();
        Notification::fake();

        // Dibalas seolah sukses supaya bot tidak tahu dia terdeteksi...
        $this->postJson('/kontak', $this->payload([
            SubmissionGuard::HONEYPOT_FIELD => 'http://spam.example.com',
        ]))->assertCreated();

        // ...tapi tidak ada yang tersimpan & tidak ada email terkirim.
        $this->assertDatabaseCount('contact_submissions', 0);
        Mail::assertNothingQueued();
        Notification::assertNothingSent();
    }

    public function test_submission_that_is_too_fast_is_silently_rejected(): void
    {
        Mail::fake();

        // Token dibuat "sekarang" = form disubmit < 3 detik setelah dirender.
        $this->postJson('/kontak', $this->payload([
            'form_token' => SubmissionGuard::issueToken(),
        ]))->assertCreated();

        $this->assertDatabaseCount('contact_submissions', 0);
        Mail::assertNothingQueued();
    }

    public function test_missing_or_invalid_token_is_silently_rejected(): void
    {
        Mail::fake();

        // Request langsung ke endpoint (curl/Postman) tanpa pernah membuka form.
        $this->postJson('/kontak', $this->payload(['form_token' => null]))->assertCreated();
        $this->postJson('/kontak', $this->payload(['form_token' => 'bukan-token-asli']))->assertCreated();

        $this->assertDatabaseCount('contact_submissions', 0);
    }

    public function test_old_token_is_still_accepted(): void
    {
        Mail::fake();

        // Pengunjung yang membiarkan tab terbuka semalaman tetap pelanggan asli
        // dan tidak boleh ditolak.
        $this->postJson('/kontak', $this->payload([
            'form_token' => Crypt::encryptString((string) now()->subDays(2)->timestamp),
        ]))->assertCreated();

        $this->assertDatabaseCount('contact_submissions', 1);
    }

    // --- Rate limit per nomor/email -----------------------------------------

    public function test_too_many_submissions_from_same_phone_are_blocked(): void
    {
        Mail::fake();

        // Throttle per IP (routes/web.php) dimatikan supaya yang diuji di sini
        // benar-benar batas per nomor telepon.
        $this->withoutMiddleware(ThrottleRequests::class);

        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/kontak', $this->payload(['email' => "budi{$i}@example.com"]))->assertCreated();
        }

        $this->postJson('/kontak', $this->payload(['email' => 'budi-lain@example.com']))->assertStatus(429);
    }

    public function test_too_many_submissions_from_same_email_are_blocked(): void
    {
        Mail::fake();

        $this->withoutMiddleware(ThrottleRequests::class);

        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/kontak', $this->payload(['phone' => '08123456'.str_pad((string) $i, 4, '0')]))->assertCreated();
        }

        $this->postJson('/kontak', $this->payload(['phone' => '089876543210']))->assertStatus(429);
    }
}
