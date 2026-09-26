---

description: "Task list for 025-technical-docs (AMC-233)"
---

# Tasks: Technical Documentation

**Input**: Design documents from `/specs/025-technical-docs/`

**Prerequisites**: plan.md, spec.md, research.md, contracts/document-structure.md, quickstart.md

**Tests**: Satu test verifikasi dokumen diminta oleh plan (research.md §2, FR-015/SC-004). Tidak ada test lain; uji baca dilakukan manual lewat quickstart.md.

**Organization**: Tasks dikelompokkan per user story supaya tiap dokumen bisa dikerjakan dan diuji sendiri.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Bisa dikerjakan paralel (file berbeda, tidak bergantung task yang belum selesai)
- **[Story]**: User story terkait (US1, US2, US3)

## Aturan penulisan untuk semua task dokumen

Berlaku untuk setiap task yang menulis `docs/*.md` (sumber: contracts/document-structure.md):

- Bahasa Indonesia, gaya mengikuti `docs/deployment.md`.
- Baris setelah judul: `**Terakhir diperbarui**: 2026-09-24` (pakai tanggal saat ditulis).
- Path repo yang sudah ada ditulis sebagai inline code dan harus benar-benar ada.
- Path file hipotetis (contoh) hanya di dalam fenced code block.
- Jangan menyalin isi `docs/deployment.md`, `docs/versioning-strategi-klien.md`, `docs/checklist-ga4-setup.md`, `docs/checklist-go-live.md`; cukup tautan relatif.
- Sebelum menulis sebuah fakta dari research.md §4/§5, cek ulang di kode (file bisa sudah berubah).

---

## Phase 1: Setup

**Purpose**: Siapkan lokasi test verifikasi.

- [X] T001 Buat file test kosong dengan `php artisan make:test --phpunit Docs/TechnicalDocsPathsTest --no-interaction`, menghasilkan `tests/Feature/Docs/TechnicalDocsPathsTest.php`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Test yang menjadi "definition of done" otomatis untuk ketiga dokumen.

**⚠️ CRITICAL**: Selesaikan sebelum menulis dokumen, supaya tiap dokumen langsung bisa diverifikasi.

- [X] T002 Implementasi `tests/Feature/Docs/TechnicalDocsPathsTest.php`:
  - Data provider bernama `arsitektur`, `panduan-section`, `panduan-tema` yang masing-masing mengembalikan path `docs/arsitektur.md`, `docs/panduan-section.md`, `docs/panduan-tema.md`, supaya tiap dokumen bisa diuji sendiri dengan `--filter`.
  - `test_document_exists_and_has_last_updated_date`: file ada dan memuat baris yang cocok dengan regex `/^\*\*Terakhir diperbarui\*\*: \d{4}-\d{2}-\d{2}$/m`.
  - `test_inline_repo_paths_exist`: buang semua fenced code block (```` ``` ````...```` ``` ````) dulu, lalu ambil isi inline code yang diawali `app/`, `resources/`, `routes/`, `database/`, `config/`, `tests/`, `docs/`, `public/`, `.specify/`, atau sama dengan salah satu file root (`vite.config.js`, `composer.json`, `package.json`, `README.md`). Buang suffix `:angka` atau `#anchor`. Tiap path harus ada via `file_exists(base_path($path))`. Pesan gagal menyebut path dan nama dokumen.
  - `test_relative_markdown_links_resolve`: ambil tautan Markdown `[teks](target)` yang bukan `http(s)://`, `mailto:`, atau `#anchor` saja; buang bagian `#anchor`; resolve relatif ke folder dokumen; harus ada.
  - Ikuti gaya test lain di `tests/Feature/` (PHPUnit class, return type `void`, PHPDoc singkat). Jalankan `vendor/bin/pint --dirty --format agent`.

**Checkpoint**: Test ada dan gagal dengan pesan jelas "dokumen belum ada" untuk ketiga dokumen.

---

