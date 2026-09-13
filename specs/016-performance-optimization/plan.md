# Implementation Plan: Optimasi Performa Halaman Publik

**Branch**: `016-performance-optimization` | **Date**: 2026-09-13 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/016-performance-optimization/spec.md`

## Summary

Tiga perbaikan performa independen di atas codebase yang sudah ada, nol dependency baru: (1) tambah atribut native `loading="lazy" decoding="async"` ke seluruh `<img>` publik KECUALI gambar hero/cover teratas tiap halaman (daftar definitif di research.md §2); (2) bungkus 8 method controller publik read-only (`HomeController`, `ProductController` ×2, `ArticleController` ×2, `PortfolioController` ×2, `FaqController`, `AboutController`) dengan `Cache::remember` TTL 5 menit lewat trait kecil `CachesPublicPages` — admin panel tidak tersentuh sama sekali karena jalur query Filament sepenuhnya terpisah; (3) ganti tag stylesheet Material Symbols di `layouts/public.blade.php` dari `rel="stylesheet"` blocking jadi pola `media="print"` + `onload` swap dengan fallback `<noscript>`.

## Technical Context

**Language/Version**: PHP 8.3, Laravel Framework 13

**Primary Dependencies**: Tidak ada dependency baru. Reuse `CACHE_STORE=database` yang sudah dikonfigurasi (`config/cache.php`), reuse pola trait (mis. `HasSeoMetadata` dari AMC-223) untuk `CachesPublicPages`.

**Storage**: Tidak ada perubahan skema. Cache memakai tabel `cache` bawaan driver `database` (sudah ada dari instalasi Laravel default).

**Testing**: PHPUnit feature test — `tests/Feature/Public/LazyLoadingTest.php` (US1: assertSee `loading="lazy"` pada gambar non-hero, assertDontSee pada gambar hero, per halaman representatif), `tests/Feature/Public/PublicPageCachingTest.php` (US2: hitung jumlah query DB per request pakai `DB::listen`/`assertQueryCount` — request kedua dalam TTL MUST nol query tambahan; ubah data lewat model langsung lalu re-request dalam TTL MUST masih tampilkan data lama; admin/Filament test existing MUST tetap hijau tanpa perubahan sama sekali (bukti FR-008 by design); test Portfolio kategori berbeda MUST cache key terpisah), `tests/Feature/Public/NonBlockingIconFontTest.php` (US3: assertSee pola `media="print"` pada tag stylesheet, assertSee `<noscript>` fallback).

**Target Platform**: Server web Laravel standar

**Project Type**: Web application — perubahan 15 file Blade (atribut `<img>`), 6 controller publik (+trait baru), 1 partial layout (`layouts/public.blade.php`)

**Performance Goals**: Lihat SC-001/SC-002/SC-006 spec.md — tidak ada target angka mutlak baru di luar yang sudah didefinisikan sebagai "membaik terukur" di spec; TTL cache 300 detik (research.md §3)

**Constraints**: Gambar hero/cover WAJIB tetap eager (FR-002) — daftar definitif research.md §2 tidak boleh berubah tanpa alasan; cache TIDAK PERNAH memengaruhi panel admin (FR-008); halaman ber-state (Kontak/Kalkulator) TIDAK dibungkus cache (FR-006); cache key Portfolio index WAJIB per-kategori (research.md §3); nol dependency baru (Principle V); tidak ada invalidasi cache instan berbasis event (Assumptions)

**Scale/Scope**: 1 trait baru (`CachesPublicPages`) + update 6 controller publik + update ~15 file Blade (atribut lazy-load, lihat research.md §2 untuk daftar lengkap) + 1 refactor kecil (`client-logos.blade.php` dari string HTML manual ke tag Blade) + update 1 partial layout (font ikon non-blocking) + 3 file test baru

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Applies? | Assessment |
|---|---|---|
| I. Multi-Client Reusability | Ya | Perilaku caching/lazy-load seragam di semua instalasi starter kit, tidak ada konfigurasi hardcoded spesifik satu klien; TTL & cakupan halaman berlaku sama untuk semua klien (Assumptions spec.md — tidak ada kontrol admin baru, konsisten sebagai perbaikan teknis latar belakang). **PASS** |
| II. White-Label by Default | Tidak langsung | Tidak menyentuh branding/identitas. |
| III. Settings-Driven Theming, No Page Builder | Ya (NON-NEGOTIABLE) | Tidak ada layout/section builder baru — murni atribut HTML & lapisan cache di atas struktur halaman tetap yang sudah ada. **PASS** |
| IV. Module Test Coverage | Ya | Ketiga user story (lazy-load, caching, non-blocking font) masing-masing dapat feature test terpisah. **PASS (planned)** |
| V. Simplicity & Dependency Discipline | Ya | Nol dependency baru (research.md §1-5 eksplisit menolak pustaka JS lazy-load, cache-tag invalidation berbasis Observer, dan resource-hint alternatif demi tetap simpel); reuse `CACHE_STORE` & pola trait yang sudah ada. **PASS** |

Tidak ada pelanggaran constitution — Complexity Tracking kosong.

## Project Structure

### Documentation (this feature)

```text
specs/016-performance-optimization/
├── plan.md              # This file
├── research.md          # Phase 0 output — termasuk inventaris definitif §2
├── data-model.md         # Phase 1 output
├── quickstart.md         # Phase 1 output
├── contracts/
│   └── rendering-behavior.md
└── tasks.md              # Phase 2 output (/speckit-tasks — NOT created here)
```

### Source Code (repository root)

```text
app/
├── Concerns/
│   └── CachesPublicPages.php              # Baru — rememberPublicPage(), TTL 300s
└── Http/Controllers/Public/
    ├── HomeController.php                  # + use CachesPublicPages, bungkus __invoke()
    ├── ProductController.php               # + use CachesPublicPages, bungkus index()+show()
    ├── ArticleController.php               # + use CachesPublicPages, bungkus index()+show()
    ├── PortfolioController.php             # + use CachesPublicPages, bungkus index() (key per-kategori)+show()
    ├── FaqController.php                   # + use CachesPublicPages, bungkus __invoke()
    └── AboutController.php                 # + use CachesPublicPages, bungkus __invoke()

resources/views/
├── layouts/
│   └── public.blade.php                    # Stylesheet Material Symbols → media="print"+onload swap + <noscript>
└── components/sections/ & pages/           # ~15 file <img> — tambah loading="lazy" decoding="async"
                                            #   (daftar lengkap: research.md §2); client-logos.blade.php
                                            #   direfactor dari string HTML manual ke tag Blade biasa

tests/Feature/Public/
├── LazyLoadingTest.php                     # Baru — US1
├── PublicPageCachingTest.php                # Baru — US2
└── NonBlockingIconFontTest.php              # Baru — US3
```

**Structure Decision**: Trait `CachesPublicPages` (di `app/Concerns/`, folder yang sama dipakai `HasSeoMetadata` dari AMC-223) memusatkan TTL & pemanggilan `Cache::remember` supaya 8 titik pemakaian tidak menduplikasi angka ajaib. Tidak ada controller/resource baru — seluruh perubahan controller adalah pembungkusan query yang sudah ada, bukan logic baru. Perubahan Blade murni penambahan atribut (`loading`/`decoding`) kecuali satu refactor kecil di `client-logos.blade.php` yang muncul alami saat menambahkan atribut ke markup yang sebelumnya dibangun lewat string manual.

## Complexity Tracking

*Tidak ada pelanggaran constitution — tabel ini kosong.*
