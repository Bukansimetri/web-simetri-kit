# Implementation Plan: Technical Documentation

**Branch**: `025-technical-docs` | **Date**: 2026-09-24 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/025-technical-docs/spec.md`

**Note**: This template is filled in by the `/speckit-plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Tiga dokumen developer di `docs/` (AMC-233): `docs/arsitektur.md` (lapisan, peta direktori, alur data admin → cache → halaman publik, daftar modul, konvensi), `docs/panduan-section.md` (langkah menambah section dengan contoh "Sertifikasi" berbasis pengaturan situs, plus Testimonials sebagai pola modul nyata), dan `docs/panduan-tema.md` (kustomisasi lewat panel Tampilan, alur token `AppearanceSettings` → `theme-vars` → `@theme` Tailwind, menambah font/default/token). README menautkan ketiganya. Satu feature test kecil memastikan semua path dan tautan di dokumen tetap valid seiring perubahan kode. Tidak ada perubahan kode aplikasi.

## Technical Context

**Language/Version**: Markdown (dokumen). Kode yang didokumentasikan: PHP 8.3, Laravel 13.23, Filament 3.3, Livewire 3.8, Tailwind CSS v4 (CSS-first `@theme`), Vite 8

**Primary Dependencies**: Tidak ada dependency baru. Test verifikasi memakai PHPUnit 12 yang sudah ada

**Storage**: N/A (file Markdown di repo)

**Testing**: `tests/Feature/Docs/TechnicalDocsPathsTest.php` (path & tautan valid, lihat research.md §2) + uji baca manual sesuai [quickstart.md](./quickstart.md)

**Target Platform**: Dibaca di GitHub dan editor lokal (Markdown standar GitHub, diagram teks/Mermaid)

**Project Type**: Dokumentasi untuk aplikasi web Laravel + Filament yang sudah ada

**Performance Goals**: N/A

**Constraints**: Bahasa Indonesia; tidak menduplikasi dokumen `docs/` yang sudah ada; path hipotetis hanya di fenced code block; tiap dokumen punya tanggal terakhir diperbarui

**Scale/Scope**: 3 dokumen baru, 1 perubahan README, 1 test baru

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **I. Multi-Client Reusability**: PASS. Dokumentasi mempercepat clone kit ke klien baru dan menegaskan aturan "data klien di settings/seeder, bukan hardcode".
- **II. White-Label by Default**: PASS. Panduan section dan tema mewajibkan token tema, bukan warna/font hardcode, dan menyebut `NoHardcodedClientDataTest`.
- **III. Settings-Driven Theming, No Page Builder**: PASS. Panduan justru mengajarkan jalur resmi (settings + CSS variable + section bernama) dan menyatakan larangan page builder. Batasan AMC-221/222 dinyatakan terbuka.
- **IV. Module Test Coverage**: N/A untuk modul (tidak ada modul baru). Panduan section mewajibkan test untuk setiap section/modul baru. Fitur ini tetap menambah test verifikasi dokumen.
- **V. Simplicity & Dependency Discipline**: PASS. Verifikasi path memakai PHPUnit yang sudah ada, bukan link checker npm.
- **Development Workflow**: Constitution menyebut tracker Jira `SIM`, sementara tracker aktual sudah pindah ke Linear (AMC). Bukan pelanggaran fitur ini, tapi dokumen arsitektur menyebut tracker aktual. Amandemen constitution di luar scope.

**Initial gate result**: PASS, tanpa pelanggaran. Complexity Tracking tidak diperlukan.

**Post-design re-check**: PASS. Desain (research.md, contracts/document-structure.md) tidak menambah dependency atau kode aplikasi; satu-satunya kode baru adalah test read-only.

## Project Structure

### Documentation (this feature)

```text
specs/025-technical-docs/
├── plan.md                        # This file
├── research.md                    # Phase 0: keputusan & fakta kode
├── quickstart.md                  # Phase 1: cara verifikasi
├── contracts/
│   └── document-structure.md      # Phase 1: bagian wajib tiap dokumen
├── checklists/requirements.md     # Dari /speckit-specify
└── tasks.md                       # Phase 2 (/speckit-tasks, belum dibuat)
```

Tidak ada `data-model.md`: fitur ini tidak punya entitas data aplikasi. "Key Entities" di spec adalah dokumen, dan strukturnya didefinisikan di `contracts/document-structure.md`.

### Source Code (repository root)

```text
docs/
├── arsitektur.md                  # BARU (US1)
├── panduan-section.md             # BARU (US2)
├── panduan-tema.md                # BARU (US3)
├── deployment.md                  # sudah ada, hanya ditautkan
├── versioning-strategi-klien.md   # sudah ada, hanya ditautkan
├── checklist-ga4-setup.md         # sudah ada, hanya ditautkan
└── checklist-go-live.md           # sudah ada, hanya ditautkan

README.md                          # UBAH: bagian "Dokumentasi Developer" + versi stack aktual

tests/Feature/Docs/
└── TechnicalDocsPathsTest.php     # BARU: path & tautan di dokumen valid
```

**Structure Decision**: Dokumen ditaruh di folder `docs/` yang sudah ada, satu topik per file seperti dokumen sebelumnya. Test ditaruh di subfolder baru `tests/Feature/Docs/` mengikuti pola pengelompokan per area di `tests/Feature/`.

## Implementation Notes

- Urutan kerja mengikuti prioritas: `arsitektur.md` dulu (MVP, dan dirujuk oleh dua panduan), lalu `panduan-section.md`, lalu `panduan-tema.md`, lalu README dan test.
- Temuan sampingan research.md §4 (cache testimoni di Home tidak diinvalidasi) dicatat di bagian "Hal yang perlu diperhatikan" di `docs/arsitektur.md` dan diusulkan sebagai tiket Linear terpisah. Tidak diperbaiki di fitur ini.
- Jebakan utama untuk panduan tema: font harus ditambahkan di `FONT_OPTIONS` **dan** `vite.config.js` (research.md §5).

## Complexity Tracking

Tidak ada pelanggaran constitution.
