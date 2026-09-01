---

description: "Task list for Theme & Branding System"
---

# Tasks: Theme & Branding System

**Input**: Design documents from `/specs/002-theme-branding-system/`

**Prerequisites**: [plan.md](./plan.md), [spec.md](./spec.md), [research.md](./research.md), [data-model.md](./data-model.md), [contracts/theme-settings-surface.md](./contracts/theme-settings-surface.md), [contracts/public-routes.md](./contracts/public-routes.md), [quickstart.md](./quickstart.md)

**Tests**: Disertakan — Constitution Principle IV (Module Test Coverage) mewajibkan feature test dasar sebelum sebuah unit kerja dianggap selesai.

**Organization**: Tasks dikelompokkan per user story (US1/US2/US3, sesuai prioritas P1/P2/P3 di spec.md) supaya masing-masing bisa dikerjakan, ditest, dan dirilis independen.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Bisa dikerjakan paralel (file berbeda, tidak saling bergantung)
- **[Story]**: User story terkait (US1, US2, US3)
- Path file selalu eksplisit

## Path Conventions

Single Laravel + Filament application (lihat [plan.md](./plan.md) § Project Structure) — semua path relatif ke root repo. Frontend publik baru di `app/Http/Controllers/Public/`, `resources/views/{layouts,components,pages}/`, terpisah dari resource Filament admin yang sudah ada.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Verifikasi baseline sebelum perubahan dimulai — belum ada infrastruktur baru yang dibagikan di phase ini (lihat Phase 2).

- [X] T001 Jalankan `vendor/bin/pint --format agent` dan `php artisan test --compact` untuk memastikan baseline hijau sebelum mulai (tidak ada file diubah di task ini)

**Checkpoint**: Baseline project terverifikasi bersih sebelum perubahan dimulai.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Infrastruktur yang dipakai bersama ketiga user story — layout publik (header/footer/nav), CSS variable Theme Settings, dan perluasan `BrandSettings`. Tanpa phase ini, tidak ada halaman publik yang bisa render dengan styling & nav yang benar.

**⚠️ CRITICAL**: T002–T011 harus selesai sebelum task apa pun di Phase 3/4/5 (US1/US2/US3) dimulai.

- [X] T002 [P] Install `alpinejs` via npm dan inisialisasi di `resources/js/app.js` (`import Alpine from 'alpinejs'; window.Alpine = Alpine; Alpine.start()`) — lihat research.md §3
- [X] T003 [P] Tambahkan daftar font kurasi ke `vite.config.js` via helper `bunny()` yang sudah dipakai (Manrope, Be Vietnam Pro, Inter, Poppins, Plus Jakarta Sans, Nunito Sans, Work Sans, Lato) — lihat research.md §5 dan data-model.md § Theme Settings
- [X] T004 Buat settings-migration `database/settings/2026_09_01_000001_add_theme_fields_to_brand_settings.php` yang menambah `secondary_color`, `font_heading`, `font_body` ke group `brand` dengan default Luminous Azure (`#3a5f94`, `Manrope`, `Be Vietnam Pro`), lalu tambahkan properti yang sama di `app/Settings/BrandSettings.php` dan jalankan `php artisan migrate` (FR-003, FR-005)
- [X] T005 [P] Tambahkan media collection `brand-og-image` (single-file, Spatie Media Library) pada `app/Settings/BrandSettings.php` beserta asset fallback default Luminous Azure di `public/images/og-default.jpg` (FR-009, FR-010)
- [X] T006 Perluas `resources/css/app.css` `@theme` dengan token warna, radius, dan spacing lengkap Luminous Azure dari `public/mockup-html/luminous_azure/DESIGN.md` sebagai default CSS statis (research.md §2)
- [X] T007 Buat `resources/views/layouts/partials/theme-vars.blade.php` yang meng-echo CSS custom property `--color-primary`, `--color-secondary`, `--font-heading`, `--font-body` dari `app(BrandSettings::class)` dengan fallback ke default Luminous Azure (FR-002, FR-005; depends on T004)
- [X] T008 Buat `resources/views/layouts/public.blade.php` sebagai layout dasar (include `theme-vars` partial di `<head>`, meta OG default dari `BrandSettings`, `@yield('content')`/slot) (depends on T005, T007)
- [X] T009 [P] Buat `resources/views/components/layout/header.blade.php` (nav + brand name/logo dari `BrandSettings`, konsisten di semua mockup "TopNavBar"/"Navigasi")
- [X] T010 [P] Buat `resources/views/components/layout/footer.blade.php` (konsisten di semua mockup "Footer")
- [X] T011 Sertakan `<x-layout.header>` dan `<x-layout.footer>` di `resources/views/layouts/public.blade.php` (depends on T008, T009, T010)

