# Implementation Plan: Modul Client Logos

**Branch**: `009-client-logos-module` | **Date**: 2026-09-07 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/009-client-logos-module/spec.md`

## Summary

Menambahkan modul "Client Logos" — entity baru `ClientLogo` (nama perusahaan, logo path, link_url opsional, order, is_active) dengan CRUD admin (Filament Resource, pola identik `TestimonialResource` dari 008) dan satu section "logo strip" baru di halaman **Tentang Kami** (`/tentang-kami`) yang merender logo **aktif** terurut, tepat setelah section testimoni (modul 008) dan sebelum CTA band. Logo memakai `ImageUploads::storeAsWebp` yang sudah ada. Section tidak dirender bila tidak ada logo aktif. `AboutController` (yang sejak modul 008 sudah mengirim `$testimonials`) ditambah mengirim `$clientLogos`. Tidak ada dependency baru.

## Technical Context

**Language/Version**: PHP 8.3, Laravel Framework 13

**Primary Dependencies**: Filament 3.3 (`ClientLogoResource` baru — `TextInput`, `FileUpload`, `Toggle`). Helper `App\Support\ImageUploads` (sudah ada). Tidak ada dependency baru.

**Storage**: MySQL — migration baru `client_logos` (`id`, `company_name` string, `logo_path` string, `link_url` string nullable, `order` integer default 0, `is_active` boolean default true, timestamps). Tidak ada perubahan skema tabel lain.

**Testing**: PHPUnit feature test memakai Livewire test helper untuk Filament Resource (pola `TestimonialResourceTest`) — create/edit/delete, validasi field wajib (company_name, logo_path), validasi `link_url` sebagai URL absolut, logo tanpa link, toggle aktif, konversi WebP; feature test terpisah untuk render section di `/tentang-kami` (hanya logo aktif, urutan, link buka tab baru vs tanpa link, empty-state section tidak dirender, section lain + testimoni utuh).

**Target Platform**: Server web Laravel standar

**Project Type**: Web application — perluasan admin panel Filament + satu section publik baru di halaman Blade yang sudah ada

**Performance Goals**: Tidak ada target khusus (admin-only untuk CRUD; section publik satu query `where is_active` + `orderBy order`, volume < 20 baris)

**Constraints**: CRUD MUST terbuka untuk semua role admin panel (FR-011); `link_url` MUST divalidasi sebagai URL absolut http/https bila diisi (FR-004); link MUST buka di tab baru dengan `rel` aman (FR-009); section MUST tidak dirender bila tidak ada logo aktif (FR-010); halaman Tentang Kami hanya boleh disisipi satu section baru setelah testimoni, section eksisting tidak diubah (FR-012, Assumptions); tidak ada dependency baru (Principle V); tidak ada toggle modul / kategori / heading konfigurabel (Assumptions)

**Scale/Scope**: 1 Filament Resource baru (`ClientLogoResource`) + 1 migration + 1 model + 1 factory + update `AboutController` (tambah 1 variabel) + 1 section Blade baru + edit `tentang-kami.blade.php` (sisip 1 baris include setelah `<x-sections.testimonials>`)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Applies? | Assessment |
|---|---|---|
| I. Multi-Client Reusability | Ya | Logo diisi lewat CRUD per klien, bukan hardcode. Section otomatis kosong sampai klien mengisi. **PASS** |
| II. White-Label by Default | Tidak langsung | Tidak menyentuh branding/identitas panel — tidak relevan. |
| III. Settings-Driven Theming, No Page Builder | Ya (NON-NEGOTIABLE) | Logo strip adalah satu Blade section tetap dengan layout tetap — bukan builder. Penempatan hardcoded di `tentang-kami.blade.php` (posisi tetap, FR-012). **PASS** |
| IV. Module Test Coverage | Ya | US1 (CRUD) & US2 (render section) masing-masing punya feature test dasar. **PASS (planned)** |
| V. Simplicity & Dependency Discipline | Ya | Nol dependency baru; reuse `ImageUploads` & pola `TestimonialResource`; tidak ada toggle modul / kategori / heading konfigurabel / carousel yang tidak diminta. **PASS** |

Tidak ada pelanggaran constitution — Complexity Tracking kosong.

## Project Structure

### Documentation (this feature)

```text
specs/009-client-logos-module/
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
│   └── ClientLogo.php                   # Baru — fillable + casts (order int, is_active bool)
├── Filament/Resources/
│   ├── ClientLogoResource.php           # Baru (pola TestimonialResource)
│   └── ClientLogoResource/Pages/{ListClientLogos,CreateClientLogo,EditClientLogo}.php
└── Http/Controllers/Public/
    └── AboutController.php               # Diperbarui — tambah $clientLogos (aktif, orderBy order,id)

database/
├── migrations/
│   └── xxxx_create_client_logos_table.php   # Baru
└── factories/
    └── ClientLogoFactory.php            # Baru (untuk test; + state inactive)

resources/views/
├── pages/tentang-kami.blade.php                     # Diperbarui — sisip <x-sections.client-logos :logos="$clientLogos" /> tepat setelah <x-sections.testimonials>, sebelum <x-sections.cta-band>
└── components/sections/client-logos.blade.php       # Baru — render logo strip; @if kosong → tidak render apa pun

tests/Feature/
├── Admin/ClientLogoResourceTest.php     # Baru — US1
└── Pages/AboutPageClientLogosTest.php   # Baru — US2
```

**Structure Decision**: Perluasan langsung struktur Filament yang sudah ada + satu Blade section component baru di `resources/views/components/sections/` (folder konvensi section). Pola identik `008-testimonials-module`: resource meniru `TestimonialResource`, section meniru `components/sections/testimonials.blade.php`, `AboutController` menambah satu variabel di samping `$testimonials` yang sudah ada.

## Complexity Tracking

*Tidak ada pelanggaran constitution — tabel ini kosong.*
