<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\ContactSubmission;
use App\Notifications\NewContactSubmission;
use App\Settings\BrandSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
        return view('pages.kontak');
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
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^[0-9+\-\s]{8,15}$/'],
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

        $submission = ContactSubmission::create([
            'name' => $validated['nama'],
            'phone' => $validated['phone'],
            'topic' => $validated['kebutuhan'] ?? null,
            'message' => $validated['pesan'],
        ]);

        $settings = app(BrandSettings::class);

        if (filled($settings->contact_notification_email)) {
            Notification::route('mail', $settings->contact_notification_email)
                ->notify(new NewContactSubmission($submission));
        }

        $whatsappMessage = sprintf(
            "Halo, saya %s.\n\n%s",
            $submission->name,
            $submission->message
        );

        return response()->json([
            'message' => 'Pesan Anda telah kami terima.',
            'whatsapp_url' => $settings->whatsappUrl($whatsappMessage),
        ], 201);
    }
}
