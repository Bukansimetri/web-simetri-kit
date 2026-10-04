---

description: "Task list for Lanjutan Penyesuaian Website dan Kelengkapan Admin CMS"
---

# Tasks: Lanjutan Penyesuaian Website dan Kelengkapan Admin CMS

**Input**: Design documents from `/specs/031-client-cms-gaps/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/public-routes.md, contracts/admin.md, quickstart.md

**Tests**: Disertakan. Principle IV mewajibkan feature test untuk modul baru (FAQ, detail lowongan, fitur artikel, invalidasi cache), dan halaman yang diubah butuh test render yang diperbarui.

**Organization**: Dikelompokkan per user story (US1–US8, sesuai spec.md).

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Dapat paralel (berkas berbeda, tanpa ketergantungan pada tugas yang belum selesai)
- **[Story]**: US1–US8
- Semua path relatif terhadap root repo. Jalankan `php artisan make:*` dengan `--no-interaction` untuk berkas baru.

---

## Phase 1: Setup

- [X] T001 Pastikan branch `031-client-cms-gaps` berbasis `030-client-design-update` terbaru, lalu jalankan `php artisan test --compact` sebagai baseline (catat jumlah lulus/dilewati)

---

## Phase 2: Foundational (memblokir US2, US4, US6, US8)

**Purpose**: Cache publik ber-versi, dipakai semua story yang mengubah data tampil di halaman publik.

- [X] T002 Ubah `app/Concerns/CachesPublicPages.php`: `rememberPublicPage()` membungkus kunci menjadi `"{$key}:v".self::publicPageVersion()`; tambah `public static function publicPageVersion(): int` (dari `Cache::get('public-page:version', 1)`) dan `public static function bumpPublicPageVersion(): void` (`Cache::forever('public-page:version', publicPageVersion() + 1)`)
- [X] T003 Buat trait `app/Concerns/FlushesPublicPageCache.php` dengan `bootFlushesPublicPageCache()` yang memanggil `CachesPublicPages::bumpPublicPageVersion()` pada event `saved` dan `deleted` (bukan `updated`, agar `incrementQuietly` tidak memicu)

**Checkpoint**: Mekanisme invalidasi tersedia.

---

## Phase 3: User Story 1 - Header Halaman Seragam (Priority: P1) 🎯 MVP

**Goal**: FAQ, Kontak, dan Tentang Kami memakai banner bergambar yang sama dengan halaman lain, dengan gambar yang bisa diedit di Banner Halaman.

**Independent Test**: Buka 7 halaman utama, bandingkan banner; ganti gambar FAQ/Kontak di admin.

### Tests for User Story 1

- [X] T004 [P] [US1] Perbarui `tests/Feature/Pages/PageBannerRenderTest.php`:
  - FAQ, Kontak, dan Tentang Kami merender `page-hero` (breadcrumb "FAQ"/"Kontak"/"Tentang Kami", gambar bawaan, gambar unggahan menggantikan bawaan, berkas hilang jatuh ke bawaan)
  - Kontak tetap memuat formulir kontak
- [X] T005 [P] [US1] Perbarui `tests/Feature/Admin/PageBannerResourceTest.php`: `FaqHero` dan `ContactHero` kini punya `data.image_path`; tidak ada lagi tipe banner tanpa kolom gambar

### Implementation for User Story 1

- [X] T006 [US1] Di `app/Enums/PageBlockType.php`, isi `defaultImagePath()` untuk `FaqHero` (mis. `images/mockup/artikel-4.jpg`) dan `ContactHero` (mis. `images/mockup/home-1.jpg`) setelah melihat gambarnya; tambahkan keduanya ke `imageMaxWidth()` 1920; tambahkan `'image_path' => null` untuk keduanya di `app/Support/PageContent/DefaultPageContent.php`
- [X] T007 [P] [US1] Ganti blok hero di `resources/views/pages/faq.blade.php` dengan `<x-sections.page-hero>` (breadcrumb "FAQ", data `PageBlockType::FaqHero`, gambar via `PageContent::imageUrl(..., FaqHero->defaultImagePath())`), dengan pola yang sama seperti `resources/views/pages/produk/index.blade.php`
- [X] T008 [P] [US1] Ganti blok hero di `resources/views/pages/kontak.blade.php` dengan `<x-sections.page-hero>` (breadcrumb "Kontak", `PageBlockType::ContactHero`). Rapikan padding section formulir di bawahnya agar tidak lagi mengandalkan `pt-32` dari judul lama
- [X] T009 [P] [US1] Ganti `<section>` Page Hero inline di `resources/views/pages/tentang-kami.blade.php` dengan `<x-sections.page-hero>` (breadcrumb "Tentang Kami", `PageBlockType::AboutHero`, gambar bawaan `AboutHero->defaultImagePath()`)
- [X] T010 [US1] Di `tests/Support/LegacyMarkup.php`, tambahkan `faq-hero`, `kontak-hero`, dan `tentang-kami-hero` ke `REDESIGNED`, lalu hapus `tests/Fixtures/legacy-page-content/faq-hero.html`, `kontak-hero.html`, dan `tentang-kami-hero.html`. Pastikan `LegacyMarkupEquivalenceTest` dan `PageContentRenderTest` lulus

**Checkpoint**: Ketujuh halaman berbanner seragam.

---

## Phase 4: User Story 2 - FAQ Bisa Dikelola dari Admin (Priority: P1)

**Goal**: Menu admin FAQ untuk halaman FAQ, Produk, dan Kontak; isi FAQ Produk/Kontak dipindah dari kode ke data bawaan.

**Independent Test**: Ubah satu jawaban per tempat di admin, nonaktifkan satu entri, lalu cek ketiga halaman.

### Tests for User Story 2

- [X] T011 [P] [US2] Buat `tests/Feature/Admin/FaqItemResourceTest.php`:
  - daftar terfilter per Tempat (default Halaman FAQ)
  - buat entri (urutan otomatis terakhir dalam tempatnya), edit, hapus, nonaktifkan
  - urutkan dengan `reorderTable`
  - validasi wajib Pertanyaan/Jawaban
  - Kategori hanya tampil untuk Halaman FAQ
- [X] T012 [P] [US2] Buat `tests/Feature/Pages/FaqPlacementRenderTest.php`:
  - entri `produk` hanya di `/produk`, `kontak` hanya di `/kontak`, `faq` hanya di `/faq`
  - entri nonaktif tidak tampil
  - urutan mengikuti `order`
  - section FAQ Produk/Kontak hilang bila kosong
  - JSON-LD FAQ hanya memuat entri `faq` aktif
- [X] T013 [P] [US2] Perbarui `tests/Feature/Database/PageContentSeederTest.php` (atau buat `tests/Feature/Database/FaqDefaultsInstallerTest.php`):
  - installer menanam 3 FAQ `produk` dan 3 FAQ `kontak` dengan teks persis dari Blade lama
  - tidak menanam ulang bila placement sudah punya entri (termasuk setelah admin mengedit/menghapus sebagian)
  - entri lama otomatis `placement = faq`

### Implementation for User Story 2

- [X] T014 [US2] Buat migrasi `php artisan make:migration add_placement_and_is_active_to_faq_items_table`: `placement` string(20) default `faq` berindeks, `is_active` boolean default true
- [X] T015 [P] [US2] Buat enum `app/Enums/FaqPlacement.php` (`Faq = 'faq'`, `Product = 'produk'`, `Contact = 'kontak'`) dengan `label()` ("Halaman FAQ", "Halaman Produk", "Halaman Kontak") dan `options()`
- [X] T016 [US2] Ubah `app/Models/FaqItem.php`:
  - `fillable` placement, is_active
  - cast `placement` → `FaqPlacement`, `is_active` → bool
  - scope `forPlacement(FaqPlacement)` dan `active()`
  - `use FlushesPublicPageCache`
  - perbarui `database/factories/FaqItemFactory.php` (state `forPlacement`, `inactive`)
- [X] T017 [US2] Tambahkan `DefaultPageContent::faqs(): array` di `app/Support/PageContent/DefaultPageContent.php` dengan 3 entri `produk` (salin persis dari `$productFaqs` di `resources/views/pages/produk/index.blade.php`) dan 3 entri `kontak` (salin persis dari `$consultFaqs` di `resources/views/pages/kontak.blade.php`)
- [X] T018 [US2] Di `app/Support/PageContent/PageContentInstaller.php`, tambahkan penanaman FAQ: per placement di `faqs()`, buat entri berurutan hanya bila `FaqItem::forPlacement($placement)->doesntExist()`. Lewati bila tabel/kolom belum ada (pola `Schema::hasColumn` seperti bagian lain)
- [X] T019 [US2] Buat migrasi data `php artisan make:migration install_default_faq_items` yang memanggil `PageContentInstaller::install()` (pola sama dengan `2026_10_04_135442_install_page_banner_blocks.php`)
- [X] T020 [P] [US2] Buat komponen `resources/views/components/sections/faq-list.blade.php`:
  - props `title`, `subtitle` (opsional), `items`
  - akordeon `<details>` dengan item pertama terbuka, mengikuti markup FAQ Produk saat ini
  - tidak merender apa pun bila `items` kosong
- [X] T021 [US2] Ubah `app/Http/Controllers/Public/ProductController.php` `index` agar mengirim `productFaqs` (FAQ `produk` aktif, urut `order`, di-cache dengan `rememberPublicPage`), lalu ganti blok `@php $productFaqs = [...]` + section FAQ di `resources/views/pages/produk/index.blade.php` dengan `<x-sections.faq-list title="Pertanyaan Seputar Produk" :items="$productFaqs" />`
- [X] T022 [US2] Ubah `app/Http/Controllers/Public/ContactController.php` `show` agar mengirim `consultFaqs` (FAQ `kontak` aktif, berurutan), lalu ganti array `$consultFaqs` + section terkait di `resources/views/pages/kontak.blade.php` dengan `<x-sections.faq-list title="Pertanyaan Seputar Konsultasi" subtitle="Informasi singkat mengenai proses setelah Anda menghubungi kami." :items="$consultFaqs" />`, dengan gaya akordeon setara dengan yang lama
- [X] T023 [US2] Ubah `app/Http/Controllers/Public/FaqController.php` agar hanya mengambil `FaqItem::forPlacement(FaqPlacement::Faq)->active()->orderBy('order')`
- [X] T024 [US2] Buat `app/Filament/Resources/FaqItemResource.php` dan halaman List/Create/Edit lewat `php artisan make:filament-resource FaqItem --no-interaction`, lalu sesuaikan dengan `contracts/admin.md`:
  - grup "Konten Halaman", label "FAQ"
  - tabel: filter Tempat (default `faq`), `reorderable('order')`, kolom Pertanyaan/Tempat/Kategori/Aktif (`ToggleColumn`)
  - form: Tempat, Pertanyaan, Jawaban, Kategori `visible` hanya untuk Halaman FAQ, Aktif
  - `mutateFormDataBeforeCreate` mengisi `order` = max+1 dalam placement
- [X] T025 [US2] Perbarui `tests/Feature/Admin/NavigationStructureTest.php` bila menghitung atau menyebut menu Konten Halaman, dan tambahkan label "FAQ"

**Checkpoint**: FAQ tiga halaman dikelola dari admin.

---

## Phase 5: User Story 3 - Halaman Detail Lowongan (Priority: P1)

**Goal**: Judul "Posisi Terbuka" seragam dan rata tengah; detail lowongan dengan deskripsi utuh.

**Independent Test**: Buka Karir, klik kartu lowongan dengan deskripsi panjang, baca utuh; lowongan nonaktif harus 404.

### Tests for User Story 3

- [X] T026 [P] [US3] Buat `tests/Feature/Pages/JobOpeningDetailTest.php`:
  - detail lowongan aktif menampilkan judul, lokasi, jenis, deskripsi utuh (termasuk baris baru), dan tombol "Lamar Sekarang" ke `/kontak`
  - 404 untuk lowongan nonaktif, id tak dikenal, dan saat `career_module_enabled = false`
  - meta title memuat judul lowongan
  - kartu di `/karir` menaut ke `route('karir.show', $job)`
  - judul "Posisi Terbuka" memuat kelas rata tengah yang sama dengan judul "Mengapa Bergabung"

### Implementation for User Story 3

- [X] T027 [US3] Tambahkan rute `Route::get('/karir/{jobOpening}', [CareerController::class, 'show'])->name('karir.show')` di `routes/web.php`. Ubah `Route::get('/karir', CareerController::class)` menjadi `[CareerController::class, 'index']`, lalu rename `__invoke` menjadi `index` di `app/Http/Controllers/Public/CareerController.php` dan perbarui pemanggil/test yang memakai invokable bila ada
- [X] T028 [US3] Tambahkan `show(JobOpening $jobOpening): View` di `app/Http/Controllers/Public/CareerController.php`: `abort_unless` modul karir aktif dan `$jobOpening->is_active`, lalu render `pages.karir.show`
- [X] T029 [P] [US3] Buat `resources/views/pages/karir/show.blade.php`:
  - extend `layouts.public`
  - `@section('title')` = judul lowongan, `meta_description` = `Str::limit(strip_tags(description), 155)`
  - `<x-sections.page-hero>` (title = judul lowongan, breadcrumb "Karir", gambar banner Karir)
  - konten `max-w-3xl`: badge lokasi/jenis, deskripsi `whitespace-pre-line`, tombol "Lamar Sekarang" → `/kontak`, tautan "← Kembali ke Karir"
- [X] T030 [P] [US3] Ubah `resources/views/components/sections/job-card.blade.php`: judul dan tombol baru "Lihat Detail" menaut ke `route('karir.show', $job)`. Ringkasan tetap `line-clamp-2`, tombol "Lamar Sekarang" tetap ada
- [X] T031 [US3] Ubah judul "Posisi Terbuka" di `resources/views/pages/karir.blade.php` menjadi `text-center` dengan kelas yang sama seperti judul section "Mengapa Bergabung" di file yang sama
- [X] T032 [US3] Tambahkan `use FlushesPublicPageCache` pada `app/Models/JobOpening.php`

**Checkpoint**: Pelamar bisa membaca lowongan utuh.

---

## Phase 6: User Story 4 - Artikel Lebih Lengkap (Priority: P1)

**Goal**: Fitur artikel dari web-ecomm-solarpanel: muat bertahap, Tag Populer, preview, jumlah dilihat, caption, sidebar detail, produk terkait.

**Independent Test**: 10 artikel bertag dan berproduk terkait; uji semua acceptance scenario US4.

### Tests for User Story 4

- [X] T033 [P] [US4] Perbarui `tests/Feature/Pages/ArticlePageTest.php`:
  - 6 artikel pertama + tombol "Muat lebih banyak" ke `halaman=2`; `?halaman=2` menampilkan 12 dan kartu ber-`id="artikel-7"`; tombol hilang bila habis
  - `?kategori=` dan `?tag=` menyaring di server; slug tak dikenal menampilkan semua
  - parameter `q`/`kategori`/`tag` dipertahankan di tautan muat lebih banyak
  - Tag Populer hanya memuat tag dari artikel terbit, maksimal 10, diurut terbanyak; tag aktif ditandai dan ada "Hapus filter"
- [X] T034 [P] [US4] Buat `tests/Feature/Pages/ArticleDetailEnhancementsTest.php`:
  - `view_count` bertambah 1 per kunjungan dan tampil "kali dilihat"
  - caption tampil bila terisi
  - produk terkait tampil berurutan, maks 4, produk terhapus diabaikan, section hilang bila kosong
  - sidebar memuat 5 artikel terbaru selain artikel ini dan kartu Update Mingguan
  - langganan non-JSON dari detail redirect kembali ke detail `#langganan`
