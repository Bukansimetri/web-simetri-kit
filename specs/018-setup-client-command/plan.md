# Implementation Plan: Setup Client Command

**Branch**: `018-setup-client-command` | **Date**: 2026-09-13 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/018-setup-client-command/spec.md`

**Note**: This template is filled in by the `/speckit-plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Satu Artisan command baru, `app:setup-client`, yang membungkus urutan langkah setup instalasi klien baru: buat `.env` dari `.env.example` (jika belum ada), set `APP_NAME` sesuai argumen, generate `APP_KEY` (jika belum ada), lalu bersihkan seluruh cache (config, route, view, application). Aman dijalankan berulang (idempoten) — langkah pembuatan `.env`/`APP_KEY` dilewati dengan pesan jelas jika sudah ada, kecuali operator memberi flag `--force` eksplisit; pembersihan cache selalu dijalankan.

## Technical Context

**Language/Version**: PHP 8.3, Laravel 13

**Primary Dependencies**: Tidak ada dependency baru — memakai Artisan Console (`Illuminate\Console\Command`) dan command bawaan Laravel yang sudah tersedia (`key:generate`, `config:clear`, `route:clear`, `view:clear`, `cache:clear`) yang dipanggil dari dalam command baru via `$this->call()`

**Storage**: N/A — command ini memanipulasi file `.env` di filesystem lokal instalasi, bukan database

**Testing**: PHPUnit (Feature test) via `Illuminate\Support\Facades\Artisan` test helpers (`$this->artisan(...)`), mengikuti pola `tests/Feature/**/*Test.php` yang sudah ada di kit ini

**Target Platform**: CLI (Artisan), dijalankan developer/ops di lingkungan server/lokal saat provisioning instalasi klien baru — bukan dipanggil dari HTTP/admin panel

**Project Type**: Single Laravel monolith (command console) — tidak ada perubahan struktur proyek

**Performance Goals**: N/A (dijalankan sekali per provisioning, bukan pada request path); harus selesai dalam hitungan detik

**Constraints**: MUST tidak menimpa `.env`/`APP_KEY` yang sudah ada tanpa flag eksplisit (FR-006, FR-007); MUST bekerja tanpa koneksi database (dijalankan sebelum migrate pada instalasi yang benar-benar baru)

**Scale/Scope**: Satu instalasi (satu klien) per eksekusi, sesuai Assumptions di spec.md

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **I. Multi-Client Reusability** — PASS. Ini justru fitur yang secara langsung melayani prinsip ini: menjadikan setup klien baru sebagai proses berulang yang reproducible, bukan langkah manual per klien.
- **II. White-Label by Default** — PASS/N/A. Command ini menata `APP_NAME` di level environment (dipakai `config('app.name')` sebagai fallback), bukan mengubah branding admin panel (`BrandSettings`, yang tersimpan di database dan tetap dikonfigurasi terpisah lewat panel setelah migrate — lihat spec.md Assumptions).
- **III. Settings-Driven Theming, No Page Builder (NON-NEGOTIABLE)** — PASS/N/A. Fitur ini tidak menyentuh theming/section sama sekali.
- **IV. Module Test Coverage** — PASS (wajib dipenuhi di implementasi). Command ini bukan "content module" (services/portfolio/dst.) sehingga tidak masuk daftar eksplisit Prinsip IV, namun tetap WAJIB memiliki feature test dasar mengikuti semangat prinsip tsb dan best practice testing kit ini secara umum.
- **V. Simplicity & Dependency Discipline** — PASS. Tidak ada dependency baru; command baru murni mengorkestrasi command Artisan bawaan Laravel yang sudah tersedia.

**Initial gate result**: PASS, tanpa pelanggaran — tabel Complexity Tracking tidak diperlukan.

## Project Structure

### Documentation (this feature)

```text
specs/018-setup-client-command/
├── plan.md              # This file (/speckit-plan command output)
├── research.md          # Phase 0 output (/speckit-plan command)
├── contracts/           # Phase 1 output (/speckit-plan command)
├── quickstart.md        # Phase 1 output (/speckit-plan command)
└── tasks.md             # Phase 2 output (/speckit-tasks command - NOT created by /speckit-plan)
```

(Tidak ada `data-model.md` — fitur ini tidak melibatkan entitas data persisten, lihat spec.md "Key Entities".)

### Source Code (repository root)

```text
app/
└── Console/
    └── Commands/
        └── SetupClientCommand.php   # NEW - `app:setup-client {name} {--force}`

tests/
└── Feature/
    └── Console/
        └── SetupClientCommandTest.php   # NEW - skenario fresh install, re-run tanpa --force, --force, validasi nama kosong
```

**Structure Decision**: Mengikuti struktur standar Laravel 13 untuk Artisan command kustom (`app/Console/Commands/`, auto-discovered lewat `routes/console.php`/`bootstrap/app.php` yang sudah ada) — tidak ada direktori/struktur baru di luar konvensi framework yang dipakai kit ini.

## Complexity Tracking

> Tidak ada pelanggaran Constitution Check — tabel ini dikosongkan (tidak ada baris).
