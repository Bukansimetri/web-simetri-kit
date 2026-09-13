---

description: "Task list for Menu Builder implementation"
---

# Tasks: Menu Builder

**Input**: Design documents from `/specs/017-menu-builder/`

**Prerequisites**: [plan.md](./plan.md), [spec.md](./spec.md), [research.md](./research.md), [data-model.md](./data-model.md), [contracts/menu-rendering-contract.md](./contracts/menu-rendering-contract.md), [quickstart.md](./quickstart.md)

**Tests**: Included. Constitution Principle IV (Module Test Coverage) requires at least a basic feature test per module before it can be marked done — Menu Builder is explicitly named in that principle.

**Organization**: Tasks are grouped by user story (US1, US2, US3 from spec.md) to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1, US2, US3)
- File paths are relative to the repository root

## Path Conventions

Single Laravel + Filament monolith project (see [plan.md](./plan.md) Project Structure) — `app/`, `database/`, `resources/`, `tests/` at repository root.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Scaffold the files both models and the admin resource will live in

- [X] T001 [P] Scaffold `MenuLocation` model + migration stub via `php artisan make:model MenuLocation -m --no-interaction` (creates `app/Models/MenuLocation.php`, `database/migrations/*_create_menu_locations_table.php`)
- [X] T002 [P] Scaffold `MenuItem` model + migration stub via `php artisan make:model MenuItem -m --no-interaction` (creates `app/Models/MenuItem.php`, `database/migrations/*_create_menu_items_table.php`)
- [X] T003 [P] Scaffold `MenuItemResource` via `php artisan make:filament-resource MenuItem --no-interaction` (creates `app/Filament/Resources/MenuItemResource.php` + `Pages/`)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Data schema, models, and the URL-resolution contract that every user story depends on

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [X] T004 Implement `menu_locations` migration (columns per [data-model.md](./data-model.md): `name`, `slug` unique, `description` nullable, timestamps) in `database/migrations/*_create_menu_locations_table.php`
- [X] T005 Implement `menu_items` migration (columns: `menu_location_id` FK, `parent_id` FK nullable self-reference, `label`, `link_type` enum, `linkable_type`/`linkable_id` nullable morph, `external_url` nullable, `open_in_new_tab` boolean default false, `order_column` integer, `is_active` boolean default true, timestamps; indexes on `(menu_location_id, parent_id, order_column)` and `(linkable_type, linkable_id)`) in `database/migrations/*_create_menu_items_table.php` (depends on T004 for FK target)
- [X] T006 [P] Implement `MenuLocation` model: `$fillable`, auto-generate `slug` from `name` on creating (Str::slug), `hasMany` MenuItem in `app/Models/MenuLocation.php`
- [X] T007 [P] Implement `MenuItem` model: `$fillable`, `$casts` (`open_in_new_tab` => bool, `is_active` => bool), `belongsTo` MenuLocation, `belongsTo` self as `parent`, `hasMany` self as `children` ordered by `order_column`, `morphTo` as `linkable`, and `resolveUrl(): ?string` method implementing the resolution rules in [contracts/menu-rendering-contract.md](./contracts/menu-rendering-contract.md) §2 in `app/Models/MenuItem.php`
- [X] T008 Add `getPublicUrl(): ?string` and a menu-label accessor to `app/Models/CustomPage.php` so it satisfies the "internal linkable" contract from [contracts/menu-rendering-contract.md](./contracts/menu-rendering-contract.md) §2
- [X] T009 [P] Register morph map aliases for linkable types (e.g. `custom-page` => `CustomPage::class`) in `app/Providers/AppServiceProvider.php`
- [X] T010 [P] Create `MenuSeeder` seeding the two default locations (`Navbar Utama` / `navbar-utama`, `Footer` / `footer`) in `database/seeders/MenuSeeder.php`, and call it from `database/seeders/DatabaseSeeder.php`
- [X] T011 [P] Unit test `MenuItem::resolveUrl()` covering internal/external/none/deleted-target cases in `tests/Unit/MenuItemUrlResolutionTest.php`

**Checkpoint**: Schema, models, and URL resolver are in place — user story implementation can now begin.

---

## Phase 3: User Story 1 - Kelola item menu navbar (Priority: P1) 🎯 MVP

**Goal**: Admin dapat menambah, mengubah, dan menghapus item menu navbar dari admin panel, dengan hasil langsung tampil di navbar publik.