- [X] T035 [P] [US4] Buat `tests/Feature/Pages/ArticlePreviewTest.php`:
  - tamu diarahkan ke login
  - user tanpa akses panel 403
  - admin bisa preview draf/terjadwal dengan banner "Mode Preview" dan `noindex`
  - preview tidak menambah `view_count`
- [X] T036 [P] [US4] Perbarui `tests/Feature/Admin/ArticleResourceTest.php`:
  - simpan caption
  - simpan produk terkait berurutan tanpa duplikat (repeater)
  - aksi header Preview ada di halaman edit dan menaut ke `artikel.preview`
  - kolom Dilihat di tabel

### Implementation for User Story 4

- [X] T037 [US4] Buat migrasi `php artisan make:migration add_view_count_and_image_caption_to_articles_table` (`view_count` unsignedInteger default 0, `image_caption` string nullable)
- [X] T038 [US4] Buat migrasi `php artisan make:migration create_article_product_table` (id, `article_id` FK cascade, `product_id` FK cascade, `sort_order` unsignedInteger default 0, timestamps, unik article+product)
- [X] T039 [P] [US4] Buat model `app/Models/ArticleRelatedProduct.php` (table `article_product`, fillable product_id/sort_order, belongsTo article & product) lewat `php artisan make:model`
- [X] T040 [US4] Ubah `app/Models/Article.php`:
  - fillable `image_caption`, cast `view_count` int
  - `relatedProductRows()` hasMany `ArticleRelatedProduct` orderBy sort_order
  - `relatedProducts()` belongsToMany `Product` via `article_product`, withPivot sort_order, orderBy pivot
  - `use FlushesPublicPageCache`
  - perbarui `database/factories/ArticleFactory.php` bila perlu
