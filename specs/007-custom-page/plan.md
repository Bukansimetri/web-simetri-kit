# Implementation Plan: Custom Page (Halaman Statis Bebas)

**Branch**: `007-custom-page` | **Date**: 2026-09-07 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/007-custom-page/spec.md`

**Note**: This template is filled in by the `/speckit-plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Menambahkan modul "Custom Page" — entity baru `CustomPage` (title, slug unik, content HTML via rich text editor) dengan CRUD admin (Filament Resource) dan satu route publik dinamis `GET /halaman/{slug}` (route-model-binding, 404 otomatis jika slug tidak ada). Tidak ada draft/publish, tidak ada listing publik — setiap halaman berdiri sendiri. Menutup gap konkret: link footer "Kebijakan Privasi" dan "Syarat & Ketentuan" yang saat ini cuma placeholder ke `/tentang-kami` diarahkan ke slug tetap (`kebijakan-privasi`, `syarat-ketentuan`) di bawah prefix `/halaman/`. Halaman "Tentang Kami" yang sudah ada (desain Blade khusus) TIDAK disentuh sama sekali.

## Technical Context

**Language/Version**: PHP 8.3, Laravel Framework 13

**Primary Dependencies**: Filament 3.3 (`CustomPageResource` baru, `RichEditor` bawaan — pola identik `ArticleResource` dari 005-artikel-crud-admin). Tidak ada dependency baru.

**Storage**: MySQL — migration baru `custom_pages` (`id`, `title`, `slug` unique, `content` longtext, timestamps). Tidak ada perubahan skema tabel lain.

**Testing**: PHPUnit feature test memakai Livewire test helper untuk Filament Resource (pola sama seperti `ArticleResourceTest`) — create/edit/delete, slug auto-generate/override, validasi unique slug, validasi field wajib; feature test terpisah untuk routing publik (`/halaman/{slug}` 200 untuk slug valid, 404 untuk slug tidak ada) dan untuk link footer (FR-012)

**Target Platform**: Server web Laravel standar (sama seperti fitur-fitur sebelumnya)

**Project Type**: Web application — perluasan admin panel Filament & satu route/controller publik baru, tidak ada perubahan struktur project

**Performance Goals**: Tidak ada target khusus (admin-only untuk CRUD; halaman publik `/halaman/{slug}` adalah halaman statis sederhana, tidak ada query kompleks)

**Constraints**: CRUD MUST terbuka untuk semua role admin panel (FR-010); slug Custom Page MUST unik antar sesama Custom Page saja, TIDAK perlu validasi terhadap route statis lain (FR-005, karena prefix `/halaman/` menghilangkan risiko bentrok — Clarifications Q2); halaman "Tentang Kami" MUST TIDAK diubah/dimigrasikan (FR-013, Clarifications Q1); tidak ada dependency baru (Principle V)

**Scale/Scope**: 1 Filament Resource baru (`CustomPageResource`) + 1 migration + 1 route publik baru + 1 controller publik baru + 1 view Blade baru + update 2 link di footer

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Applies? | Assessment |
|---|---|---|
| I. Multi-Client Reusability | Ya | Setiap klien bisa membuat halaman legal/informasi sendiri sesuai kebutuhan mereka lewat CRUD, bukan hardcoded per klien. **PASS** |
| II. White-Label by Default | Tidak langsung | Fitur ini tidak menyentuh branding/identitas panel — tidak relevan. |
| III. Settings-Driven Theming, No Page Builder | Ya (NON-NEGOTIABLE) | Custom Page adalah SATU blok rich-text bebas per halaman untuk konten legal/informasi sederhana — BUKAN mekanisme drag-and-drop/section builder generik; halaman dengan desain khusus (Tentang Kami) tetap Blade section variant tetap, tidak dikonversi (Clarifications Q1). **PASS** |
| IV. Module Test Coverage | Ya | US1 (CRUD) dan US2 (routing publik + link footer) MUST masing-masing punya feature test dasar. **PASS (planned)** |
| V. Simplicity & Dependency Discipline | Ya | Tidak ada dependency baru; tidak ada draft/publish/listing yang tidak diminta; tidak ada mekanisme "halaman mana yang berperan sebagai kebijakan privasi" yang dinamis/dikonfigurasi (dipakai slug konvensi tetap + dokumentasi, research.md §4). **PASS** |

Tidak ada pelanggaran constitution yang butuh entry Complexity Tracking.

## Project Structure

### Documentation (this feature)

```text
specs/007-custom-page/
├── plan.md              # This file (/speckit-plan command output)
├── research.md          # Phase 0 output (/speckit-plan command)
├── data-model.md        # Phase 1 output (/speckit-plan command)
├── quickstart.md        # Phase 1 output (/speckit-plan command)
├── contracts/           # Phase 1 output (/speckit-plan command)
│   └── admin-panel-surface.md
└── tasks.md             # Phase 2 output (/speckit-tasks command - NOT created by /speckit-plan)
```

### Source Code (repository root)

```text
app/
├── Models/
│   └── CustomPage.php               # Baru — title, slug (route key), content; fillable + casts minimal
└── Filament/Resources/
    ├── CustomPageResource.php       # Baru
    └── CustomPageResource/Pages/{ListCustomPages,CreateCustomPage,EditCustomPage}.php

database/
├── migrations/
│   └── xxxx_create_custom_pages_table.php   # Baru
└── factories/
    └── CustomPageFactory.php        # Baru (untuk test)

app/Http/Controllers/Public/
└── CustomPageController.php         # Baru — __invoke(CustomPage $customPage): View

routes/web.php                       # Diperbarui: tambah GET /halaman/{customPage:slug}

resources/views/
├── pages/custom-page/show.blade.php             # Baru — render title + content HTML
└── components/layout/footer.blade.php           # Diperbarui: link Kebijakan Privasi/Syarat Ketentuan → /halaman/{slug}

tests/Feature/
├── Admin/CustomPageResourceTest.php   # Baru — US1
└── Public/CustomPageRoutingTest.php   # Baru — US2
```

**Structure Decision**: Perluasan langsung dari struktur admin panel Filament yang sudah ada (`app/Filament/Resources/`) dan controller publik yang sudah ada (`app/Http/Controllers/Public/`), pola identik `005-artikel-crud-admin`. Tidak ada folder/namespace baru.

## Complexity Tracking

*Tidak ada pelanggaran constitution — tabel ini kosong.*
