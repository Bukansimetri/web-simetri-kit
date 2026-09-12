---
description: "Task list for Sitemap & Robots Otomatis"
---

# Tasks: Sitemap & Robots Otomatis

**Input**: Design documents from `/specs/015-sitemap-robots/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/public-endpoints.md, quickstart.md

**Tests**: Included — feature test required by plan.md (Testing section) dan Constitution Principle IV (Module Test Coverage).

**Organization**: Tasks dikelompokkan per user story. US1 (P1) → US2 (P2), sesuai urutan prioritas spec.md.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Bisa dikerjakan paralel (file berbeda, tidak saling bergantung)
- **[Story]**: US1 atau US2
- Path file relatif terhadap root repo

## Path Conventions

Laravel web app, single project. Source di root repo: `app/`, `resources/`, `routes/`, `tests/`, `public/`.

**Catatan dependency**: Nol dependency baru (research.md §1-4). `SitemapController` satu file dipakai bersama US1 (`xml()`) dan US2 (`robots()`) — dibuat kosong di Foundational, diisi masing-masing di fase story-nya.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Tidak ada init project — aplikasi Laravel yang sudah ada. Baseline check sebelum menyentuh apa pun.

- [x] T001 Confirm baseline hijau: `php artisan test --compact --filter='ArticlePageTest|PortfolioPageTest|CareerModuleToggleTest|ProductPageTest'` (modul yang query-nya akan disalin ke sitemap)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Scaffold `SitemapController` (file bersama US1 & US2) dan registrasi 2 route baru — prasyarat sebelum kedua user story bisa diimplementasikan.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T002 Create `app/Http/Controllers/Public/SitemapController.php` dengan 2 method kosong (placeholder) yang akan diisi tiap fase story: `public function xml(): \Illuminate\Http\Response { abort(501); }` dan `public function robots(): \Illuminate\Http\Response { abort(501); }`
- [x] T003 Tambah 2 route baru di `routes/web.php` (dekat route publik lain, sebelum route admin): `Route::get('/sitemap.xml', [SitemapController::class, 'xml'])->name('sitemap');` dan `Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');` — tambah `use App\Http\Controllers\Public\SitemapController;` bila belum ada
- [x] T004 Verifikasi via `php artisan route:list --path=sitemap` dan `php artisan route:list --path=robots` — kedua route terdaftar mengarah ke `SitemapController`

**Checkpoint**: Route + controller kosong siap — user story bisa mulai

---

## Phase 3: User Story 1 - Search engine menemukan seluruh halaman yang bisa diakses (Priority: P1) 🎯 MVP

**Goal**: `GET /sitemap.xml` mengembalikan XML valid berisi 7 halaman statis tetap, `/karir` (kondisional), dan URL detail tiap Produk/Artikel published/Halaman Statis/Proyek Portfolio aktif — selalu terkini setiap request.

**Independent Test**: Buka `/sitemap.xml`, salin beberapa `<loc>` dari tiap jenis, buka satu-satu → semua 200. Publikasikan artikel baru / aktifkan proyek portfolio → muncul di request berikutnya. Draft artikel / nonaktifkan proyek → hilang di request berikutnya.

### Tests for User Story 1 ⚠️ (write first, ensure they FAIL)

- [x] T005 [P] [US1] Create `tests/Feature/Public/SitemapTest.php` via `php artisan make:test --phpunit Public/SitemapTest` covering (FR-002 s/d FR-009, FR-012, Edge Cases): response 200 & `Content-Type` mengandung `application/xml`; assertSee 7 URL statis (`url('/')`, `url('/tentang-kami')`, `url('/kontak')`, `url('/faq')`, `url('/artikel')`, `url('/produk')`, `url('/portfolio')`); dengan `career_module_enabled = true` → assertSee `url('/karir')`, dengan `false` → assertDontSee; buat 1 Produk → assertSee `url('/produk/'.$slug)`; buat Artikel published → assertSee URL-nya, buat Artikel draft (`published_at` null) & terjadwal (`published_at` masa depan) → assertDontSee URL keduanya; buat CustomPage → assertSee URL-nya; buat PortfolioProject aktif → assertSee, buat yang `is_active=false` → assertDontSee; test terpisah "semua loc bisa diakses": generate sitemap, ekstrak semua `<loc>` via regex/DOMDocument, GET tiap satu → assertOk() untuk semuanya (SC-001); test "situs kosong tetap 200": tanpa seed apa pun (kosongkan semua tabel konten) → `/sitemap.xml` tetap `assertOk()` dan tetap memuat 7 URL statis (Edge Cases)

### Implementation for User Story 1

- [x] T006 [US1] Implementasikan `SitemapController::xml()` sesuai data-model.md § Daftar sumber: kumpulkan array `$staticUrls` (7 URL tetap, tanpa `lastmod`), tambah `/karir` bila `app(\App\Settings\BrandSettings::class)->career_module_enabled`; query `Product::all()`, `Article::whereNotNull('published_at')->where('published_at','<=',now())->get()`, `CustomPage::all()`, `PortfolioProject::where('is_active', true)->get()` — masing-masing dipetakan ke `['loc' => url(...), 'lastmod' => $item->updated_at]`; return `response()->view('sitemap', ['staticUrls' => ..., 'items' => ...])->header('Content-Type', 'application/xml; charset=UTF-8')`
- [x] T007 [US1] Create `resources/views/sitemap.blade.php` (TIDAK `@extends('layouts.public')`) — XML manual: `<?xml version="1.0" encoding="UTF-8"?>` lalu `<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">`, loop `$staticUrls` dan `$items` masing-masing jadi `<url><loc>{{ $entry['loc'] }}</loc>@if($entry['lastmod'] ?? null)<lastmod>{{ $entry['lastmod']->toAtomString() }}</lastmod>@endif</url>`, tutup `</urlset>` — pastikan `{{ }}` (bukan `{!! !!}`) dipakai supaya karakter spesial ter-escape otomatis (Edge Cases)
- [x] T008 [US1] Run `vendor/bin/pint --dirty --format agent`, then `php artisan test --compact --filter='SitemapTest'` and fix until T005 passes; also re-run T001's filter to confirm no regression

