<?php

namespace App\Mail;

use App\Models\ContactSubmission;
use App\Settings\SiteSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Email terima kasih ke pelanggan setelah mengirim form Kontak. `ShouldQueue`
 * agar kegagalan SMTP tidak memperlambat/menggagalkan response ke pengunjung
 * — pola sama dengan CalculatorLeadThankYou.
 */
class ContactSubmissionThankYou extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(public ContactSubmission $submission) {}

    public function build(): self
    {
        $brand = app(SiteSettings::class);
        $brandName = $brand->site_name ?: config('app.name');

        return $this
            ->subject("Pesan Anda telah kami terima — {$brandName}")
            ->markdown('emails.contact-submission-thank-you', [
                'submission' => $this->submission,
                'brandName' => $brandName,
                'whatsappUrl' => $brand->whatsappUrl(
                    sprintf('Halo, saya %s. Saya baru saja mengirim pesan lewat form kontak dan ingin menindaklanjutinya.', $this->submission->name)
                ),
            ]);
    }
}
