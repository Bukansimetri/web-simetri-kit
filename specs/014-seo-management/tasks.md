---
description: "Task list for SEO Management"
---

# Tasks: SEO Management

**Input**: Design documents from `/specs/014-seo-management/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/seo-surface.md, quickstart.md

**Tests**: Included — feature + unit tests required by plan.md (Testing section) and Constitution Principle IV (Module Test Coverage).

**Organization**: Tasks grouped by user story. US1 (P1) → US2 (P2) → US3 (P3), per spec.md priority order.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: US1, US2, or US3
- File paths are repository-relative

## Path Conventions

Laravel web app, single project. Source at repository root: `app/`, `database/`, `resources/`, `tests/`.

**Dependency note**: Nol dependency baru (research.md §1–8). US3 (JSON-LD `Article`/`Product`) memanggil `seoTitle()`/`seoDescription()`/`seoImageUrl()` dari trait `HasSeoMetadata` yang ditambahkan di US2 — dibangun sesuai urutan prioritas (US1→US2→US3) sehingga saat fase US3 dimulai, trait sudah ada di `Product`/`Article` (lihat research.md §3, Principle V — reuse fallback chain, bukan duplikasi).

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: No project init needed — existing Laravel app. Baseline check sebelum menyentuh apa pun.

- [x] T001 Confirm baseline hijau: `php artisan test --compact --filter='ProductResourceTest|ArticleResourceTest|CustomPageResourceTest|PortfolioProjectResourceTest|FaqPageTest|HomePageTest'` (resource + halaman yang akan disentuh fitur ini)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Default SEO site-wide (`BrandSettings::meta_description`) dan helper JSON-LD dasar (`Organization`) — dipakai US1 langsung dan jadi fondasi struktur yang diperluas US3.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T002 [P] Create settings migration via `php artisan make:migration add_meta_description_to_brand_settings --no-interaction`, isi memakai `Spatie\LaravelSettings\Migrations\SettingsMigration` (pola `add_whatsapp_and_notification_email_to_brand_settings.php`): `$this->migrator->add('brand.meta_description', null);` di `up()`, `$this->migrator->delete('brand.meta_description');` di `down()`
- [x] T003 [P] Tambah `public ?string $meta_description;` ke `app/Settings/BrandSettings.php` (property baru, sejajar `og_image_path`)
- [x] T004 [P] Create `app/Support/Seo/JsonLd.php` — class dengan method statis `organization(\App\Settings\BrandSettings $brand): array` mengembalikan `['@context' => 'https://schema.org', '@type' => 'Organization', 'name' => $brand->app_name ?: config('app.name'), 'url' => url('/')]`, tambahkan key `'logo' => Storage::disk('public')->url($brand->logo_path)` hanya bila `$brand->logo_path` terisi (data-model.md § helper JsonLd)
- [x] T005 [P] Create `resources/views/components/seo/json-ld.blade.php` — komponen anonim `@props(['schema'])`: `@if ($schema)<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>@endif` (dipakai global Organization di T007 dan US3 di Phase 5)
- [x] T006 Run `php artisan migrate` dan verifikasi `brand` settings group via `database-query` (atau `php artisan tinker --execute 'echo app(\App\Settings\BrandSettings::class)->meta_description ?? "null";'`)

**Checkpoint**: `BrandSettings::meta_description` + helper `JsonLd::organization()` + komponen `<x-seo.json-ld>` siap — user story bisa mulai

---

## Phase 3: User Story 1 - Setiap halaman publik tampil rapi saat dibagikan (Priority: P1) 🎯 MVP

**Goal**: Semua halaman publik (termasuk yang belum pernah disentuh admin) punya OG tags, Twitter Card, canonical URL, dan JSON-LD `Organization` yang lengkap — fallback dari `BrandSettings` yang sudah ada.

**Independent Test**: Bagikan URL halaman apa pun (mis. `/`, `/kontak`, `/karir`, `/faq`) ke Facebook Sharing Debugger — pratinjau tampil lengkap; view-source menunjukkan `<link rel="canonical">` yang benar dan `<script type="application/ld+json">` bertipe `Organization`.

### Tests for User Story 1 ⚠️ (write first, ensure they FAIL)

- [x] T007 [P] [US1] Create `tests/Feature/Public/SeoGlobalMetaTest.php` via `php artisan make:test --phpunit Public/SeoGlobalMetaTest` covering (FR-001/002/003/008/013): untuk rute `/`, `/kontak`, `/karir`, `/faq`, `/tentang-kami` — response 200 MUST assertSee `og:title`, `og:description`, `og:image`, `og:type`, `og:site_name`, `twitter:card" content="summary_large_image`, `twitter:title`, `twitter:description`, `twitter:image`, `rel="canonical" href="` + URL rute tsb, dan `"@type":"Organization"` (escape: false); test terpisah: set `BrandSettings::meta_description` = string custom → assertSee string itu di `/`; kosongkan lagi → assertSee string default hardcoded (fallback masih jalan, FR-013)

