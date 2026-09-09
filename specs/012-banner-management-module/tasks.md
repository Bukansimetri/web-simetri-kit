---
description: "Task list for Modul Banner Management"
---

# Tasks: Modul Banner Management

**Input**: Design documents from `/specs/012-banner-management-module/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/admin-panel-surface.md, quickstart.md

**Tests**: Included — feature + unit tests required by plan.md (Testing section) and Constitution Principle IV (Module Test Coverage).

**Organization**: Tasks grouped by user story. Both stories are Priority P1.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: US1 or US2
- File paths are repository-relative

## Path Conventions

Laravel web app, single project. Source at repository root: `app/`, `database/`, `routes/`, `resources/`, `tests/`.

**Dependency note**: `App\Support\ImageUploads::storeAsWebp` already supports the `maxWidth` param (added in modul 010). Alpine.js 3.17 is already in `package.json` and used by `<header>` — no new dependency. `resources/views/components/sections/hero.blade.php` MUST NOT be modified (it is the fallback).

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: No project init needed — existing Laravel app. Only baseline check.

- [x] T001 Confirm reference patterns still green: run `php artisan test --compact tests/Feature/Admin/ClientLogoResourceTest.php tests/Feature/Pages/HomePageTest.php tests/Unit/ImageUploadsTest.php` (Banner mirrors `ClientLogoResource`; `HomePageTest` covers the page whose hero this feature replaces)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: The `banners` table, `Banner` model (with `scopeLive()` + `displayStatus()`), and factory — required by BOTH user stories.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T002 Create migration `database/migrations/xxxx_create_banners_table.php` via `php artisan make:migration create_banners_table --no-interaction` with columns per data-model.md: `id`, `title` (string), `image_path` (string), `alt_text` (string), `link_url` (string nullable), `starts_at` (date nullable), `ends_at` (date nullable), `order` (integer default 0), `is_active` (boolean default true), `timestamps`
- [x] T003 Create model `app/Models/Banner.php` via `php artisan make:model Banner --no-interaction`: `HasFactory`; `$fillable = ['title','image_path','alt_text','link_url','starts_at','ends_at','order','is_active']`; `casts()` → `starts_at` date, `ends_at` date, `order` integer, `is_active` boolean; `scopeLive(Builder $query): Builder` per data-model.md (`is_active` true + `starts_at` null|<=today + `ends_at` null|>=today, `orderBy order,id`); `displayStatus(): string` returning `inactive` / `scheduled` / `expired` / `live` per data-model.md table
- [x] T004 [P] Create `database/factories/BannerFactory.php` via `php artisan make:factory BannerFactory --no-interaction`: `title` `fake()->sentence(3)`, `image_path` `'banners/'.fake()->uuid().'.webp'`, `alt_text` `fake()->sentence()`, `link_url` null, `starts_at` null, `ends_at` null, `order` 0, `is_active` true; states: `inactive()` (`is_active` false), `scheduled()` (`starts_at` = `today()->addDays(3)`), `expired()` (`starts_at` = `today()->subDays(10)`, `ends_at` = `today()->subDay()`)
- [x] T005 Run `php artisan migrate` and verify `banners` schema via `database-schema`

**Checkpoint**: Model + table + factory ready — user stories can begin

---

## Phase 3: User Story 1 - Admin mengelola banner (Priority: P1) 🎯 MVP

**Goal**: Admin can create/edit/delete banners — internal title, required image (resized ≤1600px + WebP), required alt text, optional link URL (http/https), optional display period (`ends_at` ≥ `starts_at`), order, active toggle; the list shows each banner's live status.

**Independent Test**: Login as admin at `/admin/banners`, create banners covering no-period / scheduled / expired / inactive, verify the status column labels each correctly; verify required-field errors (title/image/alt_text), invalid-URL rejection, `ends_at < starts_at` rejection, and that an uploaded 2000px image is stored at 1600px WebP; delete one with confirmation.

### Tests for User Story 1 ⚠️ (write first, ensure they FAIL)

- [x] T006 [P] Create `tests/Unit/BannerLiveScopeTest.php` via `php artisan make:test --phpunit --unit BannerLiveScopeTest` covering the contract matrix in contracts/admin-panel-surface.md §2: `Banner::live()->get()` includes {no period, period spanning today, starts today, ends today} and excludes {expired, not-yet-started, inactive}; and `displayStatus()` returns `live` exactly for the included rows and `inactive`/`scheduled`/`expired` for the others (use `Banner::factory()` + explicit date attributes / states)
- [x] T007 [P] [US1] Create `tests/Feature/Admin/BannerResourceTest.php` via `php artisan make:test --phpunit Admin/BannerResourceTest` (pattern: `tests/Feature/Admin/ClientLogoResourceTest.php` for `Storage::fake` + Livewire helpers) covering: render list/create/edit; create with an image persists record; `title`+`image_path`+`alt_text` required → `assertHasFormErrors`; `link_url` = `contoh.com` rejected, `https://x.test` accepted, blank accepted; `ends_at` earlier than `starts_at` → `assertHasFormErrors(['ends_at'])`; both dates blank accepted; uploaded image saved with `.webp` extension; a 2000px-wide image stored at width 1600 (read back dimensions); `is_active` and `order` persist; delete removes record

