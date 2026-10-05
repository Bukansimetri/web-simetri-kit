# Implementation Plan: Tampil/Sembunyi Section dari Admin

**Branch**: `032-section-visibility` | **Date**: 2026-10-05 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/032-section-visibility/spec.md`

## Summary

Admin dapat menyembunyikan 25 section bernama di situs publik dari satu halaman Filament **Tampilan Section**. Menu yang mengedit isi section menampilkan penanda bila section terkait tersembunyi.

- **Daftar section**: enum `PublicSection` (25 case) menyimpan halaman induk, label, dan menu isi terkait (resource + filter, bila ada).
- **Penyimpanan**: Spatie Settings grup `section_visibility` dengan satu properti `hidden` (daftar kunci yang disembunyikan). Kunci yang tidak ada di daftar berarti tampil, sehingga section baru otomatis tampil (FR-004).
- **Akses publik**: helper `SectionVisibility::shows(PublicSection)` dipakai di Blade untuk membungkus setiap section. Bila settings belum termigrasi, helper menganggap semua section tampil agar urutan deploy tidak bisa membuat situs 500.
- **Admin**:
  - Halaman `SectionVisibilitySettingsPage` di grup **Pengaturan Situs**: toggle per section, dikelompokkan per halaman, dengan jumlah tersembunyi per kelompok dan tautan "Edit isi".
  - Trait `ShowsHiddenSectionNotice` menambahkan penanda ke halaman List menu isi.
  - Kolom badge "Disembunyikan" pada tabel CTA, Blok Halaman, dan FAQ.

## Technical Context

**Language/Version**: PHP 8.3, Laravel 13

**Primary Dependencies**: Filament 3.2, Spatie Laravel Settings (sudah terpasang). Tanpa dependency baru

**Storage**: Tabel `settings` (Spatie) dengan grup baru `section_visibility`, properti `hidden` (array string, default `[]`). Tanpa tabel baru

**Testing**: PHPUnit 12, Livewire test Filament, fixture `LegacyMarkup` (memastikan markup tidak berubah saat semua tampil)

**Target Platform**: Web server Linux; produksi hanya `migrate --force`

**Project Type**: Web application Laravel monolit

**Performance Goals**: Satu pembacaan settings per request (Spatie memuat grup sekali). Tanpa query tambahan per section

**Constraints**:
- Tanpa page builder (Principle III): hanya tampil/sembunyi untuk section bernama, tanpa urutan atau tata letak.
- Markup halaman identik saat semua tampil (SC-002).
- Toggle langsung terlihat (FR-010).

**Scale/Scope**: 25 section, ±12 view publik dibungkus kondisi, 1 halaman admin, penanda di ±13 halaman List admin

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Catatan |
|---|---|---|
| I. Multi-Client Reusability | ✅ | Visibilitas disimpan di settings per instalasi, bawaan semua tampil. Tidak ada data klien di kode. |
| II. White-Label by Default | ✅ | Tidak ada branding baru. |
| III. No Page Builder (NON-NEGOTIABLE) | ✅ | Hanya on/off untuk section bernama yang tetap. Tidak ada penambahan section, pengurutan, atau tata letak dari admin. |
| IV. Module Test Coverage | ✅ | Test halaman admin (akses, simpan), test render publik per section (tersembunyi/tampil), test penanda admin, dan test kesetaraan markup saat semua tampil. |
| V. Simplicity & Dependency Discipline | ✅ | Memakai Spatie Settings yang ada. Satu properti array, bukan 25 kolom boolean. |
| Deployment: seeder demo tidak auto-run | ✅ | Settings migration hanya menambah properti dengan nilai `[]`. |
| Deployment: dokumentasi | ✅ | Manual operator diperbarui (FR-016). |

**Post-design re-check**: tetap lulus.

## Project Structure

### Documentation (this feature)

```text
specs/032-section-visibility/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── section-catalog.md
│   └── admin.md
├── checklists/requirements.md
└── tasks.md             # /speckit-tasks
```

### Source Code (repository root)

```text
app/
├── Enums/PublicSection.php                      # baru: 25 section, page(), label(), contentUrl()
├── Settings/SectionVisibilitySettings.php       # baru: grup section_visibility, ?array $hidden
├── Support/PageContent/SectionVisibility.php    # baru: shows(), hiddenSections(), aman bila settings belum ada
├── Filament/
│   ├── Pages/SectionVisibilitySettingsPage.php  # baru: halaman Tampilan Section
│   ├── Concerns/ShowsHiddenSectionNotice.php    # baru: trait subheading penanda untuk halaman List
│   ├── Support/Pages/ListSectionItems.php       # ubah: pakai trait (7 menu section item sekaligus)
│   └── Resources/
│       ├── CallToActionResource.php             # ubah: badge "Disembunyikan" per baris
│       ├── PageBlockResource.php                # ubah: badge untuk Siapa Kami / Visi
│       ├── FaqItemResource.php                  # ubah: badge per tempat tampil
│       └── {Testimonial,TeamMember,ClientLogo,Product,CallToAction,PageBlock,FaqItem}Resource/Pages/List*.php  # ubah: pakai trait
database/settings/<ts>_create_section_visibility_settings.php   # baru
resources/views/
├── filament/pages/section-visibility.blade.php  # baru (atau pakai settings-form-page)
├── pages/home.blade.php, tentang-kami.blade.php, karir.blade.php,
│   produk/index.blade.php, produk/show.blade.php, artikel/index.blade.php,
│   artikel/show.blade.php, faq.blade.php, kontak.blade.php      # ubah: bungkus @if SectionVisibility::shows(...)
tests/Feature/{Settings,Pages,Admin}/... (baru)
docs/manual-operator.md
```

**Structure Decision**: Mengikuti pola halaman pengaturan yang ada (`SiteSettingsPage` + Spatie Settings) dan pola helper `App\Support\PageContent`.

## Complexity Tracking

Tidak ada pelanggaran konstitusi.