### Implementation for User Story 1

- [x] T008 [US1] Update `resources/views/layouts/partials/og-meta.blade.php`: tambahkan `<meta name="twitter:card" content="summary_large_image">`, `<meta name="twitter:title" content="@yield('og_title', $appName)">`, `<meta name="twitter:description" content="@yield('meta_description', ...)">` (value sama dengan og:description existing), `<meta name="twitter:image" content="@yield('og_image', $brand->ogImageUrl())">`; ubah `@yield('og_title', $appName)` juga dipakai untuk `og:title` (saat ini masih literal `$appName`, samakan supaya override `og_title` per halaman ikut kepakai — lihat FR-004 persiapan US2)
- [x] T009 [US1] Update `resources/views/layouts/public.blade.php`: (a) tambah `<link rel="canonical" href="{{ url()->current() }}">` di `<head>`; (b) ubah `@yield('meta_description', 'Solusi panel surya untuk rumah, bisnis, dan industri.')` (baris `<meta name="description">`) jadi `@yield('meta_description', $brand->meta_description ?: 'Solusi panel surya untuk rumah, bisnis, dan industri.')`; (c) tambah `@include('layouts.partials.schema-organization')` sebelum `@stack('head')`
- [x] T010 [US1] Create `resources/views/layouts/partials/schema-organization.blade.php`: `<x-seo.json-ld :schema="\App\Support\Seo\JsonLd::organization($brand)" />` ($brand sudah tersedia dari `layouts/public.blade.php`)
- [x] T011 [US1] Update `app/Filament/Pages/BrandSettingsPage.php`: tambah `Section::make('SEO')->description('Default deskripsi pencarian & share untuk halaman yang belum punya pengaturan SEO sendiri.')->schema([Textarea::make('meta_description')->label('Deskripsi Default Situs')->maxLength(500)->rows(3)->helperText('Dipakai bila suatu halaman/konten belum punya deskripsi SEO sendiri. Disarankan ≤160 karakter.')])` setelah Section "Theme Settings"; update `mount()` (`'meta_description' => $settings->meta_description`) dan `save()` (`$settings->meta_description = $data['meta_description'] ?? null;`); tambah `use Filament\Forms\Components\Textarea;`
- [x] T012 [US1] Run `vendor/bin/pint --dirty --format agent`, then `php artisan test --compact --filter='SeoGlobalMetaTest'` and fix until T007 passes; also re-run T001's filter to confirm no regression

**Checkpoint**: Semua halaman publik punya meta tag & Organization JSON-LD lengkap, dengan/tanpa pengaturan admin — US1 selesai dan independen

---

## Phase 4: User Story 2 - Admin mengoptimalkan tampilan pencarian per konten (Priority: P2)

**Goal**: Admin bisa mengisi judul/deskripsi/gambar SEO kustom per Produk, Artikel, Halaman Statis, dan Proyek Portfolio lewat Section "SEO" di form masing-masing; kosong → fallback otomatis dari data konten yang sudah ada.

**Independent Test**: Isi tab SEO satu produk di admin → halaman produk publik memakai nilai kustom itu; produk lain yang field SEO-nya kosong tetap tampil lengkap lewat fallback (nama/short_description/gambar galeri pertama).

### Tests for User Story 2 ⚠️ (write first, ensure they FAIL)

