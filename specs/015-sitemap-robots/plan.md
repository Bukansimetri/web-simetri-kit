# Implementation Plan: Sitemap & Robots Otomatis

**Branch**: `015-sitemap-robots` | **Date**: 2026-09-13 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/015-sitemap-robots/spec.md`

## Summary

Menambahkan `GET /sitemap.xml` (Blade view XML manual, di-loop dari query yang sama persis dengan controller publik existing untuk tiap modul — Produk, Artikel published, Halaman Statis, Proyek Portfolio aktif, plus 7 halaman statis tetap dan `/karir` kondisional pada `BrandSettings::career_module_enabled`) dan mengganti `public/robots.txt` yang statis dengan `GET /robots.txt` dinamis (baris `User-agent`/`Disallow` tetap sama, ditambah satu baris `Sitemap:` berisi URL absolut yang otomatis mengikuti domain aktif). Nol dependency baru — reuse pola query existing dan `Content-Type` response eksplisit, konsisten dengan pendekatan AMC-223.

## Technical Context

**Language/Version**: PHP 8.3, Laravel Framework 13

**Primary Dependencies**: Tidak ada dependency baru. Reuse model existing (`Product`, `Article`, `CustomPage`, `PortfolioProject`) dan `App\Settings\BrandSettings::career_module_enabled`.

**Storage**: Tidak ada perubahan skema — murni query read-only atas tabel yang sudah ada.

**Testing**: PHPUnit feature test — `tests/Feature/Public/SitemapTest.php` (US1: 7 halaman statis selalu ada; produk/artikel-published/halaman-statis/portfolio-aktif muncul; artikel draft/terjadwal & portfolio nonaktif TIDAK muncul; toggle `career_module_enabled` on/off; setiap `<loc>` hasil generate benar-benar di-GET dan MUST 200; situs kosong tetap 200) dan `tests/Feature/Public/RobotsTxtTest.php` (US2: baris `User-agent`/`Disallow` tidak berubah; baris `Sitemap:` ada dan URL-nya sesuai domain test).

**Target Platform**: Server web Laravel standar

**Project Type**: Web application — 2 route baru + 1 controller baru (`SitemapController`, method `xml()` & `robots()`) + 1 Blade view XML baru; hapus `public/robots.txt` statis

**Performance Goals**: Tidak ada target khusus — volume konten skala starter kit (SC-005), satu query per jenis konten per request, tanpa N+1 (tidak butuh eager-load relasi karena hanya butuh `slug`/`updated_at`)

**Constraints**: `sitemap.xml` MUST selalu 200 walau tanpa konten sama sekali (Edge Cases); cakupan visibilitas per jenis konten MUST identik dengan controller publik existing (research.md §3) — bukan aturan baru; `robots.txt` MUST tidak mengubah `Disallow` existing (FR-011); `public/robots.txt` statis MUST dihapus (satu-satunya cara route baru bisa tereksekusi — research.md §2); tidak ada dependency baru (Principle V); tidak ada sitemap index/multi-file, tidak ada toggle admin per-konten untuk exclude dari sitemap (Assumptions)

**Scale/Scope**: 1 controller baru (`SitemapController`, 2 method) + 1 Blade view (`resources/views/sitemap.blade.php`) + 2 route baru di `routes/web.php` + hapus 1 file statis (`public/robots.txt`) + 2 file test baru

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Applies? | Assessment |
|---|---|---|
| I. Multi-Client Reusability | Ya | `robots.txt` dinamis (bukan file statis per-klien) — URL sitemap otomatis mengikuti domain klien mana pun tanpa edit manual. Sitemap murni derivatif data yang sudah diisi per-klien lewat CRUD, tidak ada konten hardcoded. **PASS** |
| II. White-Label by Default | Tidak langsung | Tidak menyentuh branding/identitas panel. |
| III. Settings-Driven Theming, No Page Builder | Ya (NON-NEGOTIABLE) | Tidak ada page builder/section baru — `sitemap.xml`/`robots.txt` adalah dokumen mesin-baca dengan struktur tetap (protokol sitemaps.org), bukan halaman bertema. **PASS (tidak relevan secara langsung)** |
| IV. Module Test Coverage | Ya | US1 (cakupan URL + visibilitas per jenis konten + toggle Karir) dan US2 (isi `robots.txt`) masing-masing dapat feature test. **PASS (planned)** |
| V. Simplicity & Dependency Discipline | Ya | Nol dependency baru (research.md §1 menolak `spatie/laravel-sitemap` secara eksplisit — surface area tidak sepadan untuk ≤11 sumber URL tetap); reuse query controller existing, bukan logic visibilitas baru. **PASS** |

Tidak ada pelanggaran constitution — Complexity Tracking kosong.

## Project Structure

### Documentation (this feature)

```text
specs/015-sitemap-robots/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md         # Phase 1 output
├── quickstart.md         # Phase 1 output
├── contracts/
│   └── public-endpoints.md
└── tasks.md              # Phase 2 output (/speckit-tasks — NOT created here)
```

### Source Code (repository root)

```text
app/Http/Controllers/Public/
└── SitemapController.php          # Baru — xml(): kumpulkan 7 halaman statis + /karir kondisional
                                    #   + query Product::all()/Article published/CustomPage::all()/
                                    #   PortfolioProject aktif → view('sitemap', [...])
                                    #   dengan header Content-Type application/xml
                                    # robots(): render teks robots.txt (User-agent/Disallow tetap +
                                    #   baris Sitemap: url('/sitemap.xml')) dengan Content-Type text/plain

resources/views/
└── sitemap.blade.php              # Baru — <urlset> XML manual, TIDAK @extends('layouts.public')

routes/web.php                      # + Route::get('/sitemap.xml', [SitemapController::class, 'xml'])
                                    # + Route::get('/robots.txt', [SitemapController::class, 'robots'])

public/robots.txt                   # DIHAPUS — digantikan route dinamis (lihat research.md §2)

tests/Feature/Public/
├── SitemapTest.php                # Baru — US1
└── RobotsTxtTest.php               # Baru — US2
```

**Structure Decision**: Satu `SitemapController` dengan 2 method (`xml()`, `robots()`) — bukan 2 controller terpisah — karena keduanya berbagi konsep yang sama (dokumen SEO derivatif, tanpa state) dan `robots()` memakai `url('/sitemap.xml')` yang secara alami hidup berdampingan di file yang sama; ini sejalan dengan pola `ContactController` (show/store dalam satu class) yang sudah ada di codebase. View sitemap dipisah dari `layouts.public` karena bukan halaman HTML bertema. Penghapusan `public/robots.txt` statis adalah bagian WAJIB dari implementasi (bukan cleanup opsional) — didokumentasikan eksplisit di research.md §2 supaya tidak terlewat saat eksekusi task.

## Complexity Tracking

*Tidak ada pelanggaran constitution — tabel ini kosong.*