- [X] T041 [US4] Ubah `app/Http/Controllers/Public/ArticleController.php` `index`:
  - baca `kategori`, `tag`, dan `halaman` (int ≥1, maks 50)
  - filter kategori via `article_category` slug dan tag via `withAnyTags([$slug])` (pastikan dengan tag slug Spatie)
  - ambil `6*halaman+1` baris lalu set `hasMore`
  - hitung `popularTags` (maks 10, hanya artikel terbit)
  - kunci cache memuat semua parameter
  - kirim `activeCategory`, `activeTag`, `page`, `hasMore` ke view
- [X] T042 [US4] Ubah `ArticleController::show`:
  - `incrementQuietly('view_count')` sebelum render
  - eager load `relatedProducts`
  - kirim `latest` (5 artikel terbit terbaru selain ini, di-cache)
  - tambahkan `preview(Request $request, Article $article)` yang `abort_unless($request->user()?->canAccessPanel(Filament::getPanel('admin')), 403)` dan merender view detail dengan `isPreview = true` tanpa increment
- [X] T043 [US4] Tambahkan rute `Route::get('/artikel/{article:slug}/preview', [ArticleController::class, 'preview'])->middleware('auth')->name('artikel.preview')` sebelum rute `artikel.show` di `routes/web.php`. Pastikan tamu diarahkan ke login panel admin (atur `redirectGuestsTo` di `bootstrap/app.php` bila belum mengarah ke `/admin/login`)
- [X] T044 [P] [US4] Ekstrak kartu "Update Mingguan" dari `resources/views/components/sections/article-sidebar.blade.php` ke `resources/views/components/sections/newsletter-card.blade.php` tanpa mengubah markup, lalu pakai `<x-sections.newsletter-card />` di sidebar
- [X] T045 [US4] Ubah `app/Http/Controllers/Public/NewsletterController.php`: redirect non-JSON ke `url()->previous()` (fallback `/artikel`) + `#langganan`, dan perbarui `tests/Feature/Pages/NewsletterSubscribeTest.php` sesuai
- [X] T046 [US4] Tambahkan blok "Tag Populer" di `resources/views/components/sections/article-sidebar.blade.php`:
  - props `popularTags`, `activeTag`, `search`, `activeCategory`
  - tautan `?tag=` mempertahankan `q`/`kategori`; tag aktif berkelas berbeda; tautan "Hapus filter"
