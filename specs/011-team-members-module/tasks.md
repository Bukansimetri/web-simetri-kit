---
description: "Task list for Modul Team Members"
---

# Tasks: Modul Team Members

**Input**: Design documents from `/specs/011-team-members-module/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/admin-panel-surface.md, quickstart.md

**Tests**: Included — feature tests required by plan.md (Testing section) and Constitution Principle IV (Module Test Coverage).

**Organization**: Tasks grouped by user story. Both stories are Priority P1.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: US1 or US2
- File paths are repository-relative

## Path Conventions

Laravel web app, single project. Source at repository root: `app/`, `database/`, `routes/`, `resources/`, `tests/`.

**Dependency note**: Builds on modul 008 (Testimonials) + 009 (Client Logos), both merged to `main` — `resources/views/pages/tentang-kami.blade.php` already contains `<x-sections.testimonials>` + `<x-sections.client-logos>`, and `app/Http/Controllers/Public/AboutController.php` already sends `$testimonials` + `$clientLogos`. `App\Support\ImageUploads::storeAsWebp` already supports the `maxWidth` param (added in modul 010).

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: No project init needed — existing Laravel app. Only baseline check.

- [x] T001 Confirm reference patterns still green: run `php artisan test --compact tests/Feature/Admin/TestimonialResourceTest.php tests/Feature/Pages/AboutPageTestimonialsTest.php tests/Feature/Pages/AboutPageClientLogosTest.php` (this module mirrors modul 008/009 exactly)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: The `team_members` table, `TeamMember` model, and factory — required by BOTH user stories.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T002 Create migration `database/migrations/xxxx_create_team_members_table.php` via `php artisan make:migration create_team_members_table --no-interaction` with columns per data-model.md: `id`, `name` (string), `position` (string), `photo_path` (string), `bio` (text), `linkedin_url` (string nullable), `order` (integer default 0), `is_active` (boolean default true), `timestamps`
- [x] T003 Create model `app/Models/TeamMember.php` via `php artisan make:model TeamMember --no-interaction`: `HasFactory`; `$fillable = ['name','position','photo_path','bio','linkedin_url','order','is_active']`; `casts()` → `order` integer, `is_active` boolean (pattern: `app/Models/Testimonial.php`)
- [x] T004 [P] Create `database/factories/TeamMemberFactory.php` via `php artisan make:factory TeamMemberFactory --no-interaction`: `name` `fake()->name()`, `position` `fake()->jobTitle()`, `photo_path` `'team/'.fake()->uuid().'.webp'`, `bio` `fake()->paragraph()`, `linkedin_url` null, `order` 0, `is_active` true; add `inactive()` state (pattern: `database/factories/TestimonialFactory.php`)
- [x] T005 Run `php artisan migrate` and verify `team_members` schema via `database-schema`

**Checkpoint**: Model + table + factory ready — user stories can begin

---

## Phase 3: User Story 1 - Admin mengelola anggota tim (Priority: P1) 🎯 MVP

**Goal**: Admin can create/edit/delete team members — name, position, required photo (resized ≤800px + WebP), required bio, optional `linkedin_url` (blank OK; else http/https), order, active toggle, delete with confirmation.

**Independent Test**: Login as admin at `/admin/team-members`, create 3 members (one without LinkedIn, one inactive) each with a photo (one >800px wide → stored 800px WebP), edit one, delete one with confirmation; verify required-field errors (name/position/photo/bio), blank LinkedIn accepted, invalid LinkedIn rejected.

### Tests for User Story 1 ⚠️ (write first, ensure they FAIL)

- [x] T006 [P] [US1] Create `tests/Feature/Admin/TeamMemberResourceTest.php` via `php artisan make:test --phpunit Admin/TeamMemberResourceTest` (pattern: `tests/Feature/Admin/TestimonialResourceTest.php` for `Storage::fake` + Livewire helpers) covering: render list/create/edit; create with a photo persists record; `name`+`position`+`photo_path`+`bio` required → `assertHasFormErrors`; blank `linkedin_url` accepted; `linkedin_url` = `linkedin.com/in/x` (no scheme) rejected, `https://linkedin.com/in/x` accepted; uploaded photo saved with `.webp` extension; a 2000px-wide uploaded photo is stored at width 800 (read back dimensions via `getimagesizefromstring`); `is_active` and `order` persist; delete removes record

