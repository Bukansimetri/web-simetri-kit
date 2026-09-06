# Research: Modul Testimonials

**Date**: 2026-09-07 | **Feature**: [spec.md](./spec.md)

## 1. Entity `Testimonial` baru — pola `JobOpening` (006-career), tanpa relasi

**Decision**: Tabel `testimonials` baru: `id`, `name` (string, wajib), `attribution` (string nullable — satu field teks bebas perusahaan/jabatan, hasil klarifikasi Q1), `content` (text, wajib), `rating` (unsignedTinyInteger, wajib, 1–5), `photo_path` (string nullable), `order` (integer default 0), `is_active` (boolean default true), timestamps. Tidak ada kolom relasi, tidak ada status draft/publish, tidak ada tanggal/sumber.

**Rationale**: Struktur paling sederhana yang memenuhi FR-002. `is_active` + `order` meniru pola yang sudah terbukti di `JobOpening` (`is_active`) dan `Product` (`order` integer + `defaultSort`). Rating disimpan sebagai integer (bukan decimal) karena spec menetapkan bilangan bulat 1–5 tanpa setengah bintang (Assumptions).

**Alternatives considered**: Field `company` + `role` terpisah — ditolak oleh klarifikasi Q1 (satu field teks bebas lebih sederhana, admin bebas format). Kolom `published_at` gaya Artikel — ditolak, tidak ada kebutuhan jadwal untuk testimoni.

## 2. Rating: input `Select` 1–5 + validasi `Rule::in`, render bintang di Blade

**Decision**: Form Filament pakai `Select::make('rating')->options([1=>'1',2=>'2',3=>'3',4=>'4',5=>'5'])->required()` plus `->rule('integer')` / `Rule::in([1,2,3,4,5])`. Kolom DB `unsignedTinyInteger`. Di Blade, render `str_repeat` ikon bintang terisi sebanyak `rating` dan bintang kosong sebanyak `5 - rating` (memakai `material-symbols-outlined` `star` / `star_border` yang sudah dipakai di project).

**Rationale**: `Select` menutup kemungkinan input di luar 1–5 di UI, dan `Rule::in` menegakkannya di sisi server untuk FR-004 (feature test mengirim rating 0/6 langsung ke form state). Nol dependency; ikon bintang sudah tersedia via Material Symbols yang di-load layout publik.

**Alternatives considered**: Package star-rating Filament — ditolak (dependency baru, Principle V). `TextInput::numeric()` dengan `minValue(1)->maxValue(5)` — dipertimbangkan; `Select` dipilih karena UX lebih jelas untuk skala diskrit kecil dan tidak perlu menangani input non-integer.

## 3. Foto opsional: `FileUpload` + `ImageUploads::storeAsWebp` (identik `ArticleResource`)

**Decision**: `FileUpload::make('photo_path')->image()->disk('public')->directory('testimonials')->saveUploadedFileUsing(fn ($file) => ImageUploads::storeAsWebp($file, 'testimonials'))`, tanpa `->required()`. Di Blade, jika `photo_path` null → render placeholder inisial (huruf pertama `name`) dalam lingkaran; jika ada → `<img src="{{ Storage::disk('public')->url($testimonial->photo_path) }}">`. Tambahkan guard `Storage::disk('public')->exists(...)` opsional untuk edge case file terhapus manual → fallback ke inisial.

**Rationale**: Reuse helper & pola yang sudah teruji di 005-artikel (`ImageUploads` sudah ada, sudah punya test konversi WebP). Foto opsional dengan fallback inisial memenuhi FR-005 & SC-006 tanpa gambar rusak.

**Alternatives considered**: Spatie Media Library untuk foto testimoni — ditolak, overkill untuk satu gambar opsional; `ImageUploads` sudah cukup dan konsisten dengan Artikel.

## 4. Urutan tampil: kolom `order` integer + `defaultSort`, tie-break `id`

**Decision**: Kolom `order` (integer, default 0). Form: `TextInput::make('order')->numeric()->default(0)`. Tabel admin: `->defaultSort('order')` + `TextColumn::make('order')->sortable()`. Query publik: `->orderBy('order')->orderBy('id')` sehingga urutan deterministik saat `order` sama (FR-006, edge case).

**Rationale**: Pola identik `Product` (`order` integer). Tie-break `id` memberi urutan sekunder stabil tanpa kolom tambahan.

**Alternatives considered**: `filament/spatie-eloquent-sortable` drag-reorder — ditolak (dependency baru; nilai `order` manual sudah memadai untuk volume puluhan). Urут berdasarkan `created_at` saja — ditolak, admin butuh kontrol eksplisit (FR-006).

## 5. Render di halaman Tentang Kami: section component + data via `AboutController`

**Decision**: `AboutController::__invoke` diubah dari `return view('pages.tentang-kami')` menjadi mengirim `['testimonials' => Testimonial::query()->where('is_active', true)->orderBy('order')->orderBy('id')->get()]`. Buat `resources/views/components/sections/testimonials.blade.php` (Blade anonymous component, pola sama `hero`/`why-choose`) menerima prop `:testimonials`. Di `tentang-kami.blade.php`, sisipkan `<x-sections.testimonials :testimonials="$testimonials" />` tepat setelah blok `{{-- Nilai --}}` dan sebelum `<x-sections.cta-band />`. Component membungkus seluruh output dalam `@if($testimonials->isNotEmpty())` sehingga saat kosong TIDAK ada markup yang dirender (FR-011).

**Rationale**: `components/sections/` adalah folder konvensi yang sudah dipakai untuk section beranda; anonymous component tanpa kelas PHP = paling ringan. Empty-state di dalam component menjaga `tentang-kami.blade.php` tetap bersih dan section lain tidak terpengaruh (FR-011, SC-005).

**Alternatives considered**: `@include` partial — component lebih rapi untuk passing prop & konsisten dengan section lain. View Composer global untuk inject testimonials — ditolak, hanya satu halaman yang butuh, controller-level lebih eksplisit. Menaruh query di Blade `@php` — ditolak (logika query di controller, konsisten `HomeController`).

## 6. Akses CRUD: tanpa policy (semua role panel), konsisten resource lain

**Decision**: `TestimonialResource` tidak mendefinisikan policy/`canAccess()` khusus — mengikuti `JobOpeningResource`, `ArticleResource`, `CustomPageResource` yang terbuka untuk semua user dengan akses panel (FR-012).

**Rationale**: Konsistensi dengan seluruh resource konten yang sudah ada; spec eksplisit meminta ini (FR-012). Pembatasan role adalah concern terpisah (AMC-203 roles & permission) yang tidak di-scope tiket ini.

**Alternatives considered**: `TestimonialPolicy` granular — ditolak, tidak diminta dan tidak konsisten dengan modul lain.