- [X] T047 [US4] Ubah `resources/views/pages/artikel/index.blade.php`:
  - tombol kategori menjadi tautan `?kategori=` (pertahankan `q`/`tag`, `Semua` tanpa `kategori`), hapus `x-data`/`x-show` kategori
  - kartu dibungkus `<div id="artikel-{n}" data-article-item>`
  - tombol "Muat lebih banyak" `<a>` ke `halaman+1#artikel-{6*halaman+1}` dengan Alpine `@click.prevent` yang `fetch` URL, mengambil `[data-article-item]` baru via `DOMParser`, menambahkannya ke grid, dan memperbarui/menghapus tombol
  - teruskan prop baru ke sidebar
- [X] T048 [US4] Ubah `resources/views/pages/artikel/show.blade.php`:
  - layout `lg:grid-cols-[minmax(0,1fr)_320px]`
  - "N kali dilihat" di metadata
  - `<figure>` + `<figcaption>` bila `image_caption` terisi
  - section "Produk Terkait" (maks 4, `x-sections.product-card` `simple`)
  - sidebar berisi "Artikel Terbaru" (judul, tanggal, thumbnail kecil) dan `<x-sections.newsletter-card />`
  - banner "Mode Preview" dan `<meta name="robots" content="noindex">` bila `$isPreview`
  - artikel terkait, tag, dan bagikan tetap ada