### Implementation for User Story 1

- [x] T008 [US1] Generate resource: `php artisan make:filament-resource Banner --generate --no-interaction`, then rewrite `app/Filament/Resources/BannerResource.php` form per research.md/contracts: `TextInput::make('title')` required maxLength 255 with helper "Judul internal — tidak tampil ke pengunjung"; `FileUpload::make('image_path')->image()->required()->disk('public')->directory('banners')->acceptedFileTypes(['image/png','image/jpeg','image/webp'])->helperText('Wajib. Rekomendasi 1600×600px. Gambar besar otomatis dikecilkan ke lebar 1600px & dikonversi WebP.')->saveUploadedFileUsing(fn ($file) => \App\Support\ImageUploads::storeAsWebp($file, 'banners', maxWidth: 1600))`; `TextInput::make('alt_text')->required()->maxLength(255)->helperText('Teks alternatif gambar (aksesibilitas).')`; `TextInput::make('link_url')->url()->maxLength(255)->rule('starts_with:http://,https://')`; `DatePicker::make('starts_at')`; `DatePicker::make('ends_at')->rule('after_or_equal:starts_at')`; `TextInput::make('order')->numeric()->default(0)`; `Toggle::make('is_active')->default(true)`
- [x] T009 [US1] Configure `BannerResource` table + navigation: `navigationLabel = 'Banner'`, `navigationIcon = 'heroicon-o-photo'`; `->defaultSort('order')`; columns `ImageColumn::make('image_path')->disk('public')`, `title` (searchable), `TextColumn::make('status')` computed via `->state(fn (Banner $record) => $record->displayStatus())->badge()->formatStateUsing(fn (string $state) => ['live'=>'Tayang','scheduled'=>'Terjadwal','expired'=>'Kedaluwarsa','inactive'=>'Nonaktif'][$state])->color(fn (string $state) => ['live'=>'success','scheduled'=>'warning','expired'=>'danger','inactive'=>'gray'][$state])`, `starts_at` (date, placeholder '—'), `ends_at` (date, placeholder '—'), `order` (sortable), `ToggleColumn::make('is_active')`; actions `EditAction` + `DeleteAction` (default confirmation); `bulkActions` `DeleteBulkAction`; no policy / `canAccess` override (FR-014)
- [x] T010 [US1] Verify generated pages `app/Filament/Resources/BannerResource/Pages/*` match the `ClientLogoResource` page pattern (List has `CreateAction` header, Edit has `DeleteAction` header)
- [x] T011 [US1] Run `vendor/bin/pint --dirty --format agent`, then `php artisan test --compact --filter='BannerResourceTest|BannerLiveScopeTest'` and fix until T006 + T007 pass

**Checkpoint**: US1 fully functional — admin CRUD + period validation + WebP resize + status column all verified

---

## Phase 4: User Story 2 - Banner sebagai hero beranda (Priority: P1)

**Goal**: `/` renders the currently-live banners (from `Banner::live()`) in the hero position — a single image when exactly one, an auto-rotating carousel with nav controls when more than one, each optionally wrapped in a same-tab link. When zero banners are live the page renders the existing static `<x-sections.hero />`. Other home sections and other pages are untouched.

**Independent Test**: With a mix of live/scheduled/expired/inactive banners, open `/`: only live ones render in the hero slot, ordered by `order`; a linked banner's markup contains its URL; deactivate all → the static hero markup ("Nyalakan rumah Anda dengan energi matahari") returns; `/produk` shows no banner.

### Tests for User Story 2 ⚠️ (write first, ensure they FAIL)

- [x] T012 [P] [US2] Create `tests/Feature/Pages/HomeBannerTest.php` via `php artisan make:test --phpunit Pages/HomeBannerTest` (pattern: `tests/Feature/Pages/HomePageTest.php`) covering (FR-008/011/012/015/016): with two `Banner::factory()` live banners (`order` 2 and 1, `alt_text` set), `/` shows both `alt_text` values in order 1→2 and does NOT show the static hero headline; a live banner with `link_url = 'https://x.test/promo'` produces `href="https://x.test/promo"`; a `scheduled()` and an `expired()` and an `inactive()` banner do NOT appear; with zero live banners `/` returns 200 and DOES show the static hero headline "Nyalakan rumah Anda dengan energi matahari"; `/produk` never shows a banner `alt_text`

### Implementation for User Story 2