- [ ] T013 [P] [US2] Create `tests/Unit/HasSeoMetadataTest.php` via `php artisan make:test --phpunit --unit HasSeoMetadataTest` covering matriks per model (Product/Article/CustomPage/PortfolioProject) × {field SEO terisi semua, field SEO kosong semua, hanya title terisi, hanya description terisi}: `seoTitle()`/`seoDescription()`/`seoImageUrl()` mengembalikan nilai kustom saat terisi, fallback yang benar saat kosong (per data-model.md tabel fallback), `seoDescription()` selalu ≤160 karakter dan bebas tag HTML meski sumber fallback (mis. `CustomPage::content`) mengandung HTML; `Product`/`PortfolioProject` tanpa gambar sama sekali → `seoImageUrl()` null
- [ ] T014 [P] [US2] Create `tests/Feature/Public/SeoContentOverrideTest.php` via `php artisan make:test --phpunit Public/SeoContentOverrideTest` covering FR-004/006: untuk masing-masing dari 4 halaman detail (`/produk/{slug}`, `/artikel/{slug}`, `/halaman/{slug}`, `/portfolio/{slug}`) — record dengan field SEO terisi → assertSee nilai kustom di `og:title`/`og:description`/`og:image`; record LAIN (field SEO kosong) → assertSee nilai fallback (nama/judul asli, potongan deskripsi asli, gambar konten asli) — tidak ada yang blank

### Implementation for User Story 2

