---

description: "Task list for 029-why-choose-admin"
---

# Tasks: Kelola Section Konten & CTA dari Panel Admin

**Input**: Design documents from `/specs/029-why-choose-admin/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/public-render.md, contracts/admin-modules.md, quickstart.md

**Tests**: Wajib. Constitution Principle IV mewajibkan test untuk setiap modul konten, dan SC-001/SC-006 menuntut bukti otomatis bahwa tampilan tidak berubah.

**Organization**: Dikelompokkan per user story (US1–US6 sesuai spec.md) agar tiap story bisa dikerjakan dan diuji mandiri.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Bisa paralel (file berbeda, tidak bergantung pada task yang belum selesai)
- **[Story]**: US1–US6 sesuai spec.md

## Aturan umum untuk semua task

- Ikuti konvensi file saudara: model seperti `app/Models/Testimonial.php`, resource seperti `app/Filament/Resources/TestimonialResource.php`, test admin seperti `tests/Feature/Admin/TestimonialResourceTest.php` (salin setup user/role/login-nya).
- Buat file lewat `php artisan make:*` dengan `--no-interaction` bila ada generatornya.
- **Jangan ubah class, atribut, atau struktur elemen HTML di Blade.** Hanya literal teks/ikon yang diganti variabel (FR-012a). Setiap selesai mengubah Blade, jalankan `LegacyMarkupEquivalenceTest`.
- Teks dari database dirender apa adanya lewat `{{ $x }}` (revisi 2026-10-02: `PageContent::text()` dan penggantian `{app_name}` saat render dihapus; nama situs hanya diisi sekali saat instalasi lewat `DefaultPageContent::forCurrentSite()`). Judul lewat `PageContent::multiline()`, CTA Detail Produk lewat `PageContent::withProductName()`. Satu-satunya HTML yang disisipkan adalah `<br>` dari `multiline()`.
- Setelah mengubah file PHP: `vendor/bin/pint --dirty --format agent`.

---

## Phase 1: Setup (tangkap tampilan lama — WAJIB sebelum Blade diubah)

**Purpose**: Membekukan HTML lama sebagai acuan "tampilan tidak berubah" (research R7, contracts/public-render.md §4).

- [X] T001 Buat helper `tests/Support/LegacyMarkup.php` (namespace `Tests\Support`, pastikan `Tests\\` → `tests/` di `composer.json` autoload-dev) berisi:
  - `public static function extract(string $html, string $xpath): string`: muat HTML ke `DOMDocument` dengan `libxml_use_internal_errors(true)`, prefiks `'<?xml encoding="UTF-8">'.$html` (agar `—` dan karakter UTF-8 lain tidak rusak), dan `LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD`, ambil node **terakhir** yang cocok dengan XPath, kembalikan `saveHTML($node)` yang sudah dinormalisasi. Gagal (`fail`) jika node tidak ditemukan. Tulis docblock satu baris: atribut Alpine (`@click`, `:class`) dibuang libxml, jadi tidak ikut terbandingkan.
  - `public static function normalize(string $html): string`: rapatkan whitespace berurutan jadi satu spasi, hapus spasi di antara `>` dan `<`, lalu `trim`.
  - `public const FRAGMENTS`: array `name => [path, xpath]` untuk 13 fragmen:
    - `home-why-choose` `/` `//section[.//h2[contains(., 'Mengapa Beralih')]]`
    - `home-how-it-works` `/` `//section[.//h2[contains(., 'Sederhana dan Mulus')]]`
    - `home-cta` `/` `//section[.//h2[contains(., 'Siap beralih ke')]]`
    - `karir-values` `/karir` `//section[.//h2[contains(., 'Mengapa Bergabung')]]`
    - `karir-recruitment` `/karir` `//section[.//h2[contains(., 'Proses Rekrutmen')]]`
    - `karir-cta` `/karir` `//section[.//h2[contains(., 'Tidak menemukan posisi')]]`
    - `produk-cta-kalkulator` `/produk` `//section[.//h2[contains(., 'Bingung pilih')]]`
    - `produk-cta-penutup` `/produk` `//section[.//h2[contains(., 'Belum yakin kapasitas')]]`
    - `produk-detail-cta` `/produk/produk-uji` `//section[.//h2[contains(., 'Masa Depan Energi Anda')]]`
    - `artikel-index-cta` `/artikel` `//section[.//h2[contains(., 'Punya pertanyaan seputar')]]`
    - `artikel-detail-cta` `/artikel/artikel-uji` `//section[.//h2[contains(., 'Siap beralih ke energi surya')]]`
    - `tentang-kami-cta` `/tentang-kami` `//section[.//h2[contains(., 'Ingin tahu lebih lanjut')]]`
    - `faq-cta` `/faq` `//section[.//h2[contains(., 'Masih ada pertanyaan lain')]]`
  - `public static function seedDeterministicState(): void`: set `SiteSettings::site_name = 'SUOER'` (agar `{app_name}` dirender identik dengan fixture lama, research R11), aktifkan modul karir (`SiteSettings::career_module_enabled = true`, lalu `save()`), buat `Product` dengan `name` "Produk Uji" dan `slug` "produk-uji" tanpa gambar, dan buat `Article` terbit dengan `slug` "artikel-uji". Lihat pola factory di `tests/Feature/Pages/ProductPageTest.php` & `tests/Feature/Pages/ArticlePageTest.php`.
- [X] T002 Buat `tests/Feature/Pages/CaptureLegacyPageContentFixturesTest.php` (`RefreshDatabase`). Jika `env('CAPTURE_LEGACY_FIXTURES')` kosong, panggil `markTestSkipped`. Jika tidak, panggil `LegacyMarkup::seedDeterministicState()`, lalu untuk tiap `FRAGMENTS`: GET path, `assertOk`, dan tulis `LegacyMarkup::extract(...)` ke `tests/Fixtures/legacy-page-content/{name}.html`.
- [X] T003 Pada kode yang **belum diubah**, jalankan `CAPTURE_LEGACY_FIXTURES=1 php artisan test --compact --filter=CaptureLegacyPageContentFixturesTest`. Pastikan 13 file `tests/Fixtures/legacy-page-content/*.html` terbentuk, periksa isinya berisi teks lama, lalu commit.
- [ ] T004 [P] Ambil tangkapan layar "sebelum" (390px & 1440px) untuk `/`, `/produk`, `/produk/{slug}`, `/artikel`, `/artikel/{slug}`, `/tentang-kami`, `/faq`, `/karir`, simpan di `specs/029-why-choose-admin/screenshots/before/` (manual, atau gunakan skill browse jika tersedia).

**Checkpoint**: Fixture HTML lama sudah ter-commit. Blade boleh mulai diubah.

---

## Phase 2: Foundational (blok semua user story)

**Purpose**: Skema, model, nilai bawaan, seeder + migrasi data, akses data untuk Blade, kelas dasar resource admin, dan test kesetaraan.

