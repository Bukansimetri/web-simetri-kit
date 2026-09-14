# Research: Deployment Documentation

**Feature**: 021-deployment-documentation | **Date**: 2026-09-13

## 1. Requirement server yang berlaku umum

**Decision**: PHP **8.3+** (`composer.json`: `"php": "^8.3"`), ekstensi: `mbstring`, `openssl`, `pdo`, `pdo_mysql`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd` (dipakai `App\Support\ImageUploads` untuk konversi upload ke WebP — lihat #4). Database: MySQL 8+/MariaDB 10.3+. Web server: Apache (mod_rewrite aktif — `public/.htaccess` proyek ini sudah memakai `RewriteEngine`) atau Nginx.

**Rationale**: Diambil langsung dari `composer.json` dan kode yang benar-benar dipakai (bukan asumsi generik Laravel) — `gd` secara khusus penting disebutkan karena mudah terlewat di requirement Laravel generik, padahal wajib untuk fitur upload gambar (Banner, Produk, Portfolio, dll.) di kit ini.

**Alternatives considered**: Menyebut requirement Laravel generik tanpa verifikasi ke kode proyek — ditolak, berisiko melewatkan `gd` yang justru krusial untuk kit ini.

## 2. Jalur VPS: instalasi stack & proses latar belakang

**Decision**: VPS diasumsikan server kosong (Ubuntu LTS) — dokumentasi mencakup instalasi Nginx/Apache + PHP-FPM 8.3 + MySQL + Composer + Node.js dari nol, lalu:
- **Queue worker**: proses persisten via **Supervisor** menjalankan `php artisan queue:work --sleep=3 --tries=3`.
- **Scheduler**: satu baris cron `* * * * * php artisan schedule:run` (kebutuhan Laravel standar — proyek ini sudah punya `Schedule::command('activitylog:clean')->daily()` di `routes/console.php`).
- **HTTPS**: Certbot (Let's Encrypt) dengan auto-renewal via cron/systemd timer bawaan Certbot.

**Rationale**: VPS punya akses root sehingga bisa menjalankan proses persisten (Supervisor) — cara paling andal menjaga queue worker tetap hidup (auto-restart bila crash), sesuai FR-004. Ini pola deployment Laravel standar di lingkungan VPS mana pun, tidak spesifik-vendor.

**Alternatives considered**: `nohup`/`screen` untuk queue worker — ditolak, tidak auto-restart saat crash/reboot server, kurang andal dibanding Supervisor untuk lingkungan produksi.

## 3. Jalur shared hosting cPanel: keterbatasan & solusi

**Decision**:
- **PHP version**: dipilih via cPanel **"MultiPHP Manager"**/**"Select PHP Version"** (fitur standar hampir semua cPanel) — dokumentasi MUST instruksikan memeriksa & mengaktifkan ekstensi yang dibutuhkan (termasuk `gd`) lewat menu yang sama sebelum lanjut.
- **Composer tanpa SSH**: bila paket hosting tidak menyediakan akses SSH/Terminal, jalankan `composer install --no-dev --optimize-autoloader` di lokal, lalu upload folder `vendor/` beserta kode aplikasi via File Manager/FTP. Bila SSH tersedia (banyak paket cPanel menengah-atas menyediakannya), jalankan langsung di server seperti VPS.
- **Document root**: cPanel biasanya memetakan domain ke `public_html/`, sedangkan Laravel butuh document root mengarah ke folder `public/` proyek. Dua opsi didokumentasikan: (a) bila cPanel mengizinkan "Document Root" kustom per domain (banyak provider modern mengizinkan ini di menu "Domains"), arahkan langsung ke `public/`; (b) bila tidak, isi `public_html/` dengan isi folder `public/` proyek (index.php dkk.) dan sesuaikan path `require` di `index.php` agar menunjuk ke lokasi kode aplikasi yang di-upload di luar/di atas `public_html/`.
- **Queue tanpa proses persisten**: cPanel **"Cron Jobs"** menjalankan `php artisan queue:work --stop-when-empty` setiap menit (job berhenti sendiri setelah antrean kosong, aman dijalankan tumpang tindih tiap menit oleh cron berikutnya).
- **Scheduler**: sama seperti VPS, satu baris Cron Job `* * * * * php artisan schedule:run`.
- **HTTPS**: cPanel modern umumnya menyediakan **AutoSSL** (Let's Encrypt) otomatis dan auto-renew tanpa konfigurasi tambahan — dokumentasi cukup instruksikan memastikan AutoSSL aktif untuk domain tsb.

**Rationale**: Setiap solusi di atas memakai fitur BAWAAN cPanel yang tersedia di hampir semua provider Indonesia (termasuk Hostinger), tanpa perlu akses yang tidak dijamin tersedia (root/SSH persisten/daemon). `--stop-when-empty` dipilih secara spesifik (bukan `queue:work` biasa) karena aman dipanggil berulang oleh cron tanpa menumpuk proses zombie.

**Alternatives considered**:
- Mengasumsikan SSH selalu tersedia di shared hosting — ditolak, banyak paket entry-level Indonesia tidak menyediakannya (khususnya paket termurah), melanggar FR-003.
- `QUEUE_CONNECTION=sync` (proses job langsung tanpa antrean) sebagai solusi shared hosting — dipertimbangkan sebagai catatan alternatif lebih sederhana untuk klien dengan traffic sangat rendah (email dikirim saat request, bukan di background), tapi TIDAK dijadikan default karena bisa memperlambat response time form kontak; didokumentasikan sebagai opsi cadangan bila cron tidak memungkinkan sama sekali.

## 4. Kebutuhan `storage:link` di kedua lingkungan

**Decision**: `php artisan storage:link` MUST dijalankan di kedua lingkungan (VPS maupun shared hosting) setelah deploy — dibutuhkan karena seluruh URL gambar upload (produk, portfolio, banner, tim, dst.) di-generate lewat `Storage::disk('public')->url(...)`, yang mengasumsikan symlink `public/storage → storage/app/public` ada. Bila SSH tidak tersedia di shared hosting, dokumentasikan alternatif: buat symlink manual lewat fitur "Symlink"/Terminal berbasis web cPanel (bila ada), atau — bila cPanel benar-benar tidak mendukung symlink sama sekali — salin (bukan symlink) isi `storage/app/public` ke `public/storage` setiap kali ada upload baru (dicatat sebagai keterbatasan, bukan solusi ideal).

**Rationale**: Ini kebutuhan Laravel standar yang sering terlewat di panduan deployment generik, padahal kritis untuk kit ini karena hampir semua modul konten (Product, Portfolio, Banner, TeamMember, Testimonial, Article) menyimpan gambar lewat disk `public`.

**Alternatives considered**: Mengarahkan `FILESYSTEM_DISK`/upload langsung ke `public/` tanpa symlink — ditolak, mengubah cara kerja upload yang sudah dipakai seluruh modul (perubahan kode, bukan sekadar dokumentasi, di luar scope AMC-231).

## 5. Checklist setup GA4

**Decision**: Checklist terpisah (`docs/checklist-ga4-setup.md`) mencakup: (1) pastikan klien punya/buat property GA4 di akun Google Analytics klien (bukan akun internal tim), (2) buat Service Account di Google Cloud Console untuk project yang terhubung ke property tsb, (3) unduh file kredensial JSON, (4) beri akses "Viewer" service account tsb ke property GA4 di Google Analytics, (5) upload file kredensial ke `storage/app/analytics/service-account-credentials.json` di server, (6) isi `ANALYTICS_PROPERTY_ID` di `.env` dengan Property ID (bukan Measurement ID / Tracking ID — keduanya sering tertukar), (7) verifikasi dashboard admin panel menampilkan data.

**Rationale**: Langkah ini diambil langsung dari kebutuhan config yang sudah ada di kode (`config/analytics.php`: `property_id` dari `ANALYTICS_PROPERTY_ID`, `service_account_credentials_json` di path tetap `storage/app/analytics/service-account-credentials.json`). Poin (4) — beri akses service account ke property — adalah langkah yang paling sering terlewat di panduan GA4 secara umum (service account py sendiri tidak otomatis punya akses ke property manapun), sehingga sengaja ditulis eksplisit sebagai langkah terpisah, bukan digabung ke langkah pembuatan service account.

**Alternatives considered**: OAuth-based user authentication ke GA4 alih-alih service account — tidak relevan, package yang sudah dipakai kit ini (`spatie/laravel-analytics` via `bezhansalleh/filament-google-analytics`) memakai pola service account, bukan OAuth per-user; mengubahnya adalah perubahan kode di luar scope dokumentasi.

## 6. Checklist go-live final

**Decision**: Checklist terpisah (`docs/checklist-go-live.md`), berlaku lintas platform, minimal mencakup: domain mengarah ke server yang benar (DNS A/CNAME record), HTTPS aktif & valid, `APP_ENV=production` dan `APP_DEBUG=false`, `APP_URL` sesuai domain final, queue & scheduler berjalan (verifikasi lewat uji kirim form kontak), backup database terjadwal aktif, status checklist GA4 (US3) — boleh "tertunda" dengan catatan, tapi harus eksplisit dicek, bukan diam-diam dilewati.

**Rationale**: Daftar ini adalah kondisi yang FR-010/FR-011 minta eksplisit (blocker vs boleh ditunda) dan mudah diverifikasi biner (ya/tidak) tanpa pengetahuan mendalam — cocok dipakai siapa pun di tim sebagai gerbang terakhir sebelum serah terima klien.

**Alternatives considered**: Menggabungkan checklist go-live ke dalam `deployment.md` sebagai bagian akhir — ditolak (lihat plan.md Structure Decision): checklist perlu bisa di-scan cepat berulang kali, terpisah dari narasi teknis deploy yang hanya dibaca sekali per deploy.

## Outstanding NEEDS CLARIFICATION

Tidak ada.