- [ ] T015 [P] [US2] Create migration `add_seo_fields_to_products_table` via `php artisan make:migration add_seo_fields_to_products_table --no-interaction`: `meta_title` string nullable, `meta_description` text nullable, `meta_image_path` string nullable (setelah kolom terakhir existing)
- [ ] T016 [P] [US2] Create migration `add_seo_fields_to_articles_table` via `php artisan make:migration add_seo_fields_to_articles_table --no-interaction`: kolom sama seperti T015
- [ ] T017 [P] [US2] Create migration `add_seo_fields_to_custom_pages_table` via `php artisan make:migration add_seo_fields_to_custom_pages_table --no-interaction`: kolom sama seperti T015
- [ ] T018 [P] [US2] Create migration `add_seo_fields_to_portfolio_projects_table` via `php artisan make:migration add_seo_fields_to_portfolio_projects_table --no-interaction`: kolom sama seperti T015
- [ ] T019 [US2] Run `php artisan migrate` dan verifikasi 4 tabel via `database-schema` (depends on T015-T018)
- [ ] T020 [US2] Create `app/Concerns/HasSeoMetadata.php` trait persis sesuai data-model.md § trait `HasSeoMetadata` (`seoTitle()`, `seoDescription()` dengan `Str::limit(strip_tags(...), 160)`, `seoImageUrl()` dengan `Storage::disk('public')->url($this->meta_image_path)`, 3 method abstrak) — `use Illuminate\Support\Str; use Illuminate\Support\Facades\Storage;`
- [ ] T021 [P] [US2] Update `app/Models/Product.php`: `use App\Concerns\HasSeoMetadata;`, tambah `meta_title`/`meta_description`/`meta_image_path` ke `$fillable`, implementasikan `seoTitleFallback()` → `$this->name`, `seoDescriptionFallback()` → `$this->short_description`, `seoImageFallbackUrl()` → `$this->coverImageUrl()` (depends on T020)
- [ ] T022 [P] [US2] Update `app/Models/Article.php`: `use App\Concerns\HasSeoMetadata;`, tambah 3 kolom ke `$fillable`, `seoTitleFallback()` → `$this->title`, `seoDescriptionFallback()` → `$this->excerpt`, `seoImageFallbackUrl()` → `$this->image_path ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->image_path) : null` (depends on T020)
- [ ] T023 [P] [US2] Update `app/Models/CustomPage.php`: `use App\Concerns\HasSeoMetadata;`, tambah 3 kolom ke `$fillable`, `seoTitleFallback()` → `$this->title`, `seoDescriptionFallback()` → `$this->content`, `seoImageFallbackUrl()` → `null` (depends on T020)
- [ ] T024 [P] [US2] Update `app/Models/PortfolioProject.php`: `use App\Concerns\HasSeoMetadata;`, tambah 3 kolom ke `$fillable`, `seoTitleFallback()` → `$this->title`, `seoDescriptionFallback()` → `$this->description`, `seoImageFallbackUrl()` → `$this->coverImageUrl()` (depends on T020)
- [ ] T025 [P] [US2] Update `app/Filament/Resources/ProductResource.php`: tambah `Section::make('SEO')->collapsed()->description('Kosongkan untuk pakai default otomatis dari data produk.')->schema([TextInput::make('meta_title')->label('Judul Pencarian')->maxLength(255)->live()->hint(fn (Get $get) => strlen($get('meta_title') ?? '').'/60 karakter disarankan'), Textarea::make('meta_description')->label('Deskripsi Pencarian')->maxLength(500)->rows(3)->live()->hint(fn (Get $get) => strlen($get('meta_description') ?? '').'/160 karakter disarankan'), FileUpload::make('meta_image_path')->label('Gambar SEO / Share Sosial')->image()->disk('public')->directory('seo')->saveUploadedFileUsing(fn ($file) => \App\Support\ImageUploads::storeAsWebp($file, 'seo', maxWidth: 1200))->helperText('Opsional. Rekomendasi 1200×630px. Kosongkan untuk pakai gambar galeri produk.')])` setelah Section terakhir existing; tambah `use Filament\Forms\Components\Textarea;`
- [ ] T026 [P] [US2] Update `app/Filament/Resources/ArticleResource.php`: Section "SEO" sama pola T025 (helper text sesuaikan "gambar artikel")
- [ ] T027 [P] [US2] Update `app/Filament/Resources/CustomPageResource.php`: Section "SEO" sama pola T025 (helper text "gambar OG default situs" karena CustomPage tidak punya gambar konten)
- [ ] T028 [P] [US2] Update `app/Filament/Resources/PortfolioProjectResource.php`: Section "SEO" sama pola T025 (helper text "gambar galeri proyek")
- [ ] T029 [US2] Update `resources/views/pages/produk/show.blade.php`: tambah `@section('meta_description', $product->seoDescription())` (ganti `$product->short_description` langsung), `@section('og_title', $product->seoTitle())`, `@section('og_image', $product->seoImageUrl() ?? app(\App\Settings\BrandSettings::class)->ogImageUrl())`
- [ ] T030 [US2] Update `resources/views/pages/artikel/show.blade.php`: sama pola T029 dengan `$article->seo...()`
- [ ] T031 [US2] Update `resources/views/pages/custom-page/show.blade.php`: sama pola T029 dengan `$customPage->seo...()`; HAPUS baris `$plain = trim(preg_replace(...))` yang sudah tidak dipakai (digantikan `seoDescription()`)
- [ ] T032 [US2] Update `resources/views/pages/portfolio/show.blade.php`: sama pola T029 dengan `$project->seo...()`; HAPUS baris `$plain = trim(preg_replace(...))` yang sudah tidak dipakai
- [ ] T033 [US2] Extend `tests/Feature/Admin/ProductResourceTest.php`, `ArticleResourceTest.php`, `CustomPageResourceTest.php`, `PortfolioProjectResourceTest.php`: tambah 1 test case per file — create/edit record dengan `meta_title`/`meta_description`/`meta_image_path` terisi via form Filament, assert tersimpan di database dan gambar tersimpan `.webp`
- [ ] T034 [US2] Run `vendor/bin/pint --dirty --format agent`, then `php artisan test --compact --filter='HasSeoMetadataTest|SeoContentOverrideTest|ProductResourceTest|ArticleResourceTest|CustomPageResourceTest|PortfolioProjectResourceTest'` and fix until all pass

**Checkpoint**: US1 + US2 keduanya jalan independen — admin punya kontrol penuh per konten, fallback tetap lengkap saat kosong

---

## Phase 5: User Story 3 - Search engine menampilkan hasil pencarian yang lebih kaya (Priority: P3)

**Goal**: Halaman FAQ, Artikel, dan Produk menyisipkan JSON-LD `FAQPage`/`Article`/`Product` yang valid, memakai fallback SEO dari US2 untuk teks deskripsi.

