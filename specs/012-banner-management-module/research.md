# Research: Modul Banner Management

**Date**: 2026-09-10 | **Feature**: [spec.md](./spec.md)

## 1. Entity `Banner` baru — pola `ClientLogo` (009) + dua kolom tanggal

**Decision**: Tabel `banners`: `id`, `title` (string, wajib — internal), `image_path` (string, wajib), `alt_text` (string, wajib), `link_url` (string nullable), `starts_at` (date nullable), `ends_at` (date nullable), `order` (integer default 0), `is_active` (boolean default true), timestamps. Casts: `starts_at`/`ends_at` → `date`, `order` → `integer`, `is_active` → `boolean`.

**Rationale**: `ClientLogo` sudah membuktikan pola image + `link_url` + `order` + `is_active`; banner menambah periode via dua kolom `date` nullable. `title` khusus untuk identifikasi admin (FR-002), tidak dirender publik (Assumptions). `alt_text` terpisah dari `title` karena keduanya beda peran (aksesibilitas vs identifikasi).

**Alternatives considered**: Kolom `datetime` untuk periode — ditolak, spec eksplisit berbasis tanggal (Assumptions). Satu kolom `period` JSON — ditolak, dua kolom `date` lebih mudah divalidasi & di-query. Kolom `slot`/`location` untuk multi-lokasi — ditolak Assumptions (satu lokasi = hero beranda di v1).

## 2. Aturan tayang: `scopeLive()` di model, satu sumber kebenaran

**Decision**: `Banner::scopeLive(Builder $query): Builder` →
`$query->where('is_active', true)->where(fn ($q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', today()))->where(fn ($q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', today()))->orderBy('order')->orderBy('id')`.
`HomeController` memakai `Banner::live()->get()`.

**Rationale**: Memusatkan aturan FR-008 di satu scope yang bisa di-unit-test dengan matriks lengkap (no-period / current / past / future / inactive). `today()` (Carbon, zona waktu aplikasi) memberi batas inklusif per-hari sesuai Assumptions. `whereDate` menormalkan perbandingan ke tanggal.

**Alternatives considered**: Filter di controller/Blade — ditolak, logika tersebar & sulit dites. Scheduled job yang men-toggle `is_active` saat periode lewat — ditolak, menambah kompleksitas (cron) dan race; evaluasi saat request lebih sederhana & selalu akurat.

## 3. Status tayang di tabel admin: accessor `displayStatus()`

**Decision**: `Banner::displayStatus(): string` mengembalikan salah satu: `'inactive'` (bila `! is_active`), `'scheduled'` (aktif, `starts_at` di masa depan), `'expired'` (aktif, `ends_at` di masa lalu), `'live'` (selain itu). Kolom Filament `TextColumn` memakai `formatStateUsing`/`badge()` dengan label ID: "Nonaktif" / "Terjadwal" / "Kedaluwarsa" / "Tayang" dan warna badge (gray/warning/danger/success).

**Rationale**: FR-013 — admin melihat status tanpa buka situs. Accessor terpisah dari `scopeLive()` tapi konsisten dengannya (satu banner "live" ⟺ termasuk hasil `scopeLive()`), diverifikasi di unit test.

**Alternatives considered**: Menghitung status di Blade kolom — ditolak, logika di model lebih rapi & teruji. Kolom DB `status` tersimpan — ditolak, turunan dari tanggal + is_active, tidak perlu disimpan (akan basi).

## 4. Validasi periode: `ends_at` >= `starts_at`

**Decision**: Di form, `DatePicker::make('ends_at')->rule('after_or_equal:starts_at')` (rule Laravel bawaan; hanya aktif bila keduanya diisi). `starts_at` dan `ends_at` keduanya `->nullable()`. Tidak ada batas atas/bawah lain.

**Rationale**: FR-005 (edge case tanggal selesai < mulai ditolak). `after_or_equal` mengizinkan periode 1 hari (`starts_at == ends_at`). Nol kode kustom.