## Phase 3: User Story 1 - Developer baru memahami arsitektur project (Priority: P1) 🎯 MVP

**Goal**: `docs/arsitektur.md` memberi peta lengkap project (FR-001 s.d. FR-005).

**Independent Test**: `php artisan test --compact --filter='TechnicalDocsPathsTest.*arsitektur'` lulus, lalu uji baca 5 skenario di quickstart.md §2.

- [X] T003 [US1] Buat `docs/arsitektur.md` dengan judul, baris tanggal, paragraf pembuka (pembaca & kapan dipakai), dan heading kosong sesuai tabel `docs/arsitektur.md` di contracts/document-structure.md
- [X] T004 [US1] Isi bagian "Gambaran umum" di `docs/arsitektur.md`: tujuan kit, versi aktual dari `composer show` dan `package.json` (Laravel 13, Filament 3.3, Livewire 3, Spatie Settings 3, Tailwind CSS v4 CSS-first, Vite 8, PHP 8.3), lima prinsip constitution satu kalimat masing-masing + tautan ke `../.specify/memory/constitution.md`, dan catatan bahwa tracker aktual adalah Linear (tim Amaya ECOM, project Web Solarpanel Kit)
- [X] T005 [US1] Isi bagian "Lapisan & alur data" di `docs/arsitektur.md`: diagram Mermaid alur admin panel (`app/Filament/`) → model (`app/Models/`) / pengaturan (`app/Settings/`) → cache (`app/Concerns/CachesPublicPages.php`) → controller publik (`app/Http/Controllers/Public/`) → view (`resources/views/pages/`, `resources/views/layouts/public.blade.php`) → section (`resources/views/components/sections/`). Jelaskan TTL 5 menit, pola key `public-page:{halaman}`, dan dua cara invalidasi (hook `saved`/`deleted` di model seperti `app/Models/Banner.php`; `Cache::forget` saat halaman pengaturan disimpan seperti `app/Filament/Pages/AboutPageSettingsPage.php`). Jelaskan juga jalur SEO (`app/Concerns/HasSeoMetadata.php`, `app/Support/Seo/`) dan sitemap/robots (`app/Http/Controllers/Public/SitemapController.php`)
- [X] T006 [P] [US1] Isi bagian "Peta direktori" di `docs/arsitektur.md`: tabel folder → fungsi → contoh file untuk `app/Filament/Resources/`, `app/Filament/Pages/`, `app/Settings/`, `app/Models/`, `app/Http/Controllers/Public/`, `app/Concerns/`, `app/Services/`, `app/Support/`, `app/Enums/`, `app/Mail/`, `app/Notifications/`, `app/Policies/`, `app/Console/`, `app/Providers/Filament/`, `database/migrations/`, `database/settings/`, `database/seeders/`, `resources/views/pages/`, `resources/views/components/sections/`, `resources/views/components/layout/`, `resources/views/layouts/partials/`, `resources/css/app.css`, `routes/web.php`, `tests/Feature/`
- [X] T007 [P] [US1] Isi bagian "Daftar modul konten" di `docs/arsitektur.md`: satu baris per modul (Produk & Kategori, Artikel & Kategori Artikel, Portfolio & Kategori, Team Members, Testimonials, Client Logos, Banner, Karir, Custom Page, FAQ, Menu Builder, Contact Us, Calculator Lead, Electricity Appliance) dengan kolom model, resource admin, halaman/section publik, test admin (`tests/Feature/Admin/*ResourceTest.php`) dan key cache yang diinvalidasi. Telusuri tiap modul di kode; tulis "tidak ada" bila kolom memang kosong (misal FAQ tanpa resource admin)
- [X] T008 [P] [US1] Isi bagian "Pengaturan situs" di `docs/arsitektur.md`: tabel tiap kelas di `app/Settings/` → halaman admin di `app/Filament/Pages/` (label navigasi) → di mana dipakai di tampilan (grep nama kelas di `resources/views/`), plus catatan bahwa perubahan struktur Settings butuh migrasi di `database/settings/`
- [X] T009 [P] [US1] Isi bagian "Konvensi" di `docs/arsitektur.md`: label panel admin berbahasa Indonesia; grup navigasi didefinisikan urut di `app/Providers/Filament/AdminPanelProvider.php` (sebut kesembilan grup); nama route berbahasa Indonesia di `routes/web.php`; tiap modul wajib feature test (Principle IV); `vendor/bin/pint --dirty --format agent` sebelum commit; white-label dijaga `tests/Feature/Public/NoHardcodedClientDataTest.php`; alur spec-kit (`specs/NNN-nama/`)
- [X] T010 [US1] Isi bagian "Hal yang perlu diperhatikan" di `docs/arsitektur.md`: (1) `app/Models/Testimonial.php` hanya menghapus cache `public-page:tentang-kami` padahal Home juga menampilkan testimoni, jadi perubahan bisa terlambat sampai 5 menit di Home (cek ulang di kode sebelum menulis; hapus poin ini bila sudah diperbaiki); (2) aturan umum: setiap data baru yang tampil di halaman ber-cache harus menghapus key halaman tersebut
- [X] T011 [US1] Isi bagian "Dokumen terkait" di `docs/arsitektur.md` dengan tautan relatif ke `deployment.md`, `versioning-strategi-klien.md`, `checklist-ga4-setup.md`, `checklist-go-live.md` (tautan ke dua panduan ditambahkan di T026)
- [X] T012 [US1] Jalankan `php artisan test --compact --filter='TechnicalDocsPathsTest.*arsitektur'` dan perbaiki sampai lulus

