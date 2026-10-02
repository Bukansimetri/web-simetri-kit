# Implementation Plan: Kelola Section Konten & CTA dari Panel Admin

**Branch**: `029-why-choose-admin` | **Date**: 2026-09-30 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/029-why-choose-admin/spec.md`

## Summary

Empat section daftar item ("Mengapa Beralih", "Cara Kerja" di beranda; "Mengapa Bergabung", "Proses Rekrutmen" di Karir) dan sembilan blok CTA di beranda, produk, artikel, Tentang Kami, FAQ, dan Karir dipindahkan dari literal Blade ke database. Kontennya dikelola lewat panel Filament.

- **Item section**: satu tabel `section_items` + enum `PageSection` yang memuat aturan per section. Dikelola lewat 4 Filament Resource (satu per section) yang mewarisi kelas dasar bersama.
- **Judul section**: tabel `section_headings`, diedit lewat header action di halaman daftar masing-masing resource.
- **CTA**: tabel `call_to_actions` + enum `CtaPlacement` (9 penempatan tetap). Resource hanya List + Edit.
- **Tampilan identik**: markup/class Blade tidak diubah, hanya literal yang diganti variabel. Dijaga oleh fixture HTML lama + `LegacyMarkupEquivalenceTest`.
- **Data awal**: `DefaultPageContent` (sumber tunggal, nama merek ditulis `{app_name}`) → `PageContentInstaller::install()` (idempotent, kelas aplikasi) → dipanggil migrasi data supaya production (hanya `migrate --force`) langsung punya konten. `PageContentSeeder` hanya mendelegasikan ke installer.

## Technical Context

**Language/Version**: PHP 8.3, Laravel 13

**Primary Dependencies**: Filament 3.2 (panel admin, sudah terpasang), Spatie Permission/Shield (tanpa policy baru), Tailwind (tanpa perubahan class)

**Storage**: MySQL/SQLite (sesuai `.env`). 3 tabel baru: `section_items`, `section_headings`, `call_to_actions`

**Testing**: PHPUnit 12 (`php artisan test`), Livewire test helper Filament, fixture HTML

**Target Platform**: Web server Linux (deploy via `docs/deployment.md`)

**Project Type**: Web application Laravel monolit (Blade publik + panel Filament)

**Performance Goals**: Tambahan ≤ 5 query kecil berindeks per halaman publik (2 per section + 1 per CTA; halaman Karir = 5). Tidak ada regresi waktu render yang terasa

**Constraints**: Output HTML section & CTA identik dengan kode lama (FR-012a). Tanpa dependency baru. Tanpa page builder (Principle III)

**Scale/Scope**: 4 section (≤ 4 item aktif masing-masing), 9 CTA, 8 halaman publik terdampak, 5 menu admin baru

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Catatan |
|---|---|---|
| I. Multi-Client Reusability | ✅ | Konten klien (termasuk nama "SUOER" di judul/CTA) pindah dari Blade ke DB/seeder, sehingga klien baru cukup mengedit dari admin. Aturan desain (batas aktif, penonjolan) ada di enum, bukan data klien. |
| II. White-Label by Default | ✅ | Tidak ada branding starter-kit baru. Teks bermerek kini bisa diganti admin. |
| III. No Page Builder (NON-NEGOTIABLE) | ✅ | Admin hanya mengedit isi pada section bernama. Tidak ada kontrol tata letak, warna, atau varian (FR-012b). Penempatan CTA tetap. |
| IV. Module Test Coverage | ✅ | Test admin untuk 4 resource section + CTA resource, test render publik (tampil/tersembunyi), test seeder idempoten, dan test kesetaraan markup. |
| V. Simplicity & Dependency Discipline | ✅ | Tanpa dependency baru. Satu tabel item alih-alih empat (R1). Tanpa lapisan cache baru (R6). |
| Deployment: seeder demo tidak auto-run | ✅ | Migrasi data memanggil `PageContentInstaller` (kode aplikasi), bukan seeder. Seeder tidak pernah dijalankan otomatis. |
| Deployment: dokumentasi | ✅ | `docs/manual-operator.md` & `docs/panduan-section.md` diperbarui (task dokumentasi). |

**Post-design re-check (setelah Phase 1)**: tetap lulus. Desain di data-model/contracts tidak menambah pelanggaran baru.

## Project Structure

### Documentation (this feature)

```text
specs/029-why-choose-admin/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── public-render.md
│   └── admin-modules.md
├── checklists/requirements.md
└── tasks.md             # /speckit-tasks
```

### Source Code (repository root)

```text
app/
├── Enums/
│   ├── PageSection.php                 # baru
│   └── CtaPlacement.php                # baru
├── Models/
│   ├── SectionItem.php                 # baru (hook saving: penonjolan tunggal)
│   ├── SectionHeading.php              # baru
│   └── CallToAction.php                # baru
├── Rules/
│   └── WithinActiveItemLimit.php       # baru
├── Support/PageContent/
│   ├── DefaultPageContent.php          # baru — sumber tunggal nilai bawaan ({app_name} untuk merek)
│   ├── PageContentInstaller.php        # baru — logika tanam konten idempoten (dipanggil migrasi & seeder)
│   ├── PageContent.php                 # baru — akses data untuk Blade
│   ├── SectionContent.php              # baru — DTO readonly
│   └── IconOptions.php                 # baru — daftar ikon kurasi
└── Filament/
    ├── Support/
    │   ├── SectionItemResource.php     # baru — kelas dasar abstrak
    │   └── Pages/ (ListSectionItems, CreateSectionItem, EditSectionItem — dasar abstrak)
    └── Resources/
        ├── WhyChooseItemResource.php (+ Pages/)
        ├── HowItWorksStepResource.php (+ Pages/)
        ├── CareerValueResource.php (+ Pages/)
        ├── RecruitmentStepResource.php (+ Pages/)
        └── CallToActionResource.php (+ Pages/List, Edit)

