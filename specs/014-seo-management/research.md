# Phase 0 Research: SEO Management

## 1. Mekanisme override meta per halaman (Blade)

**Decision**: Reuse pola `@section`/`@yield` yang sudah ada di `layouts/public.blade.php` dan `layouts/partials/og-meta.blade.php` (bukan bikin sistem baru). `@yield('og_title', ...)`, `@yield('og_image', ...)` sudah didefinisikan tapi belum pernah di-`@section` oleh halaman manapun — tinggal diisi dari controller/model di tiap halaman `produk/show`, `artikel/show`, `halaman/show`, `portfolio/show`.

**Rationale**: Nol dependency baru, konsisten dengan pola `@section('title', ...)`/`@section('meta_description', ...)` yang sudah dipakai di semua halaman publik saat ini. Menambah package SEO (mis. `artesaos/seotools`) untuk ini adalah overkill — kebutuhannya cuma "isi beberapa meta tag dari field model", bukan sitemap/analytics generator (Principle V).

**Alternatives considered**: Paket `artesaos/seotools` — ditolak, menambah dependency untuk masalah yang sudah terselesaikan pola `@section`/`@yield` bawaan Blade.

## 2. Canonical URL

**Decision**: `<link rel="canonical" href="{{ url()->current() }}">` langsung di `layouts/public.blade.php`, tanpa perlu di-override per halaman.

**Rationale**: `url()->current()` Laravel selalu mengembalikan URL bersih (tanpa query string) dari route yang sedang diakses — otomatis benar untuk semua halaman (statis maupun `{slug}` dinamis) tanpa perlu field tambahan di model manapun. Memenuhi FR-002 dengan nol konfigurasi.

## 3. Fallback SEO per konten (Product/Article/CustomPage/PortfolioProject)

**Decision**: Trait `App\Concerns\HasSeoMetadata` dipakai oleh keempat model, menyediakan `seoTitle()`, `seoDescription()`, `seoImageUrl()` publik yang membaca 3 kolom baru (`meta_title`, `meta_description`, `meta_image_path`) lalu jatuh ke fallback spesifik-model lewat 3 method abstrak yang wajib diimplementasikan tiap model (`seoTitleFallback()`, `seoDescriptionFallback()`, `seoImageFallbackUrl()`).

**Rationale**: Field sumber fallback berbeda-beda per model (Product: `short_description` + `coverImageUrl()`; Article: `excerpt` + `image_path`; CustomPage: `content` di-strip_tags (tidak ada excerpt/image sama sekali); PortfolioProject: `description` + `coverImageUrl()`). Trait dengan method abstrak menjaga logika fallback (limit 160 karakter, strip HTML tags, null-safe image) di SATU tempat (`HasSeoMetadata::seoDescription()`/`seoImageUrl()`) sementara tiap model cuma mendeklarasikan sumber datanya sendiri — menghindari duplikasi `Str::limit(strip_tags(...))` 4×.

**Alternatives considered**:
- Accessor Eloquent (`getSeoTitleAttribute()`) per model tanpa trait — ditolak, akan menduplikasi logika `Str::limit`/strip-tags/fallback-chain di 4 model.
- Polymorphic `SeoMetadata` model/tabel terpisah — ditolak, over-engineering untuk 3 kolom nullable; migrasi kolom langsung ke tabel existing lebih sederhana dan sudah pola yang dipakai di seluruh modul lain (Principle V, tidak ada speculative abstraction).

## 4. Upload gambar SEO (`meta_image_path`)

**Decision**: Reuse `App\Support\ImageUploads::storeAsWebp(..., maxWidth: 1200)` (helper yang sudah ada, dipakai modul Banner/Client Logo/dll.) via Filament `FileUpload` dengan `afterStateUpdated`/custom upload handler yang sama polanya dengan resource lain.

**Rationale**: Konsisten dengan seluruh upload gambar di codebase (WebP, tanpa dependency baru). `maxWidth: 1200` dipilih karena rekomendasi umum ukuran OG image adalah 1200×630 — cukup untuk kebutuhan share preview, tidak perlu setinggi produk (1600 dipakai Banner untuk hero full-width).

## 5. Default SEO tingkat situs (site-wide fallback)

**Decision**: `layouts/public.blade.php` saat ini hardcode string default `'Solusi panel surya untuk rumah, bisnis, dan industri.'` langsung di Blade. Tambahkan kolom baru `meta_description` ke `BrandSettings` (pola sama seperti kolom `og_image_path`/`app_name` yang sudah ada) sebagai default site-wide yang bisa diubah admin — hardcoded string di Blade jadi fallback TERAKHIR (dipakai hanya kalau admin belum pernah mengisi setting sama sekali sejak instalasi).