**Checkpoint**: `docs/arsitektur.md` lengkap dan terverifikasi. MVP bisa di-review.

---

## Phase 4: User Story 2 - Developer menambah section baru (Priority: P2)

**Goal**: `docs/panduan-section.md` memandu menambah section dari nol sampai test (FR-006 s.d. FR-008, FR-011).

**Independent Test**: `php artisan test --compact --filter='TechnicalDocsPathsTest.*panduan-section'` lulus, lalu uji ikut panduan di quickstart.md §3.

- [X] T013 [US2] Buat `docs/panduan-section.md` dengan judul, baris tanggal, paragraf pembuka, dan heading kosong sesuai tabel `docs/panduan-section.md` di contracts/document-structure.md
- [X] T014 [US2] Isi bagian "Batasan saat ini" di `docs/panduan-section.md`: belum ada pemilih varian section di panel admin (AMC-221, ditunda); varian saat ini dibuat sebagai komponen bernama terpisah dan dipilih di kode halaman, contoh nyata `resources/views/components/sections/hero.blade.php` vs `resources/views/components/sections/hero-slider.blade.php` di `resources/views/pages/home.blade.php`; page builder dilarang (Principle III)
- [X] T015 [US2] Isi bagian "Pilih sumber data" di `docs/panduan-section.md`: tabel kriteria pengaturan situs (konten tunggal/sedikit, jarang berubah, contoh `app/Settings/AboutPageSettings.php`) vs modul konten (daftar item, CRUD, urutan, aktif/nonaktif, contoh Testimonials) vs statis di Blade (hanya teks desain yang sama untuk semua klien; tidak boleh berisi data klien)
- [X] T016 [US2] Isi bagian "Langkah-langkah" di `docs/panduan-section.md` (8 langkah sesuai contract): komponen di `resources/views/components/sections/` dengan `@props`; pakai class token tema (`text-primary`, `bg-secondary`, `font-headline-lg`, dst. dari `resources/css/app.css`), bukan warna hex; data dikirim dari controller di `app/Http/Controllers/Public/` atau dibaca dari Settings; pasang dengan `<x-sections.nama />` di `resources/views/pages/`; empty state dengan membungkus section dalam `@if` seperti `resources/views/components/sections/testimonials.blade.php`; tambah data ke closure `rememberPublicPage` dan invalidasi key; test di `tests/Feature/Public/`; Pint + `npm run build`
- [X] T017 [US2] Isi bagian "Contoh lengkap: section Sertifikasi" di `docs/panduan-section.md`: section hipotetis di Home berbasis pengaturan situs. Semua path dan kode di dalam fenced code block: properti baru di Settings + migrasi `database/settings/`, field di halaman admin, komponen `sections/certifications.blade.php` dengan empty state, pemasangan di `pages/home.blade.php`, invalidasi `public-page:home` saat pengaturan disimpan, dan satu feature test (render saat terisi, tidak tampil saat kosong). Pastikan contoh konsisten dengan pola kode nyata (cek `app/Filament/Pages/AboutPageSettingsPage.php` dan `tests/Feature/Settings/AboutPageSettingsTest.php`)
- [X] T018 [US2] Isi bagian "Contoh modul nyata: Testimonials" di `docs/panduan-section.md`: daftar file nyata beserta perannya: `app/Models/Testimonial.php`, `app/Filament/Resources/TestimonialResource.php`, `resources/views/components/sections/testimonials.blade.php`, `app/Http/Controllers/Public/HomeController.php`, `tests/Feature/Admin/TestimonialResourceTest.php`, `database/seeders/TestimonialSeeder.php`, dan migrasinya di `database/migrations/` (cari nama file aktual)
- [X] T019 [US2] Isi bagian "Checklist selesai" di `docs/panduan-section.md`: token tema dipakai, empty state aman, cache diinvalidasi, test lulus, `NoHardcodedClientDataTest` lulus, Pint, build aset, dokumentasi arsitektur diperbarui bila menambah modul
- [X] T020 [US2] Jalankan `php artisan test --compact --filter='TechnicalDocsPathsTest.*panduan-section'` dan perbaiki sampai lulus

