---
description: "Task list for Banner Hero Slider"
---

# Tasks: Banner Hero Slider

**Input**: Design documents from `specs/022-banner-hero-slider/`

**Prerequisites**: [plan.md](./plan.md), [spec.md](./spec.md), [research.md](./research.md), [data-model.md](./data-model.md), [contracts/](./contracts/)

**Tests**: Disertakan. Principle IV konstitusi mewajibkan setiap modul konten memiliki feature test sebelum dianggap selesai.

**Organization**: Task dikelompokkan per user story agar tiap story dapat diimplementasi dan diuji secara mandiri.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Dapat dikerjakan paralel (berkas berbeda, tanpa dependensi pada task yang belum selesai)
- **[Story]**: User story yang dilayani task tersebut (US1, US2, US3, US4)

## Path Conventions

Monolit Laravel di root repositori: `app/`, `database/`, `resources/`, `tests/`. Tidak ada folder dasar baru selain `app/Enums/`.

---

## Phase 1: Setup (Shared Infrastructure)

- [ ] T001 Verifikasi berada di branch `022-banner-hero-slider` dan `php artisan test --compact` hijau sebelum perubahan apa pun, sebagai garis dasar
- [X] T002 [P] Buat direktori `app/Enums/` bila belum ada

**Checkpoint**: Garis dasar hijau, tidak ada perubahan fungsional.

---

## Phase 2: Foundational (Blocking Prerequisites)

**⚠️ Seluruh task di fase ini MUST selesai sebelum user story mana pun dikerjakan.**

- [X] T003 [P] Buat enum `App\Enums\BannerOverlayStyle` di `app/Enums/BannerOverlayStyle.php` — backed string dengan case `Dark` (`dark`), `Light` (`light`), `None` (`none`); metode `label(): string` berbahasa Indonesia, `overlayClasses(BannerTextPosition $position): string`, `headingClasses(): string`, `bodyClasses(): string`. Seluruh kelas Tailwind ditulis **literal**, tidak dirangkai (research.md R3)
- [X] T004 [P] Buat enum `App\Enums\BannerTextPosition` di `app/Enums/BannerTextPosition.php` — backed string dengan case `Left` (`left`), `Center` (`center`), `Right` (`right`); metode `label(): string` dan `containerClasses(): string` dengan kelas literal
- [X] T005 Buat migration `database/migrations/2026_09_14_000000_add_hero_fields_to_banners_table.php` yang menambahkan sepuluh kolom sesuai tabel di data-model.md (`badge_text`, `heading`, `subheading`, `cta_primary_label`, `cta_primary_url`, `cta_secondary_label`, `cta_secondary_url`, `trust_html`, `overlay_style` default `dark`, `text_position` default `left`), seluruhnya nullable kecuali dua kolom preset yang ber-default
- [X] T006 Tambahkan backfill pada `up()` migration T005: isi banner ber-`order` terkecil (pemecah seri `id` terkecil) dengan konten hero literal sesuai data-model.md §Migrasi data, **hanya bila `heading` masih null**; tidak melakukan apa-apa bila tabel kosong. `down()` cukup membuang kesepuluh kolom (research.md R10)
- [X] T007 Perbarui `app/Models/Banner.php`: tambahkan kesepuluh kolom ke `$fillable`; tambahkan cast `overlay_style => BannerOverlayStyle::class` dan `text_position => BannerTextPosition::class` pada `casts()`. Jangan ubah `scopeLive()` maupun `displayStatus()`
- [X] T008 Tambahkan metode turunan pada `app/Models/Banner.php`: `hasContent(): bool`, `hasCta(): bool`, `hasPrimaryCta(): bool`, `hasSecondaryCta(): bool`, `sanitizedTrustHtml(): ?string`, masing-masing dengan PHPDoc sesuai konvensi repositori
- [X] T009 [P] Buat `App\Support\HtmlSanitizer` di `app/Support/HtmlSanitizer.php` — metode statis `clean(?string $html): string` berbasis `DOMDocument` + `DOMXPath` dengan allowlist tag dan atribut sesuai research.md R4; buang seluruh atribut `on*`; batasi skema `href`/`src` pada `http`, `https`, `mailto`, `tel`, dan path relatif; tag di luar allowlist dibuang namun teks anaknya dipertahankan. Tanpa dependency baru
- [X] T010 Tambahkan `booted()` pada `app/Models/Banner.php` yang mendaftarkan listener `saved` dan `deleted` untuk memanggil `Cache::forget('public-page:home')` (research.md R5). Jangan ubah trait `CachesPublicPages`
- [X] T011 [P] Perbarui `database/factories/BannerFactory.php`: tambahkan nilai default untuk kolom baru (preset `dark`/`left`, konten kosong) dan state `withContent()` yang mengisi slide lengkap beserta kedua CTA, untuk dipakai test
- [ ] T012 Jalankan `php artisan migrate` pada basis data lokal dan verifikasi backfill mengenai banner yang tepat

