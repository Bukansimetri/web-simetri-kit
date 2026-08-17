# Data Model: Rapikan Epic 2 - Auth, White-labeling & Audit Trail

**Date**: 2026-08-16 | **Feature**: [spec.md](./spec.md) | **Research**: [research.md](./research.md)

## Brand Settings

Representasi konfigurasi white-label untuk satu instalasi (satu klien). Disimpan via `spatie/laravel-settings` (tabel `settings` generik milik package, bukan tabel dedicated).

| Field | Type | Required | Notes |
|---|---|---|---|
| `app_name` | string, nullable | Tidak | Fallback ke `config('app.name')` jika kosong (FR-005) |
| `logo_path` | string (media path), nullable | Tidak | Disimpan via Spatie Media Library single-file collection `brand-logo`; fallback ke logo default Filament jika kosong |
| `favicon_path` | string (media path), nullable | Tidak | Collection `brand-favicon`; fallback ke favicon default jika kosong |
| `primary_color` | string (hex), nullable | Tidak | Fallback ke `Color::Indigo` (default Filament) jika kosong |

**Validasi**:
- `logo_path` / `favicon_path`: tipe file dibatasi ke gambar umum (png/jpg/svg/webp) dan ukuran maksimum wajar untuk asset UI (edge case: format tidak didukung → tampilkan pesan validasi, jangan simpan).
- `primary_color`: harus hex valid atau nama warna yang dikenali `Filament\Support\Colors\Color`.

**Relasi**: Tidak ada relasi ke entity lain — satu baris konfigurasi berlaku global untuk seluruh instalasi (bukan per-user).

**State**: Tidak ada state transition — settings bersifat langsung berlaku (immediate effect) begitu disimpan.

## Activity Log Entry

Disediakan oleh `spatie/laravel-activitylog` (tabel `activity_log` bawaan package — tidak perlu migration/model custom, hanya publish migration package tersebut).

| Field | Type | Notes |
|---|---|---|
| `log_name` | string | Kategori log (mis. `default`) |
| `description` | string | Ringkasan aksi (mis. "updated") |
| `subject_type` / `subject_id` | polymorphic | Resource yang diubah (mis. `App\Models\User`) |
| `causer_type` / `causer_id` | polymorphic, nullable | Admin yang melakukan perubahan |
| `properties` | json | Berisi `old` dan `attributes` (nilai sebelum/sesudah) |
| `created_at` | timestamp | Waktu kejadian |

**Validasi**: Tidak ada input form langsung — entry dibuat otomatis oleh trait `LogsActivity` saat model yang diaudit disimpan/dihapus.

**Retensi (FR-009)**: Baris dengan `created_at` lebih tua dari 90 hari dihapus otomatis oleh scheduled command `activitylog:clean` (lihat research.md §3).

**Akses (FR-008)**: Hanya role Super Admin yang bisa membuka halaman browse log ini di admin panel.

**Relasi**: `causer` → model User yang melakukan aksi; `subject` → model resource yang diaudit (polymorphic, bisa User, Role, dan resource-resource yang akan dibangun di Epic 3+).

## Analytics Snapshot

**Bukan entity tersimpan** — ini adalah data live yang diambil langsung dari Google Analytics Data API (via `bezhansalleh/filament-google-analytics`) setiap widget dirender, dengan cache singkat (`cache_lifetime_in_minutes` di `config/analytics.php`, default 24 jam). Didokumentasikan di sini hanya untuk kejelasan konsep dari spec, bukan untuk migration baru.

| Konsep field | Sumber |
|---|---|
| Visitor count | `VisitorsWidget` → GA4 Data API |
| Pageviews | `PageViewsWidget` → GA4 Data API |
| Top pages | `MostVisitedPagesWidget` → GA4 Data API |
| Sumber traffic | `TopReferrersListWidget` → GA4 Data API |

**Precondition**: `config('analytics.property_id')` dan file service account (`config('analytics.service_account_credentials_json')`) harus terisi valid. Jika tidak, sistem MUST menampilkan pesan informatif (FR-002) alih-alih memanggil API dan menampilkan error mentah.
