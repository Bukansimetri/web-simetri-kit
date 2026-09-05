# Research: Contact Us Backend

**Date**: 2026-09-05 | **Feature**: [spec.md](./spec.md)

## 1. Notifikasi email ke admin: Laravel Notification (mail channel) yang di-queue

**Decision**: `App\Notifications\NewContactSubmission` (implements `ShouldQueue`), dikirim lewat `Notification::route('mail', $email)->notify(...)` ke alamat yang dikonfigurasi admin (bukan model User/Notifiable — installasi mungkin belum punya user selain admin panel).

**Rationale**: `QUEUE_CONNECTION=database` sudah dikonfigurasi sejak awal project (tabel `jobs` sudah ada dari migration bawaan Laravel) — memakai `ShouldQueue` berarti pengiriman email terjadi di luar siklus request/response HTTP. Ini secara alami memenuhi FR-009 (kegagalan notifikasi TIDAK BOLEH menggagalkan submission): begitu job masuk antrian, response ke pengunjung sudah selesai: kalaupun SMTP gagal nanti saat job diproses, submission sudah lama tersimpan dan pengunjung sudah melihat konfirmasi. Principle V — tidak perlu package notifikasi pihak ketiga, `Illuminate\Notifications` sudah bawaan Laravel.

**Alternatives considered**: Kirim `Mail::send()` langsung (synchronous) di controller dengan try/catch — ditolak, tetap menahan response HTTP menunggu SMTP (bisa lambat/timeout) dan butuh try/catch manual yang mudah lupa di-maintain; `ShouldQueue` menghilangkan kelas masalah ini sepenuhnya.

## 2. "Notifikasi WhatsApp" = redirect pengunjung ke `wa.me`, bukan API

**Decision**: Setelah submission tersimpan, response JSON dari endpoint submit menyertakan `whatsapp_url` (nullable) berbentuk `https://wa.me/<nomor>?text=<pesan-encoded>` bila nomor WhatsApp bisnis sudah dikonfigurasi di Brand Settings. Frontend (Alpine) memanggil `window.open(whatsapp_url, '_blank')` dan tetap menampilkan link yang sama sebagai tombol fallback (edge case pop-up blocker).

**Rationale**: Keputusan eksplisit dari klarifikasi (Q1) — pengunjung yang mengirim pesannya sendiri via WhatsApp mereka, bukan sistem yang memanggil WhatsApp Business API (Meta/Twilio/Fonnte) yang butuh akun berbayar + kredensial per klien. Principle V & Principle I — nol dependency baru, nol kredensial pihak ketiga yang harus diurus per instalasi klien.

**Alternatives considered**: Integrasi WhatsApp Business API sungguhan — ditolak untuk v1 (lihat spec.md Q1, Option C), scope & biaya jauh lebih besar dan perlu keputusan vendor terpisah.

## 3. Nomor WhatsApp bisnis & email notifikasi: field baru di `BrandSettings`

**Decision**: Tambah 2 properti ke `App\Settings\BrandSettings` (group `brand`, sudah ada dari Epic 2 & 002): `whatsapp_number` (string, format digit e164-ish tanpa `+`/spasi, mis. `6281234567890`) dan `contact_notification_email` (string, email tujuan notifikasi submission baru).

**Rationale**: Konsisten dengan pola yang sudah dipakai berkali-kali (`secondary_color`, `font_heading`, `og_image_path` semua ditambahkan ke `BrandSettings` yang sama, bukan settings class baru) — satu tempat untuk seluruh konfigurasi "identitas & kontak instalasi ini", sesuai Principle V (jangan gandakan konsep settings yang sudah ada).

**Alternatives considered**: Settings class baru khusus `ContactSettings` — ditolak, tidak ada kebutuhan pemisahan concern yang jelas dan akan memaksa admin membuka halaman ketiga hanya untuk 2 field kecil.

## 4. Rate limiting: Laravel `throttle` middleware bawaan

**Decision**: Route `POST /kontak` diberi middleware `throttle:5,1` (maksimum 5 submission per menit per kombinasi IP, sesuai default Laravel).

**Rationale**: Bawaan framework, nol dependency baru (Principle V), cukup untuk mencegah spam/double-submit tanpa mengganggu pengunjung sah yang hanya submit sekali.

**Alternatives considered**: CAPTCHA pihak ketiga (mis. Google reCAPTCHA) — ditolak untuk v1 sesuai Assumptions di spec.md; bisa ditambah nanti kalau spam jadi masalah nyata.

## 5. Submit form: `fetch()` AJAX ke route baru, bukan form POST biasa

**Decision**: `<form>` di `kontak.blade.php` tetap `@submit.prevent`, tapi method `submit()` Alpine memanggil `fetch('/kontak', { method: 'POST', ... })` sungguhan (menggantikan `this.submitted = true` palsu dari 002), lalu menangani response JSON (sukses + `whatsapp_url`, atau error validasi 422).

**Rationale**: Mempertahankan UX "tanpa reload halaman penuh" (FR-003) yang sudah dibangun di 002 — perubahan minimal, hanya mengganti simulasi lokal dengan request sungguhan ke server.

**Alternatives considered**: Livewire component — ditolak, menambah overhead Livewire untuk satu form sederhana yang sudah berfungsi baik dengan Alpine + fetch (konsisten dengan keputusan Alpine-only untuk halaman publik dari 002-theme-branding-system research.md §3).

## 6. Temuan implementasi: `bootstrap/app.php` cuma render JSON exception untuk path `api/*`

**Decision**: Validasi di `ContactController@store` dilakukan manual lewat `Validator::make()->fails()` + `response()->json(..., 422)` eksplisit — BUKAN `$request->validate()` (yang melempar `ValidationException` dan bergantung pada exception handler global untuk menentukan format respons).

**Rationale**: Project ini sudah punya konfigurasi `withExceptions(fn ($exceptions) => $exceptions->shouldRenderJsonWhen(fn ($request) => $request->is('api/*')))` di `bootstrap/app.php` — artinya SEMUA exception (termasuk `ValidationException`) di luar path `api/*` selalu dirender sebagai HTML/redirect, terlepas dari header `Accept: application/json` yang dikirim `fetch()`/`postJson()`. Karena route `/kontak` (sesuai kontrak spec, bukan `/api/kontak`) ada di luar `api/*`, `$request->validate()` akan selalu redirect, bukan mengembalikan 422 JSON yang dibutuhkan FR-002. Validasi manual menghindari jalur exception handler ini sepenuhnya — controller membangun response JSON-nya sendiri, tidak bergantung pada content-negotiation global.

**Alternatives considered**: Pindahkan endpoint ke `/api/kontak` — ditolak, mengubah kontrak spec/quickstart yang sudah disepakati (`POST /kontak`) hanya untuk bekerja di sekitar konfigurasi exception handler yang sifatnya global/tidak terkait fitur ini. Mengubah `shouldRenderJsonWhen` di `bootstrap/app.php` supaya lebih permisif (mis. berdasarkan `Accept` header, bukan path) — dipertimbangkan tapi berisiko mengubah perilaku endpoint lain di luar scope fitur ini; validasi manual jauh lebih terisolasi.