**Checkpoint**: Skema, enum, model, sanitizer, dan invalidasi cache siap. Sisi publik belum berubah.

---

## Phase 3: User Story 1 - Admin memasang banner tanpa kehilangan isi hero (Priority: P1) 🎯 MVP

**Goal**: Satu banner dengan teks dan CTA tampil sebagai hero utuh di beranda, dan perubahannya langsung terlihat.

**Independent Test**: Buat satu banner lengkap di admin, buka beranda, verifikasi seluruh elemen tampil dan kedua tombol menuju alamat yang diisi.

### Tests for User Story 1

- [X] T013 [P] [US1] Buat `tests/Unit/HtmlSanitizerTest.php` — menguji tag diizinkan lolos, tag terlarang dibuang namun teksnya bertahan, atribut `on*` dibuang, skema `javascript:` ditolak, dan masukan null menghasilkan string kosong
- [X] T014 [P] [US1] Tambahkan test pada `tests/Feature/Pages/HomeBannerTest.php` yang memverifikasi banner dengan `heading`, `subheading`, dan CTA merender seluruh elemen tersebut di beranda
- [X] T015 [P] [US1] Tambahkan test pada `tests/Feature/Pages/HomeBannerTest.php` yang memverifikasi banner tanpa konten apa pun merender gambar tanpa blok teks, dan banner satu CTA hanya merender satu tombol
- [X] T016 [P] [US1] Tambahkan test pada `tests/Feature/Admin/BannerResourceTest.php` yang memverifikasi penyimpanan ditolak bila label CTA diisi tanpa alamat, dan sebaliknya (FR-003)
- [X] T017 [P] [US1] Tambahkan test yang memverifikasi cache `public-page:home` terbuang setelah banner disimpan dan setelah dihapus (FR-018)

### Implementation for User Story 1

