# Tasks: Career/Lowongan Kerja CRUD Admin + Toggle Modul

**Input**: Design documents from `/specs/006-career-crud-admin/`
**Prerequisites**: [plan.md](./plan.md), [spec.md](./spec.md), [research.md](./research.md), [data-model.md](./data-model.md), [contracts/admin-panel-surface.md](./contracts/admin-panel-surface.md), [quickstart.md](./quickstart.md)

**Tests**: Feature tests are included per Constitution Principle IV (Module Test Coverage — mandatory feature tests), following the pattern established in 003/005 (Livewire/Filament resource tests).

**Organization**: Tasks are grouped by user story (US1, US2 — both P1, from spec.md) to enable independent implementation and testing.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Maps task to US1/US2
- File paths are exact, relative to repository root

---

## Phase 1: Setup

**Purpose**: No new dependencies or scaffolding needed — `spatie/laravel-settings` and Filament are already installed and in use (`BrandSettings`/`BrandSettingsPage`). This phase is a no-op; proceed directly to Foundational.

*(No tasks — nothing to set up beyond what already exists.)*

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: The employment-type constant and the module-toggle setting that both user stories depend on.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete.

- [X] T001 [P] Add `JobOpening::EMPLOYMENT_TYPES` constant (array `value => label`: `'full-time' => 'Full-time'`, `'part-time' => 'Part-time'`, `'contract' => 'Kontrak'`, `'internship' => 'Magang'`) to `app/Models/JobOpening.php` (data-model.md, research.md §1) — pattern from `BrandSettings::FONT_OPTIONS`
- [X] T002 [P] Create settings migration `database/settings/xxxx_add_career_module_enabled_to_brand_settings.php` (extends `Spatie\LaravelSettings\Migrations\SettingsMigration`) — `$this->migrator->add('brand.career_module_enabled', true)` (research.md §2)
- [X] T003 Add `public bool $career_module_enabled;` property to `app/Settings/BrandSettings.php` (depends on T002)

**Checkpoint**: `php artisan migrate` runs clean; `app(BrandSettings::class)->career_module_enabled` returns `true` by default. User story implementation can now begin.

---

## Phase 3: User Story 1 - Admin menulis & mengedit lowongan kerja (Priority: P1) 🎯 MVP

**Goal**: Admin can fully CRUD `JobOpening` records from the admin panel — create, edit, delete, and toggle `is_active` per lowongan — with `employment_type` restricted to the fixed list and required-field validation.

**Independent Test**: Login as admin, write a new job opening with all required fields and `is_active = true`, verify it appears on `/karir`; edit it, verify the change appears; toggle it inactive, verify it disappears from `/karir` but remains in the admin list.

### Tests for User Story 1 ⚠️

- [ ] T004 [P] [US1] Feature test in `tests/Feature/Admin/JobOpeningResourceTest.php` — create job opening with required fields succeeds and appears on `/karir`; edit updates the record and public page reflects it; missing required field is rejected with a field-level error; `employment_type` outside `JobOpening::EMPLOYMENT_TYPES` is rejected; toggling `is_active` to false hides it from `/karir` without deleting it; delete removes it from both admin and `/karir`

### Implementation for User Story 1

- [ ] T005 [US1] Generate resource: `php artisan make:filament-resource JobOpening --no-interaction` (creates `app/Filament/Resources/JobOpeningResource.php` + `Pages/{ListJobOpenings,CreateJobOpening,EditJobOpening}.php`)
- [ ] T006 [US1] Implement `form()` in `app/Filament/Resources/JobOpeningResource.php` — `TextInput::make('title')->required()`, `TextInput::make('location')->required()`, `Select::make('employment_type')->options(JobOpening::EMPLOYMENT_TYPES)->required()`, `Textarea::make('description')->required()`, `Toggle::make('is_active')->default(true)`
- [ ] T007 [US1] Implement `table()` in `app/Filament/Resources/JobOpeningResource.php` — columns `title`, `location`, `employment_type` (formatted via `EMPLOYMENT_TYPES` label), `is_active` (as a `ToggleColumn` for quick in-table toggling per contracts/admin-panel-surface.md §1), row actions Edit/Delete with confirmation (FR-005)

**Checkpoint**: User Story 1 fully functional and testable independently (`php artisan test --compact tests/Feature/Admin/JobOpeningResourceTest.php`) — this is the deliverable MVP.

---

## Phase 4: User Story 2 - Admin menonaktifkan seluruh modul Karir per instalasi klien (Priority: P1)

**Goal**: A single global toggle (in the existing Brand Settings page) that, when off, makes `/karir` 404 and removes the "Karir" footer link — without deleting job opening data or restricting admin CRUD access.

