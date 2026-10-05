---

description: "Task list for Halaman Legal yang Dapat Diedit"
---

# Tasks: Halaman Legal yang Dapat Diedit

**Input**: Design documents from `/specs/034-legal-pages/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/public-page.md, contracts/admin.md, quickstart.md

**Tests**: Disertakan (Principle IV): admin, render publik, dan installer.

**Organization**: Per user story (US1–US4, sesuai spec.md).

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Dapat paralel (berkas berbeda, tanpa ketergantungan pada tugas yang belum selesai)
- **[Story]**: US1–US4
- Desain acuan: `/Users/jumratyahmad/Downloads/stitch_suoer_premium_solar_website/{kebijakan_privasi_suoer,syarat_ketentuan_suoer}/code.html` dan `screen.png`

---

## Phase 1: Setup

- [x] T001 Pastikan branch `034-legal-pages` dari `main` terbaru, lalu jalankan `php artisan test --compact` sebagai baseline

---

## Phase 2: Foundational (memblokir semua story)

- [x] T002 Buat migrasi `php artisan make:migration add_template_and_legal_to_custom_pages_table --no-interaction`: kolom `template` string(20) default `standar`, `legal` json nullable, dan `content` diubah menjadi nullable (`$table->longText('content')->nullable()->change()`); `down()` mengembalikan seperti semula
- [x] T003 [P] Buat enum `app/Enums/CustomPageTemplate.php` (`Standard = 'standar'`, `Legal = 'legal'`) dengan `label()` ("Standar", "Dokumen Legal") dan `options()`
- [x] T004 Ubah `app/Models/CustomPage.php`:
  - tambah `template` dan `legal` ke `fillable`
  - cast `template` → `CustomPageTemplate`, `legal` → `array`
  - helper `isLegal(): bool` dan `legalValue(string $key, mixed $default = null): mixed`
  - `database/factories/CustomPageFactory.php` mendapat state `legal(array $legal = [])`
- [x] T005 Perbarui `tests/Feature/Pages/CustomPagePageTest.php` dan tes lain yang memakai slug `kebijakan-privasi` atau `syarat-ketentuan` (`grep -rn "kebijakan-privasi\|syarat-ketentuan" tests`), agar tidak bentrok dengan halaman yang nanti dipasang migrasi (ganti slug uji, mis. `halaman-uji`). Jalankan tes tersebut

**Checkpoint**: Skema dan model siap; halaman lama tetap Standar.

---

## Phase 3: User Story 1 - Pengunjung Membaca Halaman Legal (Priority: P1) 🎯 MVP

**Goal**: Template Dokumen Legal dirender sesuai desain; template Standar tidak berubah.

**Independent Test**: Buat halaman legal lewat factory dengan semua elemen; buka `/halaman/{slug}` dan periksa susunannya.

### Tests for User Story 1

- [x] T006 [P] [US1] Buat `tests/Feature/Pages/LegalPageRenderTest.php`:
  - **Struktur halaman**:
    - hero memuat judul dan subjudul, tanpa teks "Berlaku Efektif" atau "Terakhir Diperbarui" bila tidak ditulis admin
    - pembuka dan kotak sorotan tampil, dan hilang bila kosong
    - setiap bagian `id="bagian-{n}"` bernomor urut dengan label "PASAL 0n · LABEL" bila label diisi
  - **Daftar isi**: tautan `#bagian-{n}` sesuai urutan, tidak tampil bila tanpa bagian
  - **Kartu**: tampil dalam grid; bagian tanpa kartu tidak punya grid
  - **Kotak kontak**:
    - tombol WhatsApp ke `wa.me` saat nomor bisnis diisi, hilang saat kosong
    - email `mailto:` tampil bila diisi
    - kotak tidak tampil bila judul dan teks kosong
  - **CTA**: tampil bila judul diisi, tautan default `/kontak`
  - **Sanitasi**: `<script>`/`onerror` di `intro` dan `sections.*.body` dibuang
  - **Template Standar**: halaman Standar masih menampilkan `content` mentah seperti tes lama (FR-002)

### Implementation for User Story 1