- [X] T018 [US1] Perbarui `app/Filament/Resources/BannerResource.php`: susun ulang form menjadi enam `Section` sesuai contracts/admin-panel-surface.md §1, pertahankan seluruh field lama beserta aturan validasinya
- [X] T019 [US1] Tambahkan field Konten Slide pada `BannerResource`: `badge_text` (maks 120), `heading` (maks 160), `subheading` (`Textarea`, maks 400), seluruhnya opsional dengan helper berbahasa Indonesia yang membedakan Judul Slide dari Judul Internal
- [X] T020 [US1] Tambahkan empat field CTA pada `BannerResource` beserta validasi berpasangan `requiredWith` dua arah untuk tiap pasangan, dan aturan yang menerima path internal berawalan `/` maupun URL absolut http/https (FR-004)
- [X] T021 [US1] Buat `resources/views/components/sections/hero-slider.blade.php` — terima prop `banners`, saring banner yang berkasnya tidak ada (FR-020), lalu render mode slide tunggal bila hitungannya satu. Ekstrak markup satu slide agar dipakai bersama mode slider (research.md R1)
- [X] T022 [US1] Implementasi blok konten slide di `hero-slider.blade.php`: badge, judul, subjudul, dan tombol CTA, masing-masing dirender hanya bila datanya terisi; judul slide pertama sebagai `<h1>`, slide berikutnya `<h2>` (contracts/public-render.md §2). Gaya visual mengikuti `hero.blade.php` yang ada
- [X] T023 [US1] Implementasi aturan tautan majemuk di `hero-slider.blade.php`: gambar dibungkus tautan `link_url` hanya bila slide tidak punya CTA sama sekali (research.md R8, FR-017)
- [X] T024 [US1] Perbarui `resources/views/pages/home.blade.php` agar memanggil `<x-sections.hero-slider :banners="$banners" />` menggantikan `banner-carousel`, dengan fallback `<x-sections.hero />` saat koleksi kosong
- [X] T025 [US1] Hapus `resources/views/components/sections/banner-carousel.blade.php` dan pastikan tidak ada referensi tersisa (`grep -rn "banner-carousel" resources app tests`). **Catatan lingkungan**: shell agen tidak memiliki izin hapus berkas; bila penghapusan gagal, pindahkan berkas ke `_to_delete/` dan minta pemilik repositori menghapusnya
- [X] T026 [US1] Perbarui asersi lama di `tests/Feature/Pages/HomeBannerTest.php` yang menegaskan judul hero tidak boleh muncul saat banner ada — kini judul boleh muncul asalkan berasal dari record banner, bukan dari komponen hero statis
- [ ] T027 [US1] Jalankan `php artisan test --compact --filter=Banner` dan `--filter=HtmlSanitizer` hingga hijau

**Checkpoint**: Mengganti banner tidak lagi menghapus isi hero. MVP dapat didemokan.

---

## Phase 4: User Story 2 - Pengunjung menelusuri beberapa slide (Priority: P1)

**Goal**: Dua slide atau lebih dapat ditelusuri manual, dengan penanda yang jelas bahwa slide lain ada.

**Independent Test**: Tiga banner tayang; seluruh slide dapat dicapai lewat panah, titik, papan ketik, dan geser; tidak ada perpindahan otomatis.

### Tests for User Story 2

- [X] T028 [P] [US2] Tambahkan test pada `tests/Feature/Pages/HomeBannerTest.php` yang memverifikasi satu banner tayang merender tanpa kontrol navigasi, sedangkan tiga banner merender panah, tiga titik, dan penanda posisi
- [X] T029 [P] [US2] Tambahkan test yang memverifikasi urutan slide mengikuti kolom `order` menaik dengan pemecah seri `id`
- [X] T030 [P] [US2] Tambahkan test yang memverifikasi banner yang berkas gambarnya hilang dilewati tanpa menggagalkan render, dan jumlah titik navigasi menyesuaikan (FR-020)
- [X] T031 [P] [US2] Perbarui `tests/Feature/Public/LazyLoadingTest.php`: slide pertama tanpa `loading="lazy"` dan ber-`fetchpriority="high"`, slide berikutnya `loading="lazy"` (FR-019)

### Implementation for User Story 2

