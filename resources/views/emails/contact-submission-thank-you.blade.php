@component('mail::message')
# Terima kasih, {{ $submission->name }}

Pesan Anda sudah kami terima di **{{ $brandName }}**. Tim kami akan meninjau dan menghubungi Anda kembali dalam 1×24 jam kerja.

@component('mail::table')
| | |
| --- | --- |
| No. HP/WhatsApp | {{ $submission->phone }} |
| Topik | {{ $submission->topic ?: '-' }} |
@endcomponent

**Pesan Anda:**

{{ $submission->message }}

@if ($whatsappUrl)
Kalau ingin lebih cepat, Anda juga bisa langsung chat kami via WhatsApp.

@component('mail::button', ['url' => $whatsappUrl])
Chat WhatsApp
@endcomponent
@endif

Salam,<br>
Tim {{ $brandName }}

---

<small>Email ini dikirim karena email Anda diisikan pada form Kontak di situs kami. Bila ini bukan Anda, cukup abaikan email ini.</small>
@endcomponent
