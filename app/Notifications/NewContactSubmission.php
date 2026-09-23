<?php

namespace App\Notifications;

use App\Filament\Resources\ContactSubmissionResource;
use App\Models\ContactSubmission;
use App\Settings\SiteSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Notifikasi ke admin saat ada submission baru dari form Kontak (FR-008).
 * `ShouldQueue` membuat pengiriman terjadi di luar siklus request/response
 * HTTP — kegagalan SMTP nanti saat job diproses TIDAK MEMPENGARUHI submission
 * yang sudah tersimpan atau konfirmasi yang sudah dilihat pengunjung (FR-009).
 */
class NewContactSubmission extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ContactSubmission $submission) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $submission = $this->submission;
        $brandName = app(SiteSettings::class)->site_name ?: config('app.name');

        $message = (new MailMessage)
            ->subject("Pesan baru dari form Kontak: {$submission->name}")
            ->greeting('✉️ Ada pesan baru masuk dari form Kontak — mohon follow up dalam 1×24 jam.')
            ->line("**Nama:** {$submission->name}")
            ->line("**No. HP/WhatsApp:** {$submission->phone}")
            ->line('**Email:** '.($submission->email ?: '-'))
            ->line('**Area:** '.($submission->area ?: '-'))
            ->line('**Topik:** '.($submission->topic ?: '-'))
            ->line('**Pesan:** '.$submission->message)
            ->line('Waktu masuk: '.$submission->created_at->translatedFormat('d F Y H:i'))
            ->action('Buka di CMS ('.$brandName.')', ContactSubmissionResource::getUrl('edit', ['record' => $submission]));

        $waUrl = app(SiteSettings::class)->whatsappUrl("Halo {$submission->name}, terima kasih sudah menghubungi {$brandName} lewat form kontak. Boleh saya bantu?");

        if ($waUrl) {
            $message->line('Chat langsung: '.$waUrl);
        }

        return $message;
    }
}