- [x] T007 [US1] Ubah `app/Http/Controllers/Public/CustomPageController.php` agar merender `pages.custom-page.legal` bila `$customPage->isLegal()`, selain itu view lama tanpa perubahan
- [x] T008 [US1] Buat `resources/views/pages/custom-page/legal.blade.php` sesuai `contracts/public-page.md` dan desain:
  - **Layout**:
    - extend `layouts.public`, dengan `@section('title'/'meta_description'/'og_*')` sama seperti `show.blade.php`
    - `x-sections.page-hero` (breadcrumb judul, subjudul, gambar `PageContent::imageUrl(legal.hero_image_path, 'images/mockup/tentang-kami-3.jpg')`)
    - grid `lg:grid-cols-[300px_minmax(0,1fr)]` dengan sidebar `lg:sticky lg:top-28`
  - **Sidebar**:
    - kartu "Daftar Isi" beranchor
    - tombol PDF (T019)
    - kotak kontak (tombol WhatsApp hijau via `SiteSettings::whatsappUrl(contact_whatsapp_message)`, email)
  - **Isi**:
    - kartu putih pembuka dengan `PageContent::richText`, kotak sorotan biru muda berikon
    - setiap bagian: nomor bulat, label "PASAL 0n · {LABEL}", `<h2>`, isi `PageContent::richText` dengan kelas prose untuk `ul/li/strong/em/a`
    - grid kartu (`sm:grid-cols-2`, `lg:grid-cols-3` bila jumlah kartu habis dibagi 3), catatan miring kecil
  - **CTA**: band biru bila `cta_title` diisi
  - Gunakan token warna dan tipografi yang ada; judul `font-bold`
- [x] T009 [US1] Tambahkan scrollspy daftar isi di `legal.blade.php` dengan Alpine: `IntersectionObserver` (rootMargin `-30% 0px -60% 0px`) menandai tautan aktif; tanpa JS tautan anchor tetap bekerja; di < lg sidebar di atas isi (urutan DOM sidebar lebih dulu)

**Checkpoint**: Halaman legal tampil sesuai desain dari data.

---

## Phase 4: User Story 2 - Admin Mengedit Halaman Legal (Priority: P1)

**Goal**: Form Halaman dengan pilihan template dan semua kolom legal.

**Independent Test**: Lewat Livewire, pilih template Legal, isi bagian dan kartu, simpan, lalu render publik.

### Tests for User Story 2

- [x] T010 [P] [US2] Buat `tests/Feature/Admin/CustomPageLegalTemplateTest.php`:
  - **Template Standar**: bawaan Standar untuk halaman baru; kolom `content` ada dan wajib
  - **Template Legal**:
    - kolom `content` tersembunyi dan tidak wajib
    - kolom `legal.subtitle`, `legal.sections`, `legal.pdf_path`, `legal.cta_title` ada
  - **Simpan**: halaman legal dengan 2 bagian (satu berkartu ikon kurasi) tersimpan ke kolom `legal` sesuai urutan, `content` null
  - **Validasi**: ikon di luar daftar kurasi dan judul bagian kosong ditolak
  - **Ganti template**: Legal → Standar (isi `content`) tetap menyimpan `legal`; kembali ke Legal, data muncul lagi
  - **Tabel**: kolom Template menampilkan "Dokumen Legal"
  - **Halaman lama**: halaman Standar lama dapat diedit tanpa error

### Implementation for User Story 2

- [x] T011 [US2] Ubah `app/Filament/Resources/CustomPageResource.php` `form()` sesuai `contracts/admin.md` dan research R2:
  - `Select::make('template')` (options enum, default Standar, `live()`, required)
  - `RichEditor content` `visible`/`required` hanya Standar
  - `Section::make('Dokumen Legal')->statePath('legal')->visible(Legal)` berisi:
    - **Hero**: `FileUpload hero_image_path` (image, disk public, folder `legal-pages`, WebP via `ImageUploads::storeAsWebp`, maks 10240), `Textarea subtitle` (maks 500)
    - **Pembuka**: `RichEditor intro`
    - **Sorotan**: `Fieldset` dengan `highlight_title` (maks 160) dan `highlight_body` (maks 600)
    - **Bagian**: `Repeater sections` (`reorderable`, `collapsible`, `itemLabel` judul, `defaultItems(0)`) berisi:
      - `label` (maks 60), `title` (wajib, maks 160), `RichEditor body`
      - `Repeater cards` (`defaultItems(0)`): `Select icon` (`MaterialSymbolsIcons::selectOptions()`, `Rule::in(MaterialSymbolsIcons::keys())`, searchable), `title` (wajib, maks 100), `Textarea text` (maks 400)
      - `Textarea note` (maks 400)
    - **Kontak**: `Fieldset` dengan judul, teks, label WhatsApp, pesan WhatsApp, `email`
    - **PDF**: `FileUpload pdf_path` (`acceptedFileTypes(['application/pdf'])`, `maxSize(10240)`, folder `legal-pages/pdf`, `preserveFilenames`), `pdf_label`
    - **CTA**: `Fieldset` dengan judul, teks, label tombol, URL