- [X] T032 [P] [US2] Buat `resources/js/hero-slider.js` — komponen Alpine `heroSlider` mengikuti pola `calculatorComponent`: state `active`, `count`, metode `next()`, `prev()`, `go(i)` yang melingkar, **tanpa timer apa pun** (FR-011); penanganan papan ketik panah kiri/kanan; penanganan geser sentuh yang mengabaikan geser menegak; `IntersectionObserver` yang menyalakan penanda animasi sekali lalu mematikannya
- [X] T033 [US2] Daftarkan komponen di `resources/js/app.js` lewat `Alpine.data('heroSlider', heroSlider)` mengikuti pola registrasi yang sudah ada
- [X] T034 [US2] Implementasi mode slider di `hero-slider.blade.php`: seluruh slide dirender di HTML awal dengan transisi pudar, ditambah panah, titik navigasi, dan penanda posisi "n / m" (contracts/public-render.md §5)
- [X] T035 [US2] Terapkan kontrak aksesibilitas di `hero-slider.blade.php`: `aria-roledescription="carousel"` pada wadah, `role="group"` + `aria-roledescription="slide"` + `aria-label="Slide n dari m"` per slide, titik sebagai `role="tab"` dengan `aria-selected`, serta `aria-hidden` dan `inert` pada slide non-aktif agar tombolnya tidak dapat dijangkau `Tab` (FR-015)
- [X] T036 [US2] Terapkan strategi pemuatan gambar di `hero-slider.blade.php`: slide pertama `fetchpriority="high"` tanpa lazy, slide berikutnya `loading="lazy"` + `decoding="async"` (research.md R9)
- [X] T037 [P] [US2] Tambahkan keyframes animasi *nudge* panah dan gaya petunjuk geser di `resources/css/app.css`, beserta guard `@media (prefers-reduced-motion: reduce)` yang mematikan animasi penanda maupun transisi slide (FR-014)
- [X] T038 [US2] Implementasi perilaku layar kecil di `hero-slider.blade.php`: sembunyikan panah, tampilkan petunjuk "Geser untuk melihat lainnya" yang hilang permanen setelah interaksi pertama
- [ ] T039 [US2] Jalankan `npm run build` dan verifikasi kelas preset benar-benar ikut ter-build (research.md R3)

**Checkpoint**: Slider berfungsi penuh, manual, dan dapat diakses.

---

## Phase 5: User Story 3 - Admin mengatur tampilan slide (Priority: P2)

**Goal**: Preset lapisan dan posisi teks dapat dipilih per slide, membuat teks terbaca di atas gambar mana pun.

**Independent Test**: Dua slide dengan gambar terang dan gelap memakai preset berbeda; teks terbaca pada keduanya dan posisinya sesuai pilihan.

### Tests for User Story 3

- [X] T040 [P] [US3] Tambahkan test pada `tests/Feature/Admin/BannerResourceTest.php` yang memverifikasi `overlay_style` dan `text_position` hanya menerima nilai enum, dan nilai di luar daftar ditolak
- [X] T041 [P] [US3] Tambahkan test pada `tests/Feature/Pages/HomeBannerTest.php` yang memverifikasi preset terpilih menghasilkan kelas lapisan dan perataan yang sesuai di markup

### Implementation for User Story 3

- [X] T042 [US3] Tambahkan section Tampilan pada `app/Filament/Resources/BannerResource.php`: `Select` untuk `overlay_style` dan `text_position` yang opsinya bersumber dari enum beserta label Indonesia, dengan nilai bawaan `dark` dan `left`
- [X] T043 [US3] Terapkan preset pada `hero-slider.blade.php`: kelas lapisan dan arah gradasi dari `BannerOverlayStyle::overlayClasses()`, perataan blok konten dari `BannerTextPosition::containerClasses()`, warna teks dari `headingClasses()`/`bodyClasses()`
- [ ] T044 [US3] Pastikan warna tombol dan aksen slide bersumber dari variabel tema brand yang sudah ada, bukan nilai per banner (FR-006) — verifikasi dengan mengganti warna primer di Theme Settings

**Checkpoint**: Teks terbaca di atas gambar terang maupun gelap tanpa mengedit kode.

---

## Phase 6: User Story 4 - Trust bar buatan admin (Priority: P3)

**Goal**: Area bukti sosial di bawah tombol dapat disusun admin, termasuk gambar dan ikon.

**Independent Test**: Trust bar berisi teks bercampur gambar tampil pada slide tersebut saja; slide tanpa trust bar tidak menyisakan ruang kosong.

