# Implementation Plan: Manual Operator Panel Admin

**Branch**: `028-operator-manual` | **Date**: 2026-09-26 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/028-operator-manual/spec.md`

**Note**: This template is filled in by the `/speckit-plan` command. See `.specify/templates/plan-template.md` for the execution workflow.

## Summary

Satu dokumen `docs/manual-operator.md` (AMC-234) untuk operator klien non-teknis: login dan akun, peta menu panel, Dasbor dan tindak lanjut prospek, langkah per menu konten, pengaturan situs termasuk mode pemeliharaan, pengguna dan peran, jeda tampil di situs, masalah umum, dan istilah. Label panel ditulis tebal persis seperti di layar. Test baru memeriksa bahwa setiap menu yang dirender panel tercakup di manual dan manual bebas kode, path, dan nama vendor, sehingga manual tidak diam-diam tertinggal saat menu berubah. README dan `docs/arsitektur.md` menautkan manual. Tidak ada perubahan kode aplikasi.

## Technical Context

**Language/Version**: Markdown (dokumen). Panel yang didokumentasikan: Filament 3.3 di Laravel 13.23, PHP 8.3, locale `id`

**Primary Dependencies**: Tidak ada dependency baru. Test memakai PHPUnit 12 dan facade `Filament` yang sudah ada

**Storage**: N/A (file Markdown di repo)

**Testing**: `tests/Feature/Docs/OperatorManualTest.php` (baru, research.md §6) + `docs/manual-operator.md` ditambahkan ke data provider `tests/Feature/Docs/TechnicalDocsPathsTest.php` (tanggal dan tautan relatif) + uji label dan uji baca manual sesuai [quickstart.md](./quickstart.md)

**Target Platform**: Dibaca di GitHub, editor lokal, atau diekspor ke PDF oleh developer

**Project Type**: Dokumentasi untuk aplikasi web Laravel + Filament yang sudah ada

**Performance Goals**: N/A

**Constraints**: Bahasa Indonesia non-teknis; label sama persis dengan panel; netral klien; tanpa screenshot; tanggal terakhir diperbarui

**Scale/Scope**: 1 dokumen baru (~11 bagian, 29 menu), 2 perubahan tautan (README, `docs/arsitektur.md`), 1 test baru, 1 baris di test yang sudah ada

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- **I. Multi-Client Reusability**: PASS. Manual ditulis netral ("situs Anda") sehingga dipakai ulang di setiap clone tanpa diedit per klien.
- **II. White-Label by Default**: PASS. Manual tidak menyebut nama starter kit, vendor panel, atau klien; test menolak nama tersebut. Temuan sampingan: grup menu "Content" berlabel Inggris dari plugin (research.md §2), diusulkan sebagai tiket terpisah.
- **III. Settings-Driven Theming, No Page Builder**: N/A. Manual hanya menjelaskan pengaturan Tampilan yang sudah ada; fitur yang ditunda (AMC-221, AMC-222) tidak dibahas.
- **IV. Module Test Coverage**: N/A untuk modul (tidak ada modul baru). Fitur tetap menambah test dokumen.
- **V. Simplicity & Dependency Discipline**: PASS. Satu file Markdown, test memakai PHPUnit dan navigasi panel yang sudah ada, tanpa link checker atau generator dokumen.
- **Deployment & Client Setup Standards**: PASS. Menambah dokumentasi serah terima ke klien; tidak mengubah alur setup.

**Initial gate result**: PASS, tanpa pelanggaran. Complexity Tracking tidak diperlukan.

**Post-design re-check**: PASS. Desain (research.md, contracts/manual-structure.md) tidak menambah dependency atau kode aplikasi; kode baru hanya test read-only.

## Project Structure

### Documentation (this feature)

```text
specs/028-operator-manual/
├── plan.md                        # This file
├── research.md                    # Phase 0: menu aktual, label, fakta perilaku, desain test
├── quickstart.md                  # Phase 1: cara verifikasi
├── contracts/
│   └── manual-structure.md        # Phase 1: aturan penulisan & urutan bagian
├── checklists/requirements.md     # Dari /speckit-specify
└── tasks.md                       # Phase 2 (/speckit-tasks, belum dibuat)
```

Tidak ada `data-model.md`: fitur ini tidak punya entitas data aplikasi. Struktur manual didefinisikan di `contracts/manual-structure.md`.

### Source Code (repository root)

```text
docs/
├── manual-operator.md             # BARU (US1–US4)
└── arsitektur.md                  # UBAH: tautan di "Dokumen terkait"

README.md                          # UBAH: tautan manual di bagian dokumentasi

tests/Feature/Docs/
├── OperatorManualTest.php         # BARU: cakupan menu, bebas kode/vendor, tautan masuk
└── TechnicalDocsPathsTest.php     # UBAH: tambah docs/manual-operator.md ke data provider
```

**Structure Decision**: Manual ditaruh di folder `docs/` yang sudah ada, satu file dengan daftar isi. Test di `tests/Feature/Docs/` mengikuti test dokumentasi AMC-233.

## Implementation Notes

- **Urutan kerja** mengikuti prioritas spec: kerangka + bagian 1–3 dan "Mengelola konten" (US1, MVP) dulu, lalu Dasbor dan prospek (US2), lalu pengaturan situs (US3), lalu pengguna dan peran (US4), lalu masalah umum, istilah, dan tautan.
- **Sumber label**: baca `form()` dan `table()` tiap resource di `app/Filament/Resources/` serta tiap halaman di `app/Filament/Pages/` saat menulis subbagiannya. Jangan menulis label dari ingatan.
- **Test ditulis lebih dulu** dan dibiarkan gagal sampai manual lengkap: test cakupan menu adalah daftar periksa otomatis untuk bagian 3 dan 5.
- **Jangan menjanjikan pembatasan peran** yang belum ada (research.md §4): saat ini hanya Peran, Media Manager, dan Log Aktivitas yang dibatasi izin.
- **Temuan sampingan** (tidak diperbaiki di fitur ini, diusulkan sebagai tiket Linear):
  - Grup menu "Content" / "Media Manager" berlabel Inggris.
  - Komentar di `database/seeders/RoleSeeder.php` menyebut super_admin mendapat akses penuh lewat `Gate::before`, padahal `config/filament-shield.php` memakai `define_via_gate: false`, sehingga super_admin di instalasi baru butuh izin yang di-generate (`shield:generate`) agar bisa membuka menu Peran dan Media Manager.
- **Dampak ke production**: tidak ada. Hanya dokumen dan test.

## Complexity Tracking

Tidak ada pelanggaran constitution.
