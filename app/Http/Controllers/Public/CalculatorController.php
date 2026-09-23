<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\CalculatorLeadThankYou;
use App\Models\CalculatorLead;
use App\Notifications\NewCalculatorLead;
use App\Services\SavingsEstimator;
use App\Services\SubmissionGuard;
use App\Settings\SiteSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class CalculatorController extends Controller
{
    /**
     * Jendela waktu di mana submit ulang dari nomor yang sama dianggap orang
     * yang sama sedang mencoba angka lain — bukan lead baru.
     */
    private const DEDUPE_WINDOW_MINUTES = 30;

    /**
     * Batas submit per nomor telepon per jam. Melengkapi throttle per IP di
     * routes/web.php: satu IP kantor bisa dipakai banyak pelanggan asli,
     * sebaliknya satu bot bisa berganti-ganti IP tapi tetap memakai nomor
     * yang itu-itu saja.
     */
    private const MAX_PER_PHONE_PER_HOUR = 10;

    /**
     * Simpan lead dari form "Hitung Estimasi Penghematan" (home) sebagai
     * CalculatorLead. Perhitungan SEPENUHNYA dilakukan di sini lewat
     * SavingsEstimator — client hanya mengirim input mentah (tagihan/
     * peralatan), bukan hasil hitungan, supaya angka yang tersimpan, yang
     * dikirim lewat email, dan yang ditampilkan di CMS selalu konsisten satu
     * sama lain dan tidak bisa dimanipulasi dari browser.
     */
    public function storeLead(Request $request, SavingsEstimator $estimator, SubmissionGuard $guard): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^[0-9+\-\s]{8,15}$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'area' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:residential,industrial'],
            'method' => ['required', 'string', 'in:bill,appliance'],
            'monthly_bill' => ['required_if:method,bill', 'nullable', 'integer', 'min:1'],
            'va_capacity' => ['nullable', 'string', 'max:50'],
            'appliances' => ['required_if:method,appliance', 'nullable', 'array'],
            'appliances.*.key' => ['required_with:appliances', 'string'],
            'appliances.*.qty' => ['required_with:appliances', 'integer', 'min:0'],
            'utm' => ['nullable', 'array'],
        ], [
            'phone.regex' => 'Nomor WhatsApp tidak valid.',
            'monthly_bill.required_if' => 'Masukkan tagihan listrik bulanan.',
            'appliances.required_if' => 'Pilih minimal satu peralatan.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Data yang dikirim tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        // Submit otomatis (honeypot/token waktu) dibalas seolah sukses tapi
        // tidak disimpan & tidak memicu email apa pun — bot tidak tahu dia
        // terdeteksi sehingga tidak mencoba variasi lain.
        if ($guard->looksAutomated($request)) {
            return $this->fakeSuccessResponse();
        }

        $normalizedPhone = CalculatorLead::normalizePhone($data['phone']);

        if (RateLimiter::tooManyAttempts('calculator-lead:'.$normalizedPhone, self::MAX_PER_PHONE_PER_HOUR)) {
            return response()->json([
                'message' => 'Terlalu banyak percobaan dari nomor ini. Silakan coba lagi nanti atau hubungi kami langsung via WhatsApp.',
            ], 429);
        }

        // Industrial hanya punya metode "bill" di UI (lihat calculator.js
        // effectiveMethod) — dipaksakan di sini juga agar konsisten walau
        // request dikirim manual/di luar UI.
        $method = $data['category'] === 'industrial' ? 'bill' : $data['method'];

        try {
            $estimate = $estimator->estimate(
                method: $method,
                monthlyBill: $data['monthly_bill'] ?? null,
                appliances: $data['appliances'] ?? null,
            );
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => ['method' => [$e->getMessage()]],
            ], 422);
        }

        RateLimiter::hit('calculator-lead:'.$normalizedPhone, 3600);

        $attributes = [
            'name' => $data['name'],
            'phone' => $data['phone'],
            'phone_normalized' => $normalizedPhone,
            'email' => $data['email'] ?? null,
            'area' => $data['area'] ?? null,
            'category' => $data['category'],
            'method' => $method,
            'monthly_bill' => $method === SavingsEstimator::METHOD_BILL ? ($data['monthly_bill'] ?? null) : null,
            'va_capacity' => $data['va_capacity'] ?? null,
            'appliances' => $estimate['normalized_appliances'],
            'total_watt' => $estimate['total_watt'],
            'estimated_monthly_bill' => $estimate['estimated_monthly_bill'],
            'savings_year1' => $estimate['result']['savings_year1'],
            'total_savings_25y' => $estimate['result']['total_savings_25y'],
            'estimated_investment' => $estimate['result']['estimated_investment'],
            'breakeven_years' => $estimate['result']['breakeven_years'],
            'annual_kwh' => $estimate['result']['annual_kwh'],
            'assumptions' => $estimate['assumptions'],
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'referrer' => $request->headers->get('referer'),
            'utm' => $data['utm'] ?? null,
        ];

        $recent = $this->findRecentLead($normalizedPhone);

        if ($recent) {
            // Orang yang sama mencoba angka lain: perbarui lead yang ada
            // supaya sales melihat SATU calon pelanggan dengan hitungan
            // terbarunya, bukan tumpukan baris duplikat. Jejak follow-up
            // (status/catatan/PIC) sengaja tidak disentuh.
            $emailWasMissing = blank($recent->email);
            $recent->update($attributes);

            // Email admin tidak dikirim ulang (mereka sudah tahu lead ini).
            // Email ke pelanggan hanya dikirim bila sebelumnya dia belum
            // mengisi email dan baru sekarang mengisinya.
            if ($emailWasMissing && filled($recent->email)) {
                Mail::to($recent->email)->queue(new CalculatorLeadThankYou($recent));
            }

            return $this->successResponse($recent, $estimate);
        }

        $lead = CalculatorLead::create($attributes);

        if (filled($lead->email)) {
            Mail::to($lead->email)->queue(new CalculatorLeadThankYou($lead));
        }

        $settings = app(SiteSettings::class);

        if (filled($settings->contact_notification_email)) {
            // Boleh diisi beberapa email dipisah koma di Settings.
            $recipients = array_map('trim', explode(',', $settings->contact_notification_email));

            Notification::route('mail', $recipients)
                ->notify(new NewCalculatorLead($lead));
        }

        return $this->successResponse($lead, $estimate);
    }

    private function findRecentLead(string $normalizedPhone): ?CalculatorLead
    {
        return CalculatorLead::query()
            ->where('phone_normalized', $normalizedPhone)
            ->where('created_at', '>=', now()->subMinutes(self::DEDUPE_WINDOW_MINUTES))
            ->latest('id')
            ->first();
    }

    /**
     * @param  array<string, mixed>  $estimate
     */
    private function successResponse(CalculatorLead $lead, array $estimate): JsonResponse
    {
        $settings = app(SiteSettings::class);

        return response()->json([
            'message' => 'Estimasi Anda telah kami terima. Tim kami akan menghubungi Anda.',
            'result' => $estimate['result'],
            'chart' => $estimate['chart'],
            'whatsapp_url' => $settings->whatsappUrl(
                sprintf(
                    "Halo, saya %s. Saya baru saja menghitung estimasi hemat via kalkulator %s.\n\nEstimasi hemat tahun 1: Rp %s\nBreakeven: %s tahun",
                    $lead->name,
                    $settings->site_name ?: config('app.name'),
                    number_format($estimate['result']['savings_year1'], 0, ',', '.'),
                    $estimate['result']['breakeven_years'],
                )
            ),
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
            'message' => 'Estimasi Anda telah kami terima. Tim kami akan menghubungi Anda.',
            'result' => [
                'savings_year1' => 0,
                'total_savings_25y' => 0,
                'estimated_investment' => 0,
                'breakeven_years' => 0,
                'annual_kwh' => 0,
            ],
            'chart' => ['investment' => 0, 'points' => []],
            'whatsapp_url' => null,
        ], 201);
    }
}
