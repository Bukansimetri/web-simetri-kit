# Implementation Plan: SEO Management

**Branch**: `014-seo-management` | **Date**: 2026-09-12 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/014-seo-management/spec.md`

## Summary

Menambahkan lapisan SEO yang berlaku ke seluruh halaman publik (OG tags, Twitter Card, canonical URL, JSON-LD `Organization`) dengan fallback dari `BrandSettings` yang sudah ada (`app_name`, `og_image_path`) plus satu field baru (`meta_description`). Empat model konten (`Product`, `Article`, `CustomPage`, `PortfolioProject`) mendapat 3 kolom opsional (`meta_title`, `meta_description`, `meta_image_path`) via trait `HasSeoMetadata` yang menyediakan fallback-chain per field, diisi lewat Section "SEO" baru di form Filament masing-masing resource. Structured data tambahan (`FAQPage` di `/faq`, `Article` di `artikel/show`, `Product` di `produk/show`) digenerate lewat helper statis `App\Support\Seo\JsonLd`. Tidak ada dependency baru — seluruhnya reuse pola `@section`/`@yield` Blade, helper `ImageUploads`, dan konvensi `Section::make()` Filament yang sudah ada di codebase.

## Technical Context

**Language/Version**: PHP 8.3, Laravel Framework 13

**Primary Dependencies**: Filament 3.3 (Section "SEO" baru + `FileUpload`/`TextInput`/`Textarea` dengan `->hint()` reaktif di 4 resource existing: `ProductResource`, `ArticleResource`, `CustomPageResource`, `PortfolioProjectResource`; 1 Section baru "SEO" di `BrandSettingsPage`). Helper `App\Support\ImageUploads` (reuse, `maxWidth: 1200`). Tidak ada dependency baru.

**Storage**: MySQL — 4 migration `add_seo_fields_to_{products,articles,custom_pages,portfolio_projects}_table` (kolom identik: `meta_title` string nullable, `meta_description` text nullable, `meta_image_path` string nullable) + 1 migration Spatie Settings `add_meta_description_to_brand_settings`.

**Testing**: PHPUnit feature test — perluasan `ProductResourceTest`/`ArticleResourceTest`/`CustomPageResourceTest`/`PortfolioProjectResourceTest` (isi & simpan field SEO); test baru `SeoGlobalMetaTest` (US1, matriks halaman publik); `SeoContentOverrideTest` (US2, override vs fallback per model, termasuk edge case gambar/deskripsi kosong); `StructuredDataTest` (US3, JSON-LD per tipe halaman + FAQPage dilewati saat kosong); unit test `HasSeoMetadataTest`/`JsonLdTest` untuk logika fallback & schema murni tanpa HTTP.

**Target Platform**: Server web Laravel standar

**Project Type**: Web application — perluasan layout publik (`layouts/public.blade.php` + partial baru), 4 model + trait baru, 4 Filament Resource existing + `BrandSettingsPage`, 3 controller existing (`ArticleController`, `ProductController`, `FaqController`) mengirim data schema tambahan

**Performance Goals**: Tidak ada target khusus — seluruh komputasi (fallback string, JSON-LD array) murni in-memory per-request, tanpa query tambahan (data konten/FAQ sudah di-load controller existing)

**Constraints**: Field SEO per konten 100% opsional, tidak ada validasi wajib (FR-005); fallback tiap field independen, bukan all-or-nothing (edge case spec); `FAQPage` schema dilewati total saat `$faqItems` kosong (FR-009); `Product` schema hanya sertakan `offers.price` bila `price` terisi, tanpa `availability` (FR-011, Assumptions); perubahan `BrandSettings` MUST langsung berlaku ke halaman tanpa override tanpa cache stale (FR-012); nol dependency baru (Principle V); tidak ada sitemap/robots.txt, noindex per-item, atau `sameAs` Organization (Assumptions — di luar scope)

**Scale/Scope**: 5 migration (4 tabel konten + 1 settings) + 1 trait (`HasSeoMetadata`) diterapkan ke 4 model + 1 helper statis (`JsonLd`) + 1 komponen Blade (`<x-seo.json-ld>`) + 1 partial layout diperbarui (`og-meta.blade.php` → tambah Twitter Card + canonical, atau file baru `seo-meta.blade.php` yang menggantikannya) + 1 partial baru (`schema-organization.blade.php`) + update 4 Filament Resource + `BrandSettingsPage` (Section "SEO" baru) + update 3 controller publik (Article/Product/Faq) untuk kirim schema + update 4 halaman publik (`produk/show`, `artikel/show`, `halaman/show`, `portfolio/show`) untuk `@section('og_title'/'og_image', ...)`

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Applies? | Assessment |
|---|---|---|
| I. Multi-Client Reusability | Ya | Deskripsi SEO default situs saat ini hardcoded di `layouts/public.blade.php` — fitur ini MEMPERBAIKI pelanggaran existing dengan memindahkannya ke `BrandSettings::meta_description` (settings-driven). Field SEO per konten diisi lewat CRUD admin, bukan hardcoded. **PASS** |
| II. White-Label by Default | Tidak langsung | Tidak menyentuh branding/identitas panel admin. JSON-LD `Organization` memakai `$brand->app_name`/`logo_path` yang sudah dapat diganti per klien. |
| III. Settings-Driven Theming, No Page Builder | Ya (NON-NEGOTIABLE) | Tidak ada layout/section builder baru — field SEO adalah data teks/gambar biasa yang mengisi meta tag tetap, posisi & struktur `<head>` tetap hardcoded di layout. Bukan slot generik yang bisa dikonfigurasi bebas. **PASS** |
| IV. Module Test Coverage | Ya | Ketiga user story (global fallback, override per konten, JSON-LD) masing-masing dapat feature test. Trait & helper JSON-LD dapat unit test terpisah. **PASS (planned)** |
| V. Simplicity & Dependency Discipline | Ya | Nol dependency baru — lihat research.md §1–8 (semua keputusan eksplisit menolak paket SEO/schema pihak ketiga demi reuse pola existing). Tidak ada speculative abstraction (`SeoMetadata` model terpisah ditolak; `Tabs` Filament ditolak demi konsistensi `Section`). **PASS** |

Tidak ada pelanggaran constitution — Complexity Tracking kosong.

## Project Structure

### Documentation (this feature)

```text
specs/014-seo-management/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/
│   └── seo-surface.md
└── tasks.md             # Phase 2 output (/speckit-tasks — NOT created here)
```

### Source Code (repository root)

```text
app/
├── Concerns/
│   └── HasSeoMetadata.php                        # Baru — trait: seoTitle()/seoDescription()/seoImageUrl() + 3 method abstrak
├── Support/Seo/
│   └── JsonLd.php                                 # Baru — static: organization()/faqPage()/article()/product()
├── Models/
│   ├── Product.php                                # + use HasSeoMetadata, implementasi 3 fallback method
│   ├── Article.php                                # + use HasSeoMetadata, implementasi 3 fallback method
│   ├── CustomPage.php                             # + use HasSeoMetadata, implementasi 3 fallback method (image fallback null)
│   └── PortfolioProject.php                       # + use HasSeoMetadata, implementasi 3 fallback method
├── Settings/
│   └── BrandSettings.php                          # + public ?string $meta_description
├── Filament/
│   ├── Resources/
│   │   ├── ProductResource.php                    # + Section::make('SEO')->collapsed()
│   │   ├── ArticleResource.php                    # + Section::make('SEO')->collapsed()
│   │   ├── CustomPageResource.php                 # + Section::make('SEO')->collapsed()
│   │   └── PortfolioProjectResource.php            # + Section::make('SEO')->collapsed()
│   └── Pages/
│       └── BrandSettingsPage.php                  # + Section "SEO" (meta_description) di form + mount()/save()
└── Http/Controllers/Public/
    ├── ArticleController.php                       # show(): kirim JsonLd::article($article)
    ├── ProductController.php                       # show(): kirim JsonLd::product($product)
    └── FaqController.php                           # __invoke(): kirim JsonLd::faqPage($faqItems)

