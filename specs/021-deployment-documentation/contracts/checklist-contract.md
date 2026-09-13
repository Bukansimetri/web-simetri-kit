# Contract: Struktur Dokumen & Checklist Deployment

**Feature**: 021-deployment-documentation

Fitur ini bukan API/kode — "kontrak"-nya adalah struktur & isi minimum yang harus dipenuhi ketiga dokumen deliverable, agar konsisten dengan FR di spec.md dan bisa diverifikasi lewat Independent Test tiap user story.

## `docs/deployment.md`

MUST punya struktur:
1. **Requirement Server** (berlaku kedua platform) — versi PHP, daftar ekstensi (termasuk `gd`), database, web server.
2. **Deploy ke VPS** — instalasi stack dari nol, clone repo (rujuk `docs/versioning-strategi-klien.md`), `composer install`, `.env` + `app:setup-client` (rujuk 018-setup-client-command), `storage:link`, migrate, build asset, konfigurasi web server (virtual host mengarah ke `public/`), Supervisor untuk queue worker, cron untuk scheduler, Certbot HTTPS.
3. **Deploy ke Shared Hosting cPanel** — cek/pilih versi PHP & ekstensi via MultiPHP, upload kode (dengan/tanpa SSH), penyesuaian document root, `.env` + `app:setup-client`, `storage:link` (atau alternatifnya), migrate, Cron Jobs untuk queue (`--stop-when-empty`) & scheduler, AutoSSL.
4. Tautan ke `docs/checklist-ga4-setup.md` dan `docs/checklist-go-live.md` di bagian akhir kedua jalur.

MUST secara eksplisit menandai kondisi blocker (mis. "❌ JANGAN lanjut bila versi PHP < 8.3") vs informasi/pilihan (mis. "opsi A atau B tergantung ketersediaan SSH").

## `docs/checklist-ga4-setup.md`

MUST berformat daftar centang (`- [ ]`) dengan urutan sesuai research.md #5 (property → service account → credentials JSON → **beri akses viewer ke property** → upload ke server → isi `.env` → verifikasi dashboard). MUST mencantumkan varian "klien sudah punya akun GA4" (FR-009) sebagai catatan di langkah pertama, bukan alur terpisah penuh (perbedaannya hanya di langkah 1).

## `docs/checklist-go-live.md`

MUST berformat daftar centang, setiap butir dapat dijawab ya/tidak biner (FR-010, SC-005) — dilarang butir subjektif seperti "situs terlihat bagus". Minimal mencakup butir dari research.md #6. MUST menandai butir mana yang jadi **blocker go-live** vs yang **boleh ditunda dengan catatan** (FR-011) — mis. status GA4 boleh "belum, direncanakan tanggal X" tanpa menghalangi go-live, sedangkan `APP_DEBUG=false` tidak boleh ditunda.

## Independensi platform

Baik `docs/deployment.md` maupun kedua checklist MUST dapat diikuti tuntas untuk SATU platform (VPS saja, atau cPanel saja) tanpa perlu membaca bagian platform lain — pembaca yang hanya deploy ke shared hosting tidak wajib memahami bagian Supervisor/VPS untuk menyelesaikan deploy-nya.