**Checkpoint**: Panduan section lengkap dan terverifikasi, bisa dipakai tanpa panduan tema.

---

## Phase 5: User Story 3 - Developer menyesuaikan atau menambah tema (Priority: P3)

**Goal**: `docs/panduan-tema.md` membedakan kustomisasi lewat panel vs perluasan lewat kode (FR-009 s.d. FR-011).

**Independent Test**: `php artisan test --compact --filter='TechnicalDocsPathsTest.*panduan-tema'` lulus, lalu uji tambah font di quickstart.md §4.

- [X] T021 [P] [US3] Buat `docs/panduan-tema.md` dengan judul, baris tanggal, paragraf pembuka, dan heading kosong sesuai tabel `docs/panduan-tema.md` di contracts/document-structure.md
- [X] T022 [US3] Isi bagian "Tanpa kode (panel admin)" dan "Alur token" di `docs/panduan-tema.md`: field di halaman Tampilan (`app/Filament/Pages/AppearanceSettingsPage.php`, menu Pengaturan Situs → Tampilan) dan efeknya; alur 4 langkah research.md §5 lewat `app/Settings/AppearanceSettings.php` → `resources/views/layouts/partials/theme-vars.blade.php` → blok `@theme` di `resources/css/app.css` → class Tailwind; sebut token turunan yang tidak diekspos ke admin
- [X] T023 [US3] Isi bagian "Menambah font" di `docs/panduan-tema.md`: tambah ke `FONT_OPTIONS` di `app/Settings/AppearanceSettings.php` **dan** `bunny()` di `vite.config.js` (dengan weight yang dibutuhkan heading/body), `npm run build`, cara cek di DevTools; peringatan jelas tentang gejala bila hanya salah satu yang diubah
- [X] T024 [US3] Isi bagian "Mengubah default tema" dan "Menambah token baru" di `docs/panduan-tema.md`: samakan konstanta `DEFAULT_*` di `app/Settings/AppearanceSettings.php` dengan fallback `:root` di `resources/css/app.css`; langkah token baru (properti + migrasi `database/settings/` + field di `AppearanceSettingsPage` + echo di `theme-vars.blade.php` dengan fallback + pemetaan di `@theme` + test di `tests/Feature/Settings/AppearanceSettingsTest.php`) dengan contoh token hipotetis `accent_color` di fenced code block
- [X] T025 [US3] Isi bagian "Batasan saat ini" dan "Checklist selesai" di `docs/panduan-tema.md`: live preview tema belum ada (AMC-222, ditunda), admin harus simpan lalu buka halaman publik; checklist: default & fallback sinkron, font di dua tempat, build aset, test Settings lulus
- [X] T026 [US3] Jalankan `php artisan test --compact --filter='TechnicalDocsPathsTest.*panduan-tema'` dan perbaiki sampai lulus