**Independent Test**: Login admin, buka Menu Builder, tambah satu item menu untuk lokasi "Navbar Utama" dengan label dan tujuan tautan, simpan, lalu verifikasi item tersebut tampil di navbar pada halaman publik.

### Tests for User Story 1

- [X] T012 [P] [US1] Feature test admin CRUD `MenuItem` (create/edit/delete via Filament resource) in `tests/Feature/Admin/MenuItemResourceTest.php`
- [X] T013 [P] [US1] Feature test navbar renders an active menu item with correct label/href in `tests/Feature/Public/MenuRenderingTest.php`

### Implementation for User Story 1

- [X] T014 [US1] Build `MenuItemResource` form (label, menu location select, link type radio/select, conditional linkable-type + linkable record select, external URL, open-in-new-tab toggle, active toggle) in `app/Filament/Resources/MenuItemResource.php`
- [X] T015 [US1] Build `MenuItemResource` table (columns: label, location, resolved link summary, active badge) and wire List/Create/Edit pages in `app/Filament/Resources/MenuItemResource.php` and `app/Filament/Resources/MenuItemResource/Pages/*`
- [X] T016 [US1] Create Blade component `app/View/Components/Layout/Menu.php` + view `resources/views/components/layout/menu.blade.php` rendering active items for a given `location` slug (label, resolved `href`, `target`), per the `<x-layout.menu>` contract
- [X] T017 [US1] Replace the hardcoded navbar links in `resources/views/components/layout/header.blade.php` with `<x-layout.menu location="navbar-utama" />`

**Checkpoint**: MVP complete — admin can manage navbar items end-to-end and see them live on the public site.

---

## Phase 4: User Story 2 - Susun urutan menu dengan drag & drop (Priority: P1)

**Goal**: Admin dapat mengubah urutan tampil item menu dalam satu lokasi dengan menyeret baris pada daftar admin panel.

**Independent Test**: Dengan minimal tiga item menu pada lokasi yang sama, seret salah satu item ke posisi baru di admin panel dan verifikasi urutan pada frontend mengikuti posisi baru tersebut.

### Tests for User Story 2

- [X] T018 [P] [US2] Feature test that reordering `MenuItem` rows persists `order_column` and is reflected in the public rendering order in `tests/Feature/Admin/MenuItemReorderTest.php`

### Implementation for User Story 2

- [X] T019 [US2] Enable `reorderable('order_column')` and default `orderBy('order_column')` on the `MenuItemResource` table query in `app/Filament/Resources/MenuItemResource.php`
- [X] T020 [US2] Scope reordering and default ordering per group (`menu_location_id`, `parent_id`) so drag & drop only reorders within the same location/parent in `app/Filament/Resources/MenuItemResource.php` and `app/Models/MenuItem.php`
- [X] T021 [US2] Ensure `resources/views/components/layout/menu.blade.php` orders root items and each parent's children strictly by `order_column` with a stable tie-break (`id`)

**Checkpoint**: Drag & drop reorder works in admin panel and is reflected on the public site.

---

## Phase 5: User Story 3 - Menentukan lokasi & tujuan tautan menu (Priority: P2)

**Goal**: Admin dapat mengelola lebih dari satu lokasi menu (navbar, footer, lokasi baru), menautkan item ke berbagai jenis tujuan, membuat sub-menu satu tingkat, dan melihat peringatan bila tautan internal rusak.

**Independent Test**: Buat satu item menu bertujuan URL eksternal dan satu item bertujuan halaman internal, masing-masing ditetapkan ke lokasi footer, dan verifikasi kedua tautan berfungsi benar dari halaman publik; buat satu lokasi menu baru tanpa mengubah kode.

### Tests for User Story 3

- [X] T022 [P] [US3] Feature test creating/editing a `MenuLocation` via the admin panel in `tests/Feature/Admin/MenuLocationResourceTest.php`
- [X] T023 [P] [US3] Feature test footer renders items assigned to the `footer` location independently from `navbar-utama` in `tests/Feature/Public/MenuRenderingTest.php`
- [X] T024 [P] [US3] Feature test a parent item with one level of children renders nested sub-items, and a deleted internal `linkable` target renders the item with no active `href` (no error) in `tests/Feature/Public/MenuRenderingTest.php`

### Implementation for User Story 3