**Rationale**: String hardcoded per-client di view melanggar Principle I (Multi-Client Reusability) — deskripsi perusahaan jelas berbeda per klien starter kit ini. FR-003 spec eksplisit minta "nilai default ini bersumber dari pengaturan brand" — `app_name` & `og_image_path` sudah settings, tapi meta description belum; ini celah yang harus ditutup di fitur ini juga (bukan scope creep, tapi prasyarat supaya FR-003 benar-benar terpenuhi untuk SEMUA field, bukan cuma title/image).

## 6. Format & lokasi JSON-LD

**Decision**: Helper `App\Support\Seo\JsonLd` — kumpulan static method (`organization()`, `faqPage($items)`, `article($article)`, `product($product)`) yang mengembalikan array PHP siap `json_encode`. Tiap halaman render lewat komponen Blade tipis `<x-seo.json-ld :schema="..." />` yang membungkus `<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>`. `Organization` di-include sekali di `layouts/public.blade.php` (global); `FAQPage`/`Article`/`Product` masing-masing di-push lewat `@push('head')` dari halaman terkait (`faq.blade.php`, `artikel/show.blade.php`, `produk/show.blade.php`) — `@stack('head')` sudah ada di layout.

**Rationale**: Static helper class menjaga struktur schema.org (field wajib per `@type`) di satu tempat yang mudah di-unit-test tanpa perlu render Blade, mengikuti pola `App\Support\ImageUploads` yang sudah ada. Komponen Blade tipis menghindari duplikasi tag `<script type="application/ld+json">` di 4 tempat berbeda.

**Alternatives considered**: Paket `spatie/schema-org` — ditolak, fluent builder untuk ~4 schema type sederhana adalah overkill dibanding array PHP biasa (Principle V).

## 7. Filament UI — "tab SEO" per resource

**Decision**: Implementasi sebagai `Section::make('SEO')->collapsed()` tambahan di form masing-masing resource (Product/Article/CustomPage/PortfolioProject) — BUKAN komponen `Tabs::make()` Filament.

**Rationale**: Seluruh resource form di codebase ini konsisten memakai `Section::make(...)` bertumpuk (lihat `ProductResource`, `CustomPageResource`, dst.) — tidak ada satupun yang memakai `Tabs`. Memperkenalkan `Tabs` hanya untuk fitur ini akan jadi satu-satunya resource dengan paradigma navigasi form berbeda, menambah inkonsistensi UI admin. `Section` collapsed memberi pemisahan visual yang sama (field SEO "tersembunyi" sampai admin membukanya) tanpa pola baru. Istilah "tab" di spec.md dipakai secara bahasa awam ("bagian terpisah"), bukan literal komponen Filament `Tabs`.

## 8. Karakter counter (FR-007)

**Decision**: `TextInput`/`Textarea` Filament dengan `->live()` + `->hint(fn (Get $get) => strlen($get('meta_title') ?? '').'/60 karakter')` (pola hint reaktif bawaan Filament, tanpa JS tambahan).

**Rationale**: Filament v3 sudah mendukung `->hint()` reaktif berbasis `Get` closure — cukup untuk indikator panduan (bukan validasi keras sesuai FR-007/Assumptions). Tidak perlu Alpine/JS kustom.

## Ringkasan keputusan

| # | Area | Keputusan | Dependency baru? |
|---|---|---|---|
| 1 | Override meta per halaman | `@section`/`@yield` existing | Tidak |
| 2 | Canonical URL | `url()->current()` di layout | Tidak |
| 3 | Fallback SEO per konten | Trait `HasSeoMetadata` + 3 method abstrak per model | Tidak |
| 4 | Upload gambar SEO | `ImageUploads::storeAsWebp(maxWidth: 1200)` (reuse) | Tidak |
| 5 | Default SEO situs | Kolom baru `BrandSettings::meta_description` | Tidak |
| 6 | JSON-LD | Helper `App\Support\Seo\JsonLd` + komponen `<x-seo.json-ld>` | Tidak |
| 7 | UI admin "SEO" | `Section::make('SEO')->collapsed()`, bukan `Tabs` | Tidak |
| 8 | Character hint | `->hint()` reaktif Filament bawaan | Tidak |

Tidak ada [NEEDS CLARIFICATION] tersisa dari Technical Context — semua keputusan di atas konsisten dengan pola yang sudah ada di codebase dan Principle V (nol dependency baru).
