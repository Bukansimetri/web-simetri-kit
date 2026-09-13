# Checklist: Setup Google Analytics (GA4)

Jalankan checklist ini untuk **setiap klien baru** setelah situs live (VPS maupun shared hosting) — bagian wajib dari onboarding, bukan opsional. Tanpa ini, dashboard Google Analytics di admin panel akan **kosong**, bukan menampilkan error.

> **Catatan sebelum mulai**: Bila klien **sudah punya** akun/property Google Analytics dari sebelumnya, lewati sub-langkah "buat property baru" di langkah 1 dan gunakan property yang sudah ada — sisa langkah (2–7) tetap sama persis.

## Langkah

- [ ] **1. Property GA4** — Pastikan klien punya property GA4 di akun Google Analytics **milik klien** (bukan akun internal tim developer). Bila belum ada: klien (atau Anda atas izin klien) membuat property baru di [Google Analytics](https://analytics.google.com) untuk domain situs tsb.
- [ ] **2. Buat Service Account** — Di [Google Cloud Console](https://console.cloud.google.com), buat/gunakan sebuah project, aktifkan **Google Analytics Data API**, lalu buat Service Account baru khusus untuk instalasi klien ini.
- [ ] **3. Unduh kredensial JSON** — Dari halaman Service Account tsb, buat key baru bertipe **JSON** dan unduh filenya.
- [ ] **4. Beri akses Viewer ke property** — ⚠️ **Langkah yang paling sering terlewat**: Service Account TIDAK otomatis punya akses ke property manapun. Di Google Analytics (Admin → Property Access Management), tambahkan email Service Account (format `nama@project-id.iam.gserviceaccount.com`, ada di dalam file JSON) sebagai **Viewer** pada property klien.
- [ ] **5. Upload kredensial ke server** — Upload file JSON dari langkah 3 ke server dengan nama persis `service-account-credentials.json`, ditempatkan di `storage/app/analytics/service-account-credentials.json` pada instalasi klien.
- [ ] **6. Isi Property ID di `.env`** — Buka Google Analytics → Admin → Property Settings, salin **Property ID** (angka, contoh: `123456789`) — ⚠️ BUKAN "Measurement ID" (format `G-XXXXXXX`, dipakai untuk tracking code, berbeda kegunaan). Isi ke `.env`:
  ```
  ANALYTICS_PROPERTY_ID=123456789
  ```
  Lalu jalankan `php artisan config:clear` (atau ulangi `app:setup-client` yang sudah membersihkan cache) agar nilai baru terbaca.
- [ ] **7. Verifikasi dashboard** — Buka admin panel klien → dashboard utama. Setelah situs mendapat traffic (kunjungan nyata atau uji coba), dashboard Google Analytics MUST menampilkan data (grafik pengunjung, dsb.), bukan pesan kosong atau error konfigurasi. Data baru biasanya muncul dalam beberapa jam pertama setelah traffic masuk — bukan langsung real-time pada kunjungan pertama.

## Bila klien belum bisa/mau memberi akses GA4

Ini adalah kondisi yang **boleh ditunda** — bukan blocker go-live mutlak (lihat [`docs/checklist-go-live.md`](checklist-go-live.md)). Catat status ini secara eksplisit ke klien: dashboard Analytics akan tetap kosong sampai checklist ini diselesaikan, dan tidak memengaruhi fungsi situs lainnya.