### Implementation for User Story 1

- [x] T007 [US1] Generate resource: `php artisan make:filament-resource TeamMember --generate --no-interaction`, then rewrite `app/Filament/Resources/TeamMemberResource.php` form per research.md/contracts: `TextInput::make('name')` required maxLength 255; `TextInput::make('position')` required maxLength 255; `FileUpload::make('photo_path')->image()->required()->disk('public')->directory('team')->acceptedFileTypes(['image/png','image/jpeg','image/webp'])->helperText('Wajib. Rekomendasi 800×800px (potret). Gambar besar otomatis dikecilkan ke lebar 800px & dikonversi WebP.')->saveUploadedFileUsing(fn ($file) => \App\Support\ImageUploads::storeAsWebp($file, 'team', maxWidth: 800))`; `Textarea::make('bio')->required()->rows(3)`; `TextInput::make('linkedin_url')->url()->maxLength(255)->rule('starts_with:http://,https://')->helperText('Opsional. URL profil LinkedIn lengkap (https://...).')`; `TextInput::make('order')->numeric()->default(0)`; `Toggle::make('is_active')->default(true)->helperText('Anggota nonaktif tidak tampil di halaman Tentang Kami.')` (pattern: `app/Filament/Resources/TestimonialResource.php`)
- [x] T008 [US1] Configure `TeamMemberResource` table + navigation: `navigationLabel = 'Tim'`, `navigationIcon = 'heroicon-o-user-group'`; `->defaultSort('order')`; columns `ImageColumn::make('photo_path')->disk('public')->circular()`, `name` (searchable), `position`, `linkedin_url` (`->placeholder('—')`), `order` (sortable), `ToggleColumn::make('is_active')`; actions `EditAction` + `DeleteAction` (default confirmation); `bulkActions` `DeleteBulkAction`; no policy / `canAccess` override (FR-012)
- [x] T009 [US1] Verify generated pages `app/Filament/Resources/TeamMemberResource/Pages/*` match the `TestimonialResource` page pattern (List has `CreateAction` header, Edit has `DeleteAction` header)
- [x] T010 [US1] Run `vendor/bin/pint --dirty --format agent`, then `php artisan test --compact --filter=TeamMemberResourceTest` and fix until T006 passes

**Checkpoint**: US1 fully functional — admin CRUD + required photo resize/WebP + validation verified

---

## Phase 4: User Story 2 - Pengunjung melihat profil tim di halaman Tentang Kami (Priority: P1)

**Goal**: `/tentang-kami` renders a "Tim Kami" section after "Nilai-Nilai Kami" and before the testimonials section, showing only active members ordered by `order` then `id`, each with photo, name, position, bio, and (when set) a LinkedIn link opening in a new tab. When no active member exists the section is not rendered and all other About sections stay intact. Home page is untouched.

**Independent Test**: With active + inactive members from US1, open `/tentang-kami`: only active ones show, in order; a member with `linkedin_url` has an `href` + `target="_blank"`; a member without it has no LinkedIn anchor; deactivate all → section gone, page still renders values + testimonials + client logos + CTA; `/` shows no team section.

### Tests for User Story 2 ⚠️ (write first, ensure they FAIL)

- [x] T011 [P] [US2] Create `tests/Feature/Pages/AboutPageTeamMembersTest.php` via `php artisan make:test --phpunit Pages/AboutPageTeamMembersTest` (pattern: `tests/Feature/Pages/AboutPageTestimonialsTest.php`) covering (FR-006/007/010/011/013): `/tentang-kami` shows an active member's name + position + bio; an inactive member (`->inactive()`) does NOT appear; two active members with `order` 2 and 1 render in order 1→2 (`assertSeeInOrder`); a member with `linkedin_url = 'https://linkedin.com/in/x'` produces an `href="https://linkedin.com/in/x"` with `target="_blank"`; a member with null `linkedin_url` does not add that anchor (`assertDontSee 'rel="noopener noreferrer nofollow"'` when it is the only member and no other section uses it — or assert the specific href absent); when zero active members exist the page still returns 200 and still shows an existing About marker (e.g. "Nilai-Nilai Kami" and "Apa Kata Klien Kami") but not the "Tim Kami" heading; `/` (home) does not show a created member's name

### Implementation for User Story 2

