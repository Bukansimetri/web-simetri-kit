# Data Model: Career/Lowongan Kerja CRUD Admin + Toggle Modul

**Date**: 2026-09-05 | **Feature**: [spec.md](./spec.md) | **Research**: [research.md](./research.md)

## Job Opening (perluasan dari 002-theme-branding-system — TIDAK ada perubahan skema tabel)

| Field | Type | Required | Notes |
|---|---|---|---|
| `id` | bigint PK | | Sudah ada |
| `title` | string | Ya | Sudah ada — judul posisi (FR-002) |
| `location` | string | Ya | Sudah ada |
| `employment_type` | string | Ya | Sudah ada — kini divalidasi terhadap `JobOpening::EMPLOYMENT_TYPES` (FR-007, research.md §1), TIDAK ada perubahan kolom |
| `description` | text | Ya | Sudah ada |
| `is_active` | boolean, default true | Ya | Sudah ada — status aktif/nonaktif PER LOWONGAN (FR-006), TERPISAH dari toggle modul global |

**Validasi**:
- `title`, `location`, `description`: wajib (FR-003).
- `employment_type`: wajib, harus salah satu dari `JobOpening::EMPLOYMENT_TYPES` (`full-time`, `part-time`, `contract`, `internship`) — FR-007.
- `is_active`: boolean, default `true` saat dibuat.

**Tidak ada migrasi baru untuk tabel `job_openings`** — seluruh field yang dibutuhkan (termasuk `is_active`) sudah ada sejak 002-theme-branding-system. Fitur ini murni menambah CRUD admin di atas skema yang sudah ada.

**Relasi**: tidak ada — entity berdiri sendiri, konsisten dengan skema existing.

## Pengaturan Modul Karir (perluasan `App\Settings\BrandSettings`)

| Field | Type | Required | Notes |
|---|---|---|---|
| `career_module_enabled` | boolean, default `true` | Tidak (selalu ada nilai) | **Baru** — satu toggle global independen dari `job_openings.is_active` manapun (FR-009) |

**Penyimpanan**: bukan kolom tabel baru — ditambahkan sebagai property `BrandSettings` (spatie/laravel-settings) lewat migration `database/settings/xxxx_add_career_module_enabled_to_brand_settings.php` (`SettingsMigration`), pola identik penambahan `whatsapp_number` sebelumnya (research.md §2).

**Efek**:
- `false` → `GET /karir` MUST 404 (FR-010); link "Karir" di footer publik MUST hilang (FR-011).
- `true` (default) → perilaku saat ini tidak berubah.
- Perubahan nilai ini TIDAK menghapus/mengubah baris `job_openings` manapun (FR-012) — murni flag visibilitas baca di lapisan publik.
- TIDAK memengaruhi akses `JobOpeningResource` di admin panel (FR-013, research.md §4).

## Ringkasan perubahan

```text
job_openings — TIDAK ADA perubahan skema (semua field dibutuhkan sudah ada dari 002)

brand_settings (via spatie/laravel-settings, bukan tabel biasa)
└── career_module_enabled (BARU, boolean, default true)
```
