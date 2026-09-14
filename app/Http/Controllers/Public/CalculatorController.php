<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Mail\CalculatorLeadThankYou;
use App\Models\CalculatorLead;
use App\Notifications\NewCalculatorLead;
use App\Services\SavingsEstimator;
use App\Settings\BrandSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;

class CalculatorController extends Controller
{
    /**
     * Simpan lead dari form "Hitung Estimasi Penghematan" (home) sebagai
     * CalculatorLead. Perhitungan SEPENUHNYA dilakukan di sini lewat
     * SavingsEstimator — client hanya mengirim input mentah (tagihan/
     * peralatan), bukan hasil hitungan, supaya angka yang tersimpan, yang
     * dikirim lewat email, dan yang ditampilkan di CMS selalu konsisten satu
     * sama lain dan tidak bisa dimanipulasi dari browser.
     */
    public function storeLead(Request $request, SavingsEstimator $estimator): JsonResponse
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

        $lead = CalculatorLead::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
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
        ]);

        $settings = app(BrandSettings::class);

        if (filled($lead->email)) {
            Mail::to($lead->email)->queue(new CalculatorLeadThankYou($lead));
        }

        if (filled($settings->contact_notification_email)) {
            // Boleh diisi beberapa email dipisah koma di Settings.
            $recipients = array_map('trim', explode(',', $settings->contact_notification_email));

            Notification::route('mail', $recipients)
                ->notify(new NewCalculatorLead($lead));
        }

        return response()->json([
            'message' => 'Estimasi Anda telah kami terima. Tim kami akan menghubungi Anda.',
            'result' => $estimate['result'],
            'chart' => $estimate['chart'],
            'whatsapp_url' => $settings->whatsappUrl(
                sprintf(
                    "Halo, saya %s. Saya baru saja menghitung estimasi hemat via kalkulator %s.\n\nEstimasi hemat tahun 1: Rp %s\nBreakeven: %s tahun",
                    $lead->name,
                    $settings->app_name ?: config('app.name'),
                    number_format($estimate['result']['savings_year1'], 0, ',', '.'),
                    $estimate['result']['breakeven_years'],
                )
            ),
        ], 201);
    }
}
