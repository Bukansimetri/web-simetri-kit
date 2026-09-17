<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\ContactSubmissionThankYou;
use App\Models\ContactSubmission;
use App\Notifications\NewContactSubmission;
use App\Services\SubmissionGuard;
use App\Settings\BrandSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ContactController extends Controller
{
    /**
     * Batas submit per nomor/email per jam. Melengkapi throttle per IP di
     * routes/web.php: satu IP kantor bisa dipakai banyak pelanggan asli,
     * sebaliknya satu bot bisa berganti-ganti IP tapi tetap memakai nomor
     * atau email yang itu-itu saja — pola sama dengan CalculatorController.
     */
    private const MAX_PER_KEY_PER_HOUR = 10;

    public function show(): View
    {
        return view('pages.kontak', [
            'formToken' => SubmissionGuard::issueToken(),
        ]);
    }

    /**
     * Simpan submission form Kontak (FR-001/FR-002), lalu kembalikan link
     * WhatsApp pre-filled bila nomor bisnis dikonfigurasi (FR-012/FR-013) dan
     * dispatch notifikasi email ke admin bila alamatnya dikonfigurasi
     * (FR-008/FR-010) — lihat AMC-216.
     *
     * Validasi ditangani manual (bukan `$request->validate()`) karena
     * `bootstrap/app.php` hanya me-render exception sebagai JSON untuk path
     * `api/*` — route ini (`/kontak`) butuh respons JSON eksplisit tanpa
     * bergantung pada content-negotiation exception handler global.
     */
    public function store(Request $request, SubmissionGuard $guard): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^[0-9+\-\s]{8,15}$/'],
            'email' => ['required', 'email', 'max:255'],
            'kebutuhan' => ['nullable', 'string', 'in:umum,residensial,komersial,pompa'],
            'pesan' => ['required', 'string'],
        ], [
            'phone.regex' => 'Nomor HP/WhatsApp tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data yang dikirim tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        // Submit otomatis (honeypot/token waktu) dibalas seolah sukses tapi
        // tidak disimpan & tidak memicu email apa pun — bot tidak tahu dia
        // terdeteksi sehingga tidak mencoba variasi lain. Pola sama dengan
        // CalculatorController::storeLead().
        if ($guard->looksAutomated($request)) {
            return $this->fakeSuccessResponse();
        }

        $phoneKey = 'contact-submit:phone:'.preg_replace('/[^0-9]/', '', $validated['phone']);
        $emailKey = 'contact-submit:email:'.mb_strtolower($validated['email']);

        if (RateLimiter::tooManyAttempts($phoneKey, self::MAX_PER_KEY_PER_HOUR)
            || RateLimiter::tooManyAttempts($emailKey, self::MAX_PER_KEY_PER_HOUR)) {
            return response()->json([
                'message' => 'Terlalu banyak percobaan dari nomor/email ini. Silakan coba lagi nanti atau hubungi kami langsung via WhatsApp.',
            ], 429);
        }

        RateLimiter::hit($phoneKey, 3600);
        RateLimiter::hit($emailKey, 3600);

        $submission = ContactSubmission::create([
            'name' => $validated['nama'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'topic' => $validated['kebutuhan'] ?? null,
            'message' => $validated['pesan'],
        ]);

        Mail::to($submission->email)->queue(new ContactSubmissionThankYou($submission));

        $settings = app(BrandSettings::class);

        if (filled($settings->contact_notification_email)) {
            // Boleh diisi beberapa email dipisah koma di Settings.
            $recipients = array_map('trim', explode(',', $settings->contact_notification_email));

            Notification::route('mail', $recipients)
                ->notify(new NewContactSubmission($submission));
        }

        return response()->json([
            'message' => 'Pesan Anda telah kami terima.',
            'whatsapp_url' => $settings->whatsappUrl(sprintf(
                "Halo, saya %s.\n\n%s",
                $submission->name,
                $submission->message
            )),
        ], 201);
    }

    /**
     * Balasan untuk submit yang terdeteksi otomatis: bentuknya sama persis
     * dengan sukses (supaya bot tidak bisa membedakan), tapi tidak ada data
     * yang tersimpan dan tidak ada email yang dikirim.
     */
    private function fakeSuccessResponse(): JsonResponse
    {
        return response()->json([
            'message' => 'Pesan Anda telah kami terima.',
            'whatsapp_url' => null,
        ], 201);
    }
}