**Alternatives considered**: Closure `->rule()` manual — tidak perlu, `after_or_equal:starts_at` sudah menangani nullable dengan benar (rule di-skip bila field pembanding kosong).

## 5. Gambar: `FileUpload` + `ImageUploads::storeAsWebp(maxWidth: 1600)`

**Decision**: `FileUpload::make('image_path')->image()->required()->disk('public')->directory('banners')->acceptedFileTypes(['image/png','image/jpeg','image/webp'])->helperText('Wajib. Rekomendasi 1600×600px (banner lebar). Gambar besar otomatis dikecilkan ke lebar 1600px & dikonversi WebP.')->saveUploadedFileUsing(fn ($file) => ImageUploads::storeAsWebp($file, 'banners', maxWidth: 1600))`. Di Blade, guard `Storage::disk('public')->exists($banner->image_path)` — banner dengan file hilang dilewati (tidak render `<img>` rusak).

**Rationale**: Banner hero lebar penuh butuh resolusi lebih besar dari foto tim (800) atau galeri portfolio (1200) — 1600px cukup untuk layar besar tanpa berlebihan. Helper `maxWidth` sudah ada (modul 010), nol perubahan.

**Alternatives considered**: 1920px — sedikit lebih tajam di layar 4K tapi file lebih berat; 1600 kompromi wajar. Tanpa resize (seperti `ProductResource`) — ditolak, instruksi user & konsistensi modul 010/011.

## 6. Rendering: pilih carousel vs hero statis di `home.blade.php`, carousel Alpine.js

**Decision**:
- `HomeController::__invoke` menambah `$banners = Banner::live()->get()` ke data view (di samping `$products` yang sudah ada).
- `pages/home.blade.php`: ganti baris `<x-sections.hero />` menjadi `@if ($banners->isNotEmpty()) <x-sections.banner-carousel :banners="$banners" /> @else <x-sections.hero /> @endif`.
- `components/sections/banner-carousel.blade.php` (`@props(['banners']`): Alpine `x-data="{ active: 0, count: N, next() {...}, prev() {...} }"` dengan `x-init` `setInterval` 5000ms auto-advance (di-`clearInterval` tidak wajib v1; cukup reset saat klik). Tiap slide: `<img src alt>` (di-fit `object-cover`, tinggi tetap mis. `h-[60vh]`), dibungkus `<a href>` bila `link_url` ada. Bila `banners->count() === 1` → render `<img>` tunggal tanpa markup carousel/kontrol. Bila >1 → tampilkan dot indicators + tombol prev/next.

**Rationale**: Alpine.js sudah terpasang & dipakai (`<header>` pakai `x-data`) — nol dependency (Principle V). Keputusan carousel/hero terpusat di `home.blade.php` menjaga `hero.blade.php` tak tersentuh (FR-012). Feature test cukup memverifikasi markup (gambar, alt, href, jumlah slide, fallback) tanpa mengeksekusi JS.

**Alternatives considered**: Swiper/Glide.js — dependency baru, ditolak. Server-side rotate (pilih 1 banner acak per request) — ditolak, bukan carousel; spec minta auto-rotate. Menaruh carousel di dalam `hero.blade.php` dengan kondisi — ditolak, mencampur dua tanggung jawab; `home.blade.php` sebagai pemilih lebih bersih.

## 7. Akses CRUD & perilaku tautan

**Decision**: `BannerResource` tanpa policy/`canAccess()` (FR-014, konsisten resource lain). Tautan banner (`link_url`) membuka di **tab yang sama** (`<a href>` tanpa `target`) — perilaku banner promosi umum (Assumptions); tidak pakai `target="_blank"` seperti logo klien/portfolio karena banner promosi biasanya mengarahkan ke halaman internal/landing.

**Rationale**: Spec Assumptions eksplisit ("membuka di tab yang sama secara default"). Konsistensi akses dengan seluruh modul konten.

**Alternatives considered**: `target="_blank"` — ditolak oleh Assumptions; bisa jadi peningkatan bila klien minta.