- [X] T005 [P] Buat enum `app/Enums/PageSection.php` (string-backed) dengan case `WhyChoose='beranda.mengapa-beralih'`, `HowItWorks='beranda.cara-kerja'`, `CareerValues='karir.mengapa-bergabung'`, `RecruitmentProcess='karir.proses-rekrutmen'` dan method `label()`, `pageName()` ("Beranda"/"Karir"), `maxActiveItems()` (3/4/3/4), `hasIcon()` (WhyChoose, CareerValues), `supportsEmphasis()` (WhyChoose, HowItWorks), `hasSubtitle()` (semua kecuali RecruitmentProcess), `stepNumber(int $position): ?string` (HowItWorks → `sprintf('%02d')`, RecruitmentProcess → `(string)`, lainnya `null`). Nilai persis sesuai data-model.md.
- [X] T006 [P] Buat enum `app/Enums/CtaPlacement.php` (string-backed) dengan 9 case & value sesuai data-model.md (`Home='beranda'`, `ProductCalculator='produk-kalkulator'`, `ProductClosing='produk-penutup'`, `ProductDetail='produk-detail'`, `ArticleIndex='artikel-daftar'`, `ArticleDetail='artikel-detail'`, `About='tentang-kami'`, `Faq='faq'`, `Career='karir'`) dan method `label()`, `bodyLabel()` ("Paragraf" untuk Home/Product*/ArticleIndex, "Subjudul" untuk sisanya), `hasSecondaryButton()` (hanya Home), `supportsProductToken()` (hanya ProductDetail).
- [X] T007 [P] Buat `app/Support/PageContent/IconOptions.php` dengan `public const ICONS` (± 25 nama Material Symbols kurasi yang **wajib memuat** `savings`, `verified`, `eco`, `lightbulb`, `groups`, `public`, ditambah mis. `solar_power`, `bolt`, `energy_savings_leaf`, `battery_charging_full`, `payments`, `shield`, `workspace_premium`, `handshake`, `support_agent`, `engineering`, `build`, `schedule`, `trending_up`, `forest`, `home`, `factory`, `star`, `thumb_up`, `diversity_3`), `keys(): array`, dan `options(): array` (label HTML `<span class="material-symbols-outlined" style="vertical-align:middle">{icon}</span> {icon}`).
- [X] T008 Buat migrasi `database/migrations/2026_09_30_000001_create_section_items_table.php` (`php artisan make:migration create_section_items_table`, lalu ganti nama file sesuai timestamp): `id`, `section` string(64), `icon` string(64) nullable, `title` string(60), `description` string(200), `is_active` boolean default true, `is_emphasized` boolean default false, `order` unsignedInteger default 0, timestamps, index `['section','is_active','order']`.
- [X] T009 Buat migrasi `database/migrations/2026_09_30_000002_create_section_headings_table.php`: `id`, `section` string(64) unique, `title` string(80), `subtitle` string(250) nullable, timestamps.
- [X] T010 Buat migrasi `database/migrations/2026_09_30_000003_create_call_to_actions_table.php`: `id`, `placement` string(64) unique, `title` string(80), `body` string(300) nullable, `primary_label` string(40), `secondary_label` string(40) nullable, timestamps.
- [X] T011 Buat model `app/Models/SectionItem.php` (`php artisan make:model SectionItem --factory`): `$fillable` (section, icon, title, description, is_active, is_emphasized, order), casts (`section` → `PageSection`, boolean, integer), scopes `forSection(PageSection)`, `active()`, `ordered()` (order lalu id).
- [X] T012 [P] Buat model `app/Models/SectionHeading.php` (`--factory`): fillable (section, title, subtitle), cast `section` → `PageSection`.
- [X] T013 [P] Buat model `app/Models/CallToAction.php` (`--factory`): fillable (placement, title, body, primary_label, secondary_label), cast `placement` → `CtaPlacement`.
- [X] T014 [P] Lengkapi factory di `database/factories/SectionItemFactory.php`, `SectionHeadingFactory.php`, `CallToActionFactory.php` (judul ≤ 60/80, deskripsi ≤ 200, label ≤ 40, `section` default `PageSection::WhyChoose`, `icon` default `'savings'`). Tambahkan state `inactive()` dan `emphasized()` di SectionItemFactory.
- [X] T015 Buat `app/Support/PageContent/DefaultPageContent.php` dengan method statis `headings(): array<string, array{title:string, subtitle:?string}>` (key = value enum), `items(): array<string, list<array{icon:?string,title:string,description:string,is_emphasized:bool}>>`, dan `ctas(): array<string, array{title:string, body:?string, primary_label:string, secondary_label:?string}>`. **Salin teks langsung dari file sumber, karakter per karakter** (tanda `—`, `&`, `%`, dan `<br>` di judul ditulis sebagai `\n`). **Setiap kemunculan `SUOER` diganti token `{app_name}`** (Principle I, research R11). Kelas ini MUST NOT memuat literal "SUOER".
  - WhyChoose: `resources/views/components/sections/why-choose.blade.php` (array `$reasons` + `<h2>`/`<p>`)
  - HowItWorks: `resources/views/components/sections/how-it-works.blade.php` (array `$steps` + `<h2>`/`<p>`)
  - CareerValues & RecruitmentProcess: `resources/views/pages/karir.blade.php` (`$values`, `$process`, `<h2>`/`<p>`)
  - CTA Home: `resources/views/pages/home.blade.php` blok `{{-- CTA Penutup --}}` (judul `Siap beralih ke\nenergi matahari?`)
  - CTA ProductCalculator & ProductClosing: `resources/views/pages/produk/index.blade.php`
  - CTA ProductDetail: `resources/views/pages/produk/show.blade.php` blok "Masa Depan Energi Anda" (ganti `{{ Str::lower($product->name) }}` dengan `{produk}`)
  - CTA ArticleIndex: `resources/views/pages/artikel/index.blade.php` blok `{{-- Newsletter --}}`
  - CTA ArticleDetail, Faq, Career: prop `<x-sections.cta-band>` di `pages/artikel/show.blade.php`, `pages/faq.blade.php`, `pages/karir.blade.php`
  - CTA About: default `@props` di `resources/views/components/sections/cta-band.blade.php`
- [X] T016 Buat `app/Support/PageContent/PageContentInstaller.php` dengan `public static function install(): void`:
  - Untuk tiap `PageSection`: **lewati** bila `SectionHeading` untuk section itu sudah ada. Jika belum, dalam `DB::transaction` buat heading lalu item dari `DefaultPageContent::items()` dengan `order` = indeks.
  - Untuk tiap `CtaPlacement`: `CallToAction::firstOrCreate(['placement' => …], DefaultPageContent::ctas()[…])`.
