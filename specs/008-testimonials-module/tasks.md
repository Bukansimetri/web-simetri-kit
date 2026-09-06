---
description: "Task list for Modul Testimonials"
---

# Tasks: Modul Testimonials

**Input**: Design documents from `/specs/008-testimonials-module/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/admin-panel-surface.md, quickstart.md

**Tests**: Included — feature tests required by plan.md (Testing section) and Constitution Principle IV (Module Test Coverage).

**Organization**: Tasks grouped by user story. Both stories are Priority P1.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: US1 or US2
- File paths are repository-relative

## Path Conventions

Laravel web app, single project. Source at repository root: `app/`, `database/`, `routes/`, `resources/`, `tests/`.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: No project init needed — existing Laravel app. Only baseline check.

- [x] T001 Confirm reference pattern still green: run `php artisan test --compact tests/Feature/Admin/ArticleResourceTest.php tests/Feature/Pages/AboutPageTest.php` (no code change; `ArticleResourceTest` covers the WebP upload + resource pattern, `AboutPageTest` covers the page this feature extends)

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: The `testimonials` table, `Testimonial` model, and factory — required by BOTH user stories.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [x] T002 Create migration `database/migrations/xxxx_create_testimonials_table.php` via `php artisan make:migration create_testimonials_table --no-interaction` with columns per data-model.md: `id`, `name` (string), `attribution` (string nullable), `content` (text), `rating` (unsignedTinyInteger), `photo_path` (string nullable), `order` (integer default 0), `is_active` (boolean default true), `timestamps`
- [x] T003 Create model `app/Models/Testimonial.php` via `php artisan make:model Testimonial --no-interaction`: `HasFactory`; `$fillable = ['name','attribution','content','rating','photo_path','order','is_active']`; `casts()` → `rating` integer, `order` integer, `is_active` boolean (pattern: `app/Models/JobOpening.php`)
- [x] T004 [P] Create `database/factories/TestimonialFactory.php` via `php artisan make:factory TestimonialFactory --no-interaction`: `name` fake name, `attribution` fake job+company string, `content` `fake()->paragraph()`, `rating` `fake()->numberBetween(1,5)`, `photo_path` null, `order` 0, `is_active` true; add an `inactive()` state setting `is_active` false (pattern: `database/factories/JobOpeningFactory.php`)
- [x] T005 Run `php artisan migrate` and verify `testimonials` schema via `database-schema`

**Checkpoint**: Model + table + factory ready — user stories can begin

---

## Phase 3: User Story 1 - Admin mengelola testimoni klien (Priority: P1) 🎯 MVP

**Goal**: Admin can create/edit/delete testimonials in the Filament panel — name, optional attribution, content, rating 1–5 (rejected outside range), optional photo (converted to WebP), order, active toggle, delete with confirmation.

**Independent Test**: Login as admin at `/admin/testimonials`, create 3 testimonials (one without photo, one inactive), edit one, delete one with confirmation; verify required-field errors (name/content/rating) and rating-range rejection; verify uploaded photo is stored as `.webp`.

### Tests for User Story 1 ⚠️ (write first, ensure they FAIL)

- [x] T006 [P] [US1] Create `tests/Feature/Admin/TestimonialResourceTest.php` via `php artisan make:test --phpunit Admin/TestimonialResourceTest` (pattern: `tests/Feature/Admin/ArticleResourceTest.php` for `Storage::fake` + Livewire form helpers) covering: admin can render list/create/edit; create persists record; `name`+`content`+`rating` required → `assertHasFormErrors`; rating value `6` (or `0`) rejected; `attribution` blank is accepted; testimonial without photo saves; uploaded photo saved with `.webp` extension via `ImageUploads::storeAsWebp`; `is_active` and `order` persist; delete removes record

### Implementation for User Story 1

- [x] T007 [US1] Generate resource: `php artisan make:filament-resource Testimonial --generate --no-interaction`, then rewrite `app/Filament/Resources/TestimonialResource.php` form per research.md/contracts: `TextInput::make('name')` required maxLength 255; `TextInput::make('attribution')` nullable maxLength 255 with helper "Perusahaan/jabatan — opsional"; `Textarea::make('content')` required rows 4; `Select::make('rating')->options([1=>'1',2=>'2',3=>'3',4=>'4',5=>'5'])->required()->rule('integer')->rule(Rule::in([1,2,3,4,5]))`; `FileUpload::make('photo_path')->image()->disk('public')->directory('testimonials')->saveUploadedFileUsing(fn ($file) => \App\Support\ImageUploads::storeAsWebp($file, 'testimonials'))` (NOT required); `TextInput::make('order')->numeric()->default(0)`; `Toggle::make('is_active')->default(true)->helperText('Testimoni nonaktif tidak tampil di halaman Tentang Kami.')` (pattern: `app/Filament/Resources/JobOpeningResource.php` + `ArticleResource` FileUpload)
- [x] T008 [US1] Configure `TestimonialResource` table + navigation: `navigationLabel = 'Testimoni'`, `navigationIcon = 'heroicon-o-chat-bubble-left-right'`; `->defaultSort('order')`; columns `name` (searchable), `attribution`, `rating`, `ToggleColumn::make('is_active')`, `TextColumn::make('order')->sortable()`; actions `EditAction` + `DeleteAction` (default confirmation); `bulkActions` `DeleteBulkAction`; no policy / `canAccess` override (open to all panel roles, FR-012)
- [x] T009 [US1] Verify generated pages `app/Filament/Resources/TestimonialResource/Pages/{ListTestimonials,CreateTestimonial,EditTestimonial}.php` match the `JobOpeningResource` page pattern (List has `CreateAction` header, Edit has `DeleteAction` header)
- [x] T010 [US1] Run `vendor/bin/pint --dirty --format agent`, then `php artisan test --compact --filter=TestimonialResourceTest` and fix until T006 passes

**Checkpoint**: US1 fully functional — admin CRUD + validation + WebP photo all verified

---

## Phase 4: User Story 2 - Pengunjung melihat testimoni di halaman Tentang Kami (Priority: P1)

**Goal**: `/tentang-kami` renders a testimonials section (after "Nilai-Nilai Kami", before CTA band) showing only active testimonials ordered by `order` then `id`, each with name, attribution, content, star rating, and photo-or-initials. When no active testimonial exists the section is not rendered at all and other About sections stay intact. Beranda is untouched.

**Independent Test**: With active + inactive testimonials from US1, open `/tentang-kami`: only active ones show, in order, with correct star counts; a photo-less one shows initials not a broken image; deactivate all → section gone, page still renders hero/visi/misi/nilai/CTA; `/` shows no testimonials.

### Tests for User Story 2 ⚠️ (write first, ensure they FAIL)

- [x] T011 [P] [US2] Create `tests/Feature/Pages/AboutPageTestimonialsTest.php` via `php artisan make:test --phpunit Pages/AboutPageTestimonialsTest` covering (FR-006/007/010/011/013): `/tentang-kami` shows an active testimonial's name + attribution + content; an inactive testimonial (`Testimonial::factory()->inactive()->create()`) does NOT appear; two active testimonials with `order` 2 and 1 render in order 1→2 (`assertSeeInOrder`); when zero active testimonials exist the page still returns 200 and still shows an existing About marker (e.g. "Nilai-Nilai Kami" / "Visi Kami") but not the testimonials section heading; `/` (home) does not show a created testimonial's name

### Implementation for User Story 2

- [x] T012 [US2] Update `app/Http/Controllers/Public/AboutController.php`: inject `$testimonials = \App\Models\Testimonial::query()->where('is_active', true)->orderBy('order')->orderBy('id')->get();` and `return view('pages.tentang-kami', ['testimonials' => $testimonials]);` (pattern: `app/Http/Controllers/Public/HomeController.php`)
- [x] T013 [US2] Create `resources/views/components/sections/testimonials.blade.php` (anonymous component, `@props(['testimonials'])`): wrap entire output in `@if($testimonials->isNotEmpty())`; section heading "Apa Kata Klien Kami"; responsive grid of cards, each showing photo (`Storage::disk('public')->url($t->photo_path)` when `$t->photo_path` && exists) else initials circle from `Str::of($t->name)->substr(0,1)->upper()`; star row via loop `1..5` using `material-symbols-outlined` `star` (filled when `$i <= $t->rating`) else `star_border`; `$t->content`, `$t->name`, `$t->attribution` (only if present). Match Tailwind/utility style of `resources/views/components/sections/why-choose.blade.php` and the About page palette
- [x] T014 [US2] Edit `resources/views/pages/tentang-kami.blade.php`: insert `<x-sections.testimonials :testimonials="$testimonials" />` on its own line immediately after the closing `</section>` of the `{{-- Nilai --}}` block and immediately before `<x-sections.cta-band />` (do not modify any existing section)
- [x] T015 [US2] Run `vendor/bin/pint --dirty --format agent`, then `php artisan test --compact --filter='AboutPageTestimonialsTest|AboutPageTest'` and fix until T011 and the existing `AboutPageTest` both pass

**Checkpoint**: US2 verified — section renders active testimonials on About page, empty-state hides it, other sections + home unaffected

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
- **User Story 2 (Phase 4)**: after Phase 2. The section test (T011) needs the `Testimonial` model + factory (Phase 2) but not the Filament resource — US2 is implementable in parallel with US1. Recommended order: US1 then US2.
- **Polish (Phase 5)**: after both stories

### Within Each User Story

- Tests before implementation (write, watch fail, then implement)
- Migration/model/factory (Phase 2) before resource / controller / view
- US2: controller (T012) and component (T013) before the page edit (T014); T014 before T015

### Parallel Opportunities

- T004 [P] alongside T002/T003
- T006 [P] and T011 [P] — different test files, can be written together
- US1 implementation (T007–T010) and US2 implementation (T012–T015) can proceed in parallel by different people once Phase 2 is done

---

## Parallel Example: test authoring

```bash
Task: "Create tests/Feature/Admin/TestimonialResourceTest.php"
Task: "Create tests/Feature/Pages/AboutPageTestimonialsTest.php"
```

---

## Implementation Strategy

### MVP (User Story 1 only)

1. Phase 1 Setup
2. Phase 2 Foundational (migration + model + factory + migrate)
3. Phase 3 User Story 1 (Filament CRUD)
4. STOP and VALIDATE with quickstart US1 steps
5. Deploy/demo — admin can author testimonials

### Incremental Delivery

1. Setup + Foundational → foundation ready
2. US1 → test independently → demo (admin CRUD works)
3. US2 → About page section → test → demo (social proof visible)
4. Polish → full suite + quickstart

---

## Notes

- No new dependencies (Principle V) — `ImageUploads::storeAsWebp` and the Filament resource pattern already exist.
- No module-level toggle, no moderation, no carousel requirement, rating is integer 1–5 (spec Assumptions).
- Only `tentang-kami.blade.php` gets one inserted line; all existing About sections and the home page stay untouched (FR-013).
- Commit after each phase or logical group.
