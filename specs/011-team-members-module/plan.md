# Implementation Plan: Modul Team Members

**Branch**: `011-team-members-module` | **Date**: 2026-09-08 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/011-team-members-module/spec.md`

## Summary

Menambahkan modul "Team Members" — entity baru `TeamMember` (name, position, photo_path, bio, linkedin_url?, order, is_active) dengan CRUD admin (Filament Resource, pola identik `TestimonialResource` dari 008) dan satu section "Tim Kami" baru di halaman **Tentang Kami** (`/tentang-kami`) yang merender anggota **aktif** terurut, tepat setelah section "Nilai-Nilai Kami" dan sebelum section testimoni (modul 008). Foto **wajib** dan di-downscale ke lebar maks 800px + konversi WebP saat simpan lewat `ImageUploads::storeAsWebp(..., maxWidth: 800)` (helper yang sudah diperluas di modul 010). `AboutController` (yang sejak modul 008 & 009 mengirim `$testimonials` + `$clientLogos`) ditambah mengirim `$teamMembers`. Section tidak dirender bila tidak ada anggota aktif. Tidak ada dependency baru.

## Technical Context

**Language/Version**: PHP 8.3, Laravel Framework 13

**Primary Dependencies**: Filament 3.3 (`TeamMemberResource` baru — `TextInput`, `Textarea`, `FileUpload`, `Toggle`). Helper `App\Support\ImageUploads` (sudah ada, sudah mendukung `maxWidth`). Tidak ada dependency baru.

**Storage**: MySQL — migration baru `team_members` (`id`, `name` string, `position` string, `photo_path` string, `bio` text, `linkedin_url` string nullable, `order` integer default 0, `is_active` boolean default true, timestamps). Tidak ada perubahan skema tabel lain.

**Testing**: PHPUnit feature test memakai Livewire test helper untuk Filament Resource (pola `TestimonialResourceTest`) — create/edit/delete, validasi field wajib (name, position, photo, bio), `linkedin_url` kosong diterima + nilai tak valid ditolak, foto tersimpan WebP ≤800px (assert dimensi), toggle aktif; feature test terpisah untuk render section di `/tentang-kami` (hanya anggota aktif, urutan, LinkedIn `target=_blank` vs tanpa LinkedIn, empty-state section tidak dirender, section lain + testimoni + logo klien utuh).

**Target Platform**: Server web Laravel standar

**Project Type**: Web application — perluasan admin panel Filament + satu section publik baru di halaman Blade yang sudah ada

**Performance Goals**: Tidak ada target khusus (admin-only untuk CRUD; section publik satu query `where is_active` + `orderBy order`, volume < 20 baris)

**Constraints**: foto wajib + di-downscale ≤800px lebar + WebP saat simpan, tanpa upscale, tanpa penolakan dimensi (FR-005, klarifikasi Q3); form menampilkan rekomendasi ukuran (FR-005); `linkedin_url` opsional, boleh kosong, bila diisi validasi http/https (FR-004, klarifikasi Q1); section tidak dirender bila tidak ada anggota aktif (FR-011); halaman Tentang Kami hanya disisipi satu section baru setelah "Nilai-Nilai Kami", section eksisting tidak diubah (FR-013, Assumptions); CRUD terbuka semua role panel (FR-012); tidak ada dependency baru (Principle V); tidak ada toggle modul / grouping / halaman detail (Assumptions)

**Scale/Scope**: 1 Filament Resource baru (`TeamMemberResource`) + 1 migration + 1 model + 1 factory + update `AboutController` (tambah 1 variabel) + 1 section Blade baru + edit `tentang-kami.blade.php` (sisip 1 baris include setelah blok `{{-- Nilai --}}`, sebelum `<x-sections.testimonials>`)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Applies? | Assessment |
|---|---|---|
| I. Multi-Client Reusability | Ya | Anggota tim diisi lewat CRUD per klien; section kosong sampai klien mengisi. **PASS** |
| II. White-Label by Default | Tidak langsung | Tidak menyentuh branding/identitas panel. |
| III. Settings-Driven Theming, No Page Builder | Ya (NON-NEGOTIABLE) | Section "Tim Kami" adalah satu Blade section tetap dengan layout tetap — bukan builder. Penempatan hardcoded di `tentang-kami.blade.php` (posisi tetap, FR-013). **PASS** |
| IV. Module Test Coverage | Ya | US1 (CRUD) & US2 (render section) masing-masing punya feature test dasar. **PASS (planned)** |
| V. Simplicity & Dependency Discipline | Ya | Nol dependency baru; reuse `ImageUploads::storeAsWebp` & pola `TestimonialResource`; satu field `linkedin_url` (bukan repeater multi-platform); tidak ada toggle modul / grouping / detail page / carousel yang tidak diminta. **PASS** |

Tidak ada pelanggaran constitution — Complexity Tracking kosong.

## Project Structure

### Documentation (this feature)

```text
specs/011-team-members-module/
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
│   └── TeamMember.php                   # Baru — fillable + casts (order int, is_active bool)
├── Filament/Resources/
│   ├── TeamMemberResource.php           # Baru (pola TestimonialResource)
│   └── TeamMemberResource/Pages/{ListTeamMembers,CreateTeamMember,EditTeamMember}.php
└── Http/Controllers/Public/
    └── AboutController.php               # Diperbarui — tambah $teamMembers (aktif, orderBy order,id)

database/
├── migrations/
│   └── xxxx_create_team_members_table.php   # Baru
└── factories/
    └── TeamMemberFactory.php            # Baru (+ state inactive)

resources/views/
├── pages/tentang-kami.blade.php                     # Diperbarui — sisip <x-sections.team-members :members="$teamMembers" /> setelah blok {{-- Nilai --}}, sebelum <x-sections.testimonials>
└── components/sections/team-members.blade.php       # Baru — render grid kartu tim; @if kosong → tidak render apa pun

tests/Feature/
├── Admin/TeamMemberResourceTest.php     # Baru — US1
└── Pages/AboutPageTeamMembersTest.php   # Baru — US2
```

**Structure Decision**: Perluasan langsung struktur Filament yang sudah ada + satu Blade section component baru di `resources/views/components/sections/`. Pola identik `008-testimonials-module` & `009-client-logos-module`: resource meniru `TestimonialResource`, section meniru `components/sections/testimonials.blade.php`, `AboutController` menambah satu variabel di samping `$testimonials` & `$clientLogos` yang sudah ada. Foto memakai `ImageUploads::storeAsWebp(maxWidth: 800)` seperti modul Portfolio (010).

## Complexity Tracking

*Tidak ada pelanggaran constitution — tabel ini kosong.*
