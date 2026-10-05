# Implementation Plan: Deskripsi Lowongan Karir dengan Editor Teks Berformat

**Branch**: `035-career-description-editor` | **Date**: 2026-10-06 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/035-career-description-editor/spec.md`

## Summary

Kolom `description` pada `JobOpening` berubah dari teks polos menjadi HTML dari editor teks berformat.

- **Admin**: `Textarea` pada `JobOpeningResource` diganti `RichEditor` (tetap wajib; isi yang tampak kosong ditolak).
- **Data lama**: migrasi data mengubah teks polos yang tersimpan menjadi HTML (escape + paragraf + `<br>`), agar tampil sama di situs dan utuh di editor. Tanpa kolom atau tabel baru.
- **Publik**:
  - Detail lowongan merender deskripsi lewat `PageContent::richText` (`HtmlSanitizer`) dengan kelas gaya prose, bukan lagi teks polos `whitespace-pre-line`.
  - Kartu lowongan dan deskripsi meta memakai ringkasan teks polos dari model (`descriptionExcerpt()`).
- **Sanitizer**: daftar tag yang diizinkan ditambah judul (`h2`–`h4`) dan `blockquote`. Tanpa ini, judul bagian dari editor dibuang dan dijadikan teks biasa.
- **Seeder contoh**: `JobOpeningSeeder` menulis deskripsi dalam bentuk HTML.

## Technical Context

**Language/Version**: PHP 8.3, Laravel 13

**Primary Dependencies**: Filament 3.2 (`RichEditor`), Tailwind. Tanpa dependency baru

**Storage**: Kolom `job_openings.description` (text) tidak berubah tipe; isinya kini HTML

**Testing**: PHPUnit 12, Livewire test Filament, render Blade, tes migrasi data

**Target Platform**: Web; produksi hanya `migrate --force` dan `npm run build`

**Project Type**: Web application Laravel monolit

**Constraints**:
- Lowongan lama tampil tanpa perbedaan visual berarti.
- Semua HTML publik lewat `HtmlSanitizer`.
- Perubahan sanitizer bersifat menambah; modul lain yang memakai `richText` hanya mendapat tag judul/kutipan yang sebelumnya dibuang.

**Scale/Scope**: 1 migrasi data, 1 resource, 2 view, 1 model, 1 sanitizer, 1 seeder, ±3 berkas tes

## Constitution Check

| Principle | Status | Catatan |
|---|---|---|
| I. Multi-Client Reusability | ✅ | Tidak ada teks atau merek klien; konten sepenuhnya milik admin. |
| II. White-Label by Default | ✅ | Tidak ada branding baru. |
| III. No Page Builder (NON-NEGOTIABLE) | ✅ | Hanya editor teks pada satu kolom; tata letak halaman tetap. |
| IV. Module Test Coverage | ✅ | Tes admin (editor, wajib isi), render (detail, kartu, meta), sanitasi, dan migrasi data lowongan lama. |
| V. Simplicity & Dependency Discipline | ✅ | Komponen bawaan; tanpa kolom/tabel/dependency baru. |
| Deployment: seeder demo tidak auto-run | ✅ | Konversi data lama lewat migrasi, bukan seeder. |
| Deployment: dokumentasi | ✅ | Manual operator diperbarui. |

**Post-design re-check**: tetap lulus.

## Project Structure

### Documentation (this feature)

```text
specs/035-career-description-editor/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/public-page.md
├── checklists/requirements.md
└── tasks.md             # /speckit-tasks
```

### Source Code (repository root)

```text
app/Filament/Resources/JobOpeningResource.php          # Textarea → RichEditor
app/Models/JobOpening.php                              # descriptionExcerpt()
app/Support/HtmlSanitizer.php                          # + h2–h4, blockquote
resources/views/pages/karir/show.blade.php             # isi berformat + meta
resources/views/components/sections/job-card.blade.php # ringkasan teks polos
database/migrations/…_convert_job_opening_descriptions_to_html.php
database/seeders/JobOpeningSeeder.php                  # contoh dalam HTML
docs/manual-operator.md
tests/Unit/HtmlSanitizerTest.php                       # tag baru
tests/Feature/Admin/JobOpeningResourceTest.php
tests/Feature/Public/JobOpeningRenderTest.php          # baru
tests/Feature/Database/JobDescriptionConversionTest.php # baru
```

**Structure Decision**: Mengubah berkas yang ada; satu migrasi data dan dua berkas tes baru.

## Complexity Tracking

Tidak ada pelanggaran yang perlu dijustifikasi.