- [x] T012 [US2] Update `app/Http/Controllers/Public/AboutController.php`: add `$teamMembers = \App\Models\TeamMember::query()->where('is_active', true)->orderBy('order')->orderBy('id')->get();` and include `'teamMembers' => $teamMembers` in the existing `view('pages.tentang-kami', [...])` array (keep existing `$testimonials` + `$clientLogos` keys)
- [x] T013 [US2] Create `resources/views/components/sections/team-members.blade.php` (anonymous component, `@props(['members'])`): wrap entire output in `@if($members->isNotEmpty())`; heading "Tim Kami"; responsive grid (`grid-cols-2 md:grid-cols-3 lg:grid-cols-4`), each card: compute `$hasPhoto = $member->photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($member->photo_path)`; render `<img src="{{ Storage::disk('public')->url($member->photo_path) }}" alt="{{ $member->name }}" class="aspect-square object-cover rounded-lg w-full">` when `$hasPhoto` else an initials square from `Str::of($member->name)->substr(0,1)->upper()`; `$member->name`, `$member->position`, `$member->bio`; when `$member->linkedin_url` present render a LinkedIn icon link `<a href="{{ $member->linkedin_url }}" target="_blank" rel="noopener noreferrer nofollow">`. Match utility/palette style of `resources/views/components/sections/testimonials.blade.php`
- [x] T014 [US2] Edit `resources/views/pages/tentang-kami.blade.php`: insert `<x-sections.team-members :members="$teamMembers" />` on its own line immediately after the closing `</section>` of the `{{-- Nilai --}}` block and immediately before `<x-sections.testimonials :testimonials="$testimonials" />` (do not modify any existing section)
- [x] T015 [US2] Run `vendor/bin/pint --dirty --format agent`, then `php artisan test --compact --filter='AboutPageTeamMembersTest|AboutPageTestimonialsTest|AboutPageClientLogosTest|AboutPageTest'` and fix until T011 and the existing About page tests all pass

**Checkpoint**: US2 verified — section renders active members after "Nilai-Nilai Kami", empty-state hides it, testimonials + client logos + other sections + home unaffected

---

## Phase 5: Polish & Cross-Cutting Concerns

- [ ] T016 [P] Run full quickstart.md manual verification (US1 steps 1-9, US2 steps 1-7) — MANUAL, pending user (needs `npm run build` + browser)
- [x] T017 Run the full suite: `php artisan test --compact` and confirm no regressions
- [x] T018 Final `vendor/bin/pint --dirty --format agent` pass on all changed PHP files

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: none
- **Foundational (Phase 2)**: after Setup — BLOCKS both user stories
- **User Story 1 (Phase 3)**: after Phase 2
- **User Story 2 (Phase 4)**: after Phase 2. The section test (T011) needs the `TeamMember` model + factory (Phase 2) but not the Filament resource — US2 is implementable in parallel with US1. Recommended order: US1 then US2.
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

## Parallel Example: test authoring after Phase 2

```bash
Task: "Create tests/Feature/Admin/TeamMemberResourceTest.php"
Task: "Create tests/Feature/Pages/AboutPageTeamMembersTest.php"
```

---

## Implementation Strategy

### MVP (User Story 1 only)

1. Phase 1 Setup
2. Phase 2 Foundational (migration + model + factory + migrate)
3. Phase 3 User Story 1 (Filament CRUD)
4. STOP and VALIDATE with quickstart US1 steps
5. Deploy/demo — admin can manage team members

### Incremental Delivery

1. Setup + Foundational → foundation ready
2. US1 → test independently → demo (admin CRUD works)
3. US2 → About page "Tim Kami" section → test → demo (team visible)
4. Polish → full suite + quickstart

---

## Notes

- No new dependencies (Principle V) — `ImageUploads::storeAsWebp(maxWidth:)` already exists (modul 010); resource pattern from modul 008.
- Photo is REQUIRED here (unlike Testimonials); `linkedin_url` is optional and blank must save (spec clarification Q1 + explicit user note).
- No module toggle, no member grouping/departments, no per-member detail page (spec Assumptions).
- Only `tentang-kami.blade.php` gets one inserted line (after the `{{-- Nilai --}}` section, before the testimonials line); all other About sections and the home page stay untouched (FR-013).
- Commit after each phase or logical group.