**Independent Test**: Turn the toggle off in Brand Settings, verify `/karir` 404s and the footer link disappears on any public page, while `/admin/job-openings` remains fully usable; turn it back on, verify `/karir` and the footer link return with all data intact.

### Tests for User Story 2 ⚠️

- [ ] T008 [P] [US2] Feature test in `tests/Feature/Public/CareerModuleToggleTest.php` — with `career_module_enabled = true` (default), `/karir` is 200 and footer contains "Karir" link; with it set `false`, `/karir` is 404 and the homepage (or any public page) does NOT contain a "Karir" footer link; toggling off then back on preserves existing `JobOpening` records (still visible once re-enabled)
- [ ] T009 [P] [US2] Feature test in `tests/Feature/Admin/JobOpeningResourceTest.php` (extend, same file as T004) — `JobOpeningResource` list/create/edit remain fully accessible and functional even when `career_module_enabled = false` (FR-013)

### Implementation for User Story 2

- [ ] T010 [US2] Add `Toggle::make('career_module_enabled')` to `form()` in `app/Filament/Pages/BrandSettingsPage.php`, under a new or existing Section (e.g. "Modul Opsional"), plus wire it in `mount()` (read from settings) and `save()` (write back to settings) alongside the existing fields
- [ ] T011 [US2] Update `app/Http/Controllers/Public/CareerController.php` — `abort_unless(app(BrandSettings::class)->career_module_enabled, 404)` at the top of `__invoke()`, before querying `JobOpening` (research.md §3)
- [ ] T012 [US2] Update `resources/views/components/layout/footer.blade.php` — conditionally omit the "Karir" entry from the `Perusahaan` column when `app(\App\Settings\BrandSettings::class)->career_module_enabled` is `false`

**Checkpoint**: Both user stories independently functional — CRUD works regardless of toggle state; toggle state correctly gates only the public surface.

---

## Phase 5: Polish & Cross-Cutting Concerns

**Purpose**: Formatting, full regression check, and manual acceptance pass.

- [ ] T013 [P] Run `vendor/bin/pint --dirty --format agent` and fix any reported style issues
- [ ] T014 Run `php artisan test --compact` (full suite) and confirm no regressions in existing Product/Article/Contact/public-page tests
- [ ] T015 Walk through [quickstart.md](./quickstart.md) US1–US2 steps manually against a fresh `migrate:fresh --seed` to confirm end-to-end behavior

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: None — no-op, nothing to install
- **Foundational (Phase 2)**: No dependency on Setup — BLOCKS both user stories
- **User Stories (Phase 3–4)**: Both depend on Foundational (Phase 2) completion
  - US1 has no dependency on US2 (CRUD works standalone)
  - US2 has no dependency on US1's implementation, but T009 (admin-still-works-when-disabled test) is most naturally written alongside US1's resource, so it's listed as extending the same test file — write it after T005–T007 exist
- **Polish (Phase 5)**: Depends on both user stories being complete

### Within Each User Story

- Tests MUST be written and FAIL before implementation (TDD, per project convention)
- Foundational constants/settings before any Filament Resource or controller work
- Story complete before moving to next priority

### Parallel Opportunities

- T001, T002 (Phase 2) can run in parallel — different files; T003 depends on T002 (property needs the settings key to exist)
- T004 (US1 test) and T008 (US2 test) can be written in parallel — different files, independent scenarios
- T010, T011, T012 (US2 implementation) touch three different files and have no interdependency — can be done in parallel once Foundational is complete

---

## Parallel Example: Phase 2 (Foundational)

```bash
# These can be worked on together (different files):
Task: "Add JobOpening::EMPLOYMENT_TYPES constant"                          # T001
Task: "Create settings migration for career_module_enabled"                # T002
```

---

## Implementation Strategy

### MVP First (User Story 1 Only)

1. Complete Phase 2: Foundational (CRITICAL — blocks all stories)
2. Complete Phase 3: User Story 1 (CRUD lowongan) — **STOP and VALIDATE** independently
3. This is the deliverable MVP: admin can manage job openings end-to-end

### Incremental Delivery

1. Foundational → foundation ready
2. US1 (CRUD lowongan) → test independently → MVP deployable
3. US2 (toggle modul) → test independently → closes the per-client optionality requirement
4. Polish → full regression + manual quickstart pass

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- No schema migration for `job_openings` in this feature — all required columns already exist (data-model.md)
- Commit after each phase/story checkpoint, run `vendor/bin/pint --dirty --format agent` and the relevant `php artisan test --compact --filter=...` before marking tasks `[X]`
- Avoid: vague tasks, same-file conflicts without sequencing, cross-story dependencies that break independent testability
