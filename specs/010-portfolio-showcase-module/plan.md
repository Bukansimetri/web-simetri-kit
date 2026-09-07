# Implementation Plan: Modul Portfolio / Project Showcase

**Branch**: `010-portfolio-showcase-module` | **Date**: 2026-09-07 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/010-portfolio-showcase-module/spec.md`

## Summary

Menambahkan modul "Portfolio" — dua entity baru: `PortfolioCategory` (name, slug, order — taxonomy CRUD terpisah, pola `ArticleCategoryResource`) dan `PortfolioProject` (title, slug, kategori, description HTML, gallery images JSON array terurut, client_name?, project_url?, completed_at?, order, is_active). CRUD admin lewat dua Filament Resource. Halaman publik baru: listing `GET /portfolio` (grid proyek aktif + filter kategori via query `?kategori={slug}`, empty-state bukan 404) dan detail `GET /portfolio/{portfolioProject:slug}` (galeri + deskripsi + metadata opsional, 404 untuk nonaktif/tidak ada). Gambar galeri di-downscale ke lebar maks 1200px + konversi WebP saat simpan — memperluas helper `App\Support\ImageUploads` yang sudah ada (tambah parameter `maxWidth`). Menutup bagian "portfolio" di AMC-218. Tidak ada dependency baru.

## Technical Context

**Language/Version**: PHP 8.3, Laravel Framework 13

**Primary Dependencies**: Filament 3.3 (`PortfolioProjectResource` + `PortfolioCategoryResource` baru — `TextInput`, `Select`, `RichEditor`, `FileUpload` multiple/reorderable, `TextInput` numeric, `DatePicker`, `Toggle`). Helper `App\Support\ImageUploads` (diperluas untuk resize). Tidak ada dependency baru.

**Storage**: MySQL — 2 migration baru: `portfolio_categories` (`id`, `name`, `slug` unique, `order` integer default 0, timestamps) dan `portfolio_projects` (`id`, `portfolio_category_id` FK, `title`, `slug` unique, `description` longtext, `images` json, `client_name` string nullable, `project_url` string nullable, `completed_at` date nullable, `order` integer default 0, `is_active` boolean default true, timestamps).

**Testing**: PHPUnit feature test memakai Livewire test helper untuk kedua Filament Resource (pola `ArticleResourceTest` + `ArticleCategoryResourceTest`) — kategori: create/edit/delete, nama unik, cegah hapus bila dipakai; proyek: create/edit/delete, slug auto/override/unique, validasi field wajib (title, kategori, description, min 1 gambar), validasi `project_url` http/https, reorder galeri, gambar tersimpan WebP ≤1200px (assert via image size); feature test terpisah untuk halaman publik (listing hanya aktif + terurut, filter kategori via query param + shareable URL, empty-state 200 bukan 404, detail 200 untuk aktif / 404 untuk nonaktif & slug tidak ada, metadata opsional tidak render label kosong).

**Target Platform**: Server web Laravel standar

**Project Type**: Web application — perluasan admin panel Filament + 2 route publik baru + 2 controller + 3 view Blade baru (listing, detail; kategori tidak punya halaman sendiri)

**Performance Goals**: Tidak ada target khusus (listing = 1 query `where is_active` + eager-load kategori + `orderBy order`; volume puluhan proyek)

**Constraints**: satu kategori per proyek (FR-005, Assumptions); min 1 gambar galeri (FR-008); gambar di-downscale ≤1200px lebar + WebP saat simpan, tanpa upscale, tanpa penolakan dimensi (FR-010a/FR-010b, klarifikasi); `project_url` validasi http/https (FR-009); listing empty-state bukan 404 (FR-018); cegah hapus kategori yang dipakai (FR-003); CRUD terbuka semua role panel (FR-015); tidak ada dependency baru (Principle V); tidak ada draft/publish, multi-kategori/tag, paginasi wajib, SEO fields per proyek (Assumptions)

**Scale/Scope**: 2 Filament Resource + 2 migration + 2 model + 2 factory + perluasan `ImageUploads` (tambah param `maxWidth`) + 2 route publik + 2 controller publik + 3 view Blade (listing + detail; komponen kartu opsional) + tidak ada perubahan navigasi/footer (di luar scope inti, Assumptions)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Applies? | Assessment |
|---|---|---|
| I. Multi-Client Reusability | Ya | Proyek & kategori diisi lewat CRUD per klien; listing kosong sampai klien mengisi. **PASS** |
| II. White-Label by Default | Tidak langsung | Tidak menyentuh branding/identitas panel. |
| III. Settings-Driven Theming, No Page Builder | Ya (NON-NEGOTIABLE) | Halaman listing & detail adalah Blade tetap dengan layout tetap — bukan builder. Deskripsi proyek satu blok rich-text (pola Artikel), bukan section builder. **PASS** |
| IV. Module Test Coverage | Ya | US1 (kategori), US2 (proyek CRUD), US3 (halaman publik) masing-masing punya feature test dasar. **PASS (planned)** |
| V. Simplicity & Dependency Discipline | Ya | Nol dependency baru; reuse pola `ArticleResource`/`ProductResource`/`ArticleCategoryResource`; `ImageUploads` diperluas minimal (satu parameter opsional, backward-compatible). Tidak ada draft/publish, tag, multi-kategori, paginasi, related-projects, SEO fields yang tidak diminta. **PASS** |

Tidak ada pelanggaran constitution — Complexity Tracking kosong.

## Project Structure

### Documentation (this feature)

```text
specs/010-portfolio-showcase-module/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/
│   └── admin-panel-surface.md
└── tasks.md             # Phase 2 output (/speckit-tasks — NOT created here)
```

### Source Code (repository root)

```text
app/
├── Models/
│   ├── PortfolioCategory.php            # Baru — name, slug, order; hasMany projects
│   └── PortfolioProject.php             # Baru — fillable + casts (images array, completed_at date, is_active bool, order int); belongsTo category; getRouteKeyName = slug; coverImageUrl()/imageUrls() (pola Product)
├── Filament/Resources/
│   ├── PortfolioCategoryResource.php    # Baru (pola ArticleCategoryResource)
│   ├── PortfolioCategoryResource/Pages/{ListPortfolioCategories,CreatePortfolioCategory,EditPortfolioCategory}.php
│   ├── PortfolioProjectResource.php     # Baru (pola ArticleResource + ProductResource galeri)
│   └── PortfolioProjectResource/Pages/{ListPortfolioProjects,CreatePortfolioProject,EditPortfolioProject}.php
├── Http/Controllers/Public/
│   ├── PortfolioController.php          # Baru — index(Request): listing + filter kategori; show(PortfolioProject): detail
│   └── (satu controller dengan 2 method, pola ArticleController)
└── Support/
    └── ImageUploads.php                 # Diperbarui — storeAsWebp(..., ?int $maxWidth = null): downscale bila lebih lebar