- [x] T012 [US2] Di `CustomPageResource` `table()` tambah `TextColumn::make('template')->badge()->formatStateUsing(label)`. Di `Pages/CreateCustomPage.php` dan `Pages/EditCustomPage.php`, tanpa perubahan: kolom tersembunyi tidak disimpan sehingga data template lain tetap (dicakup tes ganti template)

**Checkpoint**: Admin dapat membuat dan mengedit halaman legal.

---

## Phase 5: User Story 3 - PDF Opsional (Priority: P2)

**Goal**: Tombol unduh hanya bila PDF ada.

**Independent Test**: Dengan dan tanpa PDF (dan berkas hilang dari disk).

### Tests for User Story 3

- [x] T013 [P] [US3] Tambah tes di `tests/Feature/Pages/LegalPageRenderTest.php`:
  - PDF ada di disk (`Storage::fake('public')`): tombol `download` ke URL berkas dengan label `pdf_label` (default "Unduh Dokumen (PDF)")
  - `pdf_path` kosong atau berkas hilang: tidak ada tombol unduh

  Tambah tes di `tests/Feature/Admin/CustomPageLegalTemplateTest.php`: unggah `.jpg` ke `pdf_path` ditolak; unggah PDF > 10 MB ditolak; unggah PDF valid tersimpan

### Implementation for User Story 3

- [x] T014 [US3] Di `legal.blade.php`, tampilkan tombol unduh di sidebar hanya bila `filled(legal.pdf_path) && Storage::disk('public')->exists(...)`, dengan atribut `download` dan ikon unduh, sesuai desain

**Checkpoint**: PDF opsional berfungsi.

---

## Phase 6: User Story 4 - Isi Awal Otomatis (Priority: P2)

**Goal**: Dua halaman legal dan 5 FAQ terpasang lewat migrasi; seeder manual tanpa duplikasi.

**Independent Test**: Migrasi bersih tanpa seed, lalu buka kedua halaman legal dan FAQ.

### Tests for User Story 4

- [x] T015 [P] [US4] Buat `tests/Feature/Database/LegalPageInstallerTest.php`:
  - **Hasil migrasi bersih**:
    - `kebijakan-privasi` (template Legal, 7 bagian, judul bagian 1 "Informasi yang Kami Kumpulkan", ada kartu di bagian 2, 3, 5)
    - `syarat-ketentuan` (8 bagian berlabel, bagian 5 punya 3 kartu garansi)
  - **Placeholder**:
    - `{app_name}`, `{company_email}`, `{company_phone}`, `{company_address}` terisi dari Pengaturan Umum saat install ulang setelah halaman dihapus
    - cadangan nilai desain dipakai bila data perusahaan kosong
    - tidak ada placeholder `{` tersisa
  - **Idempoten**: install dua kali tetap 2 halaman
  - **Tidak menimpa**: halaman dengan slug sama yang sudah diedit tidak berubah
  - **Seeder**: `LegalPageSeeder` dan `FaqItemSeeder` tanpa duplikasi
  - **Footer**: tautan footer `/halaman/kebijakan-privasi` dan `/halaman/syarat-ketentuan` mengembalikan 200
- [x] T016 [P] [US4] Perbarui `tests/Feature/Database/FaqDefaultsInstallerTest.php`:
  - setelah migrasi tempat `faq` berisi 5 entri aktif berkategori (Instalasi, Produk & Teknologi, Biaya & Penghematan, Garansi, Perawatan), dengan nama merek dari Nama Situs
  - tidak dipasang bila tempat `faq` sudah punya entri
  - ganti tes lama `test_demo_seeder_is_not_required_for_the_defaults`

### Implementation for User Story 4

- [x] T017 [US4] Buat `app/Support/PageContent/DefaultLegalPages.php` dengan `pages(): array` berisi dua halaman (`slug`, `title`, `meta_description`, `legal`). Teks disalin persis dari kedua `code.html`:
  - pembuka, sorotan, setiap bagian (label pasal untuk Syarat & Ketentuan), isi sebagai HTML `<p>`/`<ul><li><strong>…</strong> …</li></ul>`
  - kartu dengan ikon terdekat dari `MaterialSymbolsIcons::keys()`, catatan
  - kotak kontak (judul, teks, label WhatsApp, email `{company_email}`), CTA penutup

  Ganti nama merek dengan `{app_name}` dan data perusahaan dengan placeholder `{company_email}`, `{company_phone}`, `{company_address}`. Hilangkan teks label tanggal. Sediakan juga `fill(array $value): array` untuk mengganti placeholder, dengan cadangan nilai desain bila Pengaturan Umum kosong
