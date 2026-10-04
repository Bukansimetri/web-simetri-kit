<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Services\SubmissionGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    /**
     * Batas pendaftaran per email per jam. Melengkapi throttle per IP di
     * routes/web.php, pola sama dengan ContactController.
     */
    private const MAX_PER_EMAIL_PER_HOUR = 10;

    private const SUCCESS_MESSAGE = 'Terima kasih, Anda sudah berlangganan.';

    public function store(Request $request, SubmissionGuard $guard): JsonResponse|RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email' => 'Alamat email tidak valid.',
            'email.max' => 'Alamat email terlalu panjang.',
        ]);

        if ($validator->fails()) {
            return $this->failure($request, $validator->errors()->first('email'), 422, $validator->errors()->toArray());
        }

        // Submit otomatis dibalas seolah sukses tanpa menyimpan apa pun.
        if ($guard->looksAutomated($request)) {
            return $this->success($request);
        }

        $email = mb_strtolower(trim($validator->validated()['email']));
        $key = 'newsletter-subscribe:email:'.$email;

        if (RateLimiter::tooManyAttempts($key, self::MAX_PER_EMAIL_PER_HOUR)) {
            return $this->failure($request, 'Terlalu banyak percobaan. Silakan coba lagi nanti.', 429);
        }

        RateLimiter::hit($key, 3600);

        NewsletterSubscriber::query()->firstOrCreate(
            ['email' => $email],
            ['subscribed_at' => now()],
        );

        return $this->success($request);
    }

    private function success(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => self::SUCCESS_MESSAGE], 201);
        }

        return redirect(url('/artikel').'#langganan')->with('newsletter_status', self::SUCCESS_MESSAGE);
    }

    /**
     * @param  array<string, array<int, string>>  $errors
     */
    private function failure(Request $request, string $message, int $status, array $errors = []): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message, 'errors' => $errors], $status);
        }

        return redirect(url('/artikel').'#langganan')->with('newsletter_error', $message)->withInput($request->only('email'));
    }
}
