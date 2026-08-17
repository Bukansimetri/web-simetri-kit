# Implementation Plan: Rapikan Epic 2 - Auth, White-labeling & Audit Trail

**Branch**: `001-epic2-cleanup` | **Date**: 2026-08-16 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/001-epic2-cleanup/spec.md`

**Note**: This template is filled in by the `/speckit-plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Tiga potongan Epic 2 yang tersisa: (1) widget Google Analytics di dashboard admin memakai plugin yang sudah terinstall, (2) white-labeling admin panel (nama/logo/favicon/warna) yang bisa diubah per instalasi tanpa redeploy lewat Spatie Settings, dan (3) audit trail activity log (Super Admin only, retensi 90 hari) memakai spatie/laravel-activitylog + plugin Filament activity-log. Ketiganya independen satu sama lain dan bisa dikerjakan/dirilis terpisah.

## Technical Context

**Language/Version**: PHP 8.3, Laravel Framework 13

**Primary Dependencies**: Filament 3.2 (admin panel), `bezhansalleh/filament-google-analytics` ^2.1 (sudah terinstall & ter-register), `spatie/laravel-settings` ^3.9 (sudah terinstall, dipakai untuk Brand Settings), `spatie/laravel-medialibrary` ^11.23 (sudah terinstall, dipakai untuk upload logo/favicon), `spatie/laravel-activitylog` (baru, untuk audit trail), plugin Filament activity-log log-viewer (baru — kandidat: `rmsramos/activitylog`, dipilih di Phase 0)

**Storage**: MySQL/database default aplikasi (tabel `activity_log` dari spatie/laravel-activitylog; Brand Settings disimpan lewat tabel `settings` milik spatie/laravel-settings)

**Testing**: PHPUnit (feature test), sesuai konvensi project — `php artisan make:test --phpunit`

**Target Platform**: Server web Laravel standar (shared hosting/VPS per klien, sesuai Deployment & Client Setup Standards di constitution)

**Project Type**: Web application — single Laravel app dengan Filament admin panel (bukan struktur frontend+backend terpisah)

**Performance Goals**: Widget GA dashboard tampil dalam <3 detik (SC-001); tidak ada target throughput khusus untuk fitur ini (admin-only, low concurrency)

**Constraints**: Semua tiga fitur harus config-driven per instalasi klien (Principle I & II constitution — tidak boleh hardcode branding/kredensial); tidak boleh menambah page builder atau abstraksi di luar scope (Principle III & V)

**Scale/Scope**: Single-tenant per deployment (satu instalasi = satu klien); scope terbatas pada admin panel, tidak menyentuh frontend publik

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Applies? | Assessment |
|---|---|---|
| I. Multi-Client Reusability | Ya | Brand Settings & kredensial GA4 disimpan per instalasi via Settings/`.env`, bukan hardcode. Activity log tidak client-specific. **PASS** |
| II. White-Label by Default | Ya (langsung target fitur ini) | User Story 2 adalah implementasi literal dari prinsip ini — nama/logo/favicon/warna starter kit MUST fully replaceable sebelum client handoff. **PASS** (fitur ini yang memenuhi gate, bukan yang melanggarnya) |
| III. Settings-Driven Theming, No Page Builder | Ya | Branding pakai Spatie Settings + upload asset, bukan page builder. Tidak ada freeform layout ditambahkan. **PASS** |
| IV. Module Test Coverage | Ya | Ketiga user story adalah admin-panel behavior (bukan "content module" dalam pengertian Epic 3), tapi tetap MUST punya feature test dasar per FR sebelum dianggap selesai (lihat Phase 1 & tasks). **PASS (planned)** |
| V. Simplicity & Dependency Discipline | Ya | Package baru (`spatie/laravel-activitylog` + plugin log-viewer) adalah pilihan Spatie-ecosystem/well-maintained, sesuai prinsip ini. Lisensi perlu dicek ringan sebelum instalasi (bukan audit lisensi penuh — itu scope Epic 7/AMC-235). **PASS** |

Tidak ada pelanggaran constitution yang butuh entry di Complexity Tracking.

## Project Structure

### Documentation (this feature)

```text
specs/[###-feature]/
├── plan.md              # This file (/speckit-plan command output)
├── research.md          # Phase 0 output (/speckit-plan command)
├── data-model.md        # Phase 1 output (/speckit-plan command)
├── quickstart.md        # Phase 1 output (/speckit-plan command)
├── contracts/           # Phase 1 output (/speckit-plan command)
└── tasks.md             # Phase 2 output (/speckit-tasks command - NOT created by /speckit-plan)
```

### Source Code (repository root)

```text
app/
├── Providers/Filament/
│   └── AdminPanelProvider.php       # register plugin log-viewer + panel branding hooks
├── Filament/
│   ├── Pages/
│   │   └── Dashboard.php             # override default dashboard, tambah widget GA
│   └── Widgets/
│       └── GoogleAnalyticsWidget.php # widget traffic (visitor, pageviews, top pages, sumber traffic)
├── Settings/
│   └── BrandSettings.php             # Spatie Settings class: app_name, logo, favicon, primary_color
└── Models/
    └── (activity_log ditangani oleh package spatie/laravel-activitylog, tidak perlu model baru)

config/
├── analytics.php                     # existing, dipakai widget GA
├── activitylog.php                   # baru, dari spatie/laravel-activitylog (retensi/log name)
└── (activity-log-viewer plugin config, sesuai package yang dipilih di research.md)

database/migrations/
├── xxxx_create_settings_table.php    # jika belum ada dari spatie/laravel-settings
└── xxxx_create_activity_log_table.php

tests/Feature/
├── Dashboard/GoogleAnalyticsWidgetTest.php
├── Settings/BrandSettingsTest.php
└── ActivityLog/ActivityLogAccessTest.php
```

**Structure Decision**: Single Laravel + Filament application (tidak ada pemisahan frontend/backend terpisah — admin panel adalah bagian dari monolith Laravel yang sama). Semua perubahan berada di `app/Filament/*`, `app/Settings/*`, dan `config/*` yang sudah menjadi konvensi project ini (lihat `AdminPanelProvider.php` untuk pola registrasi plugin yang sudah ada).

## Complexity Tracking

Tidak ada pelanggaran constitution pada fitur ini — tabel ini sengaja dikosongkan sesuai instruksi template ("Fill ONLY if Constitution Check has violations").
