<?php

namespace Tests\Feature\Pages;

use App\Models\ContactSubmission;
use App\Notifications\NewContactSubmission;
use App\Settings\BrandSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class ContactPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_renders_form_fields(): void
    {
        $response = $this->get('/kontak');

        $response->assertOk();
        $response->assertSee('name="nama"', escape: false);
        $response->assertSee('name="phone"', escape: false);
        $response->assertSee('name="pesan"', escape: false);
    }

    public function test_submitting_valid_data_saves_the_submission(): void
    {
        $response = $this->postJson('/kontak', [
            'nama' => 'Budi Santoso',
            'phone' => '081234567890',
            'kebutuhan' => 'residensial',
            'pesan' => 'Saya tertarik dengan panel surya untuk rumah.',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('contact_submissions', [
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'topic' => 'residensial',
            'message' => 'Saya tertarik dengan panel surya untuk rumah.',
            'status' => ContactSubmission::STATUS_NEW,
        ]);
    }

    public function test_submitting_without_required_fields_is_rejected_and_not_saved(): void
    {
        $response = $this->postJson('/kontak', [
            'nama' => '',
            'phone' => 'bukan-nomor',
            'pesan' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nama', 'phone', 'pesan']);
        $this->assertSame(0, ContactSubmission::count());
    }

    public function test_response_includes_whatsapp_url_when_configured(): void
    {
        $settings = app(BrandSettings::class);
        $settings->whatsapp_number = '6281234567890';
        $settings->save();

        $response = $this->postJson('/kontak', [
            'nama' => 'Budi Santoso',
            'phone' => '081234567890',
            'kebutuhan' => 'umum',
            'pesan' => 'Halo, saya mau tanya-tanya.',
        ]);

        $response->assertCreated();
        $response->assertJsonStructure(['message', 'whatsapp_url']);
        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $response->json('whatsapp_url'));
    }

    public function test_response_whatsapp_url_is_null_when_not_configured(): void
    {
        $response = $this->postJson('/kontak', [
            'nama' => 'Budi Santoso',
            'phone' => '081234567890',
            'pesan' => 'Halo.',
        ]);

        $response->assertCreated();
        $this->assertNull($response->json('whatsapp_url'));
    }

    public function test_submission_is_rate_limited(): void
    {
        RateLimiter::clear('kontak-submit:127.0.0.1');

        $payload = [
            'nama' => 'Budi Santoso',
            'phone' => '081234567890',
            'pesan' => 'Halo.',
        ];

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/kontak', $payload)->assertCreated();
        }

        $this->postJson('/kontak', $payload)->assertStatus(429);
    }

    public function test_notification_is_dispatched_when_admin_email_is_configured(): void
    {
        Notification::fake();

        $settings = app(BrandSettings::class);
        $settings->contact_notification_email = 'admin@example.com';
        $settings->save();

        $this->postJson('/kontak', [
            'nama' => 'Budi Santoso',
            'phone' => '081234567890',
            'pesan' => 'Halo.',
        ])->assertCreated();

        Notification::assertSentOnDemand(
            NewContactSubmission::class,
            fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'admin@example.com'
        );
    }

    public function test_notification_is_not_dispatched_when_admin_email_is_not_configured(): void
    {
        Notification::fake();

        $this->postJson('/kontak', [
            'nama' => 'Budi Santoso',
            'phone' => '081234567890',
            'pesan' => 'Halo.',
        ])->assertCreated();

        Notification::assertNothingSent();
    }
}