- [X] T016a Buat `database/seeders/PageContentSeeder.php` (`php artisan make:seeder PageContentSeeder`) yang `run()`-nya **hanya** memanggil `PageContentInstaller::install();`. Daftarkan `$this->call(PageContentSeeder::class);` di `database/seeders/DatabaseSeeder.php` setelah `MenuSeeder`.
- [X] T017 Buat migrasi data `database/migrations/2026_09_30_000004_install_default_page_content.php` yang `up()` memanggil `\App\Support\PageContent\PageContentInstaller::install();` dan `down()` kosong. **Jangan mereferensikan kelas apa pun di `Database\Seeders`** (constitution: seeder tidak boleh auto-run di migrasi production, research R4). Beri docblock singkat: menanam konten live yang sebelumnya ada di Blade.
- [X] T018 Buat `app/Support/PageContent/SectionContent.php` (readonly: `string $title`, `?string $subtitle`, `Collection $items`) dan `app/Support/PageContent/PageContent.php` dengan:
  - `section(PageSection $section): ?SectionContent`: ambil item `forSection()->active()->ordered()`, kembalikan `null` bila kosong. Heading dari DB, fallback ke `DefaultPageContent::headings()` bila baris tidak ada. Paksa `is_emphasized=false` di hasil bila `!supportsEmphasis()`.
  - `cta(CtaPlacement $placement): CallToAction`: baris DB atau `new CallToAction([...DefaultPageContent::ctas()[…], 'placement' => $placement])` (FR-024).
  - `text(?string $text): string`: ganti `{app_name}` dengan `app(SiteSettings::class)->site_name ?: config('app.name')` (resolusi sama dengan `$appName` di `resources/views/pages/tentang-kami.blade.php`). Hasil belum di-escape, karena Blade `{{ }}` yang meng-escape.
  - `multiline(?string $text): HtmlString`: `str_replace("\n", '<br>', e(static::text(str_replace("\r\n", "\n", (string) $text))))`.
  - `withProductName(?string $body, string $productName): HtmlString`: `e(static::text($body))` lalu ganti `{produk}` dengan `e(Str::lower($productName))`.
- [X] T019 Buat kelas dasar abstrak `app/Filament/Support/SectionItemResource.php` (extends `Filament\Resources\Resource`, **di luar** `app/Filament/Resources` agar tidak ter-discover):
  - `protected static ?string $model = SectionItem::class`, `$navigationGroup = 'Konten Halaman'`, dan `abstract public static function section(): PageSection`.
  - `getNavigationLabel()` = `section()->label()`, `getEloquentQuery()` di-scope ke `section()`.
  - `form()`: `Select icon` (visible `hasIcon()`, required, `searchable`, `allowHtml`, `options(IconOptions::options())`, `rule(Rule::in(IconOptions::keys()))`, pesan "Pilih ikon dari daftar yang tersedia."), `TextInput title` (required, maxLength 60, helper "Tulis {app_name} untuk menampilkan nama situs."), `Textarea description` (required, maxLength 200, rows 3), `Toggle is_active` (default true).
  - `table()`: `defaultSort('order')`, kolom ikon (`TextColumn` dengan `html()` render span Material Symbols, visible `hasIcon()`), `title`, `ToggleColumn is_active`, aksi Edit/Delete, bulk Delete.
  - Buat juga kelas dasar halaman abstrak `app/Filament/Support/Pages/ListSectionItems.php` (extends `ListRecords`, header action Create), `CreateSectionItem.php` (extends `CreateRecord`, `mutateFormDataBeforeCreate` mengisi `section` = `static::getResource()::section()` dan `order` = `max(order)+1` di section itu), dan `EditSectionItem.php` (extends `EditRecord`, header action Delete).
- [X] T020 [P] Unit test `tests/Unit/DefaultPageContentTest.php` (PHPUnit `TestCase`): assert **literal** setiap judul/subjudul/item/CTA sama persis dengan teks lama, dengan satu-satunya perbedaan `SUOER` → `{app_name}` (tulis ulang literal di test sebagai acuan SC-006), jumlah item 3/4/3/4, ikon, `is_emphasized` hanya "Garansi Panjang" & "Inverter", `DefaultPageContent::ctas()` punya 9 key = semua `CtaPlacement` value, dan semua ikon bawaan ada di `IconOptions::keys()`. Tambahkan juga assert bahwa **tidak ada** string `SUOER` di seluruh output `DefaultPageContent` (serialisasi semua array), dan bahwa judul WhyChoose, judul CTA About, serta label utama CTA ProductClosing memuat `{app_name}`.
- [X] T021 [P] Test `tests/Feature/Database/PageContentSeederTest.php` (`RefreshDatabase`):
  - (a) setelah migrasi: 14 item (3/4/3/4), 4 heading, 9 CTA
  - (b) menjalankan `PageContentInstaller::install()` 2× lalu `PageContentSeeder` 1× tidak menambah baris
  - (c) heading/CTA yang diedit tidak tertimpa
  - (d) section dengan heading tapi semua item dihapus tidak di-seed ulang
  - (e) isi file `database/migrations/2026_09_30_000004_install_default_page_content.php` tidak memuat string `Database\Seeders`
- [X] T022 Buat `tests/Feature/Pages/LegacyMarkupEquivalenceTest.php` (`RefreshDatabase`) dengan data provider dari `LegacyMarkup::FRAGMENTS`. Tiap kasus menjalankan `seedDeterministicState()`, GET path, lalu `assertSame(file_get_contents(fixture), LegacyMarkup::extract($html, $xpath))`. Jalankan dan pastikan **13/13 lulus** sebelum lanjut, karena Blade belum diubah.

**Checkpoint**: `php artisan migrate:fresh` mengisi konten bawaan, test fondasi hijau, dan kelas dasar admin siap dipakai.

---

## Phase 3: User Story 1 — Admin mengubah isi kartu "Mengapa Beralih" (P1) 🎯 MVP

**Goal**: Section "Mengapa Beralih" di beranda dibaca dari database dan bisa diedit dari modul "Beranda – Mengapa Beralih", dengan tampilan identik.

**Independent Test**: `LegacyMarkupEquivalenceTest` fragmen `home-why-choose` lulus. Ubah deskripsi "Ramah Lingkungan" di admin → beranda menampilkan teks baru.

### Tests for User Story 1

- [X] T023 [P] [US1] Test render `tests/Feature/Pages/PageContentRenderTest.php` (`RefreshDatabase`), method untuk WhyChoose:
  - (a) default menampilkan 3 judul
  - (b) ubah title item "Efisien & Terjangkau" via model → GET `/` menampilkan judul baru
  - (c) semua item nonaktif → `assertDontSee('Mengapa Beralih')`
  - (d) title `<script>alert(1)</script>` tampil ter-escape (`assertSee('&lt;script&gt;', false)`)
  - (e) subjudul null → `<p>` subjudul tidak ada
  - (f) set `SiteSettings::site_name = 'Merek Lain'` → GET `/` berisi `Bersama Merek Lain?`, dan fragmen section (`LegacyMarkup::extract` dengan XPath `home-why-choose`) tidak berisi `SUOER`. Jangan assert seluruh halaman, karena bagian lain beranda di luar cakupan masih memuat "SUOER".
- [X] T024 [P] [US1] Test admin `tests/Feature/Admin/SectionItemResourcesTest.php` (salin setup login dari `TestimonialResourceTest`), untuk `WhyChooseItemResource`:
  - list render & `assertCanSeeTableRecords` 3 item seeded, **tidak** memuat item section lain
  - edit title tersimpan
  - title/description kosong → `assertHasFormErrors(['title' => 'required', 'description' => 'required'])`
  - title > 60 ditolak
  - ikon di luar `IconOptions` ditolak
  - create mengisi `section` & `order` otomatis
  - user ber-role `Editor` (tanpa permission khusus, seperti `NavigationStructureTest::test_regular_admin_…`) bisa membuka halaman list `WhyChooseItemResource` (FR-017)

### Implementation for User Story 1