- [x] T018 [US4] Buat `app/Support/PageContent/LegalPageInstaller.php` (`install(): void`): lewati bila kolom `template` belum ada; untuk tiap halaman, buat `CustomPage` (template Legal, `content` null, `legal` terisi) hanya bila slug belum ada
- [x] T019 [US4] Di `app/Support/PageContent/DefaultPageContent.php` `faqs()`, tambah kunci `FaqPlacement::Faq->value` berisi 5 entri dari `database/seeders/FaqItemSeeder.php` (dengan `category`, "SUOER" → `{app_name}`). Di `app/Support/PageContent/PageContentInstaller.php` `installFaqs()`, simpan `category` bila ada (null untuk produk/kontak)
- [x] T020 [US4] Ubah `database/seeders/FaqItemSeeder.php` agar `run()` memanggil `PageContentInstaller::install()`. Buat `database/seeders/LegalPageSeeder.php` yang memanggil `LegalPageInstaller::install()` dan daftarkan di `database/seeders/DatabaseSeeder.php`
- [x] T021 [US4] Buat migrasi data `php artisan make:migration install_legal_pages_and_faq_defaults --no-interaction` yang memanggil `PageContentInstaller::install()` lalu `LegalPageInstaller::install()`

**Checkpoint**: Instalasi baru langsung punya halaman legal dan FAQ.

---

## Phase 7: Polish

- [x] T022 [P] Perbarui `docs/manual-operator.md` bagian **Konten Halaman → Halaman**: pilihan **Template** (Standar / Dokumen Legal), kolom legal, cara mengurutkan bagian dan kartu, PDF opsional, dan catatan bahwa teks bawaan halaman legal perlu ditinjau. Jalankan `OperatorManualTest`
- [x] T023 Jalankan `vendor/bin/pint --dirty --format agent`
- [x] T024 Jalankan `php artisan test --compact`; semua lulus
- [x] T025 Jalankan `npm run build`, lalu verifikasi visual kedua halaman legal dengan database sementara (hanya migrasi) pada 360 dan 1440 px:
  - bandingkan dengan `screen.png` desain
  - uji scrollspy daftar isi
  - pastikan tidak ada gulir horizontal
  - uji form admin (template, repeater bagian/kartu, PDF)

---

## Dependencies & Execution Order

- Phase 1 → Phase 2 (T002–T005) memblokir semua story.
- **US1 dan US2** dapat dikerjakan paralel setelah Phase 2 (berkas berbeda: view/controller vs resource), tetapi tes US2 bagian render memakai view US1.
- **US3** setelah US1 (tombol di view yang sama) dan US2 (kolom PDF).
- **US4** setelah Phase 2. `DefaultLegalPages` (T017) bebas paralel, tetapi verifikasi render isi awal butuh US1.
- **Berkas bersama (berurutan)**:
  - `legal.blade.php`: T008 → T009 → T014
  - `LegalPageRenderTest.php`: T006 → T013
  - `CustomPageLegalTemplateTest.php`: T010 → T013
- **Paralel**: T003; T006/T010/T015/T016 (tes berbeda); T017 dengan T008/T011; T022.

### Contoh Paralel

```text
T006 LegalPageRenderTest   T010 CustomPageLegalTemplateTest   T015 LegalPageInstallerTest   T016 FaqDefaultsInstallerTest
T008 legal.blade.php       T011 CustomPageResource            T017 DefaultLegalPages
```

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 + US2. Halaman legal bisa dibuat admin dan tampil sesuai desain.
2. US3 (PDF), lalu US4 (isi awal dari desain dan FAQ). US4 membuat tautan footer langsung berfungsi setelah rilis.
3. Polish: manual, Pint, tes, verifikasi visual.
4. Commit per story.

## Notes

- Isi teks legal disalin persis dari desain. Akurasi hukumnya perlu ditinjau klien sebelum tayang (Assumptions spec).
- Label tanggal dari desain sengaja tidak dibuat (klarifikasi 2026-10-06).