database/
├── migrations/
│   ├── 2026_09_30_000001_create_section_items_table.php
│   ├── 2026_09_30_000002_create_section_headings_table.php
│   ├── 2026_09_30_000003_create_call_to_actions_table.php
│   └── 2026_09_30_000004_install_default_page_content.php   # memanggil PageContentInstaller, bukan seeder
├── factories/ (SectionItemFactory, SectionHeadingFactory, CallToActionFactory)
└── seeders/
    ├── PageContentSeeder.php           # baru, hanya memanggil PageContentInstaller; dipanggil DatabaseSeeder
    └── DatabaseSeeder.php              # + $this->call(PageContentSeeder::class)

resources/views/
├── components/sections/
│   ├── why-choose.blade.php            # literal → data
│   ├── how-it-works.blade.php          # literal → data
│   └── cta-band.blade.php              # prop placement
└── pages/
    ├── home.blade.php                  # CTA penutup
    ├── karir.blade.php                 # 2 section + cta-band
    ├── produk/index.blade.php          # 2 CTA
    ├── produk/show.blade.php           # CTA detail
    ├── artikel/index.blade.php         # CTA daftar
    ├── artikel/show.blade.php          # cta-band
    ├── tentang-kami.blade.php          # cta-band
    └── faq.blade.php                   # cta-band