- [X] T025 [US1] Buat `app/Filament/Resources/WhyChooseItemResource.php` (extends `App\Filament\Support\SectionItemResource`, `section()` → `PageSection::WhyChoose`, `$navigationIcon = 'heroicon-o-sparkles'`, `$navigationSort = 10`, `$modelLabel = 'Kartu Alasan'`) + `app/Filament/Resources/WhyChooseItemResource/Pages/{ListWhyChooseItems,CreateWhyChooseItem,EditWhyChooseItem}.php` yang extends kelas dasar di `app/Filament/Support/Pages/` dan menyetel `protected static string $resource`.
- [X] T026 [US1] Refactor `resources/views/components/sections/why-choose.blade.php`:
  - Hapus blok `@php $reasons … @endphp`.
  - Bungkus seluruh `<section>` dengan `@if ($content = \App\Support\PageContent\PageContent::section(\App\Enums\PageSection::WhyChoose)) … @endif`.
  - `<h2>` isi `{{ PageContent::multiline($content->title) }}`, `<p>` subjudul di dalam `@if (filled($content->subtitle))`.
  - `@foreach ($content->items as $reason)`. Ganti `$reason['icon']` → `$reason->icon`, `['title']` → `->title`, `['description']` → `->description`, `['emphasized']` → `->is_emphasized`.
  - **Class dan struktur tidak diubah.**
- [X] T027 [US1] Jalankan `php artisan test --compact --filter='LegacyMarkupEquivalenceTest|PageContentRenderTest|SectionItemResourcesTest|HomePageTest'` dan perbaiki sampai hijau. Jika fragmen `home-why-choose` berbeda, perbaiki Blade, **bukan fixture**.

**Checkpoint**: MVP siap. Beranda identik dan kartu "Mengapa Beralih" bisa diedit.

---

## Phase 4: User Story 2 — Admin mengubah isi langkah "Cara Kerja" (P2)

**Goal**: Section "Cara Kerja" dibaca dari database. Nomor, kemiringan, dan posisi mengikuti posisi tampil.

**Independent Test**: Fragmen `home-how-it-works` lulus. Ubah judul "DC Power" → tampil di posisi 2 dengan nomor `02`.

### Tests for User Story 2

- [X] T028 [P] [US2] Tambah method di `tests/Feature/Pages/PageContentRenderTest.php` untuk HowItWorks:
  - (a) nomor `01`..`04` tampil
  - (b) set `order` "Inverter" = -1 → HTML berisi nomor `01` diikuti judul "Inverter"; kartu pertama ber-class `-rotate-1` dan `md:mt-0`
  - (c) semua nonaktif → `assertDontSee('Sederhana dan Mulus')`
  - (d) item ditonjolkan menghasilkan class `bg-primary-container` pada lingkaran nomornya
- [X] T029 [P] [US2] Tambah kasus di `tests/Feature/Admin/SectionItemResourcesTest.php` untuk `HowItWorksStepResource`: list hanya 4 langkah Cara Kerja, form **tidak** punya field `icon` (`assertFormFieldIsHidden('icon')` atau `assertFormFieldDoesNotExist`), dan edit tersimpan.

### Implementation for User Story 2

- [X] T030 [US2] Buat `app/Filament/Resources/HowItWorksStepResource.php` (`section()` → `HowItWorks`, `$navigationIcon = 'heroicon-o-arrow-path'`, `$navigationSort = 11`, `$modelLabel = 'Langkah'`) + `Pages/{ListHowItWorksSteps,CreateHowItWorksStep,EditHowItWorksStep}.php`.
- [X] T031 [US2] Refactor `resources/views/components/sections/how-it-works.blade.php`:
  - Ganti array `$steps` dengan `@php $rotations = ['-rotate-1','rotate-2','-rotate-2','rotate-1']; $offsets = ['md:mt-0','md:mt-12','md:mt-4','md:mt-16']; @endphp` dan bungkus `<section>` dengan `@if ($content = PageContent::section(PageSection::HowItWorks))`.
  - Di loop: `$step['mt']` → `$offsets[$loop->index] ?? 'md:mt-0'`, `$step['rotate']` → `$rotations[$loop->index] ?? ''`, `$step['number']` → `PageSection::HowItWorks->stepNumber($loop->iteration)`, `$step['emphasized']` → `$step->is_emphasized`, dan judul/deskripsi dari model.
  - Judul `<h2>` via `multiline`, subjudul dengan `@if (filled(...))`.
  - Class dan struktur tidak diubah.
- [X] T032 [US2] Jalankan `php artisan test --compact --filter='LegacyMarkupEquivalenceTest|PageContentRenderTest|SectionItemResourcesTest'` sampai hijau.

**Checkpoint**: US1 + US2 berfungsi mandiri.

---

## Phase 5: User Story 3 — Section Karir: "Mengapa Bergabung" & "Proses Rekrutmen" (P3)

**Goal**: Dua section di `/karir` dibaca dari database, bisa diedit dari dua modul baru, dan tampilannya identik.

**Independent Test**: Fragmen `karir-values` & `karir-recruitment` lulus. Ubah "Wawancara HR" → tampil di posisi 2 dengan nomor `2`.

### Tests for User Story 3

- [X] T033 [P] [US3] Tambah method di `tests/Feature/Pages/PageContentRenderTest.php` (aktifkan `career_module_enabled` seperti `tests/Feature/Pages/CareerPageTest.php`):
  - (a) 3 kartu nilai + ikon `lightbulb`/`groups`/`public` tampil
  - (b) nomor langkah `1`..`4`
  - (c) semua langkah rekrutmen nonaktif → `assertDontSee('Proses Rekrutmen')` (termasuk kotak latar)
  - (d) semua kartu nilai nonaktif → `assertDontSee('Mengapa Bergabung')`, sementara "Posisi Terbuka" tetap tampil
- [X] T034 [P] [US3] Tambah kasus di `tests/Feature/Admin/SectionItemResourcesTest.php` untuk `CareerValueResource` (ada field `icon`, **tidak** ada `is_emphasized`) dan `RecruitmentStepResource` (tanpa `icon` & `is_emphasized`). Pastikan list masing-masing hanya berisi item section-nya.

### Implementation for User Story 3

- [X] T035 [P] [US3] Buat `app/Filament/Resources/CareerValueResource.php` (`section()` → `CareerValues`, `$navigationIcon = 'heroicon-o-heart'`, `$navigationSort = 12`, `$modelLabel = 'Kartu Nilai'`) + `Pages/{ListCareerValues,CreateCareerValue,EditCareerValue}.php`.
- [X] T036 [P] [US3] Buat `app/Filament/Resources/RecruitmentStepResource.php` (`section()` → `RecruitmentProcess`, `$navigationIcon = 'heroicon-o-clipboard-document-check'`, `$navigationSort = 13`, `$modelLabel = 'Langkah'`) + `Pages/{ListRecruitmentSteps,CreateRecruitmentStep,EditRecruitmentStep}.php`.
- [X] T037 [US3] Refactor `resources/views/pages/karir.blade.php`:
  - Hapus `$values` & `$process` dari blok `@php` teratas (pertahankan `$appName`).
  - `{{-- Values --}}`: bungkus `<section>` dengan `@if ($values = PageContent::section(PageSection::CareerValues))`, dengan `<h2>` via `multiline`, `<p>` subjudul dengan `@if (filled(...))`, dan loop `$values->items` (`->icon`, `->title`, `->description`).
  - `{{-- Recruitment Process --}}`: bungkus seluruh `<section>` dengan `@if ($process = PageContent::section(PageSection::RecruitmentProcess))`. Nomor = `PageSection::RecruitmentProcess->stepNumber($loop->iteration)`.
  - CTA **belum** diubah (US6). Class dan struktur tidak diubah.