- [X] T025 [US3] Build `MenuLocationResource` (List/Create/Edit/Delete, slug auto-generated from name) in `app/Filament/Resources/MenuLocationResource.php`
- [X] T026 [US3] Add a "parent menu" select field to the `MenuItemResource` form, restricted to items in the same location that are not themselves a child (enforcing one level of nesting) in `app/Filament/Resources/MenuItemResource.php`
- [X] T027 [US3] Update `resources/views/components/layout/menu.blade.php` to render nested children (dropdown) for items that have sub-items
- [X] T028 [US3] Add a "Tautan tidak valid" warning badge to the `MenuItemResource` table column when `resolveUrl()` returns null for an internal-type item, per [contracts/menu-rendering-contract.md](./contracts/menu-rendering-contract.md) §3, in `app/Filament/Resources/MenuItemResource.php`
- [X] T029 [US3] Replace the hardcoded footer columns in `resources/views/components/layout/footer.blade.php` with `<x-layout.menu location="footer" />`

**Checkpoint**: All three user stories work independently and together — multi-location, sub-menu, and broken-link handling are functional.

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Performance, permissions, and release hygiene affecting all stories

- [X] T030 ~~Add per-location menu caching with invalidation via Observers~~ — **superseded during implementation**: existing `App\Concerns\CachesPublicPages` (AMC-225) is deliberately TTL-only and controller-layer-only (never model-layer), so a dedicated Menu Builder cache/observer would both duplicate that mechanism and violate its documented design intent. `MenuItem::treeForLocation()` runs an indexed, uncached query instead (cheap at documented scale — dozens of items per location). See [research.md](./research.md) §6 for the full rationale.
- [X] T031 ~~Register Filament Shield permissions~~ — **superseded during implementation**: none of the existing content modules (Banner, CustomPage, Testimonial, etc.) have per-resource Shield policies; generating one for MenuItem/MenuLocation broke the established test convention (plain `User::factory()->create()` acting as admin) and was reverted to stay consistent with the rest of the codebase.
- [X] T032 Run `vendor/bin/pint --dirty --format agent` and fix any style violations
- [X] T033 Run `php artisan test --compact` for the full suite and fix regressions
- [X] T034 Execute the manual verification steps in [quickstart.md](./quickstart.md) end-to-end

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion — BLOCKS all user stories
- **User Story 1 (Phase 3)**: Depends on Foundational — no dependency on US2/US3
- **User Story 2 (Phase 4)**: Depends on Foundational; builds on the `MenuItemResource` table created in US1 (T015) but is independently testable once that table exists
- **User Story 3 (Phase 5)**: Depends on Foundational; builds on the `MenuItemResource` form created in US1 (T014) but is independently testable
- **Polish (Phase 6)**: Depends on all desired user stories being complete

### Within Each User Story

- Tests written before implementation, and MUST fail before the corresponding implementation task is done
- Models/schema (Foundational) before Filament resource before Blade rendering
- Story complete and checkpoint-verified before moving to the next priority

### Parallel Opportunities

- T001–T003 (Setup) can run in parallel
- T006, T007, T009, T010, T011 (Foundational, distinct files) can run in parallel after T004–T005 land
- T012–T013 (US1 tests) can run in parallel
- T022–T024 (US3 tests) can run in parallel
- T030–T031 (Polish) can run in parallel

---

## Parallel Example: Foundational Phase

```bash
Task: "Implement MenuLocation model in app/Models/MenuLocation.php"
Task: "Implement MenuItem model with resolveUrl() in app/Models/MenuItem.php"
Task: "Register morph map aliases in app/Providers/AppServiceProvider.php"
Task: "Create MenuSeeder in database/seeders/MenuSeeder.php"
Task: "Unit test MenuItem::resolveUrl() in tests/Unit/MenuItemUrlResolutionTest.php"
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL — blocks all stories)
3. Complete Phase 3: User Story 1
4. **STOP and VALIDATE**: admin adds a navbar item, sees it live on the public site
5. Demo if ready — this alone replaces the hardcoded navbar array

### Incremental Delivery

1. Setup + Foundational → schema and resolver ready
2. Add User Story 1 → navbar CRUD works → demo (MVP)
3. Add User Story 2 → drag & drop ordering works → demo
4. Add User Story 3 → multi-location, sub-menu, footer, broken-link badge → demo
5. Polish → caching, permissions, full test suite, quickstart validation

---

## Notes

- [P] tasks touch different files and have no unmet dependencies
- Constitution Principle IV requires the feature tests in this file (T011–T013, T018, T022–T024) before Menu Builder can be marked done in the tracker
- Constitution Principle V: no new Composer dependency is introduced — reorder uses Filament's built-in table `reorderable()`
- Commit after each task or logical group; run `vendor/bin/pint --dirty --format agent` before finalizing any PHP change