- [x] T013 [US2] Update `app/Http/Controllers/Public/HomeController.php`: add `$banners = \App\Models\Banner::live()->get();` and include `'banners' => $banners` in the existing `view('pages.home', [...])` array (keep the existing `$products` key)
- [x] T014 [US2] Create `resources/views/components/sections/banner-carousel.blade.php` (anonymous component, `@props(['banners'])`): compute `$visible = $banners->filter(fn ($b) => $b->image_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($b->image_path))->values()`; if `$visible->isEmpty()` render nothing (defensive — caller already guards); a `<section>` at hero position; when `$visible->count() === 1` render a single `<img src alt>` (wrapped in `<a href>` if `link_url`), full-width with a fixed height utility (e.g. `h-[60vh] object-cover`); when `>1` render an Alpine `x-data="{ active: 0, count: {{ $visible->count() }} }"` `x-init` `setInterval(() => active = (active + 1) % count, 5000)` slider: each slide `x-show="active === $index"`, plus dot indicators (`<button @click="active = i">`) and prev/next buttons (`@click="active = (active + count - 1) % count"` / `(active + 1) % count`). Each image uses `alt_text` for `alt`; wrap in `<a href="{{ $b->link_url }}">` (same tab, no `target`) when present. Match the utility/palette style of `resources/views/components/sections/hero.blade.php`
- [x] T015 [US2] Edit `resources/views/pages/home.blade.php`: replace the single line `<x-sections.hero />` with `@if ($banners->isNotEmpty()) <x-sections.banner-carousel :banners="$banners" /> @else <x-sections.hero /> @endif` (do NOT modify `resources/views/components/sections/hero.blade.php`)
- [x] T016 [US2] Run `vendor/bin/pint --dirty --format agent`, then `php artisan test --compact --filter='HomeBannerTest|HomePageTest'` and fix until T012 and the existing `HomePageTest` both pass

**Checkpoint**: US2 verified — live banners render in hero slot (single or carousel), static hero fallback works, other sections + other pages unaffected

---

## Phase 5: Polish & Cross-Cutting Concerns

- [ ] T017 [P] Run full quickstart.md manual verification (US1 steps 1-11, US2 steps 1-6) — MANUAL, pending user (needs `npm run build` + browser to see the Alpine carousel)
- [x] T018 Run the full suite: `php artisan test --compact` and confirm no regressions (esp. `ClientLogoResourceTest` + `HomePageTest` + `ImageUploadsTest`)
- [x] T019 Final `vendor/bin/pint --dirty --format agent` pass on all changed PHP files

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: none
- **Foundational (Phase 2)**: after Setup — BLOCKS both user stories
- **User Story 1 (Phase 3)**: after Phase 2. T006 (scope unit test) needs only the model; T007 needs the resource.
- **User Story 2 (Phase 4)**: after Phase 2. Needs `Banner::live()` (T003). Independent of the Filament resource — implementable in parallel with US1.
- **Polish (Phase 5)**: after both stories

### Within Each User Story

- Tests before implementation
- Migration/model/factory (Phase 2) before resource / controller / view
- US2: controller (T013) and component (T014) before the page edit (T015); T015 before T016

### Parallel Opportunities

- T004 [P] alongside T002/T003
- T006 [P] (unit) and T007 [P] and T012 [P] — different test files
- US1 implementation (T008–T011) and US2 implementation (T013–T016) can proceed in parallel once Phase 2 is done

---

## Parallel Example: test authoring after Phase 2

```bash
Task: "Create tests/Unit/BannerLiveScopeTest.php"
Task: "Create tests/Feature/Admin/BannerResourceTest.php"
Task: "Create tests/Feature/Pages/HomeBannerTest.php"
```

---

## Implementation Strategy

### MVP (User Story 1 only)

1. Phase 1 Setup
2. Phase 2 Foundational (migration + model w/ `scopeLive`/`displayStatus` + factory)
3. Phase 3 User Story 1 (Filament CRUD + status column)
4. STOP and VALIDATE with quickstart US1 steps
5. Deploy/demo — admin can manage banners and see their live status

### Incremental Delivery

1. Setup + Foundational → foundation ready
2. US1 → admin CRUD → demo
3. US2 → hero carousel + static fallback → demo (banners live on the homepage)
4. Polish → full suite + quickstart

---

## Notes

- No new dependencies (Principle V) — `ImageUploads::storeAsWebp(maxWidth:)` exists (modul 010); Alpine.js is already installed and used.
- `resources/views/components/sections/hero.blade.php` MUST NOT be modified — it is the fallback (FR-012).
- Banner links open in the SAME tab (no `target="_blank"`) per spec Assumptions.
- Period is date-based and inclusive; `scopeLive()` + `displayStatus()` are the single source of truth (unit-tested in T006).
- Only `home.blade.php` chooses carousel-vs-hero; `HomeController` only gains one variable.
- Commit after each phase or logical group.
