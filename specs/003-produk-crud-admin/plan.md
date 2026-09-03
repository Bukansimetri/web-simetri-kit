# Implementation Plan: Produk CRUD Admin

**Branch**: `003-produk-crud-admin` | **Date**: 2026-09-03 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/003-produk-crud-admin/spec.md`

**Note**: This template is filled in by the `/speckit-plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Menambahkan CRUD admin (Filament Resource) untuk `Product` yang sudah ada dari 002-theme-branding-system, plus entity baru `Category` (taxonomy dengan CRUD sendiri, menggantikan kolom `category` string bebas) dan galeri gambar multi-file (kolom `images` JSON, menggantikan `image_path` tunggal yang ternyata tidak pernah dirender). Admin/operator instalasi klien jadi bisa mengelola katalog produk sepenuhnya lewat panel admin tanpa developer menyentuh seeder/kode, sekaligus memperbaiki gap render gambar produk di frontend publik yang ditemukan saat riset.

## Technical Context

**Language/Version**: PHP 8.3, Laravel Framework 13

**Primary Dependencies**: Filament 3.3 (sudah ada — `ProductResource` & `CategoryResource` baru, `Repeater` untuk specs/features, `FileUpload::multiple()->reorderable()` untuk galeri; tidak ada dependency baru)

**Storage**: MySQL — migration baru tabel `categories`, perluasan `products` (`category_id` FK menggantikan `category` string, `images` json menggantikan `image_path`), dengan backfill data dari seed 002 yang sudah ada (research.md §3)

**Testing**: PHPUnit feature test memakai Livewire test helper untuk Filament Resource (pola sama seperti `BrandSettingsTest` di 002) — create/edit/delete, validasi field wajib, validasi unique, guard hapus kategori

**Target Platform**: Server web Laravel standar (sama seperti 002)

**Project Type**: Web application — perluasan admin panel Filament yang sudah ada, tidak ada perubahan struktur project

**Performance Goals**: Tidak ada target khusus (admin-only, low concurrency, konsisten dengan fitur admin lain)

**Constraints**: CRUD MUST terbuka untuk semua role admin panel (FR-017, bukan restricted); guard hapus kategori MUST mencegah data produk kehilangan kategori (Principle I); tidak menambah dependency baru untuk galeri gambar — pakai pola JSON array yang sudah ada di codebase, bukan Spatie Media Library (Principle V, research.md §2)

**Scale/Scope**: 2 Filament Resource baru (Product, Category) + migrasi skema `products` + perbaikan render gambar di 3 view publik (`produk/index`, `produk/show`, `home` — section "Produk Kami")

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Applies? | Assessment |
|---|---|---|
| I. Multi-Client Reusability | Ya | Kategori & katalog produk sepenuhnya dikelola admin per instalasi lewat CRUD, bukan hardcode/seeder-only. **PASS** |
| II. White-Label by Default | Tidak langsung | Fitur ini tidak menyentuh branding/identitas panel — tidak relevan. |
| III. Settings-Driven Theming, No Page Builder | Tidak langsung | Fitur ini CRUD data konten (produk/kategori), bukan theming/layout — tidak menambah abstraksi builder apa pun. **PASS (tidak berlaku, tidak melanggar)** |
| IV. Module Test Coverage | Ya | Setiap user story (kategori CRUD, produk CRUD, galeri, specs/features, urutan & hapus) MUST punya feature test dasar sebelum dianggap selesai — lihat Phase 1 & tasks nanti. **PASS (planned)** |
| V. Simplicity & Dependency Discipline | Ya | Tidak ada dependency baru — galeri gambar pakai kolom JSON + `FileUpload` bawaan Filament (bukan Spatie Media Library yang walau terpasang, belum pernah dipakai di model manapun), konsisten dengan pola `specs`/`features`/`logo_path` yang sudah ada. **PASS** |

Tidak ada pelanggaran constitution yang butuh entry Complexity Tracking.

## Project Structure

### Documentation (this feature)

```text
specs/003-produk-crud-admin/
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
│   ├── Category.php                 # Baru
│   └── Product.php                  # Diperluas: category() relation, images cast, hapus accessor lama
└── Filament/Resources/
    ├── CategoryResource.php         # Baru
    ├── CategoryResource/Pages/{ListCategories,CreateCategory,EditCategory}.php
    ├── ProductResource.php          # Baru
    └── ProductResource/Pages/{ListProducts,CreateProduct,EditProduct}.php

database/
├── migrations/
│   ├── xxxx_create_categories_table.php
│   └── xxxx_update_products_table_for_category_and_gallery.php   # backfill + drop kolom lama, lihat research.md §3
├── factories/
│   └── CategoryFactory.php          # Baru
└── seeders/
    ├── CategorySeeder.php           # Baru — 3 kategori dari mockup
    └── ProductSeeder.php            # Diperbarui: pakai category_id + images[]

resources/views/
├── components/sections/product-card.blade.php   # Diperbarui: render <img> dari $product->images[0] (cover), placeholder jika kosong
└── pages/produk/show.blade.php                  # Diperbarui: render galeri penuh dari $product->images, placeholder jika kosong
    (pages/home.blade.php section "Produk Kami" ikut memakai product-card yang sudah diperbarui — tidak perlu perubahan terpisah)

tests/Feature/Admin/
├── CategoryResourceTest.php         # Baru — US1
└── ProductResourceTest.php          # Baru — US2, US3, US4, US5
```

**Structure Decision**: Perluasan langsung dari struktur admin panel Filament yang sudah ada (`app/Filament/Resources/`), tidak ada folder/namespace baru. Perubahan Blade dibatasi ke 2 file view publik yang sudah ada dari 002 (bukan file baru), plus 1 migration terurut untuk transisi skema yang aman terhadap data existing.

## Complexity Tracking

*Tidak ada pelanggaran constitution — tabel ini kosong.*
