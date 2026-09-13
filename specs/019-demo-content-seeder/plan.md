# Implementation Plan: Demo Content Seeder

**Branch**: `019-demo-content-seeder` | **Date**: 2026-09-13 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/019-demo-content-seeder/spec.md`

**Note**: This template is filled in by the `/speckit-plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Dua Artisan command baru — `demo:seed` dan `demo:clean` — yang mengisi dan membersihkan konten contoh realistis di empat modul (Layanan/Produk, Tim, Testimoni, Portfolio) untuk keperluan demo penjualan, tanpa pernah otomatis berjalan sebagai bagian dari setup instalasi standar. Data demo yang di-insert dicatat pada satu tabel manifest ringan (`demo_seed_records`, polymorphic) sehingga `demo:seed` idempoten (tidak duplikat bila dijalankan ulang) dan `demo:clean` bisa menghapus tepat baris yang dibuatnya sendiri tanpa menyentuh data asli yang ditambahkan admin. Sebagai bagian pekerjaan ini, seeding demo Produk & Testimoni yang saat ini tergabung langsung di `DatabaseSeeder` dipindahkan ke jalur `demo:seed` yang eksplisit, agar konsisten dengan standar deployment kit ini.

## Technical Context

**Language/Version**: PHP 8.3, Laravel 13

**Primary Dependencies**: Tidak ada dependency baru — Eloquent, `Illuminate\Database\Seeder`, dan Artisan Console bawaan yang sudah dipakai seluruh modul kit ini

**Storage**: MySQL/MariaDB (mengikuti koneksi default aplikasi) — satu migration baru untuk tabel manifest `demo_seed_records`

**Testing**: PHPUnit (Feature test) memakai `RefreshDatabase` dan `$this->artisan(...)`, mengikuti pola `tests/Feature/**` yang sudah ada

**Target Platform**: CLI (Artisan), dijalankan developer/ops/sales-engineer saat menyiapkan atau membersihkan instalasi demo — bukan dipanggil dari admin panel/HTTP

**Project Type**: Single Laravel monolith — tidak ada perubahan struktur proyek

**Performance Goals**: N/A (dijalankan sesekali per siklus demo, bukan pada request path)

**Constraints**: MUST tidak berjalan otomatis sebagai bagian `DatabaseSeeder`/`app:setup-client` (FR-003); MUST idempoten (FR-005); pembersihan MUST tidak menyentuh data yang bukan bagian manifest demo (FR-004, FR-006)

**Scale/Scope**: Selusin-an entri contoh per modul (4 modul: Layanan, Tim, Testimoni, Portfolio+kategorinya) — skala kecil, sekali jalan per siklus demo

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **I. Multi-Client Reusability** — PASS. Konten demo eksplisit dipisah dari data produksi klien (FR-003); tidak ada asumsi klien tunggal dibangun ke kode.
- **II. White-Label by Default** — N/A. Fitur ini tidak menyentuh branding.
- **III. Settings-Driven Theming, No Page Builder** — N/A. Fitur ini hanya mengisi data lewat struktur modul yang sudah ada, tidak menyentuh layout/tema.
- **IV. Module Test Coverage** — PASS (wajib dipenuhi di implementasi). `demo:seed`/`demo:clean` bukan "content module" itu sendiri, tapi tetap wajib punya feature test dasar mengikuti semangat prinsip ini.
- **V. Simplicity & Dependency Discipline** — PASS. Tidak ada dependency baru. Satu tabel manifest baru adalah struktur data minimal yang benar-benar dibutuhkan (bukan abstraksi spekulatif) — tanpa itu, `demo:clean` tidak bisa membedakan data demo dari data asli admin secara aman (FR-004), dan alternatif berbasis pencocokan nama/slug tetap (dibahas & ditolak di research.md) lebih rapuh, bukan lebih sederhana.
- **Deployment & Client Setup Standards** (bagian konstitusi di luar 5 prinsip inti) — Fitur ini secara langsung MEMPERBAIKI pelanggaran yang sudah ada: `ProductSeeder`/`TestimonialSeeder` saat ini otomatis jalan lewat `DatabaseSeeder`, bertentangan dengan "seeders demo MUST NOT auto-run di produksi". Pekerjaan ini memindahkannya ke `demo:seed` eksplisit.

**Initial gate result**: PASS, tanpa pelanggaran yang butuh justifikasi — tabel Complexity Tracking tidak diperlukan (satu tabel manifest baru sudah dijustifikasi di atas, bukan pelanggaran).

## Project Structure

### Documentation (this feature)

```text
specs/019-demo-content-seeder/
├── plan.md              # This file (/speckit-plan command output)
├── research.md          # Phase 0 output (/speckit-plan command)
├── data-model.md        # Phase 1 output (/speckit-plan command)
├── contracts/           # Phase 1 output (/speckit-plan command)
├── quickstart.md        # Phase 1 output (/speckit-plan command)
└── tasks.md             # Phase 2 output (/speckit-tasks command - NOT created by /speckit-plan)
```

### Source Code (repository root)

```text
app/
├── Console/
│   └── Commands/
│       ├── DemoSeedCommand.php    # NEW - `demo:seed`
│       └── DemoCleanCommand.php   # NEW - `demo:clean`
└── Models/
    └── DemoSeedRecord.php         # NEW - manifest polymorphic (seedable_type/seedable_id)

database/
├── migrations/
│   └── xxxx_create_demo_seed_records_table.php   # NEW
└── seeders/
    ├── DatabaseSeeder.php          # MODIFIED - hapus panggilan ProductSeeder/TestimonialSeeder
    ├── DemoContentSeeder.php       # NEW - orkestrator, dipanggil oleh DemoSeedCommand (bukan DatabaseSeeder)
    ├── ProductSeeder.php           # MODIFIED - direkam ke manifest, dipanggil hanya dari DemoContentSeeder
    ├── TestimonialSeeder.php       # MODIFIED - direkam ke manifest, dipanggil hanya dari DemoContentSeeder
    ├── TeamMemberSeeder.php        # NEW - direkam ke manifest
    └── PortfolioDemoSeeder.php     # NEW - kategori + proyek portfolio contoh, direkam ke manifest

tests/
└── Feature/
    └── Console/
        ├── DemoSeedCommandTest.php    # NEW
        └── DemoCleanCommandTest.php   # NEW
```

**Structure Decision**: Mengikuti struktur standar Laravel (Artisan command + seeder class + satu model/migration ringan untuk manifest) — konsisten dengan konvensi kit ini (lihat 018-setup-client-command untuk pola Artisan command, dan seeder-seeder modul yang sudah ada untuk pola penulisan data contoh).

## Complexity Tracking

> Tidak ada pelanggaran Constitution Check yang butuh justifikasi tambahan di luar yang sudah dijelaskan pada Prinsip V di atas.
