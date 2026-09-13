# Implementation Plan: Menu Builder

**Branch**: `017-menu-builder` | **Date**: 2026-09-13 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/017-menu-builder/spec.md`

**Note**: This template is filled in by the `/speckit-plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Admin/editor mengelola item navigasi (navbar & footer, dan lokasi lain yang bisa ditambah tanpa kode) dari admin panel Filament: CRUD item menu, drag & drop urutan, penautan ke halaman internal (Custom Page atau modul publik lain) atau URL eksternal, dukungan satu tingkat sub-menu, dan toggle aktif/nonaktif. Pendekatan teknis: model `Menu`/`MenuItem` baru dengan kolom `order_column` (nested-set sederhana via `parent_id` untuk 1 level dropdown), Filament Resource dengan `Reorderable` table (drag handle bawaan Filament, tanpa dependency baru), dan Blade component `<x-layout.menu :location="...">` yang menggantikan array hardcoded di `header.blade.php` dan `footer.blade.php` — dicache per lokasi mengikuti pola caching halaman publik yang sudah ada di kit ini.

## Technical Context

**Language/Version**: PHP 8.3, Laravel 13

**Primary Dependencies**: Filament v3 (admin panel + reorderable table bawaan), Spatie Laravel Activitylog (audit trail, sudah terpasang), tidak ada dependency baru yang dibutuhkan

**Storage**: MySQL/MariaDB (mengikuti koneksi database default aplikasi saat ini) via Eloquent

**Testing**: PHPUnit (Feature test), mengikuti pola `tests/Feature/Admin/*ResourceTest.php` dan `tests/Feature/Pages/*Test.php` yang sudah ada

**Target Platform**: Web (server-side rendered Laravel + Filament panel), multi-tenant-per-deployment (satu instalasi per klien)

**Project Type**: Web application (monolith Laravel + Blade frontend + Filament admin panel) — single project, tanpa struktur backend/frontend terpisah

**Performance Goals**: Query menu per lokasi harus tidak menambah N+1 pada tiap page load frontend; hasil menu di-cache per lokasi (invalidasi saat item disimpan/dihapus), konsisten dengan strategi caching halaman publik yang sudah diterapkan pada Epic 5

**Constraints**: Tidak boleh menambah dependency baru untuk drag & drop (pakai fitur reorder bawaan Filament table); tidak boleh membangun page builder bebas (Prinsip III konstitusi) — menu builder hanya mengatur label/tujuan/urutan/lokasi, bukan layout halaman

**Scale/Scope**: Skala wajar situs company-profile per klien (puluhan item menu per lokasi, maksimal beberapa lokasi menu); tidak dirancang untuk ribuan item

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **I. Multi-Client Reusability** — PASS. Menu (item, urutan, lokasi) adalah data per-instalasi yang dikelola lewat admin panel/database, bukan hardcode di Blade. Lokasi menu sendiri juga bersifat data (tabel `MenuLocation` atau enum yang bisa ditambah via seeder per klien), bukan ditentukan di kode tema.
- **II. White-Label by Default** — PASS. Fitur ini tidak menyentuh branding admin panel; hanya menambahkan Resource baru yang mengikuti tema panel yang sudah di-white-label.
- **III. Settings-Driven Theming, No Page Builder (NON-NEGOTIABLE)** — PASS. Menu Builder hanya mengelola struktur navigasi (label, tautan, urutan, lokasi) — bukan layout/tampilan halaman. Tidak ada kemampuan menyusun blok konten bebas; ini sejalan dengan pola "settings + varian section tetap", bukan freeform builder.
- **IV. Module Test Coverage** — PASS (wajib dipenuhi di implementasi). Menu Builder disebut eksplisit dalam daftar modul Prinsip IV — feature test CRUD + render frontend WAJIB dibuat sebelum modul ditandai selesai, mengikuti pola `tests/Feature/Admin/BannerResourceTest.php` dan `tests/Feature/Pages/HomeBannerTest.php`.
- **V. Simplicity & Dependency Discipline** — PASS. Tidak menambah dependency baru; drag & drop urutan memakai fitur reorder bawaan Filament Table Builder yang sudah dipakai kit ini secara implisit (pattern serupa BannerResource).

**Initial gate result**: PASS, tanpa pelanggaran — tabel Complexity Tracking tidak diperlukan.

## Project Structure

### Documentation (this feature)

```text
specs/017-menu-builder/
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
├── Models/
│   ├── MenuLocation.php          # NEW - lokasi tampil menu (navbar, footer, dst.)
│   └── MenuItem.php              # NEW - item menu (label, target, order, parent_id)
├── Filament/
│   └── Resources/
│       ├── MenuLocationResource/ # NEW (opsional - lihat research.md untuk keputusan single vs dual resource)
│       │   └── Pages/
│       └── MenuItemResource/     # NEW - CRUD + reorder item menu, grouped/filtered per lokasi
│           └── Pages/
├── View/
│   └── Components/
│       └── Layout/
│           └── Menu.php          # NEW - Blade component untuk render menu per lokasi
└── (existing: Http/Controllers/Public, Models/CustomPage.php, dst. — dipakai sebagai referensi "internal link target")

database/
├── migrations/
│   ├── xxxx_create_menu_locations_table.php   # NEW
│   └── xxxx_create_menu_items_table.php       # NEW
└── seeders/
    └── MenuSeeder.php             # NEW - seed lokasi default (Navbar Utama, Footer) + migrasi isi menu lama

resources/views/
└── components/
    ├── layout/
    │   ├── header.blade.php       # MODIFIED - navbar item diganti <x-layout.menu location="navbar-utama" />
    │   ├── footer.blade.php       # MODIFIED - kolom link diganti <x-layout.menu location="footer" />
    └── layout/
        └── menu.blade.php         # NEW - render rekursif item + sub-item (1 tingkat)

tests/
├── Feature/
│   ├── Admin/
│   │   └── MenuItemResourceTest.php   # NEW - CRUD, reorder, assign lokasi, nonaktifkan
│   └── Public/
│       └── MenuRenderingTest.php      # NEW - navbar/footer render sesuai lokasi & urutan, broken-link handling
└── Unit/
    └── MenuItemUrlResolutionTest.php  # NEW - resolusi URL internal vs eksternal vs no-link
```

**Structure Decision**: Mengikuti struktur monolith Laravel + Filament yang sudah ada di repo ini (bukan struktur multi-project). Model, Filament Resource, Blade component, migration/seeder, dan test feature ditambahkan pada direktori standar yang sama persis dengan modul-modul sebelumnya (Banner, Custom Page, dll.) agar konsisten dengan konvensi kodebase yang sudah mapan.

## Complexity Tracking

> Tidak ada pelanggaran Constitution Check — tabel ini dikosongkan (tidak ada baris).
