<?php

namespace App\Notifications;

use App\Filament\Resources\CalculatorLeadResource;
use App\Models\CalculatorLead;
use App\Settings\BrandSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi ke admin/sales saat ada lead baru dari kalkulator estimasi
 * hemat (home) — tujuannya supaya tim bisa follow up cepat. Subjek sengaja
 * memuat nama, area, dan potensi hemat/tahun supaya sales bisa menentukan
 * prioritas langsung dari daftar inbox tanpa perlu membuka emailnya.
 * `ShouldQueue` agar pengiriman tidak menunda response ke pengunjung dan
 * kegagalan SMTP tidak memengaruhi lead yang sudah tersimpan.
 */
class NewCalculatorLead extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public CalculatorLead $lead) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $lead = $this->lead;
        $brandName = app(BrandSettings::class)->app_name ?: config('app.name');
        $categoryLabel = $lead->category === 'industrial' ? 'Industrial / Komersial' : 'Residential';
        $savings = number_format($lead->savings_year1, 0, ',', '.');
        $areaLabel = filled($lead->area) ? $lead->area : '-';

        $message = (new MailMessage)
            ->subject("Lead baru kalkulator: {$lead->name} — {$areaLabel} · potensi hemat Rp {$savings}/tahun")
            ->greeting('⚡ Ada calon pelanggan baru — mohon follow up dalam 1×24 jam.')
            ->line("**Nama:** {$lead->name}")
            ->line("**No. WhatsApp:** {$lead->phone}")
            ->line('**Email:** '.($lead->email ?: '-'))
            ->line("**Area:** {$areaLabel}")
            ->line("**Kategori:** {$categoryLabel}")
            ->line('')
            ->line("**Estimasi hemat tahun 1:** Rp {$savings}")
            ->line('**Total hemat 25 tahun:** Rp '.number_format($lead->total_savings_25y, 0, ',', '.'))
            ->line("**Breakeven:** {$lead->breakeven_years} tahun")
            ->line('Waktu masuk: '.$lead->created_at->translatedFormat('d F Y H:i'))
            ->action('Buka di CMS ('.$brandName.')', CalculatorLeadResource::getUrl('edit', ['record' => $lead]));

        $waUrl = app(BrandSettings::class)->whatsappUrl("Halo {$lead->name}, terima kasih sudah menghitung estimasi hemat di {$brandName}. Boleh saya bantu jadwalkan survei lokasi gratis?");

        if ($waUrl) {
            $message->line('Chat langsung: '.$waUrl);
        }

        return $message;
    }
}