- [X] T049 [US4] Ubah `app/Filament/Resources/ArticleResource.php`:
  - `TextInput::make('image_caption')` (Keterangan Gambar, maks 255) di section Featured Image
  - section baru "Produk Terkait" dengan `Repeater::make('relatedProductRows')->relationship()->orderColumn('sort_order')->reorderable()` berisi `Select::make('product_id')->relationship('product','name')->searchable()->distinct()->required()`
  - kolom tabel `view_count` (Dilihat, sortable)
- [X] T050 [US4] Tambahkan aksi header `Action::make('preview')->label('Preview')->url(fn () => route('artikel.preview', $this->record))->openUrlInNewTab()` di `app/Filament/Resources/ArticleResource/Pages/EditArticle.php`, lalu panggil `CachesPublicPages::bumpPublicPageVersion()` di `afterSave()` pada `EditArticle.php` dan `CreateArticle.php` (sinkronisasi tag/produk terkait terjadi setelah `saved`)

**Checkpoint**: Modul artikel setara web-ecomm-solarpanel ditambah fitur yang sudah ada.

---

## Phase 7: User Story 5 - Tipografi Seragam (Priority: P2)

**Goal**: Semua judul dan teks tombol CTA bold (700).

**Independent Test**: Tidak ada kelas extrabold/black di halaman publik; tombol CTA seragam bold.

### Tests for User Story 5

- [X] T051 [P] [US5] Buat `tests/Feature/Public/TypographyConsistencyTest.php`: render `/`, `/tentang-kami`, `/produk`, `/produk/{slug}`, `/artikel`, `/artikel/{slug}`, `/portfolio`, `/karir`, `/faq`, `/kontak` dengan data minimal, lalu pastikan tidak ada `font-extrabold`/`font-black` di `<body>`

### Implementation for User Story 5