database/
├── migrations/
│   ├── xxxx_create_portfolio_categories_table.php   # Baru
│   └── xxxx_create_portfolio_projects_table.php     # Baru
└── factories/
    ├── PortfolioCategoryFactory.php     # Baru
    └── PortfolioProjectFactory.php      # Baru (+ state inactive)

routes/web.php                           # Diperbarui — GET /portfolio + GET /portfolio/{portfolioProject:slug}

resources/views/pages/portfolio/
├── index.blade.php                      # Baru — grid proyek aktif + filter kategori (chip/link ?kategori=slug) + empty-state
└── show.blade.php                       # Baru — galeri + deskripsi + metadata opsional (client_name, completed_at, project_url target=_blank)

tests/Feature/
├── Admin/PortfolioCategoryResourceTest.php   # Baru — US1
├── Admin/PortfolioProjectResourceTest.php    # Baru — US2
└── Pages/PortfolioPageTest.php               # Baru — US3
```

**Structure Decision**: Perluasan langsung struktur Filament & controller publik yang sudah ada. `PortfolioProjectResource` menggabungkan pola `ArticleResource` (judul+slug auto, kategori Select, RichEditor deskripsi) dan `ProductResource` (galeri `FileUpload::multiple()->reorderable()`). `PortfolioCategoryResource` meniru `ArticleCategoryResource` (+ kolom `slug` untuk URL filter yang shareable). `ImageUploads::storeAsWebp` diperluas dengan satu parameter opsional `maxWidth` sehingga pemakaian lama (`ArticleResource`) tidak berubah.

## Complexity Tracking

*Tidak ada pelanggaran constitution — tabel ini kosong.*