**Independent Test**: Tempel URL `/faq`, 1 halaman artikel, 1 halaman produk ke Google Rich Results Test — markup terdeteksi valid tanpa error; FAQ tanpa item tidak menyisipkan schema kosong.

### Tests for User Story 3 ⚠️ (write first, ensure they FAIL)

- [ ] T035 [P] [US3] Create `tests/Unit/Seo/JsonLdTest.php` via `php artisan make:test --phpunit --unit Seo/JsonLdTest` covering: `JsonLd::faqPage($faqItems)` → array `@type FAQPage` dengan `mainEntity` sejumlah item, urut sesuai koleksi; `JsonLd::faqPage(collect())` → `null` (FR-009); `JsonLd::article($article)` → `@type Article` berisi `headline`/`image`/`datePublished`/`author`; `JsonLd::product($product)` dengan `price` terisi → key `offers.price` ada; `JsonLd::product($product)` dengan `price` null → key `offers` TIDAK ada sama sekali (FR-011)
- [ ] T036 [P] [US3] Create `tests/Feature/Public/StructuredDataTest.php` via `php artisan make:test --phpunit Public/StructuredDataTest` covering: `/faq` dengan ≥1 `FaqItem` → assertSee `"@type":"FAQPage"`; `/faq` tanpa item (`FaqItem::query()->delete()`) → assertDontSee `FAQPage`; halaman artikel published → assertSee `"@type":"Article"`; halaman produk dengan `price` → assertSee `"@type":"Product"` dan `"price"`; produk tanpa `price` → assertSee `Product` tapi assertDontSee `"offers"`

### Implementation for User Story 3

- [ ] T037 [US3] Extend `app/Support/Seo/JsonLd.php` (depends on T004, T020-T024 untuk `seoTitle()`/`seoDescription()`/`seoImageUrl()`): tambah `faqPage(\Illuminate\Support\Collection $faqItems): ?array` (return null bila `$faqItems->isEmpty()`; else `['@context'=>'https://schema.org','@type'=>'FAQPage','mainEntity'=>$faqItems->map(fn ($i) => ['@type'=>'Question','name'=>$i->question,'acceptedAnswer'=>['@type'=>'Answer','text'=>$i->answer]])->values()->all()]`); `article(\App\Models\Article $article): array` (`headline`=>`$article->seoTitle()`, `image`=>`$article->seoImageUrl()`, `datePublished`=>`$article->published_at?->toIso8601String()`, `author`=>['@type'=>'Organization','name'=>$article->redaksi ?: $appName]); `product(\App\Models\Product $product): array` (`name`, `description`=>`seoDescription()`, `image`=>`seoImageUrl()`, plus `offers` key HANYA bila `$product->price` terisi: `['@type'=>'Offer','price'=>(string) $product->price,'priceCurrency'=>'IDR']`)
- [ ] T038 [US3] Update `app/Http/Controllers/Public/FaqController.php`: tambah `'schema' => \App\Support\Seo\JsonLd::faqPage($faqItems)` ke data view
- [ ] T039 [US3] Update `resources/views/pages/faq.blade.php`: tambah `@push('head')<x-seo.json-ld :schema="$schema" />@endpush` (komponen sudah menangani null internal, tapi tetap aman dipanggil apa adanya)
- [ ] T040 [US3] Update `app/Http/Controllers/Public/ArticleController.php` method `show()`: tambah `'schema' => \App\Support\Seo\JsonLd::article($article)` ke data view
- [ ] T041 [US3] Update `resources/views/pages/artikel/show.blade.php`: tambah `@push('head')<x-seo.json-ld :schema="$schema" />@endpush`
- [ ] T042 [US3] Update `app/Http/Controllers/Public/ProductController.php` method `show()`: tambah `'schema' => \App\Support\Seo\JsonLd::product($product)` ke data view
- [ ] T043 [US3] Update `resources/views/pages/produk/show.blade.php`: tambah `@push('head')<x-seo.json-ld :schema="$schema" />@endpush`
- [ ] T044 [US3] Run `vendor/bin/pint --dirty --format agent`, then `php artisan test --compact --filter='JsonLdTest|StructuredDataTest'` and fix until all pass