- [X] T052 [US5] Ganti `font-extrabold` dan `font-black` dengan `font-bold` di semua berkas `resources/views/pages/**` dan `resources/views/components/{sections,layout}/**` (cek dengan `grep -rn "font-extrabold\|font-black" resources/views`). Jangan mengubah `resources/views/filament/**`
- [X] T053 [US5] Di `resources/css/app.css`, ubah `--text-headline-lg--font-weight` dan `--text-headline-lg-mobile--font-weight` menjadi 700
- [X] T054 [US5] Seragamkan teks tombol CTA (tombol `btn-fill`, tombol di `components/sections/cta-band.blade.php`, CTA penutup `home.blade.php`, CTA Produk/Artikel/Karir/FAQ/Tentang Kami, tombol hero/hero-slide, tombol header "Konsultasi Gratis", tombol sidebar artikel) ke `font-bold`. Hapus `font-semibold`/`font-medium` yang bertentangan pada elemen yang sama
- [X] T055 [US5] Perbarui fixture `tests/Fixtures/legacy-page-content/*.html` yang terdampak dengan penggantian kelas yang sama persis (T052–T054), lalu jalankan `LegacyMarkupEquivalenceTest`. Struktur selain kelas font tidak boleh berubah

**Checkpoint**: Tipografi seragam.

---

## Phase 8: User Story 6 - Pilih Produk di Beranda (Priority: P2)

**Goal**: Toggle "Tampilkan di Beranda" maksimal 3.

**Independent Test**: Tandai 2 dan 3 produk, cek beranda; produk keempat ditolak; tanpa tanda kembali ke 3 teratas.

### Tests for User Story 6

- [X] T056 [P] [US6] Buat `tests/Feature/Pages/HomeFeaturedProductsTest.php`:
  - tanpa tanda → 3 teratas menurut `order`
  - 2 bertanda → hanya 2 itu (urut `order`)
  - produk bertanda dihapus → sisanya / fallback
  - kartu kedua tetap berbadge "Terpopuler" bila ada ≥2 produk
- [X] T057 [P] [US6] Perbarui `tests/Feature/Admin/ProductResourceTest.php`:
  - toggle tersimpan
  - menandai produk keempat ditolak dengan pesan "Maksimal 3 produk dapat ditampilkan di Beranda."
  - mengedit produk yang sudah bertanda tetap bisa disimpan saat sudah ada 3

### Implementation for User Story 6

- [X] T058 [US6] Buat migrasi `php artisan make:migration add_show_on_home_to_products_table` (`show_on_home` boolean default false)
- [X] T059 [US6] Ubah `app/Models/Product.php`:
  - fillable + cast `show_on_home`
  - static `forHome(): Collection` (bertanda urut `order` limit 3, fallback 3 teratas)
  - `use FlushesPublicPageCache`
  - tambahkan juga trait pada `app/Models/Category.php`
- [X] T060 [US6] Ubah `app/Http/Controllers/Public/HomeController.php` agar memakai `Product::forHome()`
- [X] T061 [US6] Ubah `app/Filament/Resources/ProductResource.php`:
  - `Toggle::make('show_on_home')->label('Tampilkan di Beranda')` dengan rule closure yang menolak bila `Product::where('show_on_home', true)->whereKeyNot($record?->id)->count() >= 3`
  - `IconColumn::make('show_on_home')->label('Beranda')->boolean()` di tabel

**Checkpoint**: Admin mengatur produk beranda.

---

## Phase 9: User Story 7 - Perbaikan Kecil (Priority: P2)

**Goal**: CTA ke kalkulator, kutipan kosong, Partner Kami di Tentang Kami, latar Tim Kami, hapus filter Produk, teks footer.

**Independent Test**: Cek tiap perilaku di browser dan admin.

### Tests for User Story 7

- [X] T062 [P] [US7] Perbarui `tests/Feature/Pages/CallToActionRenderTest.php` (atau `HomePageTest.php`): tombol kedua CTA penutup Beranda `href` berakhir `/#kalkulator`, dan halaman memiliki `id="kalkulator"`
- [X] T063 [P] [US7] Perbarui `tests/Feature/Pages/AboutPageSectionsTest.php`, `AboutPageTestimonialsTest.php`, dan `AboutPageTeamMembersTest.php`:
  - kutipan kosong (null atau `<p></p>`) tidak merender blok `border-l-4`; kutipan terisi tetap tampil
  - judul testimoni "Partner Kami" dan label "Testimoni"
  - Tim Kami: `<section>` luar tanpa `max-w-7xl`, wrapper isi `max-w-7xl mx-auto`, kartu dalam `flex flex-wrap justify-center`; render dengan 1 anggota
