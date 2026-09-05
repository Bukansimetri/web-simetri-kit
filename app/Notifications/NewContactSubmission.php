<?php

namespace App\Notifications;

use App\Models\ContactSubmission;
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
        return (new MailMessage)
            ->subject('Pesan Baru dari Form Kontak')
            ->greeting('Ada pesan baru masuk dari form Kontak:')
            ->line('Nama: '.$this->submission->name)
            ->line('No. HP/WhatsApp: '.$this->submission->phone)
            ->line('Topik: '.($this->submission->topic ?: '-'))
            ->line('Pesan: '.$this->submission->message)
            ->line('Waktu masuk: '.$this->submission->created_at->translatedFormat('d F Y H:i'));
    }
}
