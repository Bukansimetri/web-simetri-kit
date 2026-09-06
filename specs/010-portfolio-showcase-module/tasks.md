---
description: "Task list for Modul Portfolio / Project Showcase"
---

# Tasks: Modul Portfolio / Project Showcase

**Input**: Design documents from `/specs/010-portfolio-showcase-module/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/admin-panel-surface.md, quickstart.md

**Tests**: Included — feature tests required by plan.md (Testing section) and Constitution Principle IV (Module Test Coverage).

**Organization**: Tasks grouped by user story. US2 & US3 are P1; US1 is P2 but its data (categories) is a prerequisite for US2/US3, so US1 is implemented within Phase 2 foundations + its own polish.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: US1 / US2 / US3
- File paths are repository-relative

## Path Conventions

Laravel web app, single project. Source at repository root: `app/`, `database/`, `routes/`, `resources/`, `tests/`.

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: No project init needed — existing Laravel app. Baseline check + shared image helper change.

- [x] T001 Confirm reference patterns still green: run `php artisan test --compact tests/Feature/Admin/ArticleResourceTest.php tests/Feature/Admin/ArticleCategoryResourceTest.php tests/Feature/Pages/ProductPageTest.php` (Portfolio mirrors ArticleResource + ArticleCategoryResource + Product gallery)
- [x] T002 Extend `app/Support/ImageUploads.php`: add optional param `?int $maxWidth = null` to `storeAsWebp(UploadedFile $file, string $directory, string $disk = 'public', int $quality = 80, ?int $maxWidth = null)`. When `$maxWidth` is set and source width `> $maxWidth`: compute proportional height, create `imagecreatetruecolor($maxWidth, $newHeight)`, `imagealphablending(false)` + `imagesavealpha(true)` on the target, `imagecopyresampled(...)`, then `imagewebp` the resized canvas. When `$maxWidth` is null or width `<= $maxWidth`: unchanged behavior (no upscale). Keep existing callers (`ArticleResource`) working.
- [x] T003 [P] Add a unit test `tests/Unit/ImageUploadsTest.php` via `php artisan make:test --phpunit --unit ImageUploadsTest`: with `Storage::fake('public')`, `storeAsWebp` of a 2000×1000 fake image with `maxWidth: 1200` produces a stored `.webp` whose width is 1200 and height 600 (read back via `getimagesizefromstring(Storage::get($path))`); a 800×600 image with `maxWidth: 1200` stays 800×600; no `maxWidth` keeps original dimensions

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Both tables, both models, both factories, and the category CRUD (US1) — everything US2 & US3 depend on.

**⚠️ CRITICAL**: No US2/US3 work can begin until this phase is complete

- [x] T004 [P] Create migration `database/migrations/xxxx_create_portfolio_categories_table.php` via `php artisan make:migration create_portfolio_categories_table --no-interaction`: `id`, `name` (string), `slug` (string unique), `order` (integer default 0), `timestamps`
- [x] T005 Create migration `database/migrations/xxxx_create_portfolio_projects_table.php` via `php artisan make:migration create_portfolio_projects_table --no-interaction` (timestamp AFTER T004): `id`, `foreignId('portfolio_category_id')->constrained()`, `title` (string), `slug` (string unique), `description` (longText), `images` (json), `client_name` (string nullable), `project_url` (string nullable), `completed_at` (date nullable), `order` (integer default 0), `is_active` (boolean default true), `timestamps`
- [x] T006 [P] Create model `app/Models/PortfolioCategory.php` via `php artisan make:model PortfolioCategory --no-interaction`: `HasFactory`; `$fillable = ['name','slug','order']`; `casts()` → `order` integer; `getRouteKeyName(): 'slug'`; `portfolioProjects(): HasMany` (pattern: `app/Models/ArticleCategory.php`)
- [x] T007 Create model `app/Models/PortfolioProject.php` via `php artisan make:model PortfolioProject --no-interaction`: `HasFactory`; `$fillable` per data-model.md; `casts()` → `images` array, `completed_at` date, `order` integer, `is_active` boolean; `$attributes = ['images' => '[]']`; `getRouteKeyName(): 'slug'`; `portfolioCategory(): BelongsTo`; `imageUrls(): array` + `coverImageUrl(): ?string` (pattern: `app/Models/Product.php`)
- [x] T008 [P] Create `database/factories/PortfolioCategoryFactory.php` via `php artisan make:factory PortfolioCategoryFactory --no-interaction`: `name` `fake()->unique()->words(2, true)`, `slug` `Str::slug($name)`, `order` 0
- [x] T009 [P] Create `database/factories/PortfolioProjectFactory.php` via `php artisan make:factory PortfolioProjectFactory --no-interaction`: `portfolio_category_id` `PortfolioCategory::factory()`, `title` `fake()->sentence(4)`, `slug` `Str::slug($title).'-'.fake()->unique()->numberBetween(1,99999)`, `description` `'<p>'.fake()->paragraph().'</p>'`, `images` `['portfolio/'.fake()->uuid().'.webp']`, `client_name` null, `project_url` null, `completed_at` null, `order` 0, `is_active` true; add `inactive()` state
- [x] T010 Run `php artisan migrate` and verify both tables + FK via `database-schema`

### User Story 1 — Kategori Portfolio CRUD (Priority: P2)

**Goal**: Admin can create/edit/delete portfolio categories (name unique, auto slug, order); a category still referenced by ≥1 project cannot be deleted.

**Independent Test**: `/admin/portfolio-categories` — create 3 categories, rename one, duplicate-name rejected, delete an unused one; attach a project to a category then try to delete that category → blocked with a notification.

- [x] T011 [P] [US1] Create `tests/Feature/Admin/PortfolioCategoryResourceTest.php` via `php artisan make:test --phpunit Admin/PortfolioCategoryResourceTest` (pattern: `tests/Feature/Admin/ArticleCategoryResourceTest.php`) covering: render list/create/edit; create persists + auto slug; duplicate `name` rejected (`assertHasFormErrors(['name'])`); delete an unused category works; deleting a category with an existing `PortfolioProject` is prevented (record still in DB + `assertDatabaseHas`)
- [x] T012 [US1] Generate + configure `php artisan make:filament-resource PortfolioCategory --generate --no-interaction`, then edit `app/Filament/Resources/PortfolioCategoryResource.php`: nav label "Kategori Portfolio", nav icon `heroicon-o-rectangle-group`, nav group "Portfolio"; form `TextInput::make('name')` required + `unique(ignoreRecord: true)` + `live(onBlur: true)` afterStateUpdated → set `slug` via `Str::slug()` when slug empty/matches old; `TextInput::make('slug')` required + `unique(ignoreRecord: true)`; `TextInput::make('order')->numeric()->default(0)->required()`; table columns `name`, `slug`, `order` (sortable), `portfolio_projects_count`; `defaultSort('order')`; `DeleteAction` with `->before()` guard that cancels + sends a `Notification::danger` when `$record->portfolioProjects()->exists()` (pattern: `app/Filament/Resources/ArticleCategoryResource.php`)
- [x] T013 [US1] Verify generated pages `app/Filament/Resources/PortfolioCategoryResource/Pages/*` match the `ArticleCategoryResource` page pattern
- [x] T014 [US1] Run `vendor/bin/pint --dirty --format agent`, then `php artisan test --compact --filter=PortfolioCategoryResourceTest` and fix until T011 passes

**Checkpoint**: Foundations + category CRUD ready — US2 & US3 can begin

---

## Phase 3: User Story 2 - Admin mengelola proyek portfolio (Priority: P1) 🎯 MVP

**Goal**: Admin can create/edit/delete portfolio projects — title, auto/override slug (unique), category, rich-text description, multi-image reorderable gallery (min 1, each resized ≤1200px + WebP), optional project_url (http/https), client_name, completed_at, order, active toggle.

**Independent Test**: `/admin/portfolio-projects` — create 2 projects in different categories with 2–3 gallery images each (one image >1200px wide → stored 1200px WebP), one project without url/client/date; verify slug generation, required-field errors (title/category/description/≥1 image), invalid-url rejection, gallery reorder persists; delete a project → its detail URL 404s.

### Tests for User Story 2 ⚠️ (write first, ensure they FAIL)

- [x] T015 [P] [US2] Create `tests/Feature/Admin/PortfolioProjectResourceTest.php` via `php artisan make:test --phpunit Admin/PortfolioProjectResourceTest` (pattern: `tests/Feature/Admin/ArticleResourceTest.php` for `Storage::fake` + Livewire form helpers) covering: render list/create/edit; create with `PortfolioCategory::factory()` + 2 uploaded images persists, slug auto-generates; manual slug override respected; duplicate slug rejected; missing `title` / `portfolio_category_id` / `description` rejected (`assertHasFormErrors`); zero gallery images rejected; `project_url` = `contoh.com` rejected, `https://x.test` accepted, blank accepted; each stored gallery path ends `.webp`; an uploaded 2000px-wide image is stored at width 1200 (read back dimensions); `client_name`/`completed_at` optional (blank saves); delete removes record

### Implementation for User Story 2

- [x] T016 [US2] Generate resource: `php artisan make:filament-resource PortfolioProject --generate --no-interaction`, then rewrite `app/Filament/Resources/PortfolioProjectResource.php` form (Sections, pattern `ArticleResource` + `ProductResource`): `TextInput::make('title')` required + `live(onBlur:true)` + `afterStateUpdated` slug auto (Str::slug when empty/matches old); `TextInput::make('slug')` required + `unique(ignoreRecord: true)`; `Select::make('portfolio_category_id')->relationship('portfolioCategory','name')->options(fn () => \App\Models\PortfolioCategory::orderBy('order')->pluck('name','id'))->required()`; `RichEditor::make('description')->required()`; `FileUpload::make('images')->image()->multiple()->reorderable()->appendFiles()->minFiles(1)->required()->disk('public')->directory('portfolio')->helperText('Rekomendasi 1200×900px (rasio bebas). Gambar besar otomatis dikecilkan ke lebar 1200px & dikonversi WebP. Gambar pertama = sampul.')->saveUploadedFileUsing(fn ($file) => \App\Support\ImageUploads::storeAsWebp($file, 'portfolio', maxWidth: 1200))`; `TextInput::make('project_url')->url()->maxLength(255)->rule('starts_with:http://,https://')`; `TextInput::make('client_name')->maxLength(255)`; `DatePicker::make('completed_at')`; `TextInput::make('order')->numeric()->default(0)->required()`; `Toggle::make('is_active')->default(true)`
- [x] T017 [US2] Configure `PortfolioProjectResource` table + navigation: nav label "Portfolio", nav icon `heroicon-o-briefcase` (or `heroicon-o-photo`), nav group "Portfolio"; `defaultSort('order')`; columns `ImageColumn::make('images')->disk('public')->limit(1)` (or first-image accessor), `title` (searchable), `portfolioCategory.name` (searchable), `order` (sortable), `ToggleColumn::make('is_active')`; actions `EditAction` + `DeleteAction` (confirm); `bulkActions` `DeleteBulkAction`; no policy override (FR-015)
- [x] T018 [US2] Verify generated pages `app/Filament/Resources/PortfolioProjectResource/Pages/*` match the `ArticleResource` page pattern (no custom slug mutation needed — handled in form)
- [x] T019 [US2] Run `vendor/bin/pint --dirty --format agent`, then `php artisan test --compact --filter=PortfolioProjectResourceTest` and fix until T015 passes

**Checkpoint**: US2 fully functional — project CRUD + gallery resize/WebP + validation verified

---

## Phase 4: User Story 3 - Pengunjung menjelajah portfolio (Priority: P1)

**Goal**: `/portfolio` lists active projects (cover, title, category) with a shareable `?kategori={slug}` filter and an empty-state (not 404); `/portfolio/{slug}` shows an active project's gallery + description + optional metadata (no empty labels), 404 for inactive/unknown.

**Independent Test**: With active + inactive projects across 2+ categories, open `/portfolio`: only active shown, ordered; pick a category chip → URL gains `?kategori=slug`, list narrows, reopening URL reproduces it; unknown category slug → all shown, no error; open a project → gallery in order + description + filled metadata (link opens new tab); a project with no optional fields → no empty labels; inactive/unknown slug → 404; deactivate all → `/portfolio` still 200 with "Belum ada proyek".

### Tests for User Story 3 ⚠️ (write first, ensure they FAIL)

- [x] T020 [P] [US3] Create `tests/Feature/Pages/PortfolioPageTest.php` via `php artisan make:test --phpunit Pages/PortfolioPageTest` (pattern: `tests/Feature/Pages/ArticlePageTest.php`) covering: `/portfolio` shows an active project's title + category, hides an inactive one (`->inactive()`); two active projects `order` 2 & 1 render 1→2 (`assertSeeInOrder`); `/portfolio?kategori={slug}` shows only that category's active projects and 200; `/portfolio?kategori=ngawur` returns 200 and shows all active; `/portfolio` with zero active projects returns 200 + sees "Belum ada proyek" (not 404); `/portfolio/{slug}` of active project → 200 shows title + description + (when set) client_name, formatted completed_at, an `href` to project_url with `target="_blank"`; a project with null optional fields → 200 and `assertDontSee` a client/date label string; `/portfolio/{inactive-slug}` and `/portfolio/ngawur` → 404
- [x] T021 [US3] Add routes in `routes/web.php`: `Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio.index');` and `Route::get('/portfolio/{portfolioProject:slug}', [PortfolioController::class, 'show'])->name('portfolio.show');` (place near `/artikel` routes; add `use App\Http\Controllers\Public\PortfolioController;`)
- [x] T022 [US3] Create `app/Http/Controllers/Public/PortfolioController.php` via `php artisan make:controller Public/PortfolioController --no-interaction` (pattern: `app/Http/Controllers/Public/ArticleController.php`): `index(Request $request): View` — `$categories = PortfolioCategory::orderBy('order')->get()`; `$activeSlug = $request->query('kategori')`; `$projects = PortfolioProject::query()->where('is_active', true)->with('portfolioCategory')->when($categories->firstWhere('slug', $activeSlug), fn ($q, $cat) => $q->where('portfolio_category_id', $cat->id))->orderBy('order')->orderBy('id')->get();` → `view('pages.portfolio.index', compact('projects','categories','activeSlug'))`. `show(PortfolioProject $portfolioProject): View` — `abort_unless($portfolioProject->is_active, 404); $portfolioProject->load('portfolioCategory');` → `view('pages.portfolio.show', ['project' => $portfolioProject])`
- [x] T023 [US3] Create `resources/views/pages/portfolio/index.blade.php` (pattern: `resources/views/pages/artikel/index.blade.php`): breadcrumb + heading; category filter row — a "Semua" link to `url('/portfolio')` + one link per `$categories` to `url('/portfolio').'?kategori='.$cat->slug`, mark the one matching `$activeSlug` as active; `@forelse ($projects as $project)` grid card: cover via `$project->coverImageUrl()` (or neutral placeholder), `$project->title`, `$project->portfolioCategory->name`, link to `route('portfolio.show', $project)`; `@empty` → "Belum ada proyek" empty-state
- [x] T024 [US3] Create `resources/views/pages/portfolio/show.blade.php` (pattern: `resources/views/pages/produk/show.blade.php` for gallery + `artikel/show` for prose): `@section('title', $project->title.' — '.$appName)`; breadcrumb `Portfolio / {category}`; title; gallery — loop `$project->imageUrls()` in order (first larger); optional metadata block — `@if($project->client_name)`, `@if($project->completed_at)` (`$project->completed_at->translatedFormat('F Y')`), `@if($project->project_url)` → `<a href="{{ $project->project_url }}" target="_blank" rel="noopener noreferrer nofollow">`; `{!! $project->description !!}` in a prose wrapper
- [x] T025 [US3] Run `vendor/bin/pint --dirty --format agent`, then `php artisan test --compact --filter=PortfolioPageTest` and fix until T020 passes

**Checkpoint**: US3 verified — listing + filter + empty-state + detail + 404 all working; AMC-218 "portfolio" part satisfied

---

## Phase 5: Polish & Cross-Cutting Concerns

- [ ] T026 [P] Run full quickstart.md manual verification (US1 steps 1-5, US2 steps 1-8, US3 steps 1-8) — MANUAL, pending user (needs `npm run build` + browser)
- [x] T027 Run the full suite: `php artisan test --compact` and confirm no regressions (esp. `ArticleResourceTest` — shares `ImageUploads`)
- [x] T028 Final `vendor/bin/pint --dirty --format agent` pass on all changed PHP files

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: T002 (ImageUploads) blocks T016 (project gallery upload); T001/T003 independent
- **Foundational (Phase 2)**: T004→T005 (migration order), T006/T007 models, T008/T009 factories, T010 migrate; then US1 (T011–T014). BLOCKS US2 & US3.
- **US2 (Phase 3)**: after Phase 2 (needs `PortfolioCategory` for the Select + factory)
- **US3 (Phase 4)**: after Phase 2. T020 test needs models/factories only; T021–T024 need the models. US3 can proceed in parallel with US2 (different files), though the manual demo is richer once US2 can create real projects.
- **Polish (Phase 5)**: after US2 + US3

### Within Each User Story

- Tests before implementation
- Migrations before models before factories before resources
- US3: routes (T021) → controller (T022) → views (T023, T024)

### Parallel Opportunities

- T003 [P] alongside T002 done
- T004/T006/T008 and T009 [P] groups; T011/T015/T020 test files [P]
- US2 (T015–T019) and US3 (T020–T025) implementable in parallel after Phase 2

---

## Parallel Example: test authoring after Phase 2

```bash
Task: "Create tests/Feature/Admin/PortfolioProjectResourceTest.php"
Task: "Create tests/Feature/Pages/PortfolioPageTest.php"
```

---

## Implementation Strategy

### MVP (categories + project CRUD)

1. Phase 1 Setup (incl. ImageUploads resize)
2. Phase 2 Foundational (both tables/models/factories + category CRUD US1)
3. Phase 3 US2 (project CRUD)
4. STOP and VALIDATE with quickstart US1 + US2
5. Deploy/demo — admin can manage the full portfolio catalogue

### Incremental Delivery

1. Setup + Foundational (+ US1) → categories manageable
2. US2 → project CRUD → demo
3. US3 → public listing + detail → demo (AMC-218 portfolio done)
4. Polish → full suite + quickstart

---

## Notes

- No new dependencies (Principle V). `ImageUploads::storeAsWebp` gains one optional param — `ArticleResource` callers unchanged (T027 re-checks).
- No draft/publish (toggle `is_active` only), no multi-category/tags, no pagination, no related-projects, no per-project SEO fields (spec Assumptions + clarification Q1).
- Category filter is server-rendered links (`?kategori=slug`) — shareable, no JS (Principle III/V).
- Navigation/footer links to `/portfolio` are out of scope for this ticket (spec Assumptions).
- Commit after each phase or logical group.