- [X] T038 [US3] Jalankan `php artisan test --compact --filter='LegacyMarkupEquivalenceTest|PageContentRenderTest|SectionItemResourcesTest|CareerPageTest'` sampai hijau.

**Checkpoint**: Keempat section dibaca dari DB dan bisa diedit.

---

## Phase 6: User Story 4 — Tambah, hapus, urutkan, aktif/nonaktif, dan tonjolkan item (P4)

**Goal**: Batas item aktif per section, reorder dengan seret, dan penonjolan tunggal berlaku di keempat modul.

**Independent Test**: Di "Mengapa Beralih", mengaktifkan kartu ke-4 ditolak lewat form **dan** lewat toggle tabel. Menonjolkan kartu lain melepas "Garansi Panjang". Seret mengubah urutan beranda.

### Tests for User Story 4

- [X] T039 [P] [US4] Tambah kasus di `tests/Feature/Admin/SectionItemResourcesTest.php`:
  - (a) Create WhyChoose aktif saat sudah ada 3 aktif → `assertHasFormErrors(['is_active'])`. Create yang sama dengan `is_active=false` → sukses.
  - (b) Toggle tabel: `Livewire::test(ListWhyChooseItems::class)->call('updateTableColumnState', 'is_active', (string) $inactive->getKey(), true)` → record tetap `is_active=false`.
  - (c) Batas 4 untuk HowItWorks & RecruitmentProcess, batas 3 untuk CareerValues.
  - (d) Edit record yang **sudah aktif** (tanpa mengubah status) tidak ditolak.
  - (e) Menandai item lain `is_emphasized=true` → "Garansi Panjang" jadi false. Hanya 1 emphasized per section, dan section lain tidak terpengaruh.
  - (f) `is_emphasized=true` pada CareerValues disimpan sebagai false.
  - (g) `call('reorderTable', [...ids terbalik])` → kolom `order` mengikuti.
  - (h) Delete item → hilang dari DB.
- [X] T040 [P] [US4] Tambah method di `tests/Feature/Pages/PageContentRenderTest.php`: item emphasized yang nonaktif → tidak ada kartu ber-class `bg-primary-container shadow-lg` di section WhyChoose. Tanpa item emphasized, halaman tetap 200.

### Implementation for User Story 4

- [X] T041 [US4] Buat rule `app/Rules/WithinActiveItemLimit.php` (`php artisan make:rule WithinActiveItemLimit`) dengan konstruktor `(PageSection $section, ?int $ignoreId = null)`. Jika value truthy dan jumlah item aktif di section (kecuali `ignoreId`) ≥ `maxActiveItems()`, gagal dengan pesan `Section "{label}" maksimal {n} item aktif. Nonaktifkan item lain terlebih dahulu.`
- [X] T042 [US4] Pasang rule di `app/Filament/Support/SectionItemResource.php`:
  - `Toggle::make('is_active')->rules(fn (?SectionItem $record) => [new WithinActiveItemLimit(static::section(), $record?->getKey())])` dengan helper text `Maksimal {n} item aktif tampil di halaman {pageName}.`
  - `ToggleColumn::make('is_active')->rules(fn (SectionItem $record) => [new WithinActiveItemLimit(static::section(), $record->getKey())])`.
  - Pastikan pesan error toggle tampil sebagai notifikasi. Jika validasi `ToggleColumn` tidak menampilkan pesan, tambahkan `->beforeStateUpdated()` yang mengirim `Notification::make()->danger()`.
- [X] T043 [US4] Tambah di `app/Models/SectionItem.php` hook `saving`:
  - Jika `!section->supportsEmphasis()`, set `is_emphasized=false`.
  - Jika `is_emphasized` true (dan dirty), `static::query()->where('section', $this->section)->whereKeyNot($this->getKey())->update(['is_emphasized' => false])`.
- [X] T044 [US4] Di `app/Filament/Support/SectionItemResource.php`:
  - Tambah `Toggle::make('is_emphasized')->label('Tonjolkan')->helperText('Hanya satu item yang bisa ditonjolkan; item lain otomatis dilepas.')->visible(static::section()->supportsEmphasis())`.
  - Tambah `IconColumn::make('is_emphasized')->boolean()->label('Ditonjolkan')->visible(...)`.
  - Tambah `->reorderable('order')->paginated(false)` dan `->description('Maksimal {n} item aktif tampil di halaman {pageName}. Urutan di sini = urutan tampil.')`.
- [X] T045 [US4] Jalankan `php artisan test --compact --filter='SectionItemResourcesTest|PageContentRenderTest|LegacyMarkupEquivalenceTest'` sampai hijau.

**Checkpoint**: Aturan desain (batas aktif, penonjolan tunggal) dijaga panel admin.

---

## Phase 7: User Story 5 — Ubah judul & subjudul section (P5)

**Goal**: Header action "Ubah Judul Section" di tiap modul section.

**Independent Test**: Ubah judul "Mengapa Beralih" menjadi dua baris → beranda menampilkan `<br>` di tempat yang sama. Modul "Proses Rekrutmen" tidak punya field subjudul.

### Tests for User Story 5

- [X] T046 [P] [US5] Tambah kasus di `tests/Feature/Admin/SectionItemResourcesTest.php`:
  - (a) `Livewire::test(ListWhyChooseItems::class)->callAction('editSectionHeading', data: ['title' => "Baris Satu\nBaris Dua", 'subtitle' => 'Sub baru'])` → DB terupdate. GET `/` berisi `Baris Satu<br>Baris Dua` dan `Sub baru`.
  - (b) title kosong → `assertHasActionErrors(['title' => 'required'])`.
  - (c) title > 80 ditolak.
  - (d) di `ListRecruitmentSteps`, field `subtitle` tidak ada/tersembunyi.
  - (e) mount action terisi nilai heading saat ini.

### Implementation for User Story 5

- [X] T047 [US5] Di `app/Filament/Support/Pages/ListSectionItems.php` tambahkan header action `Action::make('editSectionHeading')` berlabel "Ubah Judul Section" (ikon `heroicon-o-pencil-square`), dengan:
  - `fillForm()` dari `SectionHeading::firstWhere('section', $section)` (fallback `DefaultPageContent::headings()`).
  - Form: `Textarea title` (required, maxLength 80, rows 2, helper "Tekan Enter untuk pindah baris seperti desain.") dan `Textarea subtitle` (nullable, maxLength 250, rows 2, `visible($section->hasSubtitle())`).
  - `action`: `SectionHeading::updateOrCreate(['section' => $section], [...])` lalu notifikasi sukses "Judul section disimpan.".
- [X] T048 [US5] Jalankan `php artisan test --compact --filter='SectionItemResourcesTest|LegacyMarkupEquivalenceTest'` sampai hijau.

**Checkpoint**: Seluruh isi keempat section bisa dikelola.