- [X] T064 [P] [US7] Perbarui `tests/Feature/Pages/ProductPageTest.php`: `/produk` tidak memuat tombol kategori ("Semua" sebagai tombol filter) dan menampilkan produk semua kategori
- [X] T065 [P] [US7] Perbarui `tests/Feature/Public/FooterCompanyInfoTest.php` (atau buat `FooterDescriptionTest.php`):
  - teks bawaan tampil
  - teks dari settings menggantikan bawaan dengan baris baru menjadi `<br>`
  - HTML di-escape
  - kosong → paragraf tidak dirender
- [X] T066 [P] [US7] Perbarui test halaman Pengaturan Umum (cari di `tests/Feature/Settings/`): field `footer_description` ada di section "Footer" dan tersimpan

### Implementation for User Story 7

- [X] T067 [P] [US7] Di `resources/views/pages/home.blade.php` CTA penutup, ganti `href="{{ url('/kontak') }}"` tombol kedua menjadi `url('/#kalkulator')`. Perbarui fixture `tests/Fixtures/legacy-page-content/home-cta.html` dengan perubahan href yang sama
- [X] T068 [P] [US7] Di `resources/views/pages/tentang-kami.blade.php`, bungkus blok kutipan (`pl-8 border-l-4 ...`) dengan `@if (filled(trim(strip_tags((string) $whoWeAre->value('quote')))))`
- [X] T069 [P] [US7] Di `resources/views/components/sections/testimonials.blade.php`, ganti judul "Apa Kata Klien Kami" menjadi "Partner Kami" (label "Testimoni" tetap). Perbarui asersi `Apa Kata Klien Kami` di test terkait
- [X] T070 [P] [US7] Ubah `resources/views/components/sections/team-members.blade.php`:
  - `<section class="py-24 px-6 w-full">` (latar sama dengan section tetangga) dengan wrapper dalam `max-w-7xl mx-auto`
  - grid diganti `flex flex-wrap justify-center gap-8`, kartu lebar tetap `w-[calc(50%-1rem)] md:w-56`
- [X] T071 [P] [US7] Hapus blok tombol kategori dan `x-data`/`x-show` kategori di `resources/views/pages/produk/index.blade.php`; hapus `categories` dari `ProductController::index` bila tidak dipakai lagi
- [X] T072 [US7] Buat settings migration `database/settings/<timestamp>_add_footer_description_to_site_settings.php` yang menambah `site.footer_description` dengan teks bawaan footer sekarang; tambah `public ?string $footer_description;` di `app/Settings/SiteSettings.php`
- [X] T073 [US7] Tambahkan section "Footer" dengan `Textarea::make('footer_description')->label('Deskripsi Footer')->rows(3)->maxLength(500)` di `app/Filament/Pages/SiteSettingsPage.php`
- [X] T074 [US7] Di `resources/views/components/layout/footer.blade.php`, ganti teks deskripsi statis dengan `@if (filled($site->footer_description)) <p ...>{!! nl2br(e($site->footer_description)) !!}</p> @endif` (kelas `<p>` tetap)

**Checkpoint**: Semua perbaikan kecil selesai.

---

## Phase 10: User Story 8 - Perubahan Admin Langsung Tampil (Priority: P2)

**Goal**: Semua variasi halaman publik langsung segar setelah perubahan admin.

**Independent Test**: Buka Portofolio "Semua", tambah kategori+proyek, muat ulang.

### Tests for User Story 8

- [X] T075 [P] [US8] Perbarui `tests/Feature/Public/PublicPageCachingTest.php` dengan driver cache `array`:
  - kunjungi `/portfolio` dan `/portfolio?kategori=x`, lalu buat kategori+proyek baru; kunjungan berikut ke `/portfolio` langsung memuat pil dan proyek baru
  - pola sama untuk `/produk` (produk baru), `/artikel` (artikel baru), dan `/` (testimoni baru)
  - `incrementQuietly` view_count tidak menaikkan versi

### Implementation for User Story 8

- [X] T076 [US8] Tambahkan `use FlushesPublicPageCache` pada model publik yang belum: `app/Models/ArticleCategory.php`, `PortfolioProject.php`, `PortfolioCategory.php`, `Testimonial.php`, `TeamMember.php`, `ClientLogo.php`, `Banner.php` (Product, Category, Article, FaqItem, JobOpening sudah di story masing-masing)
- [X] T077 [US8] Periksa semua pemakaian `rememberPublicPage` (`grep -rn rememberPublicPage app`) dan pastikan setiap kunci memuat semua parameter request yang memengaruhi hasil

**Checkpoint**: Bug Portofolio teratasi untuk semua halaman.

---

## Phase 11: Polish & Cross-Cutting

