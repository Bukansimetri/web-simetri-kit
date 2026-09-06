---
description: "Task list for Custom Page (Halaman Statis Bebas)"
---

# Tasks: Custom Page (Halaman Statis Bebas)

**Input**: Design documents from `/specs/007-custom-page/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/admin-panel-surface.md, quickstart.md

**Tests**: Included — feature tests are explicitly required by plan.md (Testing section) and Constitution Principle IV (Module Test Coverage).

**Organization**: Tasks grouped by user story. Both stories are Priority P1.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: US1 or US2
- File paths are repository-relative

## Path Conventions

Laravel web app, single project. Source at repository root: `app/`, `database/`, `routes/`, `resources/`, `tests/`.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: No project init needed — existing Laravel app. Only feature scaffolding.

- [x] T001 Confirm Filament + PHPUnit toolchain works: run `php artisan test --compact tests/Feature/Admin/ArticleResourceTest.php` as the reference pattern for this feature (no code change; establishes baseline)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: The `custom_pages` table, `CustomPage` model, and factory — required by BOTH user stories.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T002 Create migration `database/migrations/xxxx_create_custom_pages_table.php` via `php artisan make:migration create_custom_pages_table --no-interaction` with columns: `id`, `title` (string), `slug` (string, unique), `content` (longText), `timestamps` (per data-model.md)
- [x] T003 Create model `app/Models/CustomPage.php` via `php artisan make:model CustomPage --no-interaction`: `$fillable = ['title', 'slug', 'content']`, `getRouteKeyName(): string` returns `'slug'`, `HasFactory` trait (pattern: `app/Models/Article.php`)
- [x] T004 [P] Create `database/factories/CustomPageFactory.php` via `php artisan make:factory CustomPageFactory --no-interaction`: `title` from `fake()->sentence()`, `slug` from `Str::slug($title)` (unique), `content` a small HTML string (pattern: `database/factories/ArticleFactory.php`)
- [x] T005 Run `php artisan migrate` and verify `custom_pages` table exists via `database-schema`

**Checkpoint**: Model + table + factory ready — user stories can begin

---

## Phase 3: User Story 1 - Admin membuat & mengedit halaman statis (Priority: P1) 🎯 MVP

**Goal**: Admin can create/edit/delete Custom Pages in the Filament panel (title + rich text content, auto/override slug, unique-slug + required-field validation); saved pages are immediately reachable at `/halaman/{slug}` and return 404 after delete.

**Independent Test**: Login as admin, create page "Kebijakan Privasi" with rich text content, leave slug blank → saved with slug `kebijakan-privasi`; open `/halaman/kebijakan-privasi` → content renders with formatting; edit title → change reflected; create second page with same slug → rejected; submit with empty title/content → per-field errors; delete page → `/halaman/kebijakan-privasi` returns 404.

### Tests for User Story 1 ⚠️ (write first, ensure they FAIL)

- [x] T006 [P] [US1] Create `tests/Feature/Admin/CustomPageResourceTest.php` via `php artisan make:test --phpunit Admin/CustomPageResourceTest` (pattern: `tests/Feature/Admin/ArticleResourceTest.php`) covering: admin can render list/create/edit pages; create persists record; slug auto-generates from title when blank; manual slug override is respected; duplicate slug rejected with error; missing `title` rejected; missing `content` rejected; delete removes record
- [x] T007 [P] [US1] Create `tests/Feature/Pages/CustomPagePageTest.php` via `php artisan make:test --phpunit Pages/CustomPagePageTest` (pattern: `tests/Feature/Pages/ArticlePageTest.php`) covering: `GET /halaman/{slug}` returns 200 and shows title + content HTML for an existing page; `GET /halaman/{unknown-slug}` returns 404; after model delete, the slug returns 404

### Implementation for User Story 1

- [x] T008 [US1] Generate resource: `php artisan make:filament-resource CustomPage --generate --no-interaction`, then edit `app/Filament/Resources/CustomPageResource.php` form to: `TextInput::make('title')` required + `live(onBlur: true)` + `afterStateUpdated` to set `slug` via `Str::slug()` when slug empty; `TextInput::make('slug')` required + `unique(ignoreRecord: true)`; `RichEditor::make('content')` required (pattern: `app/Filament/Resources/ArticleResource.php`)
- [x] T009 [US1] Configure `CustomPageResource` table columns (`title`, `slug`, `updated_at`) and the delete action with confirmation (Filament `DeleteAction`/`DeleteBulkAction` default confirmation), plus navigation label "Halaman" — match FR-007, FR-010 (no policy restriction: open to all panel roles, like `ArticleResource`)
- [x] T010 [US1] Verify generated pages `app/Filament/Resources/CustomPageResource/Pages/{ListCustomPages,CreateCustomPage,EditCustomPage}.php` match the `ArticleResource` page pattern (no custom slug mutation needed if handled in form)
- [x] T011 [US1] Add public route in `routes/web.php`: `Route::get('/halaman/{customPage:slug}', CustomPageController::class)->name('halaman.show');` (place near other public content routes, after `/faq`)
- [x] T012 [US1] Create `app/Http/Controllers/Public/CustomPageController.php` via `php artisan make:controller Public/CustomPageController --invokable --no-interaction`: `__invoke(CustomPage $customPage): View` returning `view('pages.custom-page.show', ['customPage' => $customPage])` (pattern: `app/Http/Controllers/Public/AboutController.php`)
- [x] T013 [US1] Create view `resources/views/pages/custom-page/show.blade.php`: extend the public layout used by `resources/views/pages/tentang-kami.blade.php`, render `$customPage->title` as `<h1>` / page `<title>`, render `{!! $customPage->content !!}` inside a prose/content wrapper
- [x] T014 [US1] Run `vendor/bin/pint --dirty --format agent`, then `php artisan test --compact --filter=CustomPage` and fix until T006 + T007 pass

**Checkpoint**: US1 fully functional — admin CRUD + public `/halaman/{slug}` rendering + 404 behavior all verified

---

## Phase 4: User Story 2 - Pengunjung mengakses Kebijakan Privasi & Syarat Ketentuan yang sungguhan (Priority: P1)

**Goal**: Footer links "Kebijakan Privasi" and "Syarat & Ketentuan" point to `/halaman/kebijakan-privasi` and `/halaman/syarat-ketentuan` instead of `/tentang-kami`. Links 404 until admin creates those pages (accepted behavior); `/tentang-kami` is untouched.

**Independent Test**: Load any public page, inspect footer → both links point to the `/halaman/...` URLs (not `/tentang-kami`). Click before pages exist → 404. Create both pages (slugs `kebijakan-privasi`, `syarat-ketentuan`) → links now land on matching content. `/tentang-kami` unchanged.

### Tests for User Story 2 ⚠️ (write first, ensure they FAIL)

- [x] T015 [P] [US2] Create `tests/Feature/Public/FooterLegalLinksTest.php` via `php artisan make:test --phpunit Public/FooterLegalLinksTest` covering (FR-012): a rendered public page's footer contains `href` to `/halaman/kebijakan-privasi` and `/halaman/syarat-ketentuan` and NO longer links those labels to `/tentang-kami`; `GET /halaman/kebijakan-privasi` returns 404 when the page does not exist; returns 200 with the page content once a `CustomPage` with that slug exists; `GET /tentang-kami` still returns 200 (unchanged, FR-013)

### Implementation for User Story 2

- [x] T016 [US2] Edit `resources/views/components/layout/footer.blade.php` lines ~54-55: change the "Kebijakan Privasi" href to `{{ url('/halaman/kebijakan-privasi') }}` and "Syarat & Ketentuan" href to `{{ url('/halaman/syarat-ketentuan') }}` (leave the "Tentang Kami" nav link at line 12 unchanged)
- [x] T017 [US2] Run `vendor/bin/pint --dirty --format agent`, then `php artisan test --compact --filter=FooterLegalLinks` and fix until T015 passes

**Checkpoint**: US2 verified — footer legal links resolve to real Custom Pages, `/tentang-kami` untouched

---

## Phase 5: Polish & Cross-Cutting Concerns

- [ ] T018 [P] Run full quickstart.md manual verification (US1 steps 1-7, US2 steps 1-5) — MANUAL, pending user
- [x] T018b Verify `custom_pages` schema via database-schema (id, title, slug unique, content longtext, timestamps)
- [x] T019 Run the full suite: `php artisan test --compact` and confirm no regressions
- [x] T020 Final `vendor/bin/pint --dirty --format agent` pass on all changed PHP files

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: none
- **Foundational (Phase 2)**: after Setup — BLOCKS both user stories
- **User Story 1 (Phase 3)**: after Phase 2
- **User Story 2 (Phase 4)**: after Phase 2. Independent of US1 for the footer-link change (T016), but its end-to-end test (T015) exercises the same `/halaman/{slug}` route + view built in US1 (T011-T013). Recommended order: US1 then US2.
- **Polish (Phase 5)**: after both stories

### Within Each User Story

- Tests before implementation (write, watch fail, then implement)
- Model/migration/factory (Phase 2) before resource + route + controller + view
- Route (T011) before controller (T012) before view (T013)

### Parallel Opportunities

- T004 can run parallel to T002/T003 grouping only after model exists; effectively T004 [P] alongside migration edits
- T006 and T007 [P] — different test files
- T015 [P] — separate test file (can be written alongside T006/T007)
- US1 and US2 test-writing can happen in parallel; US2 implementation (T016) can be done in parallel with US1 implementation

---

## Parallel Example: User Story 1 tests

```bash
Task: "Create tests/Feature/Admin/CustomPageResourceTest.php"
Task: "Create tests/Feature/Pages/CustomPagePageTest.php"
```

---

## Implementation Strategy

### MVP (User Story 1 only)

1. Phase 1 Setup
2. Phase 2 Foundational (migration + model + factory + migrate)
3. Phase 3 User Story 1 (CRUD + public route/view)
4. STOP and VALIDATE with quickstart US1 steps
5. Deploy/demo — admin can now author real static pages

### Incremental Delivery

1. Setup + Foundational → foundation ready
2. US1 → test independently → demo (MVP: static page authoring works)
3. US2 → change footer links → test → demo (legal links now real)
4. Polish → full suite + quickstart

---

## Notes

- No new dependencies (Principle V) — `RichEditor` and route-model-binding are existing patterns from 005-artikel-crud-admin.
- No draft/publish, no public listing/index, no nested pages, no SEO meta fields — explicitly out of scope (spec Assumptions).
- Slugs `kebijakan-privasi` and `syarat-ketentuan` are a fixed convention admins must use; document in quickstart (already done).
- `/tentang-kami` Blade page MUST NOT be modified (FR-013).
- Commit after each phase or logical group.