**Checkpoint**: Ketiga dokumen berdiri sendiri dan terverifikasi.

---

## Phase 6: Polish & Cross-Cutting Concerns

- [X] T027 Tambahkan tautan silang: bagian "Dokumen terkait" di `docs/arsitektur.md` menautkan `panduan-section.md` dan `panduan-tema.md`; kedua panduan menautkan `arsitektur.md` dan satu sama lain
- [X] T028 [P] Perbarui `README.md`: tambah bagian "Dokumentasi Developer" yang menautkan `docs/arsitektur.md`, `docs/panduan-section.md`, `docs/panduan-tema.md`; perbarui bagian Tech Stack ke versi aktual (Laravel 13, Filament v3.3, Tailwind CSS v4)
- [X] T029 Jalankan `php artisan test --compact tests/Feature/Docs/TechnicalDocsPathsTest.php` (semua dokumen) dan `vendor/bin/pint --dirty --format agent`
- [X] T030 Cek manual FR-013/SC-005 sesuai quickstart.md §5: tidak ada paragraf yang disalin dari dokumen `docs/` lain, semua dokumen tertaut dari README
- [X] T031 Laporkan ke user: temuan cache testimoni di Home (research.md §4) dan tawarkan membuat tiket Linear terpisah; jangan membuat tiket tanpa persetujuan

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)** → **Foundational (Phase 2)** → user stories.
- **US1, US2, US3** (Phase 3-5): masing-masing hanya bergantung pada Phase 2. Urutan yang disarankan P1 → P2 → P3 karena kedua panduan merujuk konsep di arsitektur, tapi secara teknis bisa paralel (file berbeda).
- **Polish (Phase 6)**: setelah ketiga user story selesai (T027 butuh ketiga file ada).

### Within Each User Story

- Task "Buat ... dengan heading kosong" dulu, lalu task isi bagian, lalu task jalankan test.
- Task isi bagian di file yang sama tidak diberi [P] kecuali bagiannya independen (T006-T009 menulis heading berbeda di `docs/arsitektur.md` dan bisa digarap terpisah, tetapi hindari menyunting file yang sama secara bersamaan).

### Parallel Opportunities

- T006, T007, T008, T009 (bagian independen `docs/arsitektur.md`) bisa diriset paralel lalu digabung.
- T021 (skeleton panduan tema) bisa dikerjakan paralel dengan Phase 4.
- T028 (README) paralel dengan T027.

## Parallel Example: User Story 1

```text
# Setelah T003-T005 selesai, riset keempat tabel ini bersamaan:
T006 Peta direktori di docs/arsitektur.md
T007 Daftar modul konten di docs/arsitektur.md
T008 Pengaturan situs di docs/arsitektur.md
T009 Konvensi di docs/arsitektur.md
```

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Phase 1 + Phase 2 (test verifikasi).
2. Phase 3 (`docs/arsitektur.md`).
3. **Stop & validasi**: test `arsitektur` lulus + uji baca quickstart.md §2.

### Incremental Delivery

1. MVP arsitektur → review.
2. Panduan section → test + uji ikut panduan.
3. Panduan tema → test + uji tambah font.
4. Polish: tautan silang, README, laporan temuan.