**Checkpoint**: Layout publik + Theme Settings dasar siap — US1, US2, US3 bisa mulai dikerjakan (paralel oleh developer berbeda bila perlu).

---

## Phase 3: User Story 1 - Pengunjung melihat Home & Produk dengan desain final SUOER (Priority: P1) 🎯 MVP

**Goal**: Halaman Home (dengan kalkulator estimasi) dan Produk (list + detail) tampil sesuai mockup Luminous Azure, memakai data seed Produk, styling dari Theme Settings.

**Independent Test**: Buka `/` dan halaman daftar/detail Produk tanpa login, verifikasi struktur & elemen visual sesuai mockup, verifikasi kalkulator di Home bisa dipakai dan menampilkan hasil estimasi tanpa reload halaman.

### Tests for User Story 1

- [X] T012 [P] [US1] Feature test: `GET /` mengembalikan 200 dan menampilkan section kunci (hero, kalkulator, produk kami), di `tests/Feature/Pages/HomePageTest.php`
- [X] T013 [P] [US1] Feature test: `GET /produk` menampilkan daftar produk seed; `GET /produk/{slug}` menampilkan detail produk dan related products dari kategori sama; slug tidak dikenal mengembalikan 404, di `tests/Feature/Pages/ProductPageTest.php`

### Implementation for User Story 1