database/
├── migrations/
│   ├── xxxx_add_seo_fields_to_products_table.php
│   ├── xxxx_add_seo_fields_to_articles_table.php
│   ├── xxxx_add_seo_fields_to_custom_pages_table.php
│   └── xxxx_add_seo_fields_to_portfolio_projects_table.php
└── settings/
    └── xxxx_add_meta_description_to_brand_settings.php

resources/views/
├── layouts/
│   ├── public.blade.php                            # + <link rel="canonical">, + @include schema-organization
│   └── partials/
│       ├── og-meta.blade.php                       # Diperbarui — tambah Twitter Card tags
│       └── schema-organization.blade.php           # Baru — JSON-LD Organization (global)
├── components/seo/
│   └── json-ld.blade.php                            # Baru — <x-seo.json-ld :schema="...">
└── pages/
    ├── produk/show.blade.php                        # + @section('og_title'|'og_image', $product->seo...()) + @push('head') json-ld Product
    ├── artikel/show.blade.php                       # + @section(...) + @push('head') json-ld Article
    ├── faq.blade.php                                 # + @push('head') json-ld FAQPage (skip bila kosong)
    └── custom-page/show.blade.php                    # + @section('og_title'|'og_image', $page->seo...())

tests/
├── Unit/
│   ├── HasSeoMetadataTest.php                       # Baru — fallback chain per field, per model (matriks kosong/isi)
│   └── Seo/JsonLdTest.php                            # Baru — struktur schema per @type, FAQPage null saat kosong, offers.price kondisional
└── Feature/
    ├── Public/
    │   ├── SeoGlobalMetaTest.php                     # Baru (US1) — OG/Twitter/canonical/Organization di halaman tanpa override
    │   ├── SeoContentOverrideTest.php                 # Baru (US2) — override vs fallback di 4 halaman detail
    │   └── StructuredDataTest.php                     # Baru (US3) — FAQPage/Article/Product di halaman terkait
    └── Admin/
        ├── ProductResourceTest.php                    # Diperluas — simpan field SEO
        ├── ArticleResourceTest.php                    # Diperluas — simpan field SEO
        ├── CustomPageResourceTest.php                 # Diperluas — simpan field SEO
        └── PortfolioProjectResourceTest.php           # Diperluas — simpan field SEO
```

**Structure Decision**: Trait `HasSeoMetadata` (di `app/Concerns/`, folder baru mengikuti konvensi Laravel umum untuk trait lintas-model) memusatkan logika fallback sehingga 4 model hanya mendeklarasikan sumber data masing-masing — menghindari duplikasi `Str::limit(strip_tags(...))`. Helper `JsonLd` di `app/Support/Seo/` (sub-namespace baru di bawah `Support` yang sudah dipakai `ImageUploads`) memisahkan pembentukan schema dari rendering, supaya bisa di-unit-test sebagai array PHP murni tanpa HTTP. Perubahan Blade minimal: partial `og-meta.blade.php` yang sudah ada diperluas (bukan diganti nama) supaya diff kecil; komponen `<x-seo.json-ld>` generik dipakai berulang di 4 lokasi (global Organization + 3 per-halaman) untuk menghindari duplikasi tag `<script type="application/ld+json">`. Resource Filament & controller publik yang disentuh SEMUA sudah ada — tidak ada resource/controller baru, konsisten dengan sifat fitur ini sebagai "lapisan tambahan" di atas modul konten yang sudah selesai.

## Complexity Tracking

*Tidak ada pelanggaran constitution — tabel ini kosong.*
