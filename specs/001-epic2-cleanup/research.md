# Research: Rapikan Epic 2 - Auth, White-labeling & Audit Trail

**Date**: 2026-08-16 | **Feature**: [spec.md](./spec.md) | **Plan**: [plan.md](./plan.md)

## 1. Google Analytics Dashboard Widget (User Story 1)

**Finding — mengubah scope secara signifikan**: `bezhansalleh/filament-google-analytics` v2.1 (sudah terinstall) TIDAK hanya menyediakan plugin kosong. Saat `register()` dipanggil, plugin ini otomatis:

- Mendaftarkan halaman dashboard analytics khusus di `/admin/filament-google-analytics-dashboard` (sudah live sekarang, diverifikasi via `php artisan route:list`).
- Mendaftarkan 11 widget siap pakai secara global ke panel: `VisitorsWidget`, `PageViewsWidget`, `ActiveUsersOneDayWidget`, `ActiveUsersSevenDayWidget`, `ActiveUsersTwentyEightDayWidget`, `SessionsWidget`, `SessionsDurationWidget`, `SessionsByCountryWidget`, `SessionsByDeviceWidget`, `MostVisitedPagesWidget` (top pages), `TopReferrersListWidget` (sumber traffic).
- Widget-widget ini otomatis muncul juga di dashboard default `/admin` (`Filament\Pages\Dashboard`) karena Filament merender semua widget yang ter-register secara global di panel, bukan hanya di halaman dashboard plugin itu sendiri.
- Setiap widget statistik (mis. `VisitorsWidget`) sudah punya filter rentang tanggal bawaan: Hari ini, Kemarin, Minggu lalu, Bulan lalu, 7 hari terakhir, 30 hari terakhir — via dropdown `getFilters()`. Ini sudah memenuhi kebutuhan "admin dapat memilih rentang tanggal" di FR-010 tanpa kode tambahan.
- Visibilitas widget per halaman (dashboard default vs dashboard khusus) diatur lewat config `filament-google-analytics.php` per widget (`filament_dashboard`, `global`, `dedicated_dashboard`) — bisa dipublish dan disesuaikan tanpa menulis widget baru.

**Decision**: Tidak membangun widget custom dari nol. Scope User Story 1 menjadi:
1. Publish & sesuaikan `config/filament-google-analytics.php` supaya kombinasi widget yang tampil di dashboard default sesuai kebutuhan (visitor, pageviews, top pages, sumber traffic — FR-010), dan yang lain (breakdown negara/device/durasi) tetap di dashboard khusus supaya dashboard utama tidak penuh.
2. Menangani FR-002 (pesan jelas saat kredensial GA4 belum/salah dikonfigurasi) — ini GAP nyata: plugin tidak membungkus error dari Google Analytics Data API client, jadi tanpa penanganan tambahan, widget akan melempar exception mentah saat kredensial kosong/salah. Perlu ditambahkan pemeriksaan konfigurasi (mis. cek `config('analytics.property_id')` dan keberadaan file service account) sebelum widget dirender, dan tampilkan notifikasi/banner informatif di dashboard jika belum siap.
3. Tidak perlu membuat `app/Filament/Pages/Dashboard.php` custom kecuali untuk mengatur urutan/kolom widget — cek dulu apakah cukup lewat config plugin sebelum override halaman dashboard (selaras Principle V — hindari abstraksi/kode tambahan yang tidak perlu).

**Alternatives considered**: Membangun widget custom dari GA4 Data API langsung — ditolak karena mengabaikan pekerjaan yang sudah "gratis" dari plugin yang sudah terinstall dan melanggar Principle V (Simplicity & Dependency Discipline).

## 2. White-labeling Admin Panel (User Story 2)

**Decision**: Gunakan API bawaan Filament Panel (`->brandName()`, `->brandLogo()`, `->favicon()`, `->colors()`) di `AdminPanelProvider::panel()`, dengan nilai diambil dari sebuah `BrandSettings` (kelas Settings dari `spatie/laravel-settings`, sudah terinstall) alih-alih hardcode string/asset path. Filament mendukung closure untuk masing-masing method ini sehingga nilai bisa dibaca saat runtime (lazy), bukan saat boot container.