**Checkpoint**: `/sitemap.xml` lengkap dan akurat — US1 selesai dan independen (bisa dites/dirilis tanpa US2)

---

## Phase 4: User Story 2 - Search engine diarahkan ke sitemap lewat robots.txt (Priority: P2)

**Goal**: `GET /robots.txt` (dinamis, menggantikan file statis) tetap mengizinkan semua crawl seperti sebelumnya, ditambah baris `Sitemap:` yang menunjuk ke `/sitemap.xml` pada domain yang sedang diakses.

**Independent Test**: Buka `/robots.txt` — `User-agent: *` / `Disallow:` kosong tidak berubah; ada baris `Sitemap:` dengan URL absolut yang valid; ganti domain akses (test dengan `APP_URL` berbeda) → URL sitemap ikut berubah.

### Tests for User Story 2 ⚠️ (write first, ensure they FAIL)

- [ ] T009 [P] [US2] Create `tests/Feature/Public/RobotsTxtTest.php` via `php artisan make:test --phpunit Public/RobotsTxtTest` covering (FR-010, FR-011): response 200 & `Content-Type` mengandung `text/plain`; assertSee `'User-agent: *'` dan `'Disallow:'` (tidak berubah dari isi lama); assertSee `'Sitemap: '.url('/sitemap.xml')` persis

### Implementation for User Story 2

- [ ] T010 [US2] Implementasikan `SitemapController::robots()`: return `response("User-agent: *\nDisallow:\n\nSitemap: ".url('/sitemap.xml')."\n")->header('Content-Type', 'text/plain; charset=UTF-8')` (isi `User-agent`/`Disallow` identik dengan `public/robots.txt` lama — FR-011)
- [ ] T011 [US2] **Hapus** `public/robots.txt` (file statis) — WAJIB, lihat research.md §2: selama file ini ada, web server menyajikannya langsung dan route `/robots.txt` tidak pernah tereksekusi
- [ ] T012 [US2] Run `vendor/bin/pint --dirty --format agent`, then `php artisan test --compact --filter='RobotsTxtTest'` and fix until T009 passes

**Checkpoint**: `/robots.txt` menunjuk ke `/sitemap.xml` — US2 selesai, kedua user story lengkap

---

## Phase 5: Polish & Cross-Cutting Concerns

**Purpose**: Regresi penuh + validasi manual sebelum PR.

- [ ] T013 Run `php artisan test --compact` (seluruh suite) — pastikan tidak ada regresi di modul lain
- [ ] T014 Run `vendor/bin/pint --format agent` (full pass, bukan `--dirty`)
- [ ] T015 Jalankan `quickstart.md` end-to-end secara manual (§1-4) — catat hasil di PR description

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — bisa mulai langsung
- **Foundational (Phase 2)**: Depends on Setup — BLOCKS kedua user story
- **User Story 1 (Phase 3)**: Depends on Foundational — tidak bergantung ke US2
- **User Story 2 (Phase 4)**: Depends on Foundational — method `robots()` memakai `url('/sitemap.xml')` (string URL saja, bukan pemanggilan `xml()`), jadi TIDAK butuh US1 selesai lebih dulu secara kode; tapi US1 dikerjakan lebih dulu sesuai prioritas P1
- **Polish (Phase 5)**: Depends on kedua story selesai

### Within Each User Story

- Test (T005, T009) ditulis dan dipastikan FAIL dulu sebelum implementasi
- Checkpoint test run di akhir tiap fase sebelum lanjut fase berikutnya

### Parallel Opportunities

- T005 dan T009 (test kedua story) bisa ditulis paralel — file berbeda, tidak saling bergantung
- T006+T007 (implementasi US1) sekuensial (view butuh controller siap memanggilnya), begitu juga T010+T011 (US2)

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational
3. Complete Phase 3: User Story 1 — **MVP**: `/sitemap.xml` lengkap dan akurat, bisa langsung didaftarkan manual ke Google Search Console meski `robots.txt` belum menunjuknya otomatis
4. **STOP and VALIDATE**: jalankan quickstart.md §1-3
5. Deploy/demo bila cukup

### Incremental Delivery

1. Setup + Foundational → fondasi siap
2. + User Story 1 → validasi independen → deploy (MVP)
3. + User Story 2 → validasi independen → deploy (discovery otomatis lewat robots.txt)

---

## Notes

- [P] tasks = file berbeda, tidak saling bergantung
- [Story] label memetakan task ke user story untuk traceability
- Verifikasi test FAIL dulu sebelum implementasi (TDD, sesuai Constitution Principle IV)
- Commit setelah tiap checkpoint fase (T008, T012) — pola commit message `feat(015): <fase> — AMC-224`
