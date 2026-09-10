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

class CalculatorController extends Controller
{
    /**
     * Simpan lead dari Kalkulator Estimasi Hemat (Home) sebagai
     * ContactSubmission dengan topic `kalkulator`. Ringkasan perhitungan
     * (dari sisi client) disertakan di message agar tim sales bisa langsung
     * menindaklanjuti. Notifikasi email admin memakai alur yang sama dengan
     * form Kontak (AMC-216).
     */
    public function storeLead(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^[0-9+\-\s]{8,15}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'area' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:residential,industrial'],
            'summary' => ['required', 'string', 'max:2000'],
        ], [
            'phone.regex' => 'Nomor WhatsApp tidak valid.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data yang dikirim tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        $categoryLabel = $data['category'] === 'industrial' ? 'Industrial / Komersial' : 'Residential';

        $message = sprintf(
            "Lead dari Kalkulator Estimasi Hemat (%s).\n\n%s",
            $categoryLabel,
            $data['summary'],
        );

        $submission = ContactSubmission::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'area' => $data['area'] ?? null,
            'topic' => 'kalkulator',
            'message' => $message,
        ]);

        $settings = app(BrandSettings::class);

        if (filled($settings->contact_notification_email)) {
            Notification::route('mail', $settings->contact_notification_email)
                ->notify(new NewContactSubmission($submission));
        }

        return response()->json([
            'message' => 'Estimasi Anda telah kami terima. Tim kami akan menghubungi Anda.',
            'whatsapp_url' => $settings->whatsappUrl(
                sprintf("Halo, saya %s. Saya baru saja menghitung estimasi hemat via kalkulator SUOER.\n\n%s", $submission->name, $data['summary'])
            ),
        ], 201);
    }
}
