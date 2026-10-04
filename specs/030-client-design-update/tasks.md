---

description: "Task list for Penyesuaian Desain Website Sesuai Dokumen Klien"
---

# Tasks: Penyesuaian Desain Website Sesuai Dokumen Klien

**Input**: Design documents from `/specs/030-client-design-update/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/public-routes.md, quickstart.md

**Tests**: Disertakan. Konstitusi (Principle IV) mewajibkan test fitur untuk modul baru (Langganan Newsletter), dan halaman yang diubah perlu test render yang diperbarui.

**Organization**: Dikelompokkan per user story agar tiap story dapat dikerjakan dan diuji sendiri.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Dapat paralel (berkas berbeda, tanpa ketergantungan pada tugas yang belum selesai)
- **[Story]**: US1 sampai US7 sesuai spec.md
- Semua path relatif terhadap root repo

## Path Conventions

Laravel monolit: `app/`, `database/`, `resources/views/`, `routes/`, `tests/`.

---

## Phase 1: Setup

**Purpose**: Memastikan titik awal hijau sebelum mengubah apa pun.

- [X] T001 Jalankan `php artisan test --compact --filter='HomePageTest|ProductPageTest|ArticlePageTest|PortfolioPageTest|PageBannerRenderTest|PageBannerResourceTest|LegacyMarkupEquivalenceTest'` dan catat tes yang sudah gagal sebelum perubahan (pekerjaan banner admin belum di-commit)
- [X] T002 Periksa bahwa pekerjaan banner admin yang belum di-commit lengkap: `app/Filament/Resources/PageBannerResource.php`, `app/Filament/Clusters/BannerCluster.php`, `database/migrations/2026_10_04_135442_install_page_banner_blocks.php`, dan baris `ArticlesHero`/`PortfolioHero` di `app/Support/PageContent/DefaultPageContent.php`; perbaiki kekurangan bila ada

---

## Phase 2: Foundational

**Purpose**: Prasyarat yang dipakai lebih dari satu story.

- [X] T003 Tambahkan gambar bawaan untuk `ArticlesHero` dan `PortfolioHero` di `defaultImagePath()` dan lebar unggah di `imageMaxWidth()` dalam `app/Enums/PageBlockType.php` (pilih dari `public/images/mockup/*`, mis. `home-2.jpg` dan `home-4.jpg`, setelah melihat gambarnya)
- [X] T004 Pastikan `DefaultPageContent` di `app/Support/PageContent/DefaultPageContent.php` memuat judul "Wawasan & Artikel" dan "Portofolio Proyek" beserta subjudul sesuai dokumen klien, dan `PageContentInstaller` menanamnya idempoten di `app/Support/PageContent/PageContentInstaller.php`

**Checkpoint**: Hero Artikel dan Portofolio punya data dan gambar bawaan.

---

## Phase 3: User Story 1 - Hero Banner Artikel & Portofolio (Priority: P1) 🎯 MVP

**Goal**: Artikel dan Portofolio tampil dengan hero bergambar seperti Produk, dikelola dari admin.

**Independent Test**: Buka `/artikel` dan `/portfolio`; hero, breadcrumb, judul, dan subjudul tampil. Ubah di admin lalu muat ulang.

### Tests for User Story 1

- [X] T005 [P] [US1] Perbarui `tests/Feature/Pages/PageBannerRenderTest.php`: hero Artikel dan Portofolio memuat breadcrumb, judul, subjudul, gambar bawaan bila belum diunggah, dan gambar bawaan bila berkas hilang
- [X] T006 [P] [US1] Perbarui `tests/Feature/Admin/PageBannerResourceTest.php`: edit judul, subjudul, dan gambar hero Artikel serta Portofolio tersimpan

### Implementation for User Story 1

- [X] T007 [US1] Ganti `<section>` judul polos di `resources/views/pages/artikel/index.blade.php` dengan `<x-sections.page-hero>` (breadcrumb "Artikel", title/subtitle/image dari `PageBlockType::ArticlesHero`, pola sama dengan `resources/views/pages/produk/index.blade.php`)
- [X] T008 [US1] Ganti `<section>` judul polos di `resources/views/pages/portfolio/index.blade.php` dengan `<x-sections.page-hero>` (breadcrumb "Portfolio", data dari `PageBlockType::PortfolioHero`)
- [X] T009 [US1] Perbarui `tests/Support/LegacyMarkup.php`: hapus fragmen `artikel-hero` dan `portfolio-hero` dari `FRAGMENTS`, hapus `tests/Fixtures/legacy-page-content/artikel-hero.html` dan `portfolio-hero.html` (markup hero sengaja berubah menjadi komponen `page-hero`)

**Checkpoint**: US1 selesai dan dapat didemokan sendiri.

---

## Phase 4: User Story 2 - Kartu Portofolio Lengkap (Priority: P1)

**Goal**: Kartu proyek menampilkan kategori, judul, ringkasan, dan "Lihat Detail Proyek →", dengan filter pil.

**Independent Test**: Buka `/portfolio`, pilih tiap pil, periksa isi kartu dan tautan detail.

### Tests for User Story 2

- [X] T010 [P] [US2] Perbarui `tests/Feature/Pages/PortfolioPageTest.php`: kartu memuat kategori, judul, ringkasan terpotong, teks "Lihat Detail Proyek", tautan ke `portfolio.show`; proyek tanpa deskripsi dan tanpa gambar tetap tampil; filter `?kategori=` menyaring dan pil aktif berkelas berbeda
- [X] T011 [P] [US2] Tambah test unit accessor `summary()` di `tests/Unit/PortfolioProjectSummaryTest.php`: deskripsi panjang terpotong ±140 karakter, tag HTML dibuang, deskripsi kosong menghasilkan string kosong

### Implementation for User Story 2

- [X] T012 [US2] Tambah accessor `summary()` (`Str::limit(strip_tags($this->description ?? ''), 140)`) pada `app/Models/PortfolioProject.php`
- [X] T013 [P] [US2] Buat komponen `resources/views/components/sections/project-card.blade.php` (gambar `aspect-video` dengan penampung bila kosong, kategori, judul, ringkasan `line-clamp-3` bila ada, "Lihat Detail Proyek →", seluruh kartu tertaut ke `route('portfolio.show', $project)`)
- [X] T014 [US2] Ubah `resources/views/pages/portfolio/index.blade.php`: pakai `<x-sections.project-card>` di grid 3 kolom dan rapikan filter menjadi pil (kelas aktif/nonaktif sesuai dokumen)

**Checkpoint**: US1 dan US2 membuat halaman Portofolio sesuai dokumen.

---

## Phase 5: User Story 3 - Artikel Dua Kolom, Pencarian, Langganan (Priority: P1)

**Goal**: Artikel tampil sebagai grid + sidebar (Cari Artikel dan Update Mingguan); pendaftar tersimpan dan terlihat oleh admin.

**Independent Test**: Buka `/artikel`; cari kata ada dan tidak ada; daftar dengan email valid, tidak valid, dan berulang; cek menu admin.

### Tests for User Story 3

- [X] T015 [P] [US3] Perbarui `tests/Feature/Pages/ArticlePageTest.php`: grid dan sidebar tampil; tidak ada artikel unggulan dan CTA lama; `?q=` menyaring judul dan ringkasan; `q` tidak cocok menampilkan pesan "tidak ditemukan" dan tautan hapus pencarian; `q` dengan `%`/`_`/teks sangat panjang tidak error; artikel draft/terjadwal tidak muncul di hasil; kartu memuat badge kategori, tanggal, "Baca Selengkapnya"
- [X] T016 [P] [US3] Buat `tests/Feature/Pages/NewsletterSubscribeTest.php`: email valid tersimpan huruf kecil (201 JSON, atau redirect tanpa JSON); email kosong/tidak valid 422 tanpa data tersimpan; email sama dua kali menghasilkan satu baris dan respons sukses identik; honeypot terisi dibalas sukses palsu tanpa menyimpan; token waktu terlalu cepat dibalas sukses palsu; batas throttle menghasilkan 429
- [X] T017 [P] [US3] Buat `tests/Feature/Admin/NewsletterSubscriberResourceTest.php`: pengguna berizin melihat daftar, mencari email, menghapus baris; tanpa halaman create/edit; pengguna tanpa izin ditolak

### Implementation for User Story 3

- [X] T018 [US3] Buat migrasi `database/migrations/<timestamp>_create_newsletter_subscribers_table.php` lewat `php artisan make:migration` (kolom `email` unik, `subscribed_at`, timestamps) sesuai `data-model.md`
- [X] T019 [P] [US3] Buat model `app/Models/NewsletterSubscriber.php` dan `database/factories/NewsletterSubscriberFactory.php` lewat `php artisan make:model`
- [X] T020 [US3] Buat `app/Http/Controllers/Public/NewsletterController.php` (`store`): validasi, normalisasi email, `SubmissionGuard` (honeypot + token), `RateLimiter` per email, `firstOrCreate`, respons JSON atau redirect ke `/artikel#langganan` dengan flash sesuai `contracts/public-routes.md`
- [X] T021 [US3] Daftarkan rute `POST /langganan` bernama `newsletter.subscribe` dengan `throttle:5,1` di `routes/web.php`
- [X] T022 [US3] Ubah `app/Http/Controllers/Public/ArticleController.php` `index()`: baca `q` (dipotong 100, escape `%`/`_`), saring judul/excerpt pada artikel terbit, kunci cache memuat hash `q`, hapus `featured` sehingga semua artikel masuk `articles`, teruskan `q` ke view
- [X] T023 [P] [US3] Ubah `resources/views/components/sections/article-card.blade.php`: badge kategori di atas gambar, tanggal, judul, ringkasan, "Baca Selengkapnya →"
- [X] T024 [P] [US3] Buat `resources/views/components/sections/article-sidebar.blade.php`: kartu "Cari Artikel" (form GET ke `/artikel`, input `q` terisi nilai saat ini) dan kartu "Update Mingguan" (form POST `newsletter.subscribe`, id `langganan`, `@csrf`, honeypot dan `form_token` dari `SubmissionGuard::issueToken()`, pesan sukses/galat dari flash, kirim lewat `fetch` bila JS aktif)
- [X] T025 [US3] Ubah `resources/views/pages/artikel/index.blade.php`: grid `lg:grid-cols-[1fr_320px]`, filter kategori dipertahankan di kolom utama, grid kartu 2 kolom, sidebar `lg:sticky` dan pindah ke bawah di ponsel, pesan kosong dan "tidak ditemukan" di dalam layout, hapus blok artikel unggulan dan CTA `ArticleIndex`
- [X] T026 [US3] Buat `app/Filament/Resources/NewsletterSubscriberResource.php` dan `Pages/ListNewsletterSubscribers.php` (daftar email dan tanggal, pencarian, aksi hapus, tanpa create/edit), tempatkan di grup navigasi yang sama dengan Kontak/Lead, dan perbarui `tests/Feature/Admin/NavigationStructureTest.php` bila menghitung menu
- [X] T027 [US3] (DIUBAH saat implementasi: CTA `ArticleIndex` dipertahankan di bawah grid agar menu CTA admin tetap berfungsi, fragmen dan fixture `artikel-index-cta` tidak dihapus) Periksa `tests/Feature/Pages/CallToActionRenderTest.php` dan `tests/Feature/Admin/CallToActionResourceTest.php` agar tidak lagi mengharapkan CTA `ArticleIndex` tampil di `/artikel`

**Checkpoint**: Halaman Artikel sesuai dokumen; pendaftar tercatat dan terlihat admin.

---

## Phase 6: User Story 4 - Kartu Produk Sederhana (Priority: P2)

**Goal**: Halaman Produk hanya menampilkan gambar dan judul pada kartu. Detail produk tidak berubah (FR-027).

**Independent Test**: Buka `/produk`; kartu hanya gambar dan judul; klik kartu membuka detail.

### Tests for User Story 4

- [X] T028 [P] [US4] Perbarui `tests/Feature/Pages/ProductPageTest.php`: kartu di `/produk` tidak memuat badge kategori, deskripsi, harga ("Rp"), dan "Lihat detail"; memuat judul dan tautan ke detail; produk tanpa gambar menampilkan `data-product-image-placeholder`; filter kategori masih ada; kartu "produk terkait" di `/produk/{slug}` tetap memuat harga dan deskripsi seperti sebelumnya

### Implementation for User Story 4

- [X] T029 [US4] Ubah `resources/views/components/sections/product-card.blade.php`: tambah prop `simple` (default `false`); bila `simple`, render tautan pembungkus penuh berisi gambar `aspect-[4/3]` (atau penampung) dan judul rata tengah `line-clamp-2`; bila tidak, markup lama dipertahankan agar `resources/views/pages/produk/show.blade.php` (produk terkait) tidak berubah
- [X] T030 [US4] Ubah `resources/views/pages/produk/index.blade.php` baris 49 menjadi `<x-sections.product-card :product="$product" simple />`

**Checkpoint**: Katalog Produk sesuai dokumen; detail produk tidak terdampak.

---

## Phase 7: User Story 5 - Mengapa Beralih & Sederhana dan Mulus (Priority: P2)

**Goal**: Kedua section Beranda rata, tanpa rotasi dan pergeseran.

**Independent Test**: Buka `/` di desktop dan ponsel; kartu sebaris, sama tinggi, tanpa miring.

### Tests for User Story 5

- [X] T031 [P] [US5] Perbarui `tests/Feature/Pages/HomePageTest.php`: HTML section "Mengapa Beralih" dan "Sederhana dan Mulus" tidak memuat `rotate-1`, `-rotate-1`, `rotate-2`, `-rotate-2`, `-translate-y-4`, maupun `md:mt-12`/`md:mt-16`; item penekanan memuat `border-primary-container`; judul section dan item tetap berasal dari data; section tersembunyi bila tidak ada konten
- [X] T032 [P] [US5] Perbarui `tests/Support/LegacyMarkup.php`: tandai `home-why-choose` dan `home-how-it-works` sebagai `REDESIGNED` (dikecualikan dari `LegacyMarkupEquivalenceTest`, tetap bisa diekstrak untuk tes render) dan hapus berkas `tests/Fixtures/legacy-page-content/home-why-choose.html` dan `home-how-it-works.html` (desain sengaja berubah, kesetaraan markup diganti T031)

### Implementation for User Story 5

- [X] T033 [P] [US5] Ubah `resources/views/components/sections/why-choose.blade.php`: judul/subjudul rata tengah dan lebar penuh, grid `items-stretch` dengan kartu `h-full`, ikon kecil (`w-10 h-10`, glyph `text-xl`), item penekanan `bg-white border-2 border-primary-container` tanpa `rotate`/`-translate-y`/`scale`
- [X] T034 [P] [US5] Ubah `resources/views/components/sections/how-it-works.blade.php`: hapus `$rotations` dan `$offsets` serta kelas rotasi/offset, jaga lingkaran nomor, garis putus-putus, dan kartu rata sama tinggi

**Checkpoint**: Dua section Beranda sesuai dokumen.

---

## Phase 8: User Story 6 - Testimoni "Partner Kami" (Priority: P2)

**Goal**: Section testimoni Beranda bergaya baru.

**Independent Test**: Buka `/` dengan ≥3 testimoni aktif; nonaktifkan semua dan section hilang.

### Tests for User Story 6

- [X] T035 [P] [US6] Perbarui `tests/Feature/Pages/HomePageTest.php`: memuat "TESTIMONI" dan "Partner Kami", tidak memuat "Apa Kata Mereka Tentang"; tidak ada kartu bertanda sorotan (`bg-surface-container-low` pada kartu tengah); jumlah bintang `star` sesuai rating dan sisanya `star_border`; inisial muncul tanpa foto; baris jabatan hilang bila kosong; section hilang tanpa testimoni aktif

### Implementation for User Story 6

- [X] T036 [US6] Ubah blok `{{-- Testimoni --}}` di `resources/views/pages/home.blade.php`: label `TESTIMONI` kecil kapital, judul "Partner Kami", hapus subjudul dan variabel `$highlight`, semua kartu bergaya sama, bintang dengan ikon `star`/`star_border` (bergaya garis) sesuai rating

**Checkpoint**: Testimoni sesuai dokumen.

---

## Phase 9: User Story 7 - Footer Kontak & Section Solusi (Priority: P3)

**Goal**: Memastikan footer Kontak dan section "Solusi Untuk Setiap Kebutuhan" sesuai dan terlindungi dari regresi.

**Independent Test**: Isi/kosongkan data kontak, periksa footer; periksa section Solusi tidak berubah.

### Tests for User Story 7

- [X] T037 [P] [US7] Tambah test footer di `tests/Feature/Pages/HomePageTest.php` (atau berkas test layout yang sudah ada bila ada): alamat, email, telepon tampil dengan ikon `location_on`, `mail`, `call`; baris yang kosong tidak tampil; semua kosong tidak menghasilkan `<li>` kosong
- [X] T038 [P] [US7] Tambah test di `tests/Feature/Pages/HomePageTest.php`: section "Solusi Untuk Setiap Kebutuhan" memuat badge "Terpopuler" pada kartu kedua, tombol "Lihat Detail Produk", dan tautan "Lihat semua produk"

### Implementation for User Story 7

- [X] T039 [US7] Bandingkan `resources/views/components/layout/footer.blade.php` dengan halaman 3 dokumen klien; sesuaikan hanya bila ada selisih nyata (susunan kolom Kontak, ikon, spasi). Jika sudah sesuai, tidak ada perubahan berkas
- [X] T040 [US7] Pastikan blok "Solusi Untuk Setiap Kebutuhan" di `resources/views/pages/home.blade.php` tidak berubah dari sebelum fitur ini (periksa `git diff` untuk blok tersebut)

**Checkpoint**: Seluruh tujuh story selesai.

---

## Phase 10: Polish & Cross-Cutting

- [X] T041 [P] Perbarui `docs/manual-operator.md`: menu Langganan Newsletter, cara mengubah hero Artikel dan Portofolio di Banner Halaman Lain, dan pencarian Artikel
- [X] T042 Jalankan `vendor/bin/pint --dirty --format agent`
- [X] T043 Jalankan seluruh test: `php artisan test --compact`; perbaiki tes yang memeriksa tampilan lama dan perbarui sesuai desain baru (bukan menghapus tes)
- [ ] T044 Jalankan `npm run build` dan periksa manual sesuai `specs/030-client-design-update/quickstart.md` pada lebar 360, 768, dan 1440 px (tanpa gulir horizontal, kartu sama tinggi)
- [ ] T045 Bandingkan hasil akhir dengan PDF "Confirm Update Design" per halaman dan catat penyimpangan untuk persetujuan (SC-001)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: tanpa ketergantungan.
- **Foundational (Phase 2)**: setelah Setup; memblokir US1.
- **US1 (P1)**: setelah Phase 2.
- **US2 (P1)**: setelah US1, karena mengubah `portfolio/index.blade.php` yang sama dengan T008.
- **US3 (P1)**: setelah US1, karena mengubah `artikel/index.blade.php` yang sama dengan T007.
- **US4, US5, US6, US7**: tidak bergantung pada story lain; dapat dikerjakan paralel setelah Setup.
- **Polish**: setelah semua story yang dipilih selesai.

### Dalam Satu Story

- Test ditulis dulu dan harus gagal sebelum implementasi.
- US3: migrasi (T018) → model (T019) → controller (T020) → rute (T021); `ArticleController` (T022) dan komponen Blade (T023, T024) sebelum halaman (T025); Resource admin (T026) setelah model.
- `tests/Support/LegacyMarkup.php` diubah oleh T009, T027, T032; kerjakan berurutan (berkas yang sama).
- `tests/Feature/Pages/HomePageTest.php` diubah oleh T031, T035, T037, T038; kerjakan berurutan.

### Peluang Paralel

- T005, T006 bersamaan; T010, T011 bersamaan; T015, T016, T017 bersamaan.
- T023, T024 bersamaan (berkas berbeda); T019 bersamaan dengan T022.
- T033 dan T034 bersamaan.
- US4, US5, US6 dapat dikerjakan orang berbeda secara paralel.

### Contoh Paralel: User Story 3

```text
T015 test halaman Artikel      T016 test langganan      T017 test admin
T023 article-card              T024 article-sidebar
```

---

## Implementation Strategy

### MVP Dulu (US1)

1. Setup dan Foundational (T001–T004).
2. US1 (T005–T009): hero Artikel dan Portofolio. Validasi dan demo.

### Pengiriman Bertahap

1. US1 → US2 → US3: seluruh prioritas P1 (Portofolio dan Artikel selesai).
2. US4, US5, US6: prioritas P2 (Produk dan Beranda).
3. US7 dan Polish: verifikasi, dokumentasi, uji penuh.
4. Setiap story ditambahkan tanpa merusak story sebelumnya.

## Notes

- `[P]` berarti berkas berbeda dan tanpa ketergantungan.
- Kartu produk dipakai juga oleh `produk/show.blade.php` (produk terkait); itu sebabnya T029 memakai prop `simple`, agar halaman detail produk tidak berubah.
- Aturan "jangan buat ulang fixture" pada `LegacyMarkupEquivalenceTest` berlaku untuk konversi CMS; di fitur ini desain memang berubah, jadi fragmen terkait dilepas (bukan fixture diedit) dan diganti test render.
- Commit setelah tiap story selesai.
