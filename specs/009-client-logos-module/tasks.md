---
description: "Task list for Modul Client Logos"
---

# Tasks: Modul Client Logos

**Input**: Design documents from `/specs/009-client-logos-module/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/admin-panel-surface.md, quickstart.md

**Tests**: Included — feature tests required by plan.md (Testing section) and Constitution Principle IV (Module Test Coverage).

**Organization**: Tasks grouped by user story. Both stories are Priority P1.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: US1 or US2
- File paths are repository-relative

## Path Conventions

Laravel web app, single project. Source at repository root: `app/`, `database/`, `routes/`, `resources/`, `tests/`.

**Dependency note**: This module builds on modul 008 (Testimonials), already merged to `main` — `resources/views/pages/tentang-kami.blade.php` already contains `<x-sections.testimonials :testimonials="$testimonials" />` and `app/Http/Controllers/Public/AboutController.php` already sends `$testimonials`.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: No project init needed — existing Laravel app. Only baseline check.

- [x] T001 Confirm reference pattern still green: run `php artisan test --compact tests/Feature/Admin/TestimonialResourceTest.php tests/Feature/Pages/AboutPageTestimonialsTest.php` (this module mirrors modul 008; both files are the direct template)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: The `client_logos` table, `ClientLogo` model, and factory — required by BOTH user stories.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T002 Create migration `database/migrations/xxxx_create_client_logos_table.php` via `php artisan make:migration create_client_logos_table --no-interaction` with columns per data-model.md: `id`, `company_name` (string), `logo_path` (string), `link_url` (string nullable), `order` (integer default 0), `is_active` (boolean default true), `timestamps`
- [x] T003 Create model `app/Models/ClientLogo.php` via `php artisan make:model ClientLogo --no-interaction`: `HasFactory`; `$fillable = ['company_name','logo_path','link_url','order','is_active']`; `casts()` → `order` integer, `is_active` boolean (pattern: `app/Models/Testimonial.php`)
- [x] T004 [P] Create `database/factories/ClientLogoFactory.php` via `php artisan make:factory ClientLogoFactory --no-interaction`: `company_name` `fake()->company()`, `logo_path` `'client-logos/'.fake()->uuid().'.webp'`, `link_url` null, `order` 0, `is_active` true; add an `inactive()` state setting `is_active` false (pattern: `database/factories/TestimonialFactory.php`)
- [x] T005 Run `php artisan migrate` and verify `client_logos` schema via `database-schema`

**Checkpoint**: Model + table + factory ready — user stories can begin

---

## Phase 3: User Story 1 - Admin mengelola logo klien/partner (Priority: P1) 🎯 MVP

**Goal**: Admin can create/edit/delete client logos in the Filament panel — company name, logo file (converted to WebP), optional link URL (validated as absolute http/https), order, active toggle, delete with confirmation.

**Independent Test**: Login as admin at `/admin/client-logos`, create 3 logos (one without link, one inactive), edit one, delete one with confirmation; verify required-field errors (company_name/logo_path), invalid-URL rejection (`contoh.com`), and that an uploaded logo is stored as `.webp`.

### Tests for User Story 1 ⚠️ (write first, ensure they FAIL)

- [x] T006 [P] [US1] Create `tests/Feature/Admin/ClientLogoResourceTest.php` via `php artisan make:test --phpunit Admin/ClientLogoResourceTest` (pattern: `tests/Feature/Admin/TestimonialResourceTest.php` for `Storage::fake` + Livewire helpers) covering: admin can render list/create/edit; create persists record; `company_name`+`logo_path` required → `assertHasFormErrors`; `link_url` = `contoh.com` (no scheme) rejected; `link_url` = `https://example.com` accepted; blank `link_url` accepted; uploaded logo saved with `.webp` extension via `ImageUploads::storeAsWebp`; `is_active` and `order` persist; delete removes record

### Implementation for User Story 1