---

## Phase 8: User Story 6 — Admin mengubah teks CTA (P6)

**Goal**: Sembilan CTA dibaca dari database, diedit di modul "CTA" (tanpa tambah/hapus), dan tampilannya identik.

**Independent Test**: 9 fragmen CTA di `LegacyMarkupEquivalenceTest` lulus. Edit "FAQ – CTA" hanya mengubah `/faq`.

### Tests for User Story 6

- [X] T049 [P] [US6] Test admin `tests/Feature/Admin/CallToActionResourceTest.php`:
  - (a) list memuat 9 CTA
  - (b) tidak ada route/aksi create (`CallToActionResource::canCreate()` false, `assertActionDoesNotExist`/`assertActionHidden('create')`) dan tidak ada delete
  - (c) edit FAQ title tersimpan
  - (d) title/primary_label kosong → form error
  - (e) `secondary_label` hanya tampil untuk Home dan required di sana
  - (f) batas panjang 80/300/40
  - (g) `placement` tidak berubah setelah save meski dikirim nilai lain
- [X] T050 [P] [US6] Test render `tests/Feature/Pages/CallToActionRenderTest.php` (`RefreshDatabase`, `LegacyMarkup::seedDeterministicState()`):
  - (a) untuk tiap placement, ubah title via model → hanya halaman terkait menampilkan title baru, dan halaman lain dengan CTA tetap menampilkan teks lamanya
  - (b) label tombol "Isi Form Online" diubah → tombol tetap `href` ke `/kontak`
  - (c) body ProductDetail `Beli {produk} sekarang` → `/produk/produk-uji` berisi `Beli produk uji sekarang`
  - (d) hapus baris CTA FAQ → `/faq` tetap menampilkan teks bawaan (fallback)
  - (e) body/subjudul kosong → elemen `<p>`/baris `<br>` subjudul tidak dirender
  - (f) nomor WhatsApp di `SiteSettings` kosong → tombol WA Home mengarah ke `/kontak`
  - (g) title berisi `<b>x</b>` tampil ter-escape

### Implementation for User Story 6

- [X] T051 [US6] Buat `app/Filament/Resources/CallToActionResource.php` + `Pages/{ListCallToActions,EditCallToAction}.php` sesuai contracts/admin-modules.md §2:
  - `$navigationGroup = 'Konten Halaman'`, label "CTA", ikon `heroicon-o-megaphone`, `$navigationSort = 14`.
  - `canCreate()`/`canDelete()`/`canDeleteAny()` return false. Tabel tanpa bulk action, `paginated(false)`, urutan mengikuti urutan case `CtaPlacement` (`orderByRaw` FIELD atau sort koleksi).
  - Form: `placement` sebagai `Placeholder` label enum; `Textarea title` (required, max 80, rows 2); `Textarea body` (label `bodyLabel()`, max 300, helper `{produk}` untuk ProductDetail); `TextInput primary_label` (required, max 40, label "Label Tombol WhatsApp" untuk Home, selain itu "Label Tombol"); `TextInput secondary_label` (label "Label Tombol Form", `visible`+`required` hanya jika `hasSecondaryButton()`, max 40). Helper umum: "Tujuan tombol mengikuti pengaturan situs dan tidak bisa diubah di sini. Tulis {app_name} untuk menampilkan nama situs.".
- [X] T052 [US6] Refactor `resources/views/components/sections/cta-band.blade.php`:
  - Ubah `@props` menjadi `['placement', 'buttonIcon' => 'forum', 'buttonHref' => null]`.
  - Di `@php`, tambahkan `$cta = \App\Support\PageContent\PageContent::cta($placement)`.
  - `{{ $title }}` → `{{ PageContent::multiline($cta->title) }}`, `@if ($subtitle)<br>{{ $subtitle }}@endif` → `@if (filled($cta->body))<br>{{ PageContent::text($cta->body) }}@endif`, dan `{{ $buttonLabel }}` → `{{ PageContent::text($cta->primary_label) }}`.
  - Markup/class tidak diubah.
- [X] T053 [US6] Perbarui pemanggil `cta-band`. Hapus prop `title`/`subtitle`/`button-label`/`buttonLabel`, pertahankan `button-href`/`buttonHref`/`button-icon`:
  - `resources/views/pages/tentang-kami.blade.php` → `<x-sections.cta-band :placement="\App\Enums\CtaPlacement::About" />`
  - `resources/views/pages/faq.blade.php` → `:placement="\App\Enums\CtaPlacement::Faq"`
  - `resources/views/pages/karir.blade.php` → `:placement="\App\Enums\CtaPlacement::Career"`
  - `resources/views/pages/artikel/show.blade.php` → `:placement="\App\Enums\CtaPlacement::ArticleDetail"`
- [X] T054 [P] [US6] Refactor CTA penutup di `resources/views/pages/home.blade.php`: `@php $cta = PageContent::cta(CtaPlacement::Home); @endphp`. Ganti `Siap beralih ke<br>energi matahari?` dengan `{{ PageContent::multiline($cta->title) }}`, paragraf dengan `{{ PageContent::text($cta->body) }}` (dibungkus `@if (filled(...))`), `<span>Chat via WhatsApp</span>` dengan `<span>{{ PageContent::text($cta->primary_label) }}</span>`, dan `Isi Form Online` dengan `{{ PageContent::text($cta->secondary_label) }}`. URL & SVG tetap.
- [X] T055 [P] [US6] Refactor dua CTA di `resources/views/pages/produk/index.blade.php` (`{{-- CTA Kalkulator --}}` → `ProductCalculator`, `{{-- CTA Penutup --}}` → `ProductClosing`): judul, paragraf (`@if (filled)`), dan label tombol dari `$cta`. Ikon `arrow_forward` dan `href` tetap.
- [X] T056 [P] [US6] Refactor blok "Masa Depan Energi Anda" di `resources/views/pages/produk/show.blade.php` (`ProductDetail`): judul, paragraf `{{ PageContent::withProductName($cta->body, $product->name) }}` (di dalam `@if (filled)`), dan label tautan. Ikon, href, dan blok gambar tetap.
- [X] T057 [P] [US6] Refactor blok `{{-- Newsletter --}}` di `resources/views/pages/artikel/index.blade.php` (`ArticleIndex`): judul, paragraf, dan label tombol. Ikon `forum`/`arrow_forward` dan href tetap.
- [X] T058 [US6] Jalankan `php artisan test --compact --filter='CallToActionResourceTest|CallToActionRenderTest|LegacyMarkupEquivalenceTest|ProductPageTest|ArticlePageTest|FaqPageTest|AboutPageTest|CareerPageTest|HomePageTest'` sampai hijau.

**Checkpoint**: Semua user story selesai. 13/13 fragmen kesetaraan lulus.

---

## Phase 9: Polish & Cross-Cutting Concerns