- [X] T014 [P] [US1] Buat migration & model `Product` sesuai skema di [data-model.md](./data-model.md#product-seed) (`app/Models/Product.php`, `database/migrations/xxxx_create_products_table.php`)
- [X] T015 [US1] Buat `database/seeders/ProductSeeder.php` berisi 7 produk dari mockup (`produk_suoer_luminous_azure`, `produk_detail_suoer_header_aligned` — nama, harga, spesifikasi, fitur) dan daftarkan di `DatabaseSeeder` (depends on T014)
- [X] T016 [P] [US1] Buat `resources/views/components/sections/hero.blade.php` sesuai mockup Home section "Hero" (`home_suoer_html_calculator_results`)
- [X] T017 [P] [US1] Buat `resources/views/components/sections/why-choose.blade.php` sesuai mockup Home section "Kenapa Pilih SUOER"
- [X] T018 [P] [US1] Buat `resources/views/components/sections/how-it-works.blade.php` sesuai mockup Home section "Cara Kerja" (4 langkah)
- [X] T019 [P] [US1] Buat `resources/views/components/sections/product-card.blade.php` (dipakai Home "Produk Kami", list Produk, dan related products)
- [X] T020 [US1] Buat `resources/views/components/sections/calculator.blade.php` + `resources/js/calculator.js` (komponen Alpine: mode "Per Tagihan"/"Per Alat", update grafik SVG "Investment Line"/"Savings Curve" client-side, validasi input 0/negatif) sesuai mockup Home section "Kalkulator Estimasi Hemat" (FR-006; edge case validasi kalkulator; research.md §4)
- [X] T021 [US1] Rangkai `resources/views/pages/home.blade.php` memakai `layouts.public` + section T016–T020 sesuai urutan mockup `home_suoer_html_calculator_results` (depends on T008, T016–T020)
- [X] T022 [US1] Buat `resources/views/pages/produk/index.blade.php` (daftar produk memakai `product-card`) sesuai mockup `produk_suoer_luminous_azure` (depends on T019)
- [X] T023 [US1] Buat `resources/views/pages/produk/show.blade.php` (showcase, tabel spesifikasi, fitur, related products) sesuai mockup `produk_detail_suoer_header_aligned` (depends on T014, T019)
- [X] T024 [P] [US1] Buat `app/Http/Controllers/Public/HomeController.php` (ambil 3 produk untuk section "Produk Kami")
- [X] T025 [P] [US1] Buat `app/Http/Controllers/Public/ProductController.php` (`index`, `show` by slug dengan route model binding, 404 otomatis, related products dari kategori sama)
- [X] T026 [US1] Daftarkan route `GET /`, `GET /produk`, `GET /produk/{product:slug}` di `routes/web.php` sesuai [contracts/public-routes.md](./contracts/public-routes.md) (depends on T021–T025)

**Checkpoint**: User Story 1 selesai — Home & Produk tampil penuh dengan data seed, bisa dites dan didemo independen dari US2/US3.

---

## Phase 4: User Story 2 - Pengunjung mengakses halaman pendukung company profile (Priority: P2)

**Goal**: Tentang Kami, Kontak, Karir, Artikel, dan FAQ tampil sesuai mockup masing-masing dengan data seed; form Kontak tervalidasi tanpa submit sungguhan.

**Independent Test**: Buka tiap halaman pendukung tanpa login, verifikasi struktur sesuai mockup. Di Kontak, isi form invalid → pesan validasi muncul; isi valid dan submit → konfirmasi tampil tanpa network call/error teknis.

### Tests for User Story 2

- [ ] T027 [P] [US2] Feature test: `GET /tentang-kami` mengembalikan 200 dan menampilkan section kunci (Visi, Misi, Nilai), di `tests/Feature/Pages/AboutPageTest.php`
- [ ] T028 [P] [US2] Feature test: `GET /kontak` mengembalikan 200, menampilkan seluruh field form, dan TIDAK ada route `POST /kontak` terdaftar, di `tests/Feature/Pages/ContactPageTest.php`
- [ ] T029 [P] [US2] Feature test: `GET /karir` hanya menampilkan `JobOpening` dengan `is_active = true`, di `tests/Feature/Pages/CareerPageTest.php`
- [ ] T030 [P] [US2] Feature test: `GET /artikel` menampilkan daftar artikel published terbaru dulu dan empty state wajar saat kosong; `GET /artikel/{slug}` menampilkan detail atau 404, di `tests/Feature/Pages/ArticlePageTest.php`
- [ ] T031 [P] [US2] Feature test: `GET /faq` menampilkan FAQ item terurut sesuai `order`, di `tests/Feature/Pages/FaqPageTest.php`

### Implementation for User Story 2

- [ ] T032 [P] [US2] Buat migration & model `Article` sesuai [data-model.md](./data-model.md#article-seed) (`app/Models/Article.php`)
- [ ] T033 [P] [US2] Buat migration & model `JobOpening` sesuai [data-model.md](./data-model.md#job-opening-seed) (`app/Models/JobOpening.php`)
- [ ] T034 [P] [US2] Buat migration & model `FaqItem` sesuai [data-model.md](./data-model.md#faq-item-seed) (`app/Models/FaqItem.php`)
- [ ] T035 [US2] Buat `database/seeders/ArticleSeeder.php` dari konten mockup `artikel_suoer_consistent_header_footer` dan daftarkan di `DatabaseSeeder` (depends on T032)
- [ ] T036 [US2] Buat `database/seeders/JobOpeningSeeder.php` dari konten mockup `karir_suoer_header_consistent` (3 lowongan) dan daftarkan di `DatabaseSeeder` (depends on T033)
- [ ] T037 [US2] Buat `database/seeders/FaqItemSeeder.php` dari konten mockup `faq_suoer_100_consistent_header_footer` (5 item) dan daftarkan di `DatabaseSeeder` (depends on T034)
- [ ] T038 [P] [US2] Buat `resources/views/components/sections/article-card.blade.php`
- [ ] T039 [P] [US2] Buat `resources/views/components/sections/job-card.blade.php`
- [ ] T040 [P] [US2] Buat `resources/views/components/sections/faq-accordion.blade.php` (komponen Alpine `x-data` untuk expand/collapse)
- [ ] T041 [P] [US2] Buat `resources/views/components/sections/cta-band.blade.php` (dipakai Kontak/Karir/FAQ/Tentang Kami)
- [ ] T042 [US2] Buat `resources/views/pages/tentang-kami.blade.php` sesuai mockup `tentang_kami_suoer_luminous_azure` (depends on T041)
- [ ] T043 [US2] Buat `resources/views/pages/kontak.blade.php` + validasi client-side Alpine (field wajib, format nomor HP) sesuai mockup `kontak_suoer_proportional_fix`, tanpa route POST (FR-007; research.md §7; depends on T041)
- [ ] T044 [US2] Buat `resources/views/pages/karir.blade.php` memakai `job-card` sesuai mockup `karir_suoer_header_consistent` (depends on T039, T041)
- [ ] T045 [US2] Buat `resources/views/pages/artikel/index.blade.php` dan `resources/views/pages/artikel/show.blade.php` memakai `article-card` sesuai mockup `artikel_suoer_consistent_header_footer`, termasuk empty state saat data kosong (depends on T038)
- [ ] T046 [US2] Buat `resources/views/pages/faq.blade.php` memakai `faq-accordion` sesuai mockup `faq_suoer_100_consistent_header_footer` (depends on T040, T041)
- [ ] T047 [P] [US2] Buat `app/Http/Controllers/Public/AboutController.php`
- [ ] T048 [P] [US2] Buat `app/Http/Controllers/Public/ContactController.php` (`show` saja — tanpa method submit, FR-007)
- [ ] T049 [P] [US2] Buat `app/Http/Controllers/Public/CareerController.php` (filter `is_active`)
- [ ] T050 [P] [US2] Buat `app/Http/Controllers/Public/ArticleController.php` (`index`, `show` by slug dengan route model binding, 404 otomatis)
- [ ] T051 [P] [US2] Buat `app/Http/Controllers/Public/FaqController.php`
- [ ] T052 [US2] Daftarkan route `GET /tentang-kami`, `GET /kontak`, `GET /karir`, `GET /artikel`, `GET /artikel/{article:slug}`, `GET /faq` di `routes/web.php` sesuai [contracts/public-routes.md](./contracts/public-routes.md) (depends on T042–T051; edit file yang sama dengan T026, kerjakan setelahnya — bukan paralel)

**Checkpoint**: User Story 2 selesai — seluruh halaman pendukung tampil dengan data seed, independen dari US1/US3 (asalkan Foundational sudah selesai).

---

## Phase 5: User Story 3 - Admin menyesuaikan warna sekunder, font, dan OG image brand (Priority: P3)

**Goal**: Admin bisa melihat & mengubah warna sekunder, font heading/body, dan OG image lewat halaman Brand/Theme Settings, dengan default terisi Luminous Azure.

**Independent Test**: Login sebagai admin, buka Theme Settings, verifikasi default Luminous Azure terisi. Ubah nilai, simpan, verifikasi berlaku di halaman publik tanpa redeploy. Upload OG image, verifikasi meta tag berubah.

**Depends on**: Phase 2 (Foundational) — T004, T005, T007 harus selesai lebih dulu.

### Tests for User Story 3

- [ ] T053 [P] [US3] Feature test: Theme Settings menampilkan default Luminous Azure saat belum diubah; admin bisa mengubah `secondary_color`/`font_heading`/`font_body`/OG image dan tersimpan; submit font di luar daftar kurasi ditolak, di `tests/Feature/Settings/BrandSettingsTest.php` (perluas file yang sudah ada dari Epic 2)
- [ ] T054 [P] [US3] Feature test: meta tag `og:image` halaman publik memakai default Luminous Azure saat belum diatur, dan berubah ke gambar yang diupload admin setelah disimpan, di `tests/Feature/Settings/OgMetaTagTest.php`

### Implementation for User Story 3

- [ ] T055 [US3] Perluas `app/Filament/Pages/BrandSettingsPage.php`: tambahkan `ColorPicker` untuk `secondary_color`, `Select` (opsi kurasi) untuk `font_heading`/`font_body`, dan `FileUpload` untuk `og_image` (FR-003, FR-004, FR-009; depends on T004, T005)
- [ ] T056 [US3] Buat `resources/views/layouts/partials/og-meta.blade.php` yang meng-echo `<meta property="og:image">` dari `BrandSettings` dengan fallback default, sertakan di `resources/views/layouts/public.blade.php` (FR-010; depends on T005, T008)
- [ ] T057 [US3] Pastikan accessor/getter `BrandSettings` fallback ke konstanta default Luminous Azure saat `secondary_color`/`font_heading`/`font_body` dikosongkan kembali oleh admin (edge case reset; depends on T004)

**Checkpoint**: User Story 3 selesai — Theme Settings admin berfungsi penuh, independen dari US1/US2 (asalkan Foundational sudah selesai).

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Verifikasi akhir lintas story sebelum dianggap selesai.

- [ ] T058 [P] Jalankan `vendor/bin/pint --dirty --format agent` untuk merapikan seluruh file PHP yang diubah
- [ ] T059 Jalankan test yang relevan: `php artisan test --compact --filter=PageTest`, `--filter=BrandSettingsTest`, `--filter=OgMetaTagTest`
- [ ] T060 Jalankan `php artisan test --compact` penuh untuk memastikan tidak ada regresi ke test lain (termasuk Epic 2)
- [ ] T061 Ikuti langkah verifikasi manual di [quickstart.md](./quickstart.md) untuk ketiga user story

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Tidak ada dependency — mulai langsung
- **Foundational (Phase 2)**: Memblokir SEMUA user story (layout publik & Theme Settings dasar dipakai di ketiganya) — beda dari Epic 2 yang foundational-nya cuma memblokir satu story
- **US1 (Phase 3)**: Butuh Phase 2 selesai; tidak bergantung pada US2/US3
- **US2 (Phase 4)**: Butuh Phase 2 selesai; tidak bergantung pada US1/US3 secara fungsional, TAPI T052 mengedit `routes/web.php` yang sama dengan T026 (US1) — kerjakan setelah T026 selesai untuk menghindari conflict, bukan karena dependency fungsional
- **US3 (Phase 5)**: Butuh Phase 2 selesai (khususnya T004/T005/T007); tidak bergantung pada US1/US2
- **Polish (Phase 6)**: Setelah story yang ingin dirilis selesai

### Parallel Opportunities

- T002 & T003 (Foundational) paralel — file berbeda (`resources/js/app.js` vs `vite.config.js`)
- T005 paralel dengan T002/T003 — properti settings berbeda dari font/JS
- T009 & T010 (header/footer) paralel — file berbeda
- T012 & T013 (test US1) paralel — file berbeda
- T016–T019 (komponen section US1) paralel — file berbeda, tidak saling bergantung
- T024 & T025 (controller US1) paralel — file berbeda
- T027–T031 (test US2) paralel — file berbeda
- T032, T033, T034 (model US2) paralel — file berbeda
- T038–T041 (komponen section US2) paralel — file berbeda
- T047–T051 (controller US2) paralel — file berbeda
- T053 & T054 (test US3) paralel — file berbeda
- **Antar story**: US1 (Phase 3), US2 (Phase 4), dan US3 (Phase 5) bisa dikerjakan paralel oleh developer berbeda setelah Foundational selesai, KECUALI penyelarasan `routes/web.php` (T026 sebelum T052) yang perlu dikoordinasikan

---

## Parallel Example: Mengerjakan ketiga story sekaligus (3 developer, setelah Foundational)

```bash
# Developer A — User Story 1
Task: "T012–T026: model Product + seeder + section Home/Produk + kalkulator + routes"

# Developer B — User Story 2
Task: "T027–T052: model Article/JobOpening/FaqItem + seeder + halaman pendukung + routes (setelah T026 Developer A selesai)"

# Developer C — User Story 3
Task: "T053–T057: perluas BrandSettingsPage + OG meta + fallback default"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Phase 1: Setup
2. Phase 2: Foundational (layout publik + Theme Settings dasar)
3. Phase 3: User Story 1 (Home + Produk + kalkulator)
4. **STOP & VALIDATE**: Test independen sesuai Independent Test di atas
5. Demo/rilis — halaman inti bisnis (Home, Produk) sudah tampil penuh dengan desain SUOER

### Incremental Delivery

1. Phase 1 → Phase 2 (Foundational) → Phase 3 (US1) → validasi → rilis (MVP)
2. Phase 4 (US2) → validasi → rilis (halaman pendukung company profile)
3. Phase 5 (US3) → validasi → rilis (Theme Settings admin siap untuk instalasi klien lain)
4. Phase 6: Polish setelah ketiganya rilis

### Catatan Constitution

- Setiap story WAJIB punya feature test sebelum ditandai selesai di tracker (Principle IV) — lihat T012/T013, T027–T031, T053/T054.
- Tidak ada task yang menambah sistem varian/page builder di luar yang disepakati klarifikasi (FR-011; Principle III) — hanya 1 desain Luminous Azure per section pada v1.
- Data Produk/Artikel/Karir/FAQ dibangun sebagai model Eloquent + seeder (bukan hardcode di Blade) supaya CRUD Epic 3 di masa depan tinggal "colok" tanpa migrasi ulang (FR-008).

---

## Notes

- [P] = file berbeda / bagian independen, tidak saling bergantung
- [Story] memetakan task ke user story untuk traceability ke Linear (mapping ke Epic 4/AMC-193 — sub-task Linear baru bisa dibuat manual per US bila diperlukan, belum ada tiket AMC dedicated untuk 8 halaman ini karena scope pivot terjadi setelah tiket awal dibuat)
- Commit setelah setiap task atau kelompok task logis
- Berhenti di checkpoint mana pun untuk validasi story secara independen
