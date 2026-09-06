# Implementation Plan: Modul Testimonials

**Branch**: `008-testimonials-module` | **Date**: 2026-09-07 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/008-testimonials-module/spec.md`

## Summary

Menambahkan modul "Testimonials" — entity baru `Testimonial` (nama, atribusi perusahaan/jabatan opsional, isi, rating 1–5, foto opsional, urutan, aktif) dengan CRUD admin (Filament Resource, pola identik `JobOpeningResource` dari 006-career) dan satu section baru di halaman **Tentang Kami** (`/tentang-kami`) yang merender testimoni **aktif** terurut. Foto memakai helper `ImageUploads::storeAsWebp` yang sudah ada (pola `ArticleResource`). Jika tidak ada testimoni aktif, section tidak dirender (pola empty-state modul opsional). `AboutController` yang sekarang tanpa data diubah untuk mengirim koleksi testimoni aktif ke view; section lain halaman Tentang Kami tidak disentuh. Tidak ada dependency baru.

## Technical Context

**Language/Version**: PHP 8.3, Laravel Framework 13

**Primary Dependencies**: Filament 3.3 (`TestimonialResource` baru — `TextInput`, `Textarea`, `FileUpload`, `Toggle`, `Select`/`TextInput` untuk rating). Helper `App\Support\ImageUploads` (sudah ada, konversi WebP via GD, tanpa dependency). Tidak ada dependency baru.

**Storage**: MySQL — migration baru `testimonials` (`id`, `name`, `attribution` nullable, `content` text, `rating` unsignedTinyInteger, `photo_path` nullable, `order` integer default 0, `is_active` boolean default true, timestamps). Tidak ada perubahan skema tabel lain.

**Testing**: PHPUnit feature test memakai Livewire test helper untuk Filament Resource (pola `JobOpeningResource`/`ArticleResourceTest`) — create/edit/delete, validasi field wajib (name, content, rating), validasi rating 1–5, foto opsional + konversi WebP, toggle aktif; feature test terpisah untuk render section di `/tentang-kami` (hanya testimoni aktif, urutan, empty-state section tidak dirender, section lain utuh).

**Target Platform**: Server web Laravel standar (sama seperti fitur sebelumnya)

**Project Type**: Web application — perluasan admin panel Filament + satu section publik baru di halaman Blade yang sudah ada, tidak ada perubahan struktur project

**Performance Goals**: Tidak ada target khusus (admin-only untuk CRUD; section publik satu query `where is_active` + `orderBy order`, volume puluhan baris)

**Constraints**: CRUD MUST terbuka untuk semua role admin panel (FR-012); rating MUST bilangan bulat 1–5 (FR-004); section MUST tidak dirender bila tidak ada testimoni aktif (FR-011); halaman Tentang Kami hanya boleh disisipi satu section baru, section eksisting tidak diubah (FR-013, Assumptions); tidak ada dependency baru (Principle V); tidak ada toggle modul tingkat-atas (Assumptions)

**Scale/Scope**: 1 Filament Resource baru (`TestimonialResource`) + 1 migration + 1 model + 1 factory + update `AboutController` + 1 partial/section Blade baru + edit `tentang-kami.blade.php` (sisip 1 baris include)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Applies? | Assessment |
|---|---|---|
| I. Multi-Client Reusability | Ya | Testimoni diisi lewat CRUD per klien, bukan hardcode di view. Section otomatis kosong sampai klien mengisi. **PASS** |
| II. White-Label by Default | Tidak langsung | Tidak menyentuh branding/identitas panel — tidak relevan. |
| III. Settings-Driven Theming, No Page Builder | Ya (NON-NEGOTIABLE) | Section testimoni adalah satu Blade section tetap dengan layout tetap — bukan builder. Penempatan hardcoded di `tentang-kami.blade.php` (posisi tetap, FR-013). **PASS** |
| IV. Module Test Coverage | Ya | US1 (CRUD) & US2 (render section) masing-masing punya feature test dasar. **PASS (planned)** |
| V. Simplicity & Dependency Discipline | Ya | Nol dependency baru; reuse `ImageUploads` & pola `JobOpeningResource`; tidak ada toggle modul / moderasi / carousel yang tidak diminta; rating integer tanpa half-star. **PASS** |

Tidak ada pelanggaran constitution — Complexity Tracking kosong.

## Project Structure

### Documentation (this feature)

```text
specs/008-testimonials-module/
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
│   └── Testimonial.php                  # Baru — fillable + casts (rating int, is_active bool, order int)
├── Filament/Resources/
│   ├── TestimonialResource.php          # Baru (pola JobOpeningResource)
│   └── TestimonialResource/Pages/{ListTestimonials,CreateTestimonial,EditTestimonial}.php
└── Http/Controllers/Public/
    └── AboutController.php               # Diperbarui — kirim $testimonials (aktif, orderBy order) ke view

database/
├── migrations/
│   └── xxxx_create_testimonials_table.php   # Baru
└── factories/
    └── TestimonialFactory.php           # Baru (untuk test; + state inactive)

resources/views/
├── pages/tentang-kami.blade.php                     # Diperbarui — sisip <x-sections.testimonials :testimonials="$testimonials" /> setelah "Nilai-Nilai Kami", sebelum <x-sections.cta-band />
└── components/sections/testimonials.blade.php       # Baru — render grid testimoni + bintang; @if kosong → tidak render apa pun

tests/Feature/
├── Admin/TestimonialResourceTest.php    # Baru — US1
└── Pages/AboutPageTestimonialsTest.php  # Baru — US2 (atau perluas AboutPageTest)
```

**Structure Decision**: Perluasan langsung struktur Filament yang sudah ada (`app/Filament/Resources/`) + satu Blade section component baru di `resources/views/components/sections/` (folder yang sudah dipakai `hero`, `why-choose`, dll). `AboutController` yang sekarang mengembalikan `view('pages.tentang-kami')` tanpa data diubah minimal untuk mengirim satu variabel. Pola identik `006-career-crud-admin` untuk resource dan `002-theme-branding-system` untuk section component.

## Complexity Tracking

*Tidak ada pelanggaran constitution — tabel ini kosong.*