- [X] T059 [P] Perbarui `tests/Feature/Admin/NavigationStructureTest.php`: assert label "Beranda – Mengapa Beralih", "Beranda – Cara Kerja", "Karir – Mengapa Bergabung", "Karir – Proses Rekrutmen", dan "CTA" tampil. Perhatikan escape `–` jika perlu (`escape: false`).
- [X] T060 [P] Tambah bagian "Mengelola Section & CTA" di `docs/manual-operator.md`: lokasi menu, batas item aktif per section, cara menonjolkan/mengurutkan, "Ubah Judul Section", pindah baris judul, token `{produk}` & `{app_name}`, dan bahwa tujuan tombol CTA diatur di Pengaturan Situs. Tambahkan juga satu catatan di `docs/deployment.md`: `migrate --force` kini menanam konten section & CTA, dan Nama Situs harus sudah benar sebelum deploy (token `{app_name}`).
- [X] T061 [P] Perbarui `docs/panduan-section.md`: tambahkan baris di tabel "Pilih sumber data" untuk section daftar item berdesain tetap (pola `SectionItem` + `PageSection` + resource turunan `App\Filament\Support\SectionItemResource`), serta catatan menambah `CtaPlacement` baru. Perbaiki contoh "Teks desain … Langsung di Blade" yang masih merujuk `how-it-works.blade.php`.
- [X] T062 Jalankan `vendor/bin/pint --dirty --format agent`.
- [X] T063 Jalankan `php artisan test --compact tests/Feature/Admin tests/Feature/Pages tests/Feature/Database tests/Feature/Public tests/Unit/DefaultPageContentTest.php`, lalu tanyakan ke pengguna apakah ingin menjalankan seluruh suite.
- [ ] T064 Verifikasi manual quickstart.md §2–§3. Ambil tangkapan layar "sesudah" ke `specs/029-why-choose-admin/screenshots/after/` dan bandingkan dengan `before/` (390px & 1440px) untuk kedelapan halaman. Harus identik.
- [X] T065 Pastikan `CaptureLegacyPageContentFixturesTest` tetap ter-skip tanpa env, dan tidak ada literal teks lama tersisa di Blade yang direfactor: `grep -n "Efisien & Terjangkau\|Panel & PV Cell\|Inovasi Berkelanjutan\|Wawancara HR\|Bingung pilih" resources/views` harus kosong.

---

## Addendum 2026-10-02: Tentang Kami & Info Kontak (US7, US8)

### Phase 10: Setup — tangkap tampilan lama (WAJIB sebelum Blade Tentang Kami/Kontak diubah)

- [X] T066 Tambah 7 fragmen ke `LegacyMarkup::FRAGMENTS` di `tests/Support/LegacyMarkup.php`:
  - `tentang-kami-hero` `/tentang-kami` `//section[.//h1[contains(., 'Lebih Dekat')]]`
  - `tentang-kami-siapa-kami` `/tentang-kami` `//section[.//div[contains(@class,'-rotate-3')]]`
  - `tentang-kami-visi` `/tentang-kami` `//section[.//p[contains(@class,'tracking-[0.3em]')]]`
  - `tentang-kami-misi` `/tentang-kami` `//section[.//span[contains(., 'check_circle')]]`
  - `tentang-kami-nilai` `/tentang-kami` `//section[.//div[contains(@class,'md:col-span-3') and .//img]]`
  - `tentang-kami-trust` `/tentang-kami` `//section[contains(@class,'border-y')]`
  - `kontak-info` `/kontak` `//div[contains(@class,'lg:w-2/5') and contains(@class,'bg-primary')]`
  Pastikan tiap XPath mengembalikan section yang benar (cek isi fixture). Ubah juga `tests/Feature/Pages/CaptureLegacyPageContentFixturesTest.php` agar **hanya menulis fixture yang belum ada**. 13 fixture lama diambil dari kode sebelum refactor dan MUST NOT ditimpa.
- [X] T067 Jalankan `CAPTURE_LEGACY_FIXTURES=1 php artisan test --compact --filter=CaptureLegacyPageContentFixturesTest` pada kode Tentang Kami/Kontak yang **belum diubah**; verifikasi 7 fixture baru berisi teks lama, lalu pastikan `LegacyMarkupEquivalenceTest` lulus 20/20.

### Phase 11: Fondasi addendum

- [X] T068 Ganti `App\Support\PageContent\IconOptions` dengan `App\Support\MaterialSymbolsIcons` (tambahkan `groups` ke `OPTIONS`): perbarui `app/Filament/Support/SectionItemResource.php`, `tests/Unit/DefaultPageContentTest.php`, lalu hapus `app/Support/PageContent/IconOptions.php`.
- [X] T069 Edit migrasi `database/migrations/2026_10_02_030956_create_section_items_table.php` (title string(120), description string(500)) dan `2026_10_02_030957_create_section_headings_table.php` (title string(255), subtitle string(500), + eyebrow string(60) null, featured_image_path string null, featured_icon string(64) null, featured_title string(160) null, featured_description string(500) null). Perbarui `$fillable` `SectionHeading`.
- [X] T070 Tambah case `AboutMission`, `AboutValues`, `AboutTrust` di `app/Enums/PageSection.php` + method `hasHeading()`, `hasEyebrow()`, `hasFeaturedCard()`, `itemTitleLabel()`, `itemDescriptionLabel()`, `itemTitleMaxLength()`, `itemDescriptionMaxLength()`, `headingTitleMaxLength()`, `headingSubtitleMaxLength()` sesuai data-model addendum.
- [X] T071 Buat enum `app/Enums/PageBlockType.php` (4 case, `label()`, `defaultImagePath()`), migrasi `create_page_blocks_table` (`block` unik, `data` json), model `app/Models/PageBlock.php` (cast `block` enum, `data` array) + factory.
- [X] T072 Tambah nilai bawaan Tentang Kami (salin dari `database/settings/2026_09_17_154431_create_about_page_settings.php`) dan `ContactInfo` (salin dari `resources/views/pages/kontak.blade.php`, merek → `{app_name}`) ke `DefaultPageContent` (`headings()`, `items()`, `blocks()`).
- [X] T073 Perluas `PageContentInstaller::install()`: section & blok Tentang Kami diisi dari baris tabel `settings` grup `about_page` bila ada (decode payload JSON per `name`), fallback `DefaultPageContent`; `{app_name}` diisi Nama Situs sekali (`forCurrentSite`). Blok baru `firstOrCreate` per `block`. Buat migrasi data `migrate_about_page_settings_to_page_content` yang memanggil installer (tanpa referensi `Database\Seeders`).
- [X] T074 Tambah `PageContent::block()`, `PageContent::whatsappMessage()`, helper gambar (bawaan bila kosong/hilang dari disk) dan rich text tersanitasi (`HtmlSanitizer::clean`).
- [X] T075 [P] Test `tests/Feature/Database/AboutPageMigrationTest.php`: (a) instalasi baru → isi sama dengan nilai bawaan settings; (b) nilai settings yang diedit (mis. judul misi, gambar hero, trust item) dipindahkan apa adanya; (c) `{app_name}` di body diisi Nama Situs; (d) idempoten; (e) baris `settings` lama tidak dihapus.

### Phase 12: User Story 7 — Misi, Nilai, Trust Strip sebagai section (P7)

