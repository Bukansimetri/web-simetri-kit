# Implementation Plan: Career/Lowongan Kerja CRUD Admin + Toggle Modul

**Branch**: `006-career-crud-admin` | **Date**: 2026-09-05 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/006-career-crud-admin/spec.md`

**Note**: This template is filled in by the `/speckit-plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Menambahkan CRUD admin (Filament Resource) untuk `JobOpening` yang sudah ada dari 002-theme-branding-system — tanpa perubahan skema tabel sama sekali, karena seluruh field yang dibutuhkan (`title`, `location`, `employment_type`, `description`, `is_active`) sudah ada — plus satu kemampuan baru: toggle global "modul Karir aktif/nonaktif" per instalasi klien, disimpan sebagai field boolean baru di `App\Settings\BrandSettings` yang sudah ada. Saat modul dinonaktifkan, halaman publik `/karir` mengembalikan 404 dan link "Karir" hilang dari navigasi footer, TANPA menghapus data lowongan maupun membatasi akses CRUD admin. `employment_type` kini divalidasi terhadap daftar pilihan tetap di kode (bukan teks bebas, bukan taxonomy baru).

## Technical Context

**Language/Version**: PHP 8.3, Laravel Framework 13

**Primary Dependencies**: Filament 3.3 (`JobOpeningResource` baru; `Toggle`/`Select` bawaan); `spatie/laravel-settings` (SUDAH dipakai lewat `App\Settings\BrandSettings` sejak 002 — diperluas dengan satu property baru, bukan package baru). Tidak ada dependency baru.

**Storage**: MySQL — TIDAK ADA migrasi skema tabel baru untuk `job_openings` (research.md § Ringkasan); satu migration `database/settings/` baru untuk `brand.career_module_enabled` (pola `SettingsMigration`, sama seperti `whatsapp_number`).

**Testing**: PHPUnit feature test memakai Livewire test helper untuk Filament Resource (pola sama seperti `ProductResourceTest`/`ArticleResourceTest`) — create/edit/delete/toggle `is_active`, validasi field wajib, validasi `employment_type` dari daftar tetap; feature test terpisah untuk perilaku toggle modul (404 saat nonaktif, link footer hilang, admin CRUD tetap jalan, data tidak hilang setelah toggle ulang)

**Target Platform**: Server web Laravel standar (sama seperti fitur-fitur sebelumnya)

**Project Type**: Web application — perluasan admin panel Filament & controller/Blade publik yang sudah ada, tidak ada perubahan struktur project

**Performance Goals**: Tidak ada target khusus (admin-only untuk CRUD; halaman publik `/karir` tetap harus render cepat seperti sebelumnya)

**Constraints**: CRUD MUST terbuka untuk semua role admin panel (FR-008) SELALU, terlepas dari status toggle modul (FR-013); `employment_type` MUST dari daftar tetap di kode, bukan entity/taxonomy baru (FR-007, Clarifications); toggle modul MUST ditempatkan di halaman Brand Settings yang sudah ada, bukan halaman admin baru (FR-014, Clarifications); menonaktifkan modul MUST TIDAK menghapus data `job_openings` (FR-012); tidak ada dependency baru (Principle V)

**Scale/Scope**: 1 Filament Resource baru (`JobOpeningResource`) + 1 field baru di `BrandSettingsPage`/`BrandSettings` + 1 migration settings + guard 404 di `CareerController` + kondisional link footer

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Applies? | Assessment |
|---|---|---|
| I. Multi-Client Reusability | Ya | Inti dari User Story 2 — modul Karir jadi toggleable per instalasi klien lewat settings, bukan hardcoded selalu tampil. **PASS** |
| II. White-Label by Default | Tidak langsung | Fitur ini tidak menyentuh branding/identitas panel — tidak relevan. |
| III. Settings-Driven Theming, No Page Builder | Tidak langsung | CRUD data konten (lowongan) + satu toggle settings — bukan theming/layout, tidak menambah abstraksi builder apa pun. |
| IV. Module Test Coverage | Ya | US1 (CRUD lowongan) dan US2 (toggle modul) MUST masing-masing punya feature test dasar sebelum dianggap selesai. **PASS (planned)** |
| V. Simplicity & Dependency Discipline | Ya | Tidak ada dependency baru; `employment_type` pakai konstanta PHP (bukan taxonomy entity baru); toggle modul pakai `BrandSettings` yang sudah ada (bukan tabel/halaman Settings baru); tidak ada middleware/module-registry generik untuk kasus satu modul. **PASS** |

Tidak ada pelanggaran constitution yang butuh entry Complexity Tracking.

## Project Structure

### Documentation (this feature)

```text
specs/006-career-crud-admin/
├── plan.md              # This file (/speckit-plan command output)
├── research.md          # Phase 0 output (/speckit-plan command)
├── data-model.md        # Phase 1 output (/speckit-plan command)
├── quickstart.md        # Phase 1 output (/speckit-plan command)
├── contracts/           # Phase 1 output (/speckit-plan command)
│   └── admin-panel-surface.md
└── tasks.md             # Phase 2 output (/speckit-tasks command - NOT created by /speckit-plan)
```

### Source Code (repository root)

```text
app/
├── Models/
│   └── JobOpening.php               # Diperluas: EMPLOYMENT_TYPES const, tidak ada perubahan kolom
├── Settings/
│   └── BrandSettings.php            # Diperluas: property career_module_enabled (bool)
├── Filament/
│   ├── Resources/
│   │   ├── JobOpeningResource.php   # Baru
│   │   └── JobOpeningResource/Pages/{ListJobOpenings,CreateJobOpening,EditJobOpening}.php
│   └── Pages/
│       └── BrandSettingsPage.php    # Diperluas: field Toggle('career_module_enabled') + wiring save()
└── Http/Controllers/Public/
    └── CareerController.php         # Diperluas: abort(404) saat career_module_enabled = false

database/
└── settings/
    └── xxxx_add_career_module_enabled_to_brand_settings.php   # Baru — SettingsMigration, default true

resources/views/
└── components/layout/footer.blade.php   # Diperbarui: sembunyikan entri link "Karir" saat modul nonaktif

tests/Feature/
├── Admin/
│   └── JobOpeningResourceTest.php   # Baru — US1
└── Public/
    └── CareerModuleToggleTest.php   # Baru — US2
```

**Structure Decision**: Perluasan langsung dari struktur admin panel Filament yang sudah ada (`app/Filament/Resources/`), pola identik `003-produk-crud-admin`/`005-artikel-crud-admin`. Tidak ada folder/namespace baru — `App\Settings\BrandSettings` dan `App\Filament\Pages\BrandSettingsPage` yang sudah ada diperluas, bukan diganti.

## Complexity Tracking

*Tidak ada pelanggaran constitution — tabel ini kosong.*