### Tests for User Story 4

- [X] T045 [P] [US4] Tambahkan test pada `tests/Feature/Pages/HomeBannerTest.php` yang memverifikasi `trust_html` dirender pada slide terkait, tidak dirender saat kosong, dan elemen terlarang sudah dibuang saat sampai di halaman

### Implementation for User Story 4

- [X] T046 [US4] Tambahkan section Trust Bar pada `app/Filament/Resources/BannerResource.php`: `RichEditor` opsional dengan toolbar terbatas (bold, italic, link, bullet list, gambar), unggahan gambar ke disk `public` direktori `banners/trust` lewat `ImageUploads::storeAsWebp`
- [X] T047 [US4] Render trust bar di `hero-slider.blade.php` memakai `sanitizedTrustHtml()`, hanya bila hasilnya tidak kosong, beserta pemisah visual yang ikut hilang saat kosong (FR-002, FR-007)

**Checkpoint**: Seluruh empat user story selesai.

---

## Phase 7: Polish & Cross-Cutting Concerns

- [X] T048 [P] Tambahkan kolom Judul Slide pada tabel `BannerResource` dengan placeholder "—" saat kosong (contracts/admin-panel-surface.md §4)
- [X] T049 [P] Aktifkan pengurutan seret-dan-lepas pada tabel `BannerResource` lewat `reorderable('order')` dan hapus keharusan mengetik `order` manual (FR-022)
- [X] T050 [P] Perbarui `database/seeders/DemoContentSeeder.php` agar banner contoh berisi slide lengkap dengan teks, CTA, trust bar, dan preset, sehingga instalasi baru langsung menampilkan hero utuh
- [X] T051 Perbarui `specs/012-banner-management-module/spec.md` dengan catatan bahwa modul diperluas oleh fitur 022, agar dokumen lama tidak menyesatkan
- [ ] T052 Jalankan `vendor/bin/pint --dirty --format agent` dan perbaiki seluruh temuan format
- [ ] T053 Jalankan seluruh suite `php artisan test --compact` dan pastikan hijau
- [ ] T054 Telusuri `specs/022-banner-hero-slider/quickstart.md` secara manual dari awal hingga akhir, termasuk seluruh kasus batas
- [ ] T055 Ambil tangkapan layar beranda untuk empat kondisi — satu slide, tiga slide, preset lapisan terang, dan tampilan lebar ponsel — untuk ditinjau pemilik produk
- [X] T056 [P] Buat `tests/Feature/Database/BannerHeroBackfillTest.php` yang menjalankan migration pada basis data berisi dua banner dan memverifikasi banner ber-`order` terkecil terisi konten hero, banner kedua tetap kosong, dan tidak ada data lama yang hilang (FR-016, SC-008) — *ditambahkan hasil /speckit-analyze, temuan C1*
- [ ] T057 [P] Verifikasi kontras teks pada ketiga preset lapisan memakai alat pemeriksa kontras peramban, dan catat hasilnya di `specs/022-banner-hero-slider/quickstart.md` (SC-006) — *ditambahkan hasil /speckit-analyze, temuan C2*
- [ ] T058 Ukur LCP beranda sebelum dan sesudah fitur pada kondisi tiga slide, dan pastikan tidak memburuk (SC-007) — *ditambahkan hasil /speckit-analyze, temuan C3*
- [X] T059 [P] Perbarui bagian modul Banner pada `README.md` dan `docs/deployment.md` agar mencerminkan kemampuan hero slider — konstitusi menyatakan modul yang tidak terdokumentasi dianggap belum selesai (Deployment & Client Setup Standards) — *ditambahkan hasil /speckit-analyze, temuan D1*

