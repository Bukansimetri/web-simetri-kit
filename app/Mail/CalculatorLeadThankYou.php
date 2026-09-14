<?php

namespace App\Mail;

use App\Models\CalculatorLead;
use App\Settings\BrandSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Email terima kasih ke pelanggan setelah mengisi form "Hitung Estimasi
 * Penghematan" (home). Hanya dikirim bila pelanggan mengisi kolom email
 * (opsional) — lihat CalculatorController::storeLead(). `ShouldQueue` agar
 * kegagalan SMTP tidak memperlambat/menggagalkan response ke pengunjung.
 */
class CalculatorLeadThankYou extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public CalculatorLead $lead) {}

    public function build(): self
    {
        $brand = app(BrandSettings::class);
        $brandName = $brand->app_name ?: config('app.name');

        return $this
            ->subject("Estimasi penghematan listrik Anda — {$brandName}")
            ->markdown('emails.calculator-lead-thank-you', [
                'lead' => $this->lead,
                'brandName' => $brandName,
                'whatsappUrl' => $brand->whatsappUrl(
                    sprintf('Halo, saya %s. Saya ingin bertanya lebih lanjut soal estimasi hemat yang baru saya hitung.', $this->lead->name)
                ),
                'inputSummary' => $this->buildInputSummary(),
            ]);
    }

    private function buildInputSummary(): string
    {
        $lead = $this->lead;

        if ($lead->method === 'bill') {
            $lines = ['Tagihan listrik bulanan: Rp '.number_format((int) $lead->monthly_bill, 0, ',', '.')];

            if (filled($lead->va_capacity)) {
                $lines[] = "Kapasitas daya PLN: {$lead->va_capacity} VA";
            }

            return implode(' · ', $lines);
        }

        $appliances = collect($lead->appliances ?? [])
            ->map(fn (array $a) => "{$a['label']} ×{$a['qty']}")
            ->implode(', ');

        return "Peralatan: {$appliances}";
    }
}