- [X] T078 [P] Perbarui `docs/manual-operator.md`:
  - menu **FAQ** (Tempat, Kategori, Aktif, urutan)
  - **Tampilkan di Beranda** (maks 3)
  - section **Footer** di Pengaturan Umum
  - Artikel (**Keterangan Gambar**, **Produk Terkait**, **Preview**, kolom Dilihat)
  - Banner Halaman (FAQ dan Kontak kini bergambar)
  - Karir (halaman detail lowongan)
  - Artikel publik (Tag Populer, Muat lebih banyak)

  Jalankan `php artisan test --compact --filter=OperatorManualTest`
- [X] T079 Jalankan `vendor/bin/pint --dirty --format agent`
- [X] T080 Jalankan `php artisan test --compact`; perbarui tes yang memeriksa tampilan lama sesuai desain baru (jangan menghapus tes)
- [X] T081 Jalankan `npm run build` lalu verifikasi visual sesuai `specs/031-client-cms-gaps/quickstart.md` pada 390 px dan 1440 px dengan database sementara (pola verifikasi spec 030), termasuk "Muat lebih banyak" dengan dan tanpa JavaScript
- [X] T082 (selesai: 13 item klien terpenuhi; interpretasi yang perlu konfirmasi: "divide" Tim Kami dipahami sebagai latar section yang tidak penuh, dan tampilan tombol CTA diseragamkan bold 700) Bandingkan hasil dengan dokumen klien "Fitur yang belum ada untuk merubah tampilan pada website" per item ❌/⚠️ dan catat penyimpangan (SC-001)

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)** → **Foundational (Phase 2)**: T002–T003 memblokir US2, US3, US4, US6, US8 (trait `FlushesPublicPageCache`).
- **US1** hanya butuh Setup, bisa langsung setelah Phase 1.
- **US2, US3, US4, US6, US8** setelah Phase 2.
- **US5 (tipografi)** sebaiknya dikerjakan **setelah** US1, US2, US3, US4, dan US7, karena menyentuh banyak view yang sama dan fixture. Mengerjakannya terakhir di antara story mencegah konflik dan pengulangan pembaruan fixture.
- **US7** independen, tetapi T067 dan T055 sama-sama mengubah `home-cta.html`, jadi kerjakan berurutan.
- **Polish** setelah semua story.

### Berkas yang disentuh banyak story (kerjakan berurutan)

- `resources/views/pages/kontak.blade.php`: T008 (US1) → T022 (US2) → T052 (US5)
- `resources/views/pages/tentang-kami.blade.php`: T009 (US1) → T068 (US7) → T052 (US5)
- `resources/views/pages/produk/index.blade.php`: T021 (US2) → T071 (US7) → T052 (US5)
- `resources/views/pages/artikel/index.blade.php` dan `components/sections/article-sidebar.blade.php`: T044 → T046 → T047 (US4)
- `routes/web.php`: T027 (US3) → T043 (US4)
- `tests/Support/LegacyMarkup.php` dan fixtures: T010 (US1) → T055 (US5) / T067 (US7)

### Peluang Paralel

- T004/T005, T011/T012/T013, T026, T033–T036, T056/T057, T062–T066 (test) bisa ditulis bersamaan.
- T007/T008/T009 (tiga view berbeda).
- T015, T020 (enum dan komponen baru) paralel dengan T014.
- T029/T030 paralel.
- T039 dan T044 paralel dengan migrasi artikel.
- T067–T071 (berkas berbeda).

### Contoh Paralel: User Story 4

```text
T033 ArticlePageTest   T034 ArticleDetailEnhancementsTest   T035 ArticlePreviewTest   T036 ArticleResourceTest
T039 ArticleRelatedProduct model   T044 newsletter-card component
```

---

## Implementation Strategy

### MVP

1. Phase 1–2 (T001–T003).
2. US1 header seragam (T004–T010), lalu validasi visual. Ini perubahan yang paling terlihat klien.

### Pengiriman Bertahap

1. P1: US1 → US2 (FAQ) → US3 (lowongan) → US4 (artikel).
2. P2: US6 (produk beranda) → US7 (perbaikan kecil) → US8 (cache) → US5 (tipografi, terakhir).
3. Polish: dokumentasi, uji penuh, verifikasi visual.
4. Commit per story agar mudah ditinjau.

## Notes

- Spec 031 dibangun di atas PR #27. Bila PR #27 berubah saat review, rebase branch ini sebelum implementasi.
- Pembaruan fixture `LegacyMarkup` hanya untuk perubahan desain yang disengaja di spec ini. Selain itu fixture harus tetap identik.
- `FaqItemSeeder` tetap seeder demo dan tidak dipanggil migrasi.
