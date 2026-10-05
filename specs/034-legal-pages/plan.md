# Implementation Plan: Halaman Legal yang Dapat Diedit

**Branch**: `034-legal-pages` | **Date**: 2026-10-06 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/034-legal-pages/spec.md`

## Summary

Modul **Halaman** (`CustomPage`, rute `/halaman/{slug}`) mendapat template kedua, **Dokumen Legal**. Dua halaman legal dan entri FAQ awal dipasang oleh migrasi data.

- **Data**:
  - `custom_pages.template` (string, default `standar`)
  - `custom_pages.legal` (JSON): gambar hero, subjudul, pembuka, sorotan, bagian (label, judul, isi, kartu, catatan), kontak, PDF, CTA
  - `content` menjadi nullable karena halaman legal tidak memakainya
- **Admin**: `CustomPageResource` diberi `Select template` (live). Kolom Standar (Isi Halaman) atau kolom Legal (Section, Repeater bagian dengan Repeater kartu, FileUpload PDF) tampil sesuai pilihan. Data legal tetap tersimpan saat template diganti.
- **Publik**: `CustomPageController` memilih view menurut template.
  - Standar: `pages.custom-page.show`, tidak diubah.
  - Legal: `pages.custom-page.legal` (baru) dengan `x-sections.page-hero`, grid isi + sidebar, daftar isi ber-scrollspy (Alpine + IntersectionObserver), kartu, kotak kontak, tombol PDF, dan CTA band.
- **Isi awal**:
  - `DefaultLegalPages` berisi teks kedua desain dalam struktur JSON di atas, dengan placeholder `{app_name}`, `{company_email}`, `{company_phone}`, `{company_address}`.
  - `LegalPageInstaller` membuat halaman bila slug belum ada.
  - FAQ halaman FAQ ditambahkan ke `DefaultPageContent::faqs()` (tempat `faq`, dari `FaqItemSeeder`), dan `PageContentInstaller::installFaqs()` memasangnya bila tempat itu kosong.
  - Satu migrasi data memanggil kedua installer. `FaqItemSeeder` dan `LegalPageSeeder` baru hanya mendelegasikan ke installer.

## Technical Context

**Language/Version**: PHP 8.3, Laravel 13

**Primary Dependencies**: Filament 3.2 (Repeater, FileUpload, RichEditor), Alpine, Tailwind. Tanpa dependency baru

**Storage**: MySQL/SQLite. Kolom baru `custom_pages.template`, `custom_pages.legal` (json); `content` nullable. Berkas PDF dan gambar hero di disk `public`

**Testing**: PHPUnit 12, Livewire test Filament, render Blade

**Target Platform**: Web; produksi hanya `migrate --force`

**Project Type**: Web application Laravel monolit

**Performance Goals**: Satu query per halaman (data legal di satu kolom JSON)

**Constraints**:
- Template Standar tampil identik.
- Rich text legal dibersihkan dengan `HtmlSanitizer`.
- Label tanggal tidak dibuat (klarifikasi 2026-10-06).
- Tanpa page builder: template bernama dengan bentuk tetap.

**Scale/Scope**: 1 migrasi skema + 1 migrasi data, 1 enum, 2 kelas isi awal/installer, 1 seeder baru, 1 view baru, perubahan resource/controller, ±4 berkas tes

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Catatan |
|---|---|---|
| I. Multi-Client Reusability | ✅ | Teks bawaan memakai placeholder nama situs dan data perusahaan dari Pengaturan Umum; semua isi dapat diedit admin. |
| II. White-Label by Default | ✅ | Tidak ada branding starter-kit; merek dari Nama Situs. |
| III. No Page Builder (NON-NEGOTIABLE) | ✅ | "Dokumen Legal" adalah varian template bernama dengan struktur tetap (hero, bagian, kartu, sidebar). Admin mengisi konten, bukan menyusun tata letak bebas. |
| IV. Module Test Coverage | ✅ | Tes admin (template, kolom legal, PDF), tes render publik (kedua template, PDF opsional, sanitasi, daftar isi), tes installer (idempoten, placeholder, FAQ). |
| V. Simplicity & Dependency Discipline | ✅ | Satu kolom JSON alih-alih tabel bagian/kartu; komponen Filament bawaan; tanpa pembuat PDF. |
| Deployment: seeder demo tidak auto-run | ✅ | Isi legal dan FAQ awal dipasang lewat installer di migrasi (konten tampil, bukan demo); seeder hanya mendelegasikan. |
| Deployment: dokumentasi | ✅ | Manual operator diperbarui (FR-018). |

**Post-design re-check**: tetap lulus.

## Project Structure

### Documentation (this feature)

```text
specs/034-legal-pages/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── public-page.md
│   └── admin.md
├── checklists/requirements.md
└── tasks.md             # /speckit-tasks
```

### Source Code (repository root)

```text
app/
├── Enums/CustomPageTemplate.php                     # baru: Standard ('standar'), Legal ('legal')
├── Models/CustomPage.php                            # ubah: fillable template, legal; cast; helper legal()
├── Http/Controllers/Public/CustomPageController.php # ubah: pilih view per template
├── Filament/Resources/CustomPageResource.php        # ubah: Select template + kolom legal kondisional + kolom tabel Template
├── Support/PageContent/
│   ├── DefaultLegalPages.php                        # baru: isi kedua dokumen dari desain
│   ├── LegalPageInstaller.php                       # baru: pasang halaman legal bila slug belum ada
│   ├── DefaultPageContent.php                       # ubah: faqs() + tempat 'faq'
│   └── PageContentInstaller.php                     # ubah: installFaqs mencakup tempat 'faq'
database/
├── migrations/<ts>_add_template_and_legal_to_custom_pages_table.php
├── migrations/<ts>_install_legal_pages_and_faq_defaults.php
└── seeders/{LegalPageSeeder.php (baru), FaqItemSeeder.php (ubah: delegasi), DatabaseSeeder.php (ubah)}
resources/views/pages/custom-page/legal.blade.php    # baru
tests/Feature/{Pages/LegalPageRenderTest.php, Admin/CustomPageLegalTemplateTest.php, Database/LegalPageInstallerTest.php} (baru), FaqDefaultsInstallerTest (ubah)
docs/manual-operator.md                              # ubah
```

**Structure Decision**: Mengikuti pola `DefaultPageContent` + `PageContentInstaller` (spec 029/031) dan modul Halaman yang ada.

## Complexity Tracking

Tidak ada pelanggaran konstitusi.
