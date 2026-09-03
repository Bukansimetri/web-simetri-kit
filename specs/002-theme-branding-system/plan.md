# Implementation Plan: Theme & Branding System

**Branch**: `002-theme-branding-system` | **Date**: 2026-09-01 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/002-theme-branding-system/spec.md`

**Note**: This template is filled in by the `/speckit-plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Mengimplementasikan 8 halaman company profile publik (Home, Produk, Produk Detail, Tentang Kami, Kontak, Karir, Artikel, FAQ) mengikuti mockup desain "Luminous Azure" (brand SUOER) yang tersedia di `public/mockup-html/`, dengan styling (warna sekunder, font, OG image) yang settings-driven lewat perluasan `BrandSettings` (Epic 2) — bukan hardcode — dan tanpa sistem "pilih varian per section" (lihat spec.md FR-011). Konten Produk/Artikel/Karir/FAQ memakai data seed sementara yang terstruktur agar siap digantikan modul CRUD Epic 3. Form Kontak & kalkulator estimasi berjalan sepenuhnya di sisi client (Alpine.js), tanpa backend baru.

## Technical Context

**Language/Version**: PHP 8.3, Laravel Framework 13

**Primary Dependencies**: Filament 3.2 (admin panel, sudah ada), `spatie/laravel-settings` (perluasan `BrandSettings` — sudah ada), `spatie/laravel-medialibrary` (collection `brand-og-image` — sudah ada), Tailwind CSS v4 (`@theme` CSS-first, sudah ada), `alpinejs` (**baru** — interaktivitas client-side publik: kalkulator, accordion FAQ, nav mobile, validasi form Kontak), `laravel-vite-plugin` fonts `bunny()` helper (sudah ada, diperluas untuk daftar font kurasi)

**Storage**: MySQL — perluasan tabel `settings` (grup `brand`, sudah ada) + 4 tabel baru (`products`, `articles`, `job_openings`, `faq_items`) untuk data seed sementara (lihat data-model.md)

**Testing**: PHPUnit feature test (`php artisan make:test --phpunit`) — render test per route publik, test validasi Theme Settings, test empty-state data kosong

**Target Platform**: Server web Laravel standar (shared hosting/VPS per klien, sesuai Deployment & Client Setup Standards di constitution)

**Project Type**: Web application — single Laravel app (Filament admin panel + Blade public frontend), bukan struktur frontend+backend terpisah

**Performance Goals**: Halaman publik selesai render (TTFB + render) dalam waktu wajar untuk koneksi standar (tidak ada target numerik eksplisit di spec — mengikuti ekspektasi web app standar); kalkulator Home merespons instan (client-side, tanpa round-trip server, FR-006)

**Constraints**: Semua styling MUST config-driven per instalasi (Principle I & II — warna sekunder/font/OG image dari Theme Settings, bukan hardcode); tidak ada sistem varian/page builder baru (Principle III, FR-011 — satu desain Luminous Azure adalah default final v1); data Produk/Artikel/Karir/FAQ MUST berupa seed terstruktur, bukan hardcode di Blade (FR-008); form Kontak MUST NOT punya endpoint submit sungguhan di fitur ini (FR-007)

**Scale/Scope**: Single-tenant per deployment (satu instalasi = satu klien, SUOER untuk instalasi saat ini); scope terbatas ke 8 halaman publik + perluasan Theme Settings admin, tidak menyentuh CRUD admin modul Epic 3 (ditunda, lihat Assumptions di spec.md)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Applies? | Assessment |
|---|---|---|
| I. Multi-Client Reusability | Ya | Warna sekunder/font/OG image tetap settings-driven (bukan hardcode) meski nilai defaultnya diambil dari desain SUOER; data Produk/Artikel/Karir/FAQ adalah seed model, bukan teks hardcode di Blade (FR-008). **PASS** |
| II. White-Label by Default | Ya | Luminous Azure adalah default yang bisa diganti admin (klarifikasi Q2=A, FR-005) — bukan hardcode permanen. Instalasi klien lain tetap bisa mengganti warna sekunder/font/OG image tanpa developer. **PASS** |
| III. Settings-Driven Theming, No Page Builder (NON-NEGOTIABLE) | Ya | Section tetap berupa Blade component tetap (bukan freeform builder). Deviasi dari rencana awal: fitur ini TIDAK menyediakan ≥2 varian per section (FR-011) — hanya 1 varian resmi (Luminous Azure) untuk v1, keputusan eksplisit dari klarifikasi (Q1=A), bukan penambahan abstraksi builder. Arsitektur tetap fixed-component, jadi tidak melanggar larangan "no page builder"; hanya mengurangi *jumlah* varian yang tersedia, yang tetap bisa ditambah nanti tanpa refactor besar. **PASS (dengan catatan, lihat Complexity Tracking)** |
| IV. Module Test Coverage | Ya | Tiap halaman publik & Theme Settings baru MUST punya feature test dasar (render + data kosong + validasi) sebelum dianggap selesai — lihat Phase 1 & tasks nanti. **PASS (planned)** |
| V. Simplicity & Dependency Discipline | Ya | Alpine.js dipilih karena ringan & sudah lazim di ekosistem Laravel/Livewire (research.md §3); SVG manual dipilih di atas library charting (research.md §4); font di-preload via `bunny()` yang sudah dipakai project (research.md §5) — semua keputusan menghindari dependency baru yang tidak perlu. **PASS** |

