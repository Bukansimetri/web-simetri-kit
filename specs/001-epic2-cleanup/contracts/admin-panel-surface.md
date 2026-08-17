# Contract: Admin Panel Surface (UI/Route/Permission)

**Feature**: [spec.md](../spec.md) | **Date**: 2026-08-16

Fitur ini tidak mengekspos API publik. "Kontrak" di sini adalah permukaan admin panel (route, permission, dan perilaku UI) yang harus konsisten dipenuhi implementasi, supaya bisa diverifikasi lewat feature test tanpa bergantung pada detail implementasi.

## 1. Dashboard Analytics (User Story 1)

| Aspek | Kontrak |
|---|---|
| Route | `GET /admin` (dashboard default) menampilkan widget: Visitors, Pageviews, Most Visited Pages, Top Referrers |
| Akses | Semua role yang punya akses ke admin panel (tidak dibatasi khusus) |
| Kondisi kredensial GA4 kosong/invalid | Halaman tetap render (HTTP 200), menampilkan pesan informatif pengganti widget — bukan HTTP 500 / exception |
| Kondisi kredensial GA4 valid | Widget menampilkan angka numerik (bukan placeholder), dan dropdown filter rentang tanggal berfungsi mengubah data yang ditampilkan |

## 2. White-labeling (User Story 2)

| Aspek | Kontrak |
|---|---|
| Route | Semua halaman admin panel (`/admin/*`) |
| Sumber nilai | `BrandSettings` (Spatie Settings); fallback ke default Filament/`config('app.name')` bila belum dikonfigurasi |
| Perilaku | Judul halaman/browser tab, sidebar brand name, logo, favicon, dan warna primer tombol/aksen mengikuti `BrandSettings` saat sudah dikonfigurasi |
| Halaman pengaturan | Tersedia form (Filament Settings page) untuk mengubah `app_name`, upload logo, upload favicon, dan pilih warna primer — perubahan berlaku tanpa perlu deploy ulang |

## 3. Activity Log (User Story 3)

| Aspek | Kontrak |
|---|---|
| Route | Halaman baru di admin panel (disediakan `rmsramos/activitylog`), mis. `/admin/activity-log` |
| Akses | HTTP 403 / halaman tidak muncul di navigasi untuk role selain Super Admin; Super Admin bisa membuka dan melihat isi log |
| Pencatatan | Setiap create/update/delete pada model yang memakai `LogsActivity` menghasilkan satu baris di `activity_log` berisi causer, waktu, subject, dan nilai before/after |
| Retensi | Baris log dengan `created_at` > 90 hari tidak lagi muncul setelah scheduled cleanup command berjalan |
