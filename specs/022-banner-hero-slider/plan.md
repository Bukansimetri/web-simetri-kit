# Implementation Plan: Banner Hero Slider

**Branch**: `022-banner-hero-slider` | **Date**: 2026-09-14 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/022-banner-hero-slider/spec.md`

## Summary

Menaikkan entitas Banner dari "gambar promosi" menjadi **slide hero utuh**: satu record membawa gambar, badge, judul, subjudul, dua CTA, konten trust bar, serta preset tampilan (gaya lapisan + posisi teks). Komponen `banner-carousel` dan `hero` dilebur menjadi satu komponen `hero-slider` yang merender 1 slide sebagai hero statis, ≥2 slide sebagai slider manual tanpa auto-rotate, dan 0 slide sebagai fallback ke hero bawaan.

Pendekatan teknis: sepuluh kolom nullable baru pada tabel `banners` dengan backfill dari teks hero yang berlaku sekarang; dua PHP enum (backed string) untuk preset tampilan; form Filament dipecah menjadi `Section` dengan validasi CTA berpasangan; sanitizer HTML berbasis `DOMDocument` (tanpa dependency baru) untuk trust bar; komponen Blade + Alpine untuk slider; dan `Banner::saved()`/`deleted()` yang membuang cache `public-page:home` agar perubahan admin langsung terlihat.

## Technical Context

**Language/Version**: PHP 8.3

**Primary Dependencies**: Laravel 13, Filament 3.2, Alpine.js 3, Tailwind CSS 4 (via Vite 8). **Tidak ada dependency baru** yang ditambahkan fitur ini.

**Storage**: Basis data relasional lewat Eloquent (tabel `banners`); berkas gambar pada disk `public` (`storage/app/public/banners`).

**Testing**: PHPUnit 12 lewat `php artisan test --compact`; format kode lewat `vendor/bin/pint --dirty`.

**Target Platform**: Aplikasi web yang dirender di server, diakses lewat peramban desktop dan ponsel modern.

**Project Type**: Aplikasi web monolit Laravel — Blade + Alpine di sisi publik, Filament di sisi admin.

**Performance Goals**: Tidak memperburuk LCP beranda dibanding sebelum fitur ini. Slide pertama dimuat dengan prioritas tinggi, slide berikutnya tertunda. Seluruh slide dirender di HTML awal (tanpa permintaan tambahan saat berpindah slide).

**Constraints**: Tanpa dependency baru (Principle V); tanpa kapabilitas page builder (Principle III); animasi wajib tunduk pada `prefers-reduced-motion`; markup slider wajib memenuhi pola carousel yang dapat diakses.

**Scale/Scope**: Segelintir slide per situs (< 10). Satu komponen Blade baru, satu Filament Resource yang diperluas, satu migration, dua enum, satu helper sanitizer, satu berkas JS komponen Alpine.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Prinsip | Status | Catatan |
|---|---|---|
| I. Multi-Client Reusability | ✅ PASS | Seluruh isi slide tersimpan sebagai data, bukan di view. Teks hero yang sekarang hardcoded justru dipindahkan ke basis data lewat backfill. Modul tetap bisa dimatikan dengan menonaktifkan seluruh banner (jatuh ke hero fallback). |
| II. White-Label by Default | ✅ PASS | Tidak ada identitas starter kit yang ditambahkan. Warna dan tipografi slide dibaca dari `BrandSettings`, tidak disimpan per banner. |
| III. Settings-Driven Theming, No Page Builder | ⚠️ PASS dengan catatan | Preset lapisan dan posisi teks adalah **enum tertutup** = varian section bernama, persis pola yang diizinkan prinsip ini. Field `trust_html` adalah satu-satunya area HTML bebas; lihat Complexity Tracking untuk justifikasinya. Tidak ada penyusunan section, tidak ada drag-drop tata letak, tidak ada input warna bebas. |
| IV. Module Test Coverage | ✅ PASS | Fitur menambah dan memperbarui feature test untuk jalur render publik dan CRUD admin; lihat tasks.md fase pengujian. |
| V. Simplicity & Dependency Discipline | ✅ PASS | Nol dependency baru. Sanitizer memakai `DOMDocument` bawaan PHP, mengikuti preseden `ImageUploads` yang memakai GD bawaan alih-alih paket gambar. |

**Hasil evaluasi awal**: LULUS. **Evaluasi ulang setelah Phase 1**: LULUS — desain akhir tidak menambah dependency maupun primitif page builder.

## Project Structure

### Documentation (this feature)

```text
specs/022-banner-hero-slider/
├── plan.md              # Berkas ini
├── research.md          # Phase 0
├── data-model.md        # Phase 1
├── quickstart.md        # Phase 1
├── contracts/
│   ├── admin-panel-surface.md    # Kontrak form & tabel Filament
│   └── public-render.md          # Kontrak markup & perilaku slider publik
├── checklists/
│   └── requirements.md
└── tasks.md             # Phase 2 (dibuat /speckit-tasks)
```

### Source Code (repository root)

```text
app/
├── Enums/
│   ├── BannerOverlayStyle.php         # BARU — preset lapisan
│   └── BannerTextPosition.php         # BARU — preset posisi teks
├── Models/
│   └── Banner.php                     # DIUBAH — fillable, casts, accessor slide, boot() invalidasi cache
├── Support/
│   └── HtmlSanitizer.php              # BARU — allowlist berbasis DOMDocument
├── Filament/Resources/
│   └── BannerResource.php             # DIUBAH — Section, field konten, validasi CTA, reorder
└── Http/Controllers/Public/
    └── HomeController.php             # TIDAK berubah (invalidasi ditangani model)