- `brandName(fn () => app(BrandSettings::class)->app_name ?? config('app.name'))`
- `favicon(fn () => app(BrandSettings::class)->favicon_url)`
- `colors(['primary' => fn () => app(BrandSettings::class)->primary_color ?? Color::Indigo])`
- Logo: gunakan `brandLogo()` dengan URL yang disimpan via `spatie/laravel-medialibrary` (sudah terinstall) pada `BrandSettings` sebagai single-file collection, supaya admin bisa upload logo/favicon lewat form Filament (FileUpload) tanpa menyentuh filesystem manual.

**Rationale**: Tidak perlu dependency baru — kombinasi Filament native config API + Spatie Settings + Spatie Media Library yang sudah ada di composer.json sudah cukup (Principle V). Nilai fallback ke `config('app.name')`/warna default Filament memenuhi FR-005 (branding default yang wajar saat belum dikonfigurasi).

**Alternatives considered**:
- Simpan branding di `.env` — ditolak untuk logo/favicon/warna karena `.env` tidak cocok untuk file upload dan tidak bisa diubah tanpa akses server (bertentangan dengan tujuan "klien bisa ubah sendiri lewat panel").
- Multi-tenancy package (mis. `stancl/tenancy`) — di luar scope, karena Assumption di spec sudah menetapkan model satu-deployment-per-klien, bukan multi-tenant dalam satu instance.

## 3. Activity Log Audit Trail (User Story 3)

**Decision**: Kombinasi dua package:
1. `spatie/laravel-activitylog` **v4.12.3** (BUKAN v5.x) — v5.x mensyaratkan `php: ^8.4` sedangkan project ini `php: ^8.3`, jadi tidak kompatibel. v4.12.3 mendukung `php: ^8.1` dan Laravel `^13.0`, cocok.
2. `rmsramos/activitylog` **v2.0.5** (BUKAN v3.x/v4.x) — versi v3.x butuh `filament/filament: ^4.0` dan v4.x butuh `^5.0`, keduanya tidak kompatibel dengan `filament/filament: ^3.2` yang dipakai project ini. v2.0.5 secara eksplisit mensyaratkan `filament: ^3.3` dan `php: ^8.2`, cocok (composer constraint `^3.2` project akan otomatis resolve ke `^3.3+` yang terinstall).

Kedua package berlisensi MIT — kompatibel untuk resale komersial (Principle V).

**Implementasi**:
- Model `User` (dan resource lain yang akan diaudit ke depan) menggunakan trait `Spatie\Activitylog\Traits\LogsActivity` dengan `getActivitylogOptions()` untuk mencatat perubahan atribut (before/after otomatis disediakan package ini).
- `rmsramos/activitylog` menyediakan halaman Filament siap pakai untuk browse/filter/search log — daftar sebagai page di panel plugin, bukan Resource custom, sehingga tidak perlu membangun UI dari nol.
- **Akses (FR-008)**: batasi page tersebut ke role Super Admin memakai policy/permission Filament Shield yang sudah terinstall (`Gate` atau `can()` check pada page, konsisten dengan pola ACL yang sudah dipakai `RoleResource`).
- **Retensi (FR-009)**: `spatie/laravel-activitylog` menyediakan Artisan command bawaan `activitylog:clean` yang menghapus log lebih tua dari `config('activitylog.delete_records_older_than_days')`. Set nilai ini ke `90` dan jadwalkan command tersebut lewat Laravel Scheduler (`routes/console.php`) agar berjalan otomatis — tidak perlu job custom.

**Alternatives considered**:
- Package Filament activity-log lain (`z3d0x/filament-logger`) — tidak dievaluasi lebih lanjut karena `rmsramos/activitylog` v2.0.5 sudah cocok versi dan merupakan package yang disebut langsung di deskripsi task Linear (AMC-206: "filament-logger" secara generik, `rmsramos/activitylog` adalah implementasi paling umum untuk kombinasi ini).
- Membangun tabel & UI log custom — ditolak, melanggar Principle V.

## Ringkasan Dependency Baru

| Package | Versi yang dipilih | Alasan versi |
|---|---|---|
| `spatie/laravel-activitylog` | `^4.12` | v5 butuh PHP 8.4, project masih PHP 8.3 |
| `rmsramos/activitylog` | `^2.0` | v3/v4 butuh Filament 4/5, project masih Filament 3.2 |

Tidak ada dependency baru untuk User Story 1 (GA) dan User Story 2 (white-labeling) — keduanya memakai package yang sudah terinstall.
