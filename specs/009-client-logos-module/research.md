# Research: Modul Client Logos

**Date**: 2026-09-07 | **Feature**: [spec.md](./spec.md)

## 1. Entity `ClientLogo` baru — pola `Testimonial` (008), tanpa relasi

**Decision**: Tabel `client_logos` baru: `id`, `company_name` (string, wajib), `logo_path` (string, wajib), `link_url` (string nullable), `order` (integer default 0), `is_active` (boolean default true), timestamps. Tidak ada relasi, kategori, atau status draft/publish.

**Rationale**: Struktur minimal yang memenuhi FR-002. `is_active` + `order` meniru `Testimonial` yang sudah terbukti. Nama field `company_name` eksplisit (bukan `name`) supaya jelas maknanya sebagai perusahaan.

**Alternatives considered**: Kolom `type` (partner/klien) — ditolak oleh Assumptions (satu daftar flat di v1). Spatie Media Library untuk logo — ditolak, `ImageUploads` sudah cukup untuk satu gambar.

## 2. Upload logo: `FileUpload` + `ImageUploads::storeAsWebp` (identik `TestimonialResource`)

**Decision**: `FileUpload::make('logo_path')->image()->disk('public')->directory('client-logos')->required()->saveUploadedFileUsing(fn ($file) => ImageUploads::storeAsWebp($file, 'client-logos'))`. Di Blade, guard `Storage::disk('public')->exists($logo->logo_path)` — bila file hilang, slot disembunyikan (bukan `<img>` rusak), memenuhi edge case.

**Rationale**: Reuse helper & pola yang sudah teruji di 005/008. Konversi WebP konsisten dengan modul gambar lain.

**Alternatives considered**: Menyimpan SVG apa adanya tanpa konversi — `ImageUploads::storeAsWebp` via GD tidak mendukung SVG; namun Assumptions menyatakan PNG/SVG "direkomendasikan tapi tidak dipaksakan" dan konversi WebP adalah perilaku standar project. Bila klien mengunggah SVG, GD akan gagal — mitigasi: `->acceptedFileTypes(['image/png','image/jpeg','image/webp'])` pada `FileUpload` untuk mengarahkan ke raster; SVG di luar scope v1 (dicatat sebagai batasan diketahui).

## 3. Validasi `link_url`: opsional, URL absolut http/https

**Decision**: `TextInput::make('link_url')->url()->nullable()->maxLength(255)`. Filament `->url()` menerapkan rule `url` Laravel. Tambahan `->rule('starts_with:http://,https://')` atau regex ringan untuk menegakkan skema absolut (FR-004, edge case "contoh.com" tanpa skema ditolak).

**Rationale**: Rule `url` bawaan Laravel sudah menolak string non-URL. Guard skema mencegah tautan relatif yang salah arah saat dirender di `href`.

**Alternatives considered**: Normalisasi otomatis (prepend `https://` bila skema hilang) — ditolak, menebak intent admin; menolak dengan pesan jelas lebih aman dan sesuai FR-004.

## 4. Urutan tampil: kolom `order` integer + `defaultSort`, tie-break `id`

**Decision**: Kolom `order` (integer, default 0). Form `TextInput::make('order')->numeric()->default(0)`. Tabel admin `->defaultSort('order')`. Query publik `->orderBy('order')->orderBy('id')` — deterministik saat `order` sama (FR-005, edge case).

**Rationale**: Identik pola `Testimonial`/`Product`. Tie-break `id` = urutan sekunder stabil tanpa kolom tambahan.

**Alternatives considered**: Drag-reorder plugin — ditolak (dependency baru, Principle V).

## 5. Render di halaman Tentang Kami: section component setelah testimoni, data via `AboutController`

**Decision**: `AboutController::__invoke` (sejak modul 008 sudah mengirim `$testimonials`) ditambah `$clientLogos = ClientLogo::query()->where('is_active', true)->orderBy('order')->orderBy('id')->get();` dan dikirim ke view sebagai `clientLogos`. Buat `resources/views/components/sections/client-logos.blade.php` (anonymous component, `@props(['logos'])`), membungkus output dalam `@if($logos->isNotEmpty())`. Di `tentang-kami.blade.php`, sisipkan `<x-sections.client-logos :logos="$clientLogos" />` tepat setelah `<x-sections.testimonials :testimonials="$testimonials" />` dan sebelum `<x-sections.cta-band />`.

**Rationale**: Konsisten penuh dengan modul 008 (folder, pola component, controller-level query, empty-state di dalam component). Menempatkan tepat setelah testimoni sesuai klarifikasi Q1.

**Alternatives considered**: View Composer / share global — ditolak, hanya satu halaman butuh. Query di Blade `@php` — ditolak, logika query di controller (konsisten `HomeController`/`AboutController`).

## 6. Rendering link vs non-link + keamanan tab baru

**Decision**: Di component, per logo: bila `link_url` ada → bungkus `<img>` dalam `<a href="{{ $logo->link_url }}" target="_blank" rel="noopener noreferrer nofollow">`; bila tidak → render `<img>` saja. `alt` = `company_name` untuk semua (FR-009). Tinggi logo diseragamkan via util class (mis. `h-10 md:h-12 object-contain`), grayscale opsional untuk konsistensi visual strip.

**Rationale**: `rel="noopener noreferrer"` = praktik keamanan standar untuk `target="_blank"`; `nofollow` wajar untuk tautan keluar ke pihak ketiga. `object-contain` + tinggi tetap memenuhi edge case rasio logo tidak seragam.

**Alternatives considered**: Membuka di tab yang sama — ditolak oleh FR-009 (buka tab baru). Lightbox/modal — ditolak oleh Assumptions.

## 7. Akses CRUD: tanpa policy (semua role panel)

**Decision**: `ClientLogoResource` tidak mendefinisikan policy/`canAccess()` — mengikuti `TestimonialResource`, `JobOpeningResource`, dll (FR-011).

**Rationale**: Konsistensi dengan seluruh resource konten; spec eksplisit meminta ini. Pembatasan role adalah concern AMC-203 yang terpisah.

**Alternatives considered**: Policy granular — ditolak, tidak diminta & tidak konsisten.
