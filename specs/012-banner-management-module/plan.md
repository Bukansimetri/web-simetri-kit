# Implementation Plan: Modul Banner Management

**Branch**: `012-banner-management-module` | **Date**: 2026-09-10 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/012-banner-management-module/spec.md`

## Summary

Menambahkan modul "Banner Management" — entity baru `Banner` (title internal, image_path, alt_text, link_url?, starts_at? date, ends_at? date, order, is_active) dengan CRUD admin (Filament Resource, pola `ClientLogoResource` dari 009) plus kolom status tayang di tabel admin. Di sisi publik, `HomeController` mengirim koleksi banner yang **tayang** (aktif + dalam periode inklusif) ke `pages/home.blade.php`; bila ada ≥1 banner tayang, `home.blade.php` merender `<x-sections.banner-carousel>` (Alpine.js, auto-rotate, kontrol navigasi bila >1) di posisi Hero; bila kosong → fallback ke `<x-sections.hero />` statis yang sudah ada. Gambar banner di-downscale ke lebar maks 1600px + konversi WebP saat simpan lewat `ImageUploads::storeAsWebp(..., maxWidth: 1600)` (helper yang sudah mendukung `maxWidth` sejak modul 010). Tidak ada dependency baru (Alpine.js sudah terpasang).

## Technical Context

**Language/Version**: PHP 8.3, Laravel Framework 13

**Primary Dependencies**: Filament 3.3 (`BannerResource` baru — `TextInput`, `FileUpload`, `Textarea`/`TextInput` alt, `DatePicker` ×2, `Toggle`). Alpine.js 3.17 (sudah di `package.json`, dipakai `<header>` — untuk carousel). Helper `App\Support\ImageUploads` (sudah mendukung `maxWidth`). Tidak ada dependency baru.

**Storage**: MySQL — migration baru `banners` (`id`, `title` string, `image_path` string, `alt_text` string, `link_url` string nullable, `starts_at` date nullable, `ends_at` date nullable, `order` integer default 0, `is_active` boolean default true, timestamps). Tidak ada perubahan skema tabel lain.

**Testing**: PHPUnit feature test memakai Livewire test helper untuk Filament Resource (pola `ClientLogoResourceTest`) — create/edit/delete, validasi field wajib (title, image, alt_text), `link_url` http/https, `ends_at` < `starts_at` ditolak, gambar tersimpan WebP ≤1600px, kolom status tayang; unit test untuk scope `live()` (matriks: no-period, period-current, period-past, period-future, inactive) + accessor `displayStatus()`; feature test untuk `HomeController`/`home` (carousel dirender saat ada banner tayang, urutan, link vs non-link, fallback ke hero statis saat kosong, banner kedaluwarsa/terjadwal/nonaktif tidak tampil).

**Target Platform**: Server web Laravel standar

**Project Type**: Web application — perluasan admin panel Filament + perubahan pada `HomeController` & `pages/home.blade.php` + satu Blade section component baru (carousel)

**Performance Goals**: Tidak ada target khusus (admin-only untuk CRUD; beranda satu query `where is_active` + rentang tanggal + `orderBy order`, volume < 10 baris; carousel client-side)

**Constraints**: banner tayang = aktif AND (`starts_at` null OR ≤ today) AND (`ends_at` null OR ≥ today), inklusif (FR-008); `ends_at` < `starts_at` ditolak (FR-005); `link_url` http/https bila diisi (FR-004); gambar downscale ≤1600px + WebP, tanpa upscale, form tampilkan rekomendasi ukuran (FR-006); bila tidak ada banner tayang → beranda pakai Hero statis lama, `hero.blade.php` TIDAK dihapus (FR-012, Assumptions); hanya `home.blade.php` yang memilih carousel vs hero; tabel admin tampilkan status tayang (FR-013); CRUD terbuka semua role panel (FR-014); tidak ada dependency baru (Principle V); tidak ada per-slot grouping / analytics klik / toggle modul (Assumptions)

**Scale/Scope**: 1 Filament Resource baru (`BannerResource`) + 1 migration + 1 model (scope `live()` + accessor `displayStatus()`) + 1 factory (+ states) + update `HomeController` + update `pages/home.blade.php` (pilih carousel/hero) + 1 section Blade baru (`banner-carousel.blade.php`, Alpine)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Applies? | Assessment |
|---|---|---|
| I. Multi-Client Reusability | Ya | Banner diisi lewat CRUD per klien; carousel kosong → fallback hero statis. Tidak ada banner hardcoded. **PASS** |
| II. White-Label by Default | Tidak langsung | Tidak menyentuh branding/identitas panel. |
| III. Settings-Driven Theming, No Page Builder | Ya (NON-NEGOTIABLE) | Banner carousel adalah SATU Blade section tetap dengan layout tetap yang menampilkan daftar gambar — BUKAN page builder / drag-drop section. Menggantikan hero adalah keputusan penempatan tetap (hardcoded di `home.blade.php`), bukan slot generik yang bisa dikonfigurasi. **PASS** |
| IV. Module Test Coverage | Ya | US1 (CRUD + validasi periode), US2 (aturan tayang + carousel/fallback) masing-masing punya test. Scope `live()` punya unit test matriks. **PASS (planned)** |
| V. Simplicity & Dependency Discipline | Ya | Nol dependency baru (Alpine sudah ada); reuse `ImageUploads` & pola `ClientLogoResource`; carousel Alpine minimal; tidak ada analytics / per-slot / multi-lokasi yang tidak diminta. **PASS** |

Tidak ada pelanggaran constitution — Complexity Tracking kosong.

## Project Structure

### Documentation (this feature)

```text
specs/012-banner-management-module/
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
│   └── Banner.php                       # Baru — fillable + casts (starts_at/ends_at date, order int, is_active bool); scopeLive(); displayStatus(): string
├── Filament/Resources/
│   ├── BannerResource.php               # Baru (pola ClientLogoResource + DatePicker ×2 + kolom status)
│   └── BannerResource/Pages/{ListBanners,CreateBanner,EditBanner}.php
└── Http/Controllers/Public/
    └── HomeController.php                # Diperbarui — kirim $banners = Banner::live()->get()