---

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1 (Setup)**: Tanpa dependensi
- **Phase 2 (Foundational)**: Bergantung pada Phase 1 — **memblokir seluruh user story**
- **Phase 3 (US1)**: Bergantung pada Phase 2
- **Phase 4 (US2)**: Bergantung pada Phase 2; **juga** bergantung pada T021–T022 karena memperluas komponen yang sama
- **Phase 5 (US3)**: Bergantung pada Phase 2 dan T021–T022
- **Phase 6 (US4)**: Bergantung pada Phase 2 dan T021–T022
- **Phase 7 (Polish)**: Bergantung pada seluruh fase user story

### User Story Dependencies

- **US1 (P1)**: Fondasi bersama — tidak bergantung pada story lain
- **US2 (P1)**: Membutuhkan komponen slide dari US1; setelah itu mandiri
- **US3 (P2)**: Membutuhkan komponen slide dari US1; mandiri terhadap US2 dan US4
- **US4 (P3)**: Membutuhkan komponen slide dari US1; mandiri terhadap US2 dan US3

Berbeda dari fitur yang murni saling lepas, keempat story di sini berbagi satu berkas komponen. US1 sengaja menciptakan berkas tersebut lebih dulu agar tiga story sisanya dapat berjalan paralel di atasnya.

### Within Each User Story

Test ditulis lebih dulu, lalu form admin, lalu render publik, ditutup dengan menjalankan test.

### Parallel Opportunities

- T003 dan T004 (dua berkas enum) paralel
- T009 (sanitizer) paralel terhadap seluruh task migration dan model
- Seluruh task test dalam satu fase paralel satu sama lain (berkas berbeda atau blok berbeda)
- Setelah US1 selesai, US2, US3, dan US4 dapat dikerjakan tiga orang secara paralel selama koordinasi pada `hero-slider.blade.php` dijaga
- T048, T049, T050 di fase polish paralel

---

## Parallel Example: User Story 1

```text
# Jalankan seluruh test US1 bersamaan:
T013 HtmlSanitizerTest
T014 HomeBannerTest — render konten slide
T015 HomeBannerTest — elemen opsional
T016 BannerResourceTest — validasi CTA berpasangan
T017 Invalidasi cache

# Lalu implementasi, berurut karena menyentuh berkas yang sama:
T018 → T019 → T020 (BannerResource)
T021 → T022 → T023 (hero-slider.blade.php)
T024 → T025 (home.blade.php, hapus komponen lama)
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

Selesaikan Phase 1 → Phase 2 → Phase 3, lalu berhenti dan demokan. Pada titik ini keluhan utama sudah teratasi: mengganti gambar banner tidak lagi menghapus isi hero. Situs sepenuhnya dapat dirilis meski US2–US4 belum dikerjakan.

### Incremental Delivery

1. Phase 1 + 2 → skema dan fondasi siap, sisi publik belum berubah
2. Phase 3 → **MVP dapat dirilis**
3. Phase 4 → beberapa slide dapat ditelusuri
4. Phase 5 → kontrol tampilan per slide
5. Phase 6 → trust bar buatan admin
6. Phase 7 → rapikan, uji, tinjau

### Parallel Team Strategy

Dengan dua pengembang: keduanya menyelesaikan Phase 1–3 bersama, lalu satu mengambil US2 (JS dan aksesibilitas slider) sementara yang lain mengambil US3 + US4 (form admin dan preset). Keduanya menyentuh `hero-slider.blade.php`, jadi sepakati batas bagian sebelum mulai.

---

## Notes

- Tanpa dependency baru. Setiap kebutuhan dipenuhi dengan Alpine, Tailwind, GD, dan `DOMDocument` yang sudah ada (Principle V)
- Seluruh kelas Tailwind ditulis literal — kelas hasil interpolasi akan hilang di build produksi
- `hero.blade.php` **tidak** dihapus; ia tetap menjadi fallback nol-banner dan masih dipakai halaman lain
- Jalankan `vendor/bin/pint --dirty --format agent` setelah setiap perubahan PHP
- Commit per task atau per checkpoint, mengikuti kebiasaan repositori