- [x] T007 [US1] Generate resource: `php artisan make:filament-resource ClientLogo --generate --no-interaction`, then rewrite `app/Filament/Resources/ClientLogoResource.php` form per research.md/contracts: `TextInput::make('company_name')` required maxLength 255; `FileUpload::make('logo_path')->image()->required()->disk('public')->directory('client-logos')->acceptedFileTypes(['image/png','image/jpeg','image/webp'])->saveUploadedFileUsing(fn ($file) => \App\Support\ImageUploads::storeAsWebp($file, 'client-logos'))`; `TextInput::make('link_url')->url()->maxLength(255)->rule('nullable')->rule('starts_with:http://,https://')->helperText('Opsional. Harus diawali http:// atau https://')`; `TextInput::make('order')->numeric()->default(0)`; `Toggle::make('is_active')->default(true)->helperText('Logo nonaktif tidak tampil di halaman Tentang Kami.')` (pattern: `app/Filament/Resources/TestimonialResource.php`)
- [x] T008 [US1] Configure `ClientLogoResource` table + navigation: `navigationLabel = 'Logo Klien'`, `navigationIcon = 'heroicon-o-building-office-2'`; `->defaultSort('order')`; columns `ImageColumn::make('logo_path')->disk('public')`, `company_name` (searchable), `link_url` (`->url(fn ($state) => $state)->openUrlInNewTab()` / placeholder '—'), `TextColumn::make('order')->sortable()`, `ToggleColumn::make('is_active')`; actions `EditAction` + `DeleteAction` (default confirmation); `bulkActions` `DeleteBulkAction`; no policy / `canAccess` override (FR-011)
- [x] T009 [US1] Verify generated pages `app/Filament/Resources/ClientLogoResource/Pages/{ListClientLogos,CreateClientLogo,EditClientLogo}.php` match the `TestimonialResource` page pattern (List has `CreateAction` header, Edit has `DeleteAction` header)
- [x] T010 [US1] Run `vendor/bin/pint --dirty --format agent`, then `php artisan test --compact --filter=ClientLogoResourceTest` and fix until T006 passes

**Checkpoint**: US1 fully functional — admin CRUD + validation + WebP logo all verified

---

## Phase 4: User Story 2 - Pengunjung melihat logo strip "dipercaya oleh" (Priority: P1)

**Goal**: `/tentang-kami` renders a client-logo strip immediately after the testimonials section and before the CTA band, showing only active logos ordered by `order` then `id`. Each logo uses `company_name` as alt text; logos with `link_url` are wrapped in an `<a target="_blank" rel="noopener noreferrer nofollow">`, others render as plain images. When no active logo exists the strip is not rendered and all other About sections (including testimonials) stay intact. Home page is untouched.

**Independent Test**: With active + inactive logos from US1, open `/tentang-kami`: only active ones show, in order; a linked logo's markup contains its URL + `target="_blank"`; an unlinked logo has no anchor; deactivate all → strip gone, page still renders testimonials + CTA; `/` shows no logo strip.

### Tests for User Story 2 ⚠️ (write first, ensure they FAIL)

- [x] T011 [P] [US2] Create `tests/Feature/Pages/AboutPageClientLogosTest.php` via `php artisan make:test --phpunit Pages/AboutPageClientLogosTest` covering (FR-005/006/009/010/012): `/tentang-kami` shows an active logo's `company_name` (as alt) and its `logo_path` URL; an inactive logo (`ClientLogo::factory()->inactive()->create()`) does NOT appear; two active logos with `order` 2 and 1 render in order 1→2 (`assertSeeInOrder` on company names); a logo with `link_url = 'https://example.com'` produces an `href="https://example.com"` with `target="_blank"`; a logo with null `link_url` does not wrap its company name in that href; when zero active logos exist the page still returns 200 and still shows an existing About marker (e.g. "Nilai-Nilai Kami") but not the logo-strip heading/marker; `/` (home) does not show a created logo's `company_name`

### Implementation for User Story 2