tests/
├── Fixtures/legacy-page-content/*.html          # HTML lama (diambil sebelum refactor)
├── Feature/Admin/SectionItemResourcesTest.php
├── Feature/Admin/CallToActionResourceTest.php
├── Feature/Admin/NavigationStructureTest.php    # + assert 5 menu baru
├── Support/LegacyMarkup.php                     # helper ekstraksi & normalisasi fragmen HTML
├── Feature/Pages/CaptureLegacyPageContentFixturesTest.php   # hanya jalan dengan CAPTURE_LEGACY_FIXTURES=1
├── Feature/Pages/PageContentRenderTest.php
├── Feature/Pages/CallToActionRenderTest.php
├── Feature/Pages/LegacyMarkupEquivalenceTest.php
├── Feature/Database/PageContentSeederTest.php
└── Unit/DefaultPageContentTest.php

docs/
├── manual-operator.md                  # + cara mengelola section & CTA
└── panduan-section.md                  # + pola SectionItem/PageSection untuk section baru
```

**Structure Decision**: Mengikuti struktur Laravel/Filament yang sudah ada. Kelas dasar resource diletakkan di `app/Filament/Support/` (di luar `discoverResources(in: app_path('Filament/Resources'))`) agar tidak ikut terdaftar sebagai resource.

## Rencana implementasi bertahap (mengikuti prioritas story)

1. **Fondasi** (blokir semua story): enum, migrasi, model, factory, `DefaultPageContent`, seeder + migrasi data, `PageContent`, fixture HTML lama (**sebelum** view diubah).
2. **US1 (P1)**: `why-choose` → data, `WhyChooseItemResource` (form/tabel dasar), test render + kesetaraan.
3. **US2 (P2)**: `how-it-works` → data (dekorasi posisi), `HowItWorksStepResource`.
4. **US3 (P3)**: dua section Karir → data, `CareerValueResource`, `RecruitmentStepResource`.
5. **US4 (P4)**: batas aktif (`WithinActiveItemLimit` di form & ToggleColumn), reorder, penonjolan tunggal.
6. **US5 (P5)**: header action "Ubah Judul Section".
7. **US6 (P6)**: `CallToActionResource`, 9 CTA di Blade, token `{produk}`, fallback.
8. **Polish**: NavigationStructureTest, dokumentasi, pint, verifikasi tangkapan layar.

## Risiko & mitigasi

| Risiko | Mitigasi |
|---|---|
| Whitespace/markup bergeser sehingga tampilan berubah halus | Fixture HTML lama + `LegacyMarkupEquivalenceTest`. Loop Blade dipertahankan strukturnya. `multiline()` menghasilkan `<br>` persis |
| Production kehilangan konten karena seeder tidak dijalankan | Migrasi data memanggil `PageContentInstaller` (R4) |
| Judul/CTA production berubah karena `{app_name}` ≠ "SUOER" | Sebelum deploy, pastikan Pengaturan Situs → Nama Situs = "SUOER" (atau `APP_NAME=SUOER` bila Nama Situs kosong). Dicantumkan di quickstart §5 & `docs/deployment.md` |
| Seeder menimpa/menduplikasi editan admin | Seed per section hanya jika heading belum ada. CTA `firstOrCreate`. Diuji `PageContentSeederTest` |
| Test halaman lama (mis. `ProductPageTest`) gagal | Konten ada via migrasi di `RefreshDatabase`, sehingga teks sama tetap tampil |
| Kelas dasar resource ter-discover sebagai resource | Diletakkan di `app/Filament/Support/` + `abstract` |

## Complexity Tracking

Tidak ada pelanggaran. Dua temuan analisis (migrasi menjalankan seeder, dan merek klien tertanam di kode) diselesaikan lewat `PageContentInstaller` dan token `{app_name}` (research R4 & R11).

## Addendum 2026-10-02: Tentang Kami & Info Kontak

**Scope tambahan** (spec US7, US8, FR-027–FR-034): Misi, Nilai, dan Trust Strip Tentang Kami menjadi section item. Hero, Siapa Kami, Visi Tentang Kami, dan Info Kontak menjadi **blok halaman**. Halaman pengaturan lama "Halaman Tentang Kami" dipensiunkan.

**Desain:**

- `PageSection` +3 case (`AboutMission`, `AboutValues`, `AboutTrust`) dengan aturan baru di enum: `hasHeading()`, `hasEyebrow()`, `hasFeaturedCard()`, `itemTitleLabel()`, `itemDescriptionLabel()`, `itemTitleMaxLength()`, `itemDescriptionMaxLength()`, `headingTitleMaxLength()`.
- `section_items.title` → string(120), `description` → string(500). `section_headings` + `eyebrow`, `featured_image_path`, `featured_icon`, `featured_title`, `featured_description`; `title` → string(255), `subtitle` → string(500). Migrasi `create_*` 029 diedit langsung karena fitur belum pernah di-deploy.
- Tabel baru `page_blocks` (`block` unik, `data` JSON) + enum `PageBlockType` (`AboutHero`, `AboutWhoWeAre`, `AboutVision`, `ContactInfo`) yang mendefinisikan kolom tiap blok. Satu `PageBlockResource` (List + Edit, tanpa create/delete), form dirakit per tipe blok.
- `App\Support\PageContent\IconOptions` dihapus, diganti `App\Support\MaterialSymbolsIcons` yang sudah ada (+ `groups`).
- Migrasi data `migrate_about_page_settings_to_page_content` membaca baris `settings` grup `about_page` **langsung dari tabel** (tidak bergantung kelas settings), lalu mengisi section/blok bila belum ada. `{app_name}` di isi Siapa Kami diisi Nama Situs sekali. Instalasi baru: settings migration lama menanam nilai bawaan → disalin. `PageContentInstaller` memakai sumber yang sama.
- Dihapus: `app/Filament/Pages/AboutPageSettingsPage.php`, `app/Settings/AboutPageSettings.php` (+ entri `config/settings.php` bila ada), dan `tests/Feature/Settings/AboutPageSettingsTest.php` diganti test baru (**perlu persetujuan pengguna**). Baris `settings` grup `about_page` di DB **tidak** dihapus (cadangan rollback).
- Pesan WhatsApp: `PageContent::whatsappMessage()` dari blok `ContactInfo` dipakai `kontak.blade.php`, `home.blade.php`, `cta-band.blade.php`.
- Kesetaraan tampilan: fixture baru `tentang-kami-hero`, `tentang-kami-siapa-kami`, `tentang-kami-visi`, `tentang-kami-misi`, `tentang-kami-nilai`, `tentang-kami-trust`, `kontak-info` diambil **sebelum** Blade diubah.

**Constitution re-check**: I ✅ (konten klien tetap di DB, merek diisi sekali dari Nama Situs), III ✅ (blok tetap, bukan page builder; tanpa kontrol tata letak), IV ✅ (test admin + render + migrasi), V ✅ (memakai `MaterialSymbolsIcons` yang ada, satu tabel blok JSON alih-alih tabel per blok).
