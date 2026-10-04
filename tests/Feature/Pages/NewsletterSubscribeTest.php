<?php

namespace Tests\Feature\Pages;

use App\Services\SubmissionGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class NewsletterSubscribeTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Token yang dirender 10 detik lalu, melewati batas isi minimum.
     */
    private function token(int $secondsAgo = 10): string
    {
        $this->travel(0)->seconds();
        $token = Crypt::encryptString((string) now()->subSeconds($secondsAgo)->timestamp);

        return $token;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return [
            'email' => 'Pengunjung@Example.com',
            'form_token' => $this->token(),
            ...$overrides,
        ];
    }

    public function test_valid_email_is_stored_lowercase_and_confirmed(): void
    {
        $this->postJson('/langganan', $this->payload())
            ->assertCreated()
            ->assertJson(['message' => 'Terima kasih, Anda sudah berlangganan.']);

        $this->assertDatabaseHas('newsletter_subscribers', ['email' => 'pengunjung@example.com']);
    }

    public function test_invalid_or_missing_email_is_rejected_and_nothing_is_stored(): void
    {
        $this->postJson('/langganan', $this->payload(['email' => 'bukan-email']))
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');

        $this->postJson('/langganan', $this->payload(['email' => '']))
            ->assertStatus(422);

        $this->assertDatabaseCount('newsletter_subscribers', 0);
    }

    public function test_same_email_twice_creates_one_row_with_identical_response(): void
    {
        $first = $this->postJson('/langganan', $this->payload());
        $second = $this->postJson('/langganan', $this->payload(['email' => 'pengunjung@example.com']));

        $first->assertCreated();
        $second->assertCreated();
        $this->assertSame($first->json(), $second->json());
        $this->assertDatabaseCount('newsletter_subscribers', 1);
    }

    public function test_filled_honeypot_gets_fake_success_without_storing(): void
    {
        $this->postJson('/langganan', $this->payload([SubmissionGuard::HONEYPOT_FIELD => 'http://spam.test']))
            ->assertCreated();

        $this->assertDatabaseCount('newsletter_subscribers', 0);
    }

    public function test_submit_too_fast_or_without_token_gets_fake_success_without_storing(): void
    {
        $this->postJson('/langganan', $this->payload(['form_token' => $this->token(0)]))->assertCreated();
        $this->postJson('/langganan', ['email' => 'tanpa-token@example.com'])->assertCreated();

        $this->assertDatabaseCount('newsletter_subscribers', 0);
    }

    public function test_too_many_attempts_for_one_email_returns_429(): void
    {
        RateLimiter::clear('newsletter-subscribe:email:pengunjung@example.com');

        for ($i = 0; $i < 10; $i++) {
            RateLimiter::hit('newsletter-subscribe:email:pengunjung@example.com', 3600);
        }

        $this->postJson('/langganan', $this->payload())->assertStatus(429);
        $this->assertDatabaseCount('newsletter_subscribers', 0);
    }

    public function test_route_is_throttled_per_ip(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/langganan', $this->payload(['email' => "u{$i}@example.com"]))->assertCreated();
        }

        $this->postJson('/langganan', $this->payload(['email' => 'u6@example.com']))->assertStatus(429);
    }

    public function test_without_javascript_success_redirects_back_with_flash(): void
    {
        $this->post('/langganan', $this->payload())
            ->assertRedirect(url('/artikel').'#langganan')
            ->assertSessionHas('newsletter_status');

        $this->assertDatabaseCount('newsletter_subscribers', 1);
    }

    public function test_without_javascript_invalid_email_redirects_back_with_error(): void
    {
        $this->post('/langganan', $this->payload(['email' => 'salah']))
            ->assertRedirect(url('/artikel').'#langganan')
            ->assertSessionHas('newsletter_error');

        $this->assertDatabaseCount('newsletter_subscribers', 0);
    }
}
