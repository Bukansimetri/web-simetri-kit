<?php

namespace Tests\Unit\Services;

use App\Services\SubmissionGuard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class SubmissionGuardTest extends TestCase
{
    private function request(array $input): Request
    {
        return Request::create('/kalkulator/lead', 'POST', $input);
    }

    private function guard(): SubmissionGuard
    {
        return new SubmissionGuard;
    }

    private function tokenAgedSeconds(int $seconds): string
    {
        return Crypt::encryptString((string) now()->subSeconds($seconds)->timestamp);
    }

    public function test_normal_submission_passes(): void
    {
        $this->assertFalse($this->guard()->looksAutomated($this->request([
            'form_token' => $this->tokenAgedSeconds(30),
        ])));
    }

    public function test_filled_honeypot_is_flagged(): void
    {
        $this->assertTrue($this->guard()->looksAutomated($this->request([
            'form_token' => $this->tokenAgedSeconds(30),
            SubmissionGuard::HONEYPOT_FIELD => 'apa saja',
        ])));
    }

    public function test_missing_token_is_flagged(): void
    {
        $this->assertTrue($this->guard()->looksAutomated($this->request([])));
    }

    public function test_garbage_token_is_flagged(): void
    {
        $this->assertTrue($this->guard()->looksAutomated($this->request([
            'form_token' => 'bukan-token-asli',
        ])));
    }

    public function test_submission_faster_than_minimum_is_flagged(): void
    {
        $this->assertTrue($this->guard()->looksAutomated($this->request([
            'form_token' => $this->tokenAgedSeconds(0),
        ])));
    }

    public function test_old_token_is_not_flagged(): void
    {
        // Tab dibiarkan terbuka berhari-hari tetap pelanggan asli.
        $this->assertFalse($this->guard()->looksAutomated($this->request([
            'form_token' => $this->tokenAgedSeconds(60 * 60 * 24 * 7),
        ])));
    }
}