database/
├── migrations/
│   └── 2026_09_14_*_add_hero_fields_to_banners_table.php   # BARU — kolom + backfill
├── factories/
│   └── BannerFactory.php              # DIUBAH — state untuk slide lengkap
└── seeders/
    └── (seeder demo)                  # DIUBAH — banner contoh berisi konten slide

resources/
├── views/components/sections/
│   ├── hero-slider.blade.php          # BARU — menggantikan banner-carousel
│   ├── banner-carousel.blade.php      # DIHAPUS
│   └── hero.blade.php                 # TETAP — fallback nol banner & dipakai halaman lain
├── views/pages/
│   └── home.blade.php                 # DIUBAH — memanggil hero-slider
├── js/
│   ├── hero-slider.js                 # BARU — komponen Alpine
│   └── app.js                         # DIUBAH — registrasi komponen
└── css/
    └── app.css                        # DIUBAH — keyframes penanda panah + guard reduced-motion

tests/
├── Feature/Pages/HomeBannerTest.php           # DIUBAH — asersi hero-in-banner
├── Feature/Admin/BannerResourceTest.php       # DIUBAH — field & validasi baru
├── Feature/Public/LazyLoadingTest.php         # DIUBAH — prioritas slide pertama
└── Unit/HtmlSanitizerTest.php                 # BARU
```

**Structure Decision**: Mengikuti struktur Laravel yang sudah ada di repositori — tidak ada folder dasar baru (sesuai `CLAUDE.md`). Satu-satunya direktori baru adalah `app/Enums/`, yang mengikuti konvensi Laravel standar dan konvensi TitleCase untuk key enum di `CLAUDE.md`.

## Complexity Tracking

> Diisi hanya untuk temuan Constitution Check yang perlu justifikasi.

| Violation | Why Needed | Simpler Alternative Rejected Because |
|-----------|------------|-------------------------------------|
| Field HTML bebas `trust_html` di dalam varian section tetap (menyerempet Principle III) | Area bukti sosial berisi campuran teks, avatar pelanggan, ikon, dan logo sertifikasi yang **berbeda bentuk** di tiap klien — satu klien memakai tiga avatar, klien lain memakai dua logo sertifikasi. Memodelkannya sebagai field terstruktur berarti menebak bentuk yang belum diketahui. | Field terstruktur (mis. daftar avatar + satu baris teks) ditolak karena mengunci satu bentuk saja dan memaksa perubahan kode setiap kali klien baru punya bentuk bukti sosial berbeda — justru melanggar Principle I. Editor kaya sudah menjadi pola mapan di repositori ini (Artikel, Custom Page, Portfolio) dan cakupannya tetap satu blok di dalam slide, bukan penyusun tata letak. |