Tidak ada pelanggaran constitution yang butuh entry Complexity Tracking formal — catatan Principle III didokumentasikan sebagai keputusan produk (bukan penyimpangan arsitektur) di baris Complexity Tracking di bawah untuk transparansi.

## Project Structure

### Documentation (this feature)

```text
specs/002-theme-branding-system/
├── plan.md              # This file (/speckit-plan command output)
├── research.md          # Phase 0 output (/speckit-plan command)
├── data-model.md         # Phase 1 output (/speckit-plan command)
├── quickstart.md        # Phase 1 output (/speckit-plan command)
├── contracts/           # Phase 1 output (/speckit-plan command)
│   ├── theme-settings-surface.md
│   └── public-routes.md
└── tasks.md             # Phase 2 output (/speckit-tasks command - NOT created by /speckit-plan)
```

### Source Code (repository root)

```text
app/
├── Models/
│   ├── Product.php
│   ├── Article.php
│   ├── JobOpening.php
│   └── FaqItem.php
├── Settings/
│   └── BrandSettings.php          # diperluas: secondary_color, font_heading, font_body
├── Filament/Pages/
│   └── BrandSettingsPage.php      # diperluas: field baru + OG image upload
└── Http/Controllers/Public/
    ├── HomeController.php
    ├── ProductController.php      # index + show
    ├── AboutController.php
    ├── ContactController.php
    ├── CareerController.php
    ├── ArticleController.php      # index + show
    └── FaqController.php

resources/
├── views/
│   ├── layouts/
│   │   └── public.blade.php       # header/footer/nav + inject CSS variable Theme Settings
│   ├── components/
│   │   ├── layout/ (header, footer, nav)
│   │   └── sections/ (hero, calculator, why-choose, how-it-works, product-card,
│   │                    article-card, job-card, faq-accordion, cta-band, ...)
│   └── pages/
│       ├── home.blade.php
│       ├── produk/{index,show}.blade.php
│       ├── tentang-kami.blade.php
│       ├── kontak.blade.php
│       ├── karir.blade.php
│       ├── artikel/{index,show}.blade.php
│       └── faq.blade.php
├── css/app.css                    # @theme diperluas dengan token Luminous Azure (research.md §2)
└── js/
    ├── app.js                     # init Alpine.js
    └── calculator.js              # logic kalkulator estimasi (Alpine component)

database/
├── migrations/                    # create_products/articles/job_openings/faq_items_table
│                                    + settings-migration tambah secondary_color/font_heading/font_body
└── seeders/
    ├── ProductSeeder.php          # konten dari mockup produk
    ├── ArticleSeeder.php
    ├── JobOpeningSeeder.php
    └── FaqItemSeeder.php

routes/web.php                     # route 8 halaman publik (lihat contracts/public-routes.md)

tests/Feature/
├── Pages/                         # render test per route publik + empty state
│   ├── HomePageTest.php
│   ├── ProductPageTest.php
│   ├── AboutPageTest.php
│   ├── ContactPageTest.php
│   ├── CareerPageTest.php
│   ├── ArticlePageTest.php
│   └── FaqPageTest.php
└── Settings/
    └── BrandSettingsTest.php      # diperluas: secondary_color, font, OG image
```

**Structure Decision**: Single Laravel app (bukan monorepo/multi-service) — admin panel (Filament, sudah ada) dan frontend publik baru (Blade + Tailwind v4 + Alpine.js) hidup di codebase yang sama, konsisten dengan `Project Type` di plan Epic 2. Frontend publik ditempatkan di namespace/folder terpisah (`Http/Controllers/Public`, `views/pages`, `views/components`) supaya tidak bercampur dengan resource Filament admin yang sudah ada.

## Complexity Tracking

> Bukan pelanggaran constitution yang butuh justifikasi formal — dicatat untuk transparansi keputusan produk (Principle III).

| Keputusan | Kenapa dibutuhkan | Alternatif yang ditolak |
|-----------|------------|-------------------------------------|
| Hanya 1 varian tampilan per section (bukan ≥2 seperti rencana awal Epic 4) | Mockup lengkap "Luminous Azure" baru tersedia untuk 1 desain; membangun varian kedua sekaligus akan menggandakan scope tanpa desain referensi untuk varian tsb | Tetap wajibkan ≥2 varian per section — ditolak di klarifikasi (Q1=A) karena tidak ada desain kedua yang siap, dan akan menunda rilis desain SUOER yang sudah jadi |