**Checkpoint**: Ketiga user story lengkap dan independen — FAQ/Artikel/Produk punya structured data valid

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Regresi penuh + validasi manual sebelum PR.

- [ ] T045 Run `php artisan test --compact` (seluruh suite) — pastikan tidak ada regresi di modul lain
- [ ] T046 Run `vendor/bin/pint --format agent` (full pass, bukan `--dirty`) untuk memastikan seluruh file yang disentuh konsisten
- [ ] T047 Jalankan `quickstart.md` end-to-end secara manual: fallback global (§1), override per konten (§2), structured data via Google Rich Results Test (§3) — catat hasil di PR description

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Setup — BLOCKS all user stories
- **User Story 1 (Phase 3)**: Depends on Foundational — tidak bergantung ke US2/US3
- **User Story 2 (Phase 4)**: Depends on Foundational — tidak bergantung ke US1 secara kode (boleh dikerjakan paralel), tapi dibangun setelah US1 sesuai urutan prioritas
- **User Story 3 (Phase 5)**: Depends on Foundational (T004 `JsonLd` base) DAN Phase 4 T020-T024 (trait `HasSeoMetadata` di `Product`/`Article` — dipakai `JsonLd::article()`/`JsonLd::product()`) — TIDAK bisa dikerjakan sebelum US2 selesai
- **Polish (Phase 6)**: Depends on semua story yang ingin dirilis selesai

### Within Each User Story

- Tests (T007, T013-T014, T035-T036) MUST ditulis dan dipastikan FAIL dulu sebelum implementasi
- Migration sebelum model, model (trait) sebelum Filament resource & Blade page
- Checkpoint test run di akhir tiap fase sebelum lanjut fase berikutnya

### Parallel Opportunities

- T002-T005 (Foundational) — file berbeda, jalan paralel
- T015-T018 (4 migration US2) — jalan paralel, lalu T019 migrate satu kali setelah semua selesai
- T021-T024 (4 model) — jalan paralel setelah T020 (trait) selesai
- T025-T028 (4 Filament resource) — jalan paralel, tidak saling bergantung
- T007, T013, T014, T035, T036 — semua test task per fase jalan paralel dengan test task lain di fase yang sama

---

## Parallel Example: User Story 2

```bash
# Migration 4 tabel sekaligus:
Task: "Create migration add_seo_fields_to_products_table"
Task: "Create migration add_seo_fields_to_articles_table"
Task: "Create migration add_seo_fields_to_custom_pages_table"
Task: "Create migration add_seo_fields_to_portfolio_projects_table"

# Setelah trait (T020) selesai, 4 model paralel:
Task: "Update Product model — use HasSeoMetadata + fallback methods"
Task: "Update Article model — use HasSeoMetadata + fallback methods"
Task: "Update CustomPage model — use HasSeoMetadata + fallback methods"
Task: "Update PortfolioProject model — use HasSeoMetadata + fallback methods"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational
3. Complete Phase 3: User Story 1 — **MVP**: semua halaman publik langsung dapat OG/Twitter/canonical/Organization lengkap, tanpa admin perlu mengisi apa pun
4. **STOP and VALIDATE**: jalankan quickstart.md §1
5. Deploy/demo bila cukup untuk kebutuhan saat ini

### Incremental Delivery

1. Setup + Foundational → fondasi siap
2. + User Story 1 → validasi independen → deploy (MVP)
3. + User Story 2 → validasi independen → deploy (admin dapat kontrol per konten)
4. + User Story 3 → validasi independen (Rich Results Test) → deploy (rich snippet di search engine)

---

## Notes

- [P] tasks = file berbeda, tidak saling bergantung
- [Story] label memetakan task ke user story untuk traceability
- US3 satu-satunya yang punya dependency kode eksplisit ke fase sebelumnya (US2, lewat trait) — didokumentasikan di atas, bukan dilanggar diam-diam
- Verifikasi test FAIL dulu sebelum implementasi (TDD, sesuai Constitution Principle IV)
- Commit setelah tiap checkpoint fase (T012, T034, T044) — pola commit message `feat(014): <fase> — AMC-223`
