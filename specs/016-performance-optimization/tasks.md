---
description: "Task list for Optimasi Performa Halaman Publik"
---

# Tasks: Optimasi Performa Halaman Publik

**Input**: Design documents from `/specs/016-performance-optimization/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/rendering-behavior.md, quickstart.md

**Tests**: Included — feature test required by plan.md (Testing section) dan Constitution Principle IV (Module Test Coverage).

**Organization**: Tasks dikelompokkan per user story. US1 (P1) → US2 (P2) → US3 (P3), sesuai urutan prioritas spec.md.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Bisa dikerjakan paralel (file berbeda, tidak saling bergantung)
- **[Story]**: US1, US2, atau US3
- Path file relatif terhadap root repo

## Path Conventions

Laravel web app, single project. Source di root repo: `app/`, `resources/`, `tests/`.

**Catatan dependency**: Nol dependency baru (research.md §1-5). Ketiga user story menyentuh file yang SEPENUHNYA terpisah (Blade `<img>` vs controller+trait vs satu partial layout) — TIDAK ada Foundational phase, ketiganya independen sejak awal dan bisa dikerjakan dalam urutan apa pun (dikerjakan sesuai prioritas P1→P2→P3 di sini).

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Tidak ada init project — aplikasi Laravel yang sudah ada. Baseline check sebelum menyentuh apa pun.

- [x] T001 Confirm baseline hijau: `php artisan test --compact --filter='HomePageTest|HomeBannerTest|ProductPageTest|ArticlePageTest|PortfolioPageTest|FaqPageTest|AboutPageTest|ContactPageTest|CalculatorLeadTest'` (seluruh halaman & controller yang akan disentuh fitur ini)

---

## Phase 2: Foundational

**Tidak ada tugas foundational** — lihat catatan dependency di atas: US1 (Blade `<img>`), US2 (controller + trait baru), dan US3 (satu partial layout) sama sekali tidak berbagi file, sehingga tidak ada prasyarat bersama yang perlu diselesaikan lebih dulu.

---

## Phase 3: User Story 1 - Halaman bergambar banyak terasa lebih cepat dan hemat kuota (Priority: P1) 🎯 MVP

**Goal**: Seluruh `<img>` publik di luar komponen hero/cover teratas (research.md §2, daftar definitif) MUST punya `loading="lazy" decoding="async"`; gambar hero/cover teratas TETAP tanpa atribut `loading` (eager, default browser).

**Independent Test**: Buka halaman bergambar banyak (Produk/Artikel/Portfolio) dengan throttle jaringan — gambar hero tampil segera, gambar grid/kartu baru terunduh saat discroll mendekat; nonaktifkan JavaScript → semua gambar tetap tampil.

### Tests for User Story 1 ⚠️ (write first, ensure they FAIL)

- [x] T002 [P] [US1] Create `tests/Feature/Public/LazyLoadingTest.php` via `php artisan make:test --phpunit Public/LazyLoadingTest` — untuk tiap halaman representatif (Beranda dengan/tanpa banner aktif, `/produk`, `/produk/{slug}`, `/artikel`, `/artikel/{slug}`, `/portfolio`, `/portfolio/{slug}`, `/tentang-kami`), seed minimal data lewat factory supaya komponen bergambar (product-card/article-card/testimonials/client-logos/team-members) benar-benar render, lalu assert via `substr_count($response->getContent(), 'loading="lazy" decoding="async"')` sama dengan jumlah gambar "Lazy" yang diharapkan per halaman (research.md §2); assert gambar hero/cover (dicari lewat substring unik seperti `alt_text` banner atau `asset('images/mockup/...')` hero) TIDAK diikuti `loading="lazy"` dalam tag `<img>` yang sama (ekstrak tag lewat `preg_match` pada substring unik tsb ± beberapa karakter); test terpisah: nonaktifkan JS tidak relevan untuk PHPUnit (server-rendered), cukup pastikan atribut `loading="lazy"` adalah HTML attribute biasa (bukan dalam blok `x-show`/`@if` yang butuh JS) — cukup dibuktikan lewat assertSee di atas karena server-rendered HTML sudah lengkap tanpa JS (FR-004 otomatis terpenuhi selama atribut ada di markup awal, bukan disuntik JS)

### Implementation for User Story 1

- [x] T003 [P] [US1] Update `resources/views/components/sections/product-card.blade.php` baris 6: tambah `loading="lazy" decoding="async"` ke tag `<img>`
- [x] T004 [P] [US1] Update `resources/views/components/sections/testimonials.blade.php` baris 30: tambah `loading="lazy" decoding="async"`
- [x] T005 [P] [US1] Update `resources/views/components/sections/client-logos.blade.php`: refactor dari string HTML manual (`$img = '<img ...>'; {!! $img !!}`) jadi tag Blade biasa `<img src="{{ ... }}" alt="{{ ... }}" loading="lazy" decoding="async" class="h-10 md:h-12 w-auto object-contain opacity-70 hover:opacity-100 transition-opacity">` (hapus variabel `$img`/`{!! !!}`, pindahkan langsung ke dalam `@if ($logo->link_url)`/`@else`)
- [x] T006 [P] [US1] Update `resources/views/components/sections/article-card.blade.php` baris 6: tambah `loading="lazy" decoding="async"`
- [x] T007 [P] [US1] Update `resources/views/components/sections/team-members.blade.php` baris 18: tambah `loading="lazy" decoding="async"`
- [x] T008 [P] [US1] Update `resources/views/components/sections/banner-carousel.blade.php`: pada branch banner tunggal (baris 15/19) TIDAK diubah (eager, posisi hero); pada branch carousel multi-banner (baris 43/47, di dalam `@foreach ($visible as $i => $banner)`), tambah `loading="lazy" decoding="async"` HANYA saat `$i > 0` (mis. `@if ($i > 0) loading="lazy" decoding="async" @endif` di dalam tag `<img>`, atau duplikasi blok `@if($i === 0) ... @else ... @endif`)
- [x] T009 [P] [US1] Update `resources/views/pages/home.blade.php`: baris 49 (gambar section "Produk Kami") dan baris 109 (avatar testimoni) — tambah `loading="lazy" decoding="async"` ke keduanya
- [x] T010 [P] [US1] Update `resources/views/pages/artikel/index.blade.php` baris 41 (gambar artikel unggulan): tambah `loading="lazy" decoding="async"`
- [x] T011 [P] [US1] Update `resources/views/pages/tentang-kami.blade.php`: baris 34 (hero bespoke) TIDAK diubah (eager); baris 52 dan 126 — tambah `loading="lazy" decoding="async"` ke keduanya
- [x] T012 [P] [US1] Update `resources/views/pages/artikel/show.blade.php` baris 32 (gambar sampul artikel, bukan hero): tambah `loading="lazy" decoding="async"`
- [x] T013 [P] [US1] Update `resources/views/pages/portfolio/index.blade.php` baris 45 (thumbnail kartu proyek): tambah `loading="lazy" decoding="async"`
- [x] T014 [P] [US1] Update `resources/views/pages/portfolio/show.blade.php`: baris 25 (gambar sampul utama) TIDAK diubah (eager); baris 31 (thumbnail galeri tambahan, di dalam loop) — tambah `loading="lazy" decoding="async"`
- [x] T015 [US1] Run `vendor/bin/pint --dirty --format agent`, then `php artisan test --compact --filter='LazyLoadingTest'` and fix until T002 passes; also re-run T001's filter to confirm no regression

**Checkpoint**: Seluruh gambar publik konsisten eager (hero) atau lazy (selebihnya) sesuai research.md §2 — US1 selesai dan independen

---

## Phase 4: User Story 2 - Halaman publik yang sering dibuka tetap cepat tanpa mengulang seluruh proses dari awal (Priority: P2)

**Goal**: 8 method controller publik read-only membungkus query dengan `Cache::remember` TTL 300 detik lewat trait `CachesPublicPages`; panel admin (Filament) tidak tersentuh sama sekali; halaman ber-state (Kontak/Kalkulator) tidak ikut di-cache; cache key Portfolio index terpisah per kategori.

**Independent Test**: Buka halaman publik read-only dua kali — kunjungan kedua tidak lebih lambat; ubah data lewat DB langsung (bypass cache) lalu buka lagi dalam TTL → data lama masih tampil (bukti cache-hit); admin tetap lihat data terbaru seketika.

### Tests for User Story 2 ⚠️ (write first, ensure they FAIL)

- [ ] T016 [P] [US2] Create `tests/Feature/Public/PublicPageCachingTest.php` via `php artisan make:test --phpunit Public/PublicPageCachingTest` covering (FR-005 s/d FR-008, research.md §3): untuk `/`, `/produk`, `/artikel`, `/faq`, `/tentang-kami` — buat 1 record relevan (mis. Produk dgn `name` "A"), hit halaman sekali (isi cache), ubah `name` jadi "B" LEWAT `DB::table(...)->update(...)` (bypass Eloquent, memastikan murni menguji cache bukan side-effect lain), hit halaman lagi dalam TTL → assertSee "A" (masih cache lama, BUKAN "B"); test terpisah untuk `/produk/{slug}` dan `/artikel/{slug}`: ubah `relatedProducts`/`related` artikel (tambah 1 produk/artikel baru ke kategori yang sama) → dalam TTL, related list yang tampil TIDAK termasuk item baru (bukti query sekunder ter-cache — lihat research.md §3 catatan `show()`); majukan waktu lewat `Carbon::setTestNow(now()->addMinutes(6))` → hit lagi → assertSee "B"/related baru (cache kedaluwarsa, FR-007/SC-004); test `/portfolio?kategori=X` vs `?kategori=Y` (2 kategori beda dgn masing-masing 1 proyek) → assert masing-masing HANYA menampilkan proyeknya sendiri, tidak tertukar (cache key per-kategori); test `/kontak` (POST) dua kali dengan data beda → assertDatabaseHas untuk KEDUANYA (tidak ada cache yang mengganggu form submission, FR-006)

### Implementation for User Story 2

- [ ] T017 [US2] Create `app/Concerns/CachesPublicPages.php` persis sesuai data-model.md § Trait: `private const CACHE_TTL = 300;` dan `protected function rememberPublicPage(string $key, \Closure $callback) { return \Illuminate\Support\Facades\Cache::remember($key, self::CACHE_TTL, $callback); }`
- [ ] T018 [P] [US2] Update `app/Http/Controllers/Public/HomeController.php`: `use App\Concerns\CachesPublicPages;`, tambah `use CachesPublicPages;` di class, bungkus SELURUH isi `__invoke()` (query `$products`/`$banners`/`$testimonials`) dalam `$this->rememberPublicPage('public-page:home', function () { ... return compact('products', 'banners', 'testimonials'); })`, lalu `return view('pages.home', $data);`
- [ ] T019 [P] [US2] Update `app/Http/Controllers/Public/ProductController.php`: tambah trait; `index()` — bungkus `$products`+`$categories` dengan key `'public-page:produk.index'`; `show(Product $product)` — bungkus HANYA `$relatedProducts` (bukan `$product`, sudah fresh via route binding — lihat research.md §3 catatan) dengan key `"public-page:produk.show:{$product->slug}"`, `$schema` tetap dihitung di luar cache (murah, tergantung `$product` yang selalu fresh)
- [ ] T020 [P] [US2] Update `app/Http/Controllers/Public/ArticleController.php`: tambah trait; `index()` — bungkus `$articles` (lalu turunkan `$featured`/`$articles->skip(1)` di luar closure) + `$categories` dengan key `'public-page:artikel.index'`; `show(Article $article)` — bungkus HANYA `$related` dengan key `"public-page:artikel.show:{$article->slug}"`
- [ ] T021 [P] [US2] Update `app/Http/Controllers/Public/PortfolioController.php`: tambah trait; `index(Request $request)` — bungkus `$projects` (dan `$categories`) dengan key `'public-page:portfolio.index:'.($activeSlug ?: 'all')`; `show(PortfolioProject $portfolioProject)` — bungkus panggilan `->load('portfolioCategory')` dengan key `"public-page:portfolio.show:{$portfolioProject->slug}"` (`rememberPublicPage` mengembalikan `$portfolioProject` yang sudah di-load, atau simpan hasil load ke variabel dalam closure)
- [ ] T022 [P] [US2] Update `app/Http/Controllers/Public/FaqController.php`: tambah trait, bungkus `$faqItems`+`$categories`+`$schema` dengan key `'public-page:faq'`
- [ ] T023 [P] [US2] Update `app/Http/Controllers/Public/AboutController.php`: tambah trait, bungkus `$testimonials`+`$clientLogos`+`$teamMembers` dengan key `'public-page:tentang-kami'`
- [ ] T024 [US2] Run `vendor/bin/pint --dirty --format agent`, then `php artisan test --compact --filter='PublicPageCachingTest'` and fix until T016 passes; also re-run T001's filter DAN seluruh `tests/Feature/Admin/*ResourceTest.php` yang relevan (Product/Article/PortfolioProject) untuk membuktikan admin tidak terpengaruh (FR-008)

**Checkpoint**: 8 method ter-cache TTL 5 menit, admin tetap real-time, Portfolio kategori terisolasi — US2 selesai dan independen

---

## Phase 5: User Story 3 - Konten utama halaman tampil tanpa terhalang sumber daya pendukung (Priority: P3)

**Goal**: Stylesheet Material Symbols dimuat non-blocking (`media="print"` + `onload` swap) dengan fallback `<noscript>` — tidak ada regresi visual pada ikon.

**Independent Test**: Throttle jaringan untuk request font ikon — teks/tata letak utama tampil duluan; begitu font ikon siap, seluruh ikon tampil normal.

### Tests for User Story 3 ⚠️ (write first, ensure they FAIL)

- [ ] T025 [P] [US3] Create `tests/Feature/Public/NonBlockingIconFontTest.php` via `php artisan make:test --phpunit Public/NonBlockingIconFontTest` covering (FR-009/FR-010): buka `/` → assertSee `media="print"` dan `onload="this.media='all'"` pada tag `<link>` yang mengarah ke `fonts.googleapis.com/css2?family=Material+Symbols`; assertSee `<noscript>` yang berisi `<link rel="stylesheet"` ke URL sama (fallback); assertSee minimal satu penanda ikon (`material-symbols-outlined`) tetap ada di markup (bukti tidak ada regresi struktur HTML ikon)

### Implementation for User Story 3

- [ ] T026 [US3] Update `resources/views/layouts/public.blade.php`: ganti `<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">` menjadi:
      ```html
      <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" media="print" onload="this.media='all'">
      <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"></noscript>
      ```
- [ ] T027 [US3] Run `vendor/bin/pint --dirty --format agent`, then `php artisan test --compact --filter='NonBlockingIconFontTest'` and fix until T025 passes

**Checkpoint**: Font ikon non-blocking, nol regresi visual — ketiga user story lengkap dan independen

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Regresi penuh + validasi manual sebelum PR.

- [ ] T028 Run `php artisan test --compact` (seluruh suite) — pastikan tidak ada regresi di modul lain
- [ ] T029 Run `vendor/bin/pint --format agent` (full pass, bukan `--dirty`)
- [ ] T030 Jalankan `quickstart.md` end-to-end secara manual (§1-3), termasuk pengukuran Lighthouse pada `/`, `/produk`, `/artikel` (§4) — catat hasil skor sebelum/sesudah di PR description

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — bisa mulai langsung
- **Foundational (Phase 2)**: Tidak ada — lihat catatan di atas
- **User Story 1, 2, 3 (Phase 3-5)**: Masing-masing hanya depends on Setup — TIDAK saling bergantung satu sama lain, bisa dikerjakan paralel oleh developer berbeda atau berurutan P1→P2→P3 sesuai prioritas
- **Polish (Phase 6)**: Depends on ketiga story yang ingin dirilis selesai

### Within Each User Story

- Test (T002, T016, T025) ditulis dan dipastikan FAIL dulu sebelum implementasi
- Checkpoint test run di akhir tiap fase sebelum lanjut fase berikutnya

### Parallel Opportunities

- T003-T014 (12 file `<img>` US1) — SEMUA file berbeda, jalan paralel
- T018-T023 (6 controller US2) — SEMUA file berbeda, jalan paralel, setelah T017 (trait) selesai
- Ketiga user story (Phase 3, 4, 5) — bisa dikerjakan paralel oleh tim berbeda karena nol file bersama

---

## Parallel Example: User Story 1

```bash
# 12 file <img> sekaligus (setelah T002 ditulis):
Task: "Update product-card.blade.php — tambah loading=lazy"
Task: "Update testimonials.blade.php — tambah loading=lazy"
Task: "Update client-logos.blade.php — refactor + loading=lazy"
Task: "Update article-card.blade.php — tambah loading=lazy"
# ...dst hingga T014
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 3: User Story 1 — **MVP**: seluruh gambar publik lazy-load, manfaat langsung terasa tanpa risiko caching/invalidasi
3. **STOP and VALIDATE**: jalankan quickstart.md §1
4. Deploy/demo bila cukup

### Incremental Delivery

1. Setup → fondasi siap (tanpa Foundational phase)
2. + User Story 1 → validasi independen → deploy (MVP, risiko terendah)
3. + User Story 2 → validasi independen (termasuk regresi admin) → deploy (manfaat skalabilitas)
4. + User Story 3 → validasi independen → deploy (penyempurnaan first-paint)

---

## Notes

- [P] tasks = file berbeda, tidak saling bergantung
- [Story] label memetakan task ke user story untuk traceability
- Verifikasi test FAIL dulu sebelum implementasi (TDD, sesuai Constitution Principle IV)
- Commit setelah tiap checkpoint fase (T015, T024, T027) — pola commit message `feat(016): <fase> — AMC-225`
