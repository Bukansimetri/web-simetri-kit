# Implementation Plan: Artikel CRUD Admin

**Branch**: `005-artikel-crud-admin` | **Date**: 2026-09-05 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/005-artikel-crud-admin/spec.md`

**Note**: This template is filled in by the `/speckit-plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Menambahkan CRUD admin (Filament Resource) untuk `Article` yang sudah ada dari 002-theme-branding-system, plus entity baru `ArticleCategory` (taxonomy terpisah dari Kategori Produk), tag lewat package `spatie/laravel-tags` yang sudah terpasang tapi belum pernah dipakai, rich text editor untuk isi artikel, status draft/terjadwal/publish berbasis `published_at`, featured image yang otomatis dikonversi ke WebP saat diupload, dan field byline bebas "Redaksi" (bukan sistem author terautentikasi). Admin jadi bisa mengelola blog sepenuhnya lewat panel admin tanpa developer menyentuh seeder/kode, sekaligus memperbaiki gap render `image_path` yang ditemukan saat riset (identik dengan temuan `Product.image_path` di 003).

## Technical Context

**Language/Version**: PHP 8.3, Laravel Framework 13

**Primary Dependencies**: Filament 3.3 (`ArticleResource` & `ArticleCategoryResource` baru, `RichEditor` bawaan, `TagsInput` bawaan); `spatie/laravel-tags` ^4.12 (SUDAH terpasang sejak Epic 1, belum pernah dipakai — dipakai pertama kali di fitur ini); GD (PHP extension bawaan, dipakai untuk konversi WebP, sudah pernah dipakai di 002 untuk `og-default.jpg`). Tidak ada dependency baru yang ditambahkan.

**Storage**: MySQL — migration baru `article_categories`, migration publish package `tags`/`taggables`, perluasan `articles` (`article_category_id` FK menggantikan `category` string, `image_path` jadi nullable, `redaksi` string nullable baru), dengan backfill data dari seed 002 yang sudah ada (research.md §1)

**Testing**: PHPUnit feature test memakai Livewire test helper untuk Filament Resource (pola sama seperti `ProductResourceTest`/`CategoryResourceTest` dari 003) — create/edit/delete, validasi field wajib & unique, guard hapus kategori, draft/terjadwal/publish visibility, tag attach/detach, konversi WebP

**Target Platform**: Server web Laravel standar (sama seperti fitur-fitur sebelumnya)

**Project Type**: Web application — perluasan admin panel Filament & controller/Blade publik yang sudah ada, tidak ada perubahan struktur project

**Performance Goals**: Tidak ada target khusus (admin-only untuk CRUD; halaman publik `/artikel` tetap harus render cepat seperti sebelumnya — tidak ada query N+1 baru dari relasi kategori/tag)

**Constraints**: CRUD MUST terbuka untuk semua role admin panel (FR-017); kategori artikel MUST entity terpisah dari kategori produk (FR-018); upload gambar MUST dikonversi WebP TANPA validasi dimensi (FR-020/FR-021); field "Redaksi" MUST teks bebas, BUKAN relasi ke `User`/sistem auth (FR-022); tidak ada dependency baru — pakai package yang sudah terpasang (`spatie/laravel-tags`) dan extension bawaan (GD) (Principle V)

**Scale/Scope**: 2 Filament Resource baru (Article, ArticleCategory) + migrasi skema `articles` + publish migration package tags + perbaikan render gambar & konten di 2 view publik (`artikel/index`/`article-card`, `artikel/show`)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Applies? | Assessment |
|---|---|---|
| I. Multi-Client Reusability | Ya | Kategori & tag artikel sepenuhnya dikelola admin per instalasi lewat CRUD, bukan hardcode/seeder-only. **PASS** |
| II. White-Label by Default | Tidak langsung | Fitur ini tidak menyentuh branding/identitas panel — tidak relevan. |
| III. Settings-Driven Theming, No Page Builder | Tidak langsung | CRUD data konten (artikel/kategori/tag), bukan theming/layout — tidak menambah abstraksi builder apa pun. |
| IV. Module Test Coverage | Ya | Setiap user story (kategori CRUD, tulis/edit artikel, draft/publish, tag, featured image) MUST punya feature test dasar sebelum dianggap selesai. **PASS (planned)** |
| V. Simplicity & Dependency Discipline | Ya | Tidak ada dependency baru — `spatie/laravel-tags` sudah terpasang (baru dipakai pertama kali), `RichEditor`/`TagsInput` bawaan Filament, WebP pakai GD bawaan PHP, field `redaksi` cukup kolom string (bukan sistem auth/author baru). **PASS** |

Tidak ada pelanggaran constitution yang butuh entry Complexity Tracking.

## Project Structure

### Documentation (this feature)

```text
specs/005-artikel-crud-admin/
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
│   ├── ArticleCategory.php          # Baru
│   └── Article.php                  # Diperluas: articleCategory() relation, HasTags trait, redaksi, hapus accessor lama
├── Support/
│   └── ImageUploads.php             # Baru — helper statis konversi upload → WebP (research.md §6)
└── Filament/Resources/
    ├── ArticleCategoryResource.php  # Baru
    ├── ArticleCategoryResource/Pages/{ListArticleCategories,CreateArticleCategory,EditArticleCategory}.php
    ├── ArticleResource.php          # Baru
    └── ArticleResource/Pages/{ListArticles,CreateArticle,EditArticle}.php

database/
├── migrations/
│   ├── xxxx_create_article_categories_table.php
│   ├── xxxx_create_tags_table.php           # Publish dari spatie/laravel-tags
│   ├── xxxx_create_taggables_table.php      # Publish dari spatie/laravel-tags
│   └── xxxx_update_articles_table_for_category_and_image.php   # backfill + drop kolom lama, nullable image_path, tambah redaksi
├── factories/
│   └── ArticleCategoryFactory.php   # Baru
└── seeders/
    ├── ArticleCategorySeeder.php    # Baru — 3 kategori dari seed 002 (Tips, Berita, Edukasi)
    └── ArticleSeeder.php            # Diperbarui: pakai article_category_id, isi redaksi, published_at tetap, image_path tetap kosong (demo)

resources/views/
├── components/sections/article-card.blade.php   # Diperbarui: render <img> dari $article->image_path, placeholder jika kosong
└── pages/artikel/show.blade.php                 # Diperbarui: render `content` sebagai HTML, tampilkan `redaksi` sebagai byline

app/Http/Controllers/Public/ArticleController.php  # Diperbarui: filter published_at <= now() (menutup celah FR-010)

tests/Feature/Admin/
├── ArticleCategoryResourceTest.php   # Baru — US1
└── ArticleResourceTest.php           # Baru — US2, US3, US4, US5
```

**Structure Decision**: Perluasan langsung dari struktur admin panel Filament yang sudah ada (`app/Filament/Resources/`), pola identik dengan `003-produk-crud-admin`. Tambahan baru: `app/Support/` untuk helper `ImageUploads` (belum ada namespace ini sebelumnya, tapi merupakan lokasi konvensional Laravel untuk helper class lintas-model, bukan struktur custom).

## Complexity Tracking

*Tidak ada pelanggaran constitution — tabel ini kosong.*