database/
├── migrations/
│   └── xxxx_create_banners_table.php    # Baru
└── factories/
    └── BannerFactory.php                # Baru (+ states: inactive, scheduled, expired)

resources/views/
├── pages/home.blade.php                             # Diperbarui — @if($banners->isNotEmpty()) <x-sections.banner-carousel :banners="$banners" /> @else <x-sections.hero /> @endif
├── components/sections/hero.blade.php               # TIDAK diubah — tetap dipakai sebagai fallback
└── components/sections/banner-carousel.blade.php    # Baru — Alpine x-data carousel; 1 banner = tunggal tanpa kontrol; >1 = auto-rotate + dots/prev-next

tests/
├── Unit/BannerLiveScopeTest.php         # Baru — matriks scope live() + displayStatus()
└── Feature/
    ├── Admin/BannerResourceTest.php     # Baru — US1
    └── Pages/HomeBannerTest.php         # Baru — US2
```

**Structure Decision**: `BannerResource` meniru `ClientLogoResource` (image + link_url + order + is_active) dengan tambahan dua `DatePicker` dan kolom status tayang. Model `Banner` memusatkan aturan tayang di `scopeLive()` (dipakai controller) dan `displayStatus()` (dipakai kolom admin) sehingga logika periode ada di satu tempat dan mudah di-unit-test. `home.blade.php` adalah satu-satunya tempat keputusan "carousel atau hero statis" — `hero.blade.php` tidak disentuh. Carousel memakai Alpine.js yang sudah terpasang, pola `x-data` yang sama dengan `<header>`.

## Complexity Tracking

*Tidak ada pelanggaran constitution — tabel ini kosong.*