- [x] T012 [US2] Update `app/Http/Controllers/Public/AboutController.php`: add `$clientLogos = \App\Models\ClientLogo::query()->where('is_active', true)->orderBy('order')->orderBy('id')->get();` and include `'clientLogos' => $clientLogos` in the existing `view('pages.tentang-kami', [...])` array (keep the existing `$testimonials` key)
- [x] T013 [US2] Create `resources/views/components/sections/client-logos.blade.php` (anonymous component, `@props(['logos'])`): wrap entire output in `@if($logos->isNotEmpty())`; optional static heading (e.g. "Dipercaya oleh"); a flex-wrap / grid strip of logos, each: compute `$exists = $logo->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($logo->logo_path)`, skip item when `! $exists`; render `<img src="{{ Storage::disk('public')->url($logo->logo_path) }}" alt="{{ $logo->company_name }}" class="h-10 md:h-12 object-contain ...">`; when `$logo->link_url` present wrap the `<img>` in `<a href="{{ $logo->link_url }}" target="_blank" rel="noopener noreferrer nofollow">`. Match the utility/palette style of `resources/views/components/sections/testimonials.blade.php`
- [x] T014 [US2] Edit `resources/views/pages/tentang-kami.blade.php`: insert `<x-sections.client-logos :logos="$clientLogos" />` on its own line immediately after the existing `<x-sections.testimonials :testimonials="$testimonials" />` line and immediately before `<x-sections.cta-band />` (do not modify any existing section)
- [x] T015 [US2] Run `vendor/bin/pint --dirty --format agent`, then `php artisan test --compact --filter='AboutPageClientLogosTest|AboutPageTestimonialsTest|AboutPageTest'` and fix until T011 and the existing About page tests all pass

**Checkpoint**: US2 verified — logo strip renders active logos after testimonials, empty-state hides it, testimonials + other sections + home unaffected

---

## Phase 5: Polish & Cross-Cutting Concerns

- [ ] T016 [P] Run full quickstart.md manual verification (US1 steps 1-8, US2 steps 1-7) — MANUAL, pending user (needs `npm run build` + browser)
- [x] T017 Run the full suite: `php artisan test --compact` and confirm no regressions
- [x] T018 Final `vendor/bin/pint --dirty --format agent` pass on all changed PHP files

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: none
- **Foundational (Phase 2)**: after Setup — BLOCKS both user stories
- **User Story 1 (Phase 3)**: after Phase 2
- **User Story 2 (Phase 4)**: after Phase 2. The section test (T011) needs the `ClientLogo` model + factory (Phase 2) but not the Filament resource — US2 is implementable in parallel with US1. Recommended order: US1 then US2.
- **Polish (Phase 5)**: after both stories

### Within Each User Story

- Tests before implementation (write, watch fail, then implement)
- Migration/model/factory (Phase 2) before resource / controller / view
- US2: controller (T012) and component (T013) before the page edit (T014); T014 before T015

### Parallel Opportunities

- T004 [P] alongside T002/T003
- T006 [P] and T011 [P] — different test files, can be written together
- US1 implementation (T007–T010) and US2 implementation (T012–T015) can proceed in parallel once Phase 2 is done

---

## Parallel Example: test authoring

```bash
Task: "Create tests/Feature/Admin/ClientLogoResourceTest.php"
Task: "Create tests/Feature/Pages/AboutPageClientLogosTest.php"
```

---

## Implementation Strategy

### MVP (User Story 1 only)

1. Phase 1 Setup
2. Phase 2 Foundational (migration + model + factory + migrate)
3. Phase 3 User Story 1 (Filament CRUD)
4. STOP and VALIDATE with quickstart US1 steps
5. Deploy/demo — admin can manage client logos

### Incremental Delivery

1. Setup + Foundational → foundation ready
2. US1 → test independently → demo (admin CRUD works)
3. US2 → About page logo strip → test → demo (social proof visible)
4. Polish → full suite + quickstart

---

## Notes

- No new dependencies (Principle V) — `ImageUploads::storeAsWebp` and the Filament resource pattern already exist.
- SVG upload out of scope (GD converts raster only) — `acceptedFileTypes` steers admins to PNG/JPEG/WebP.
- No module-level toggle, no logo categories, no admin-configurable heading (spec Assumptions).
- Only `tentang-kami.blade.php` gets one inserted line (after the testimonials line from modul 008); all other About sections and the home page stay untouched (FR-012).
- Commit after each phase or logical group.