- [X] T076 [P] [US7] Test render di `tests/Feature/Pages/AboutPageSectionsTest.php`: Misi 3 kiri + sisanya kanan dan pindah kolom saat urutan diubah; Nilai kartu besar dari heading (gambar bawaan bila kosong); Trust tampil angka/keterangan; section kosong disembunyikan.
- [X] T077 [P] [US7] Test admin di `tests/Feature/Admin/SectionItemResourcesTest.php`: tiga resource baru ter-scope; batas aktif 5/3/3; label "Angka"/"Keterangan"; batas panjang per section; Trust tanpa action `editSectionHeading`; Misi heading punya eyebrow; Nilai heading menyimpan kartu besar (ikon/judul/deskripsi wajib); reorder ujung-ke-ujung.
- [X] T078 [US7] Buat `AboutMissionResource`, `AboutValueResource`, `AboutTrustResource` (+ Pages) di `app/Filament/Resources/`; perluas `SectionItemResource` (label & batas dari enum) dan `ListSectionItems` (action disembunyikan bila `!hasHeading()`, field eyebrow, bagian Kartu Besar dengan FileUpload WebP folder `about-page`).
- [X] T079 [US7] Refactor Misi, Nilai, Trust Strip di `resources/views/pages/tentang-kami.blade.php` ke `PageContent::section(...)`; markup tidak berubah. Jalankan `LegacyMarkupEquivalenceTest` sampai hijau.

### Phase 13: User Story 8 — Blok halaman (P8)

- [X] T080 [P] [US8] Test admin `tests/Feature/Admin/PageBlockResourceTest.php`: 4 blok terdaftar; tanpa create/delete; form per tipe hanya menampilkan kolom miliknya; batas panjang; upload gambar tersimpan; menu "Halaman Tentang Kami" tidak ada lagi.
- [X] T081 [P] [US8] Test render `tests/Feature/Pages/PageBlockRenderTest.php`: Hero/Siapa Kami/Visi menampilkan isi blok; gambar kosong → bawaan; rich text tersanitasi (skrip tidak jalan); jam operasional & label WhatsApp di Kontak; pesan WhatsApp dipakai di Kontak, CTA Beranda, CTA Band (set `whatsapp_number`, cek `href` memuat pesan ter-encode); blok hilang → nilai bawaan.
- [X] T082 [US8] Buat `app/Filament/Resources/PageBlockResource.php` (+ Pages List/Edit) sesuai contracts/admin-modules.md addendum.
- [X] T083 [US8] Refactor Hero/Siapa Kami/Visi di `tentang-kami.blade.php` ke `PageContent::block(...)` (hapus pemakaian `AboutPageSettings`), blok Info Kontak di `kontak.blade.php`, dan pesan WhatsApp di `kontak.blade.php`, `home.blade.php`, `components/sections/cta-band.blade.php` ke `PageContent::whatsappMessage()`. Markup tidak berubah; `LegacyMarkupEquivalenceTest` 20/20.
- [X] T084 [US8] Hapus `app/Filament/Pages/AboutPageSettingsPage.php`, `app/Settings/AboutPageSettings.php` (+ registrasi di `config/settings.php` bila ada), dan ganti `tests/Feature/Settings/AboutPageSettingsTest.php` dengan test baru (**setelah persetujuan pengguna**). Jangan hapus baris `settings` grup `about_page` di DB. Perbarui `NavigationStructureTest` (label "Halaman Tentang Kami" → menu baru).

### Phase 14: Polish addendum

- [X] T085 [P] Perbarui `docs/manual-operator.md` (menu Tentang Kami – Misi/Nilai/Trust Strip, Blok Halaman; hapus bagian "Halaman Tentang Kami"; tanpa backtick, label menu ditulis tebal) dan `docs/panduan-section.md` (pola blok halaman; contoh `AboutPageSettings` diganti).
- [X] T086 Jalankan `vendor/bin/pint --dirty --format agent` dan `php artisan test --compact tests/Feature/Admin tests/Feature/Pages tests/Feature/Database tests/Feature/Public tests/Feature/Docs tests/Feature/Settings tests/Unit`.

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1 (Setup)**: tanpa dependensi. **Wajib selesai sebelum Blade apa pun diubah.**
- **Phase 2 (Foundational)**: setelah Phase 1. Memblokir semua user story.
- **US1 (Phase 3)**: setelah Phase 2.
- **US2 (Phase 4)**, **US3 (Phase 5)**: setelah Phase 2. Independen dari US1 (hanya memakai kelas dasar dari T019).
- **US4 (Phase 6)**: setelah Phase 2. Paling bernilai setelah minimal satu resource section ada (US1). Test-nya memakai `ListWhyChooseItems` (US1), `HowItWorksStepResource` (US2), dan `CareerValueResource`/`RecruitmentStepResource` (US3).
- **US5 (Phase 7)**: setelah Phase 2 + minimal satu resource section (test memakai US1 & US3).
- **US6 (Phase 8)**: setelah Phase 2. Independen dari US1–US5, kecuali T053 yang menyentuh `karir.blade.php` (kerjakan setelah T037 bila US3 berjalan paralel).
- **Polish (Phase 9)**: setelah semua story yang dirilis.

### Within Each User Story

- Test ditulis dulu dan dipastikan gagal (kecuali kasus kesetaraan yang memang harus lulus sejak awal) → resource admin → refactor Blade → jalankan filter test.
- Refactor Blade selalu diikuti `LegacyMarkupEquivalenceTest`.

### Parallel Opportunities

- Phase 2: T005, T006, T007 paralel. T012, T013, T014 paralel setelah migrasi. T020 & T021 paralel setelah T016–T017.
- US1–US3 & US6 bisa dikerjakan paralel oleh orang berbeda setelah Phase 2 (perhatikan `karir.blade.php` bersama antara T037 & T053).
- US3: T035 & T036 paralel.
- US6: T054, T055, T056, T057 paralel (file berbeda).
- Polish: T059, T060, T061 paralel.

---

## Parallel Example: User Story 6

```bash
# Test dulu (paralel):
Task: "T049 CallToActionResourceTest di tests/Feature/Admin/CallToActionResourceTest.php"
Task: "T050 CallToActionRenderTest di tests/Feature/Pages/CallToActionRenderTest.php"

# Setelah T051–T053, refactor Blade paralel:
Task: "T054 home.blade.php CTA penutup"
Task: "T055 produk/index.blade.php 2 CTA"
Task: "T056 produk/show.blade.php CTA detail"
Task: "T057 artikel/index.blade.php CTA daftar"
```

---

## Implementation Strategy

### MVP First (User Story 1)

1. Phase 1 (fixture HTML lama) → Phase 2 (fondasi, termasuk migrasi data).
2. Phase 3 (US1) → validasi: beranda identik, kartu "Mengapa Beralih" bisa diedit.
3. **STOP & VALIDATE**. Bisa di-deploy: `migrate --force` menanam semua konten bawaan (section lain & CTA sudah ada di DB tetapi masih dirender dari Blade lama, tanpa efek visual).

### Incremental Delivery

1. US1 → US2 (beranda lengkap) → US3 (Karir) → US4 (aturan kelola) → US5 (judul) → US6 (CTA).
2. Setiap tahap: `LegacyMarkupEquivalenceTest` hijau = aman dirilis.

### Notes

- Jika fragmen kesetaraan gagal, **perbaiki Blade, jangan regenerasi fixture**. Fixture hanya boleh dibuat ulang pada kode lama (sebelum refactor).
- Commit setelah setiap task atau kelompok logis.
