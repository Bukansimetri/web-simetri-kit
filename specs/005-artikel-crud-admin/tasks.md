# Tasks: Artikel CRUD Admin

**Input**: Design documents from `/specs/005-artikel-crud-admin/`
**Prerequisites**: [plan.md](./plan.md), [spec.md](./spec.md), [research.md](./research.md), [data-model.md](./data-model.md), [contracts/admin-panel-surface.md](./contracts/admin-panel-surface.md), [quickstart.md](./quickstart.md)

**Tests**: Feature tests are included per Constitution Principle IV (Module Test Coverage — mandatory feature tests), following the pattern established in 003-produk-crud-admin (Livewire/Filament resource tests).

**Organization**: Tasks are grouped by user story (US1–US5, priorities from spec.md) to enable independent implementation and testing.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Maps task to US1–US5
- File paths are exact, relative to repository root

---

## Phase 1: Setup

**Purpose**: Bring in the one package surface this feature newly activates (`spatie/laravel-tags` — already in `composer.json` since Epic 1, but never published/used).

- [X] T001 Publish `spatie/laravel-tags` config & migrations: `php artisan vendor:publish --provider="Spatie\Tags\TagsServiceProvider" --tag="tags-migrations" --no-interaction` and `--tag="tags-config"` — produces `config/tags.php` and `database/migrations/*_create_tags_table.php` / `*_create_taggables_table.php` (research.md §3)

**Checkpoint**: Package migrations present on disk, not yet run.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Schema, models, factories/seeders, and the WebP helper that every user story below depends on.

**⚠️ CRITICAL**: No user story work can begin until this phase is complete.

- [X] T002 [P] Create migration `database/migrations/xxxx_create_article_categories_table.php` — `id`, `name` (unique), `order` (integer, default 0), timestamps (data-model.md § Article Category)
- [X] T003 Create migration `database/migrations/xxxx_update_articles_table_for_category_and_image.php` — add nullable `article_category_id` (FK → `article_categories.id`, `restrictOnDelete()`), add nullable `redaksi` (string), make `image_path` nullable; in a `data.php`-less approach, backfill `article_category_id` from the existing `category` string column by matching against canonical category names (pattern from research.md 003-produk-crud-admin §3 backfill), THEN drop the old `category` column and make `article_category_id` non-nullable (depends on T002)
- [X] T004 [P] Create `ArticleCategory` model in `app/Models/ArticleCategory.php` — fillable `name`, `order`; `articles(): HasMany`
- [X] T005 Update `Article` model in `app/Models/Article.php` — replace `category` in `$fillable` with `article_category_id` and add `redaksi`; add `use Spatie\Tags\HasTags;`; add `articleCategory(): BelongsTo`; add derived status helpers (`isDraft()`, `isScheduled()`, `isPublished()`) based on `published_at` (data-model.md § Status turunan) (depends on T004)
- [X] T006 [P] Create `ArticleCategoryFactory` in `database/factories/ArticleCategoryFactory.php`
- [X] T007 [P] Create `ArticleCategorySeeder` in `database/seeders/ArticleCategorySeeder.php` — seed canonical categories "Tips", "Berita", "Edukasi" via `updateOrCreate` (idempotent, matches names used in migration backfill in T003)
- [X] T008 Update `ArticleFactory` in `database/factories/ArticleFactory.php` — replace `category` string with `article_category_id` (via `ArticleCategory::factory()` or `ArticleCategory::inRandomOrder()->first()->id`), add `redaksi` fake value (depends on T006)
- [X] T009 Update `ArticleSeeder` in `database/seeders/ArticleSeeder.php` — look up seeded `ArticleCategory` records by name instead of writing a `category` string, fill `redaksi` (e.g. "Tim Redaksi SUOER") (depends on T007)
- [X] T010 Update `database/seeders/DatabaseSeeder.php` — call `ArticleCategorySeeder::class` before `ArticleSeeder::class` (depends on T007, T009)
- [X] T011 [P] Create `App\Support\ImageUploads` helper in `app/Support/ImageUploads.php` — static `storeAsWebp(TemporaryUploadedFile $file, string $directory): string` using GD (`imagecreatefromstring`, `imagewebp`), returns the stored relative path (research.md §6)

**Checkpoint**: `php artisan migrate:fresh --seed` runs clean; `Article`/`ArticleCategory` models and factories are usable in tests. User story implementation can now begin.

---

## Phase 3: User Story 1 - Admin mengelola kategori artikel (Priority: P1)

**Goal**: Admin can fully CRUD `ArticleCategory` records from the admin panel, with unique-name validation and delete-guard when a category is still referenced by articles.

**Independent Test**: Login as admin, create a category, verify it becomes selectable when writing an article; attempt to delete a category still in use, verify rejection.

### Tests for User Story 1 ⚠️

- [X] T012 [P] [US1] Feature test in `tests/Feature/Admin/ArticleCategoryResourceTest.php` — create category, reject duplicate `name`, edit category, delete unused category succeeds, delete category with `articles_count > 0` is blocked with a notification (pattern: `CategoryResourceTest` from 003-produk-crud-admin)

### Implementation for User Story 1

- [X] T013 [US1] Generate resource: `php artisan make:filament-resource ArticleCategory --no-interaction` (creates `app/Filament/Resources/ArticleCategoryResource.php` + `Pages/{ListArticleCategories,CreateArticleCategory,EditArticleCategory}.php`)
- [X] T014 [US1] Implement `form()` in `app/Filament/Resources/ArticleCategoryResource.php` — `TextInput::make('name')->required()->unique(ignoreRecord: true)`, `TextInput::make('order')->numeric()->default(0)`
- [X] T015 [US1] Implement `table()` in `app/Filament/Resources/ArticleCategoryResource.php` — columns `name`, `order`, `articles_count` (withCount), row actions Edit/Delete
- [X] T016 [US1] Add delete guard in `app/Filament/Resources/ArticleCategoryResource/Pages/ListArticleCategories.php` (or table `DeleteAction`) — `DeleteAction::make()->before(function ($record, $action) { if ($record->articles()->count() > 0) { Notification::make()->danger()->title('Kategori masih dipakai')->send(); $action->cancel(); } })` (pattern from `CategoryResource` in 003-produk-crud-admin)

**Checkpoint**: User Story 1 fully functional and testable independently (`php artisan test --compact tests/Feature/Admin/ArticleCategoryResourceTest.php`).

---

## Phase 4: User Story 2 - Admin menulis & mengedit artikel (Priority: P1) 🎯 MVP

**Goal**: Admin can write, edit, and delete articles (title, excerpt, rich-text content, category, redaksi) from the admin panel, with auto/manual slug and validation, and changes reflect immediately on `/artikel` and `/artikel/{slug}`.

**Independent Test**: (Requires ≥1 `ArticleCategory` from US1.) Create an article with all required fields and status Publish, verify it appears on `/artikel`; edit it, verify the change appears on `/artikel/{slug}`.

### Tests for User Story 2 ⚠️

- [ ] T017 [P] [US2] Feature test in `tests/Feature/Admin/ArticleResourceTest.php` — create article with required fields (title, excerpt, content, article_category_id) succeeds; slug auto-generates from title when left blank; manual slug override is respected; duplicate slug is rejected; missing required field is rejected with a field-level error; edit updates the record (route-key gotcha: pass `$article->getRouteKey()`, not `getKey()`, per 003/004 convention)

### Implementation for User Story 2

- [ ] T018 [US2] Generate resource: `php artisan make:filament-resource Article --no-interaction` (creates `app/Filament/Resources/ArticleResource.php` + `Pages/{ListArticles,CreateArticle,EditArticle}.php`)
- [ ] T019 [US2] Implement `form()` in `app/Filament/Resources/ArticleResource.php` — `TextInput::make('title')->required()->live(onBlur: true)->afterStateUpdated(...)` to auto-fill `slug` via `Str::slug()`, `TextInput::make('slug')->required()->unique(ignoreRecord: true)`, `Select::make('article_category_id')->relationship('articleCategory', 'name')->required()`, `Textarea::make('excerpt')->required()`, `RichEditor::make('content')->required()`, `TextInput::make('redaksi')` (optional, FR-022)
- [ ] T020 [US2] Implement `table()` in `app/Filament/Resources/ArticleResource.php` — columns `title`, `articleCategory.name`, `published_at`, row actions Edit/Delete with confirmation (FR-008)
- [ ] T021 [US2] Update `resources/views/pages/artikel/show.blade.php` — render `content` as raw HTML (`{!! $article->content !!}`) instead of `explode("\n", ...)`, and display `redaksi` as a byline near the title/date (FR-019, FR-022)

**Checkpoint**: User Stories 1 AND 2 both work independently; MVP is deliverable at this point.

---

## Phase 5: User Story 3 - Admin mengatur draft/publish artikel (Priority: P1)

**Goal**: Admin can save an article as Draft (hidden from public), Publish immediately, or Schedule a future publish date — and the public site respects this automatically, including scheduled articles becoming visible once their date arrives with no further admin action.

**Independent Test**: Create a Draft article, verify it's absent from `/artikel` and `/artikel/{slug}` 404s; publish it, verify it appears; create one scheduled for tomorrow, verify it does not yet appear.

### Tests for User Story 3 ⚠️

- [ ] T022 [P] [US3] Feature test in `tests/Feature/Public/ArticleVisibilityTest.php` — article with `published_at = null` (Draft) is absent from `/artikel` and 404s on `/artikel/{slug}`; article with `published_at` in the future (Scheduled) is absent from `/artikel` and 404s on direct access; article with `published_at <= now()` is visible on both

### Implementation for User Story 3

- [ ] T023 [US3] Add a status control to `form()` in `app/Filament/Resources/ArticleResource.php` — e.g. `Radio::make('publish_status')` (Draft / Publish now / Schedule) as a non-persisted form field, plus `DateTimePicker::make('published_at')` shown only for Schedule, with `mutateFormDataBeforeCreate()`/`mutateFormDataBeforeSave()` translating the choice into `published_at` (`null` for Draft, `now()` for Publish now, the picked datetime for Schedule) — data-model.md § Status turunan
- [ ] T024 [US3] Fix `app/Http/Controllers/Public/ArticleController.php@index` — add `->where('published_at', '<=', now())` to the existing `whereNotNull('published_at')` query, closing the FR-010 gap identified in research.md §2
- [ ] T025 [US3] Update `app/Http/Controllers/Public/ArticleController.php@show` — return 404 (via `abort(404)`) when the resolved `Article`'s `published_at` is null or in the future, instead of always rendering

**Checkpoint**: User Stories 1, 2, and 3 all independently functional — draft/schedule/publish semantics fully enforced.

---

## Phase 6: User Story 4 - Admin memberi tag pada artikel (Priority: P2)

**Goal**: Admin can attach free-form tags to an article (create-on-the-fly or pick existing), with no duplicate tags created and detaching a tag from one article not affecting its availability elsewhere.

**Independent Test**: Write an article with 2–3 tags (mix of new and existing), verify all appear on `/artikel/{slug}`; remove one tag, verify it disappears from that article but remains selectable elsewhere.

### Tests for User Story 4 ⚠️

- [ ] T026 [P] [US4] Feature test in `tests/Feature/Admin/ArticleResourceTest.php` (extend, same file as T017) — attaching new tag names creates them; attaching an existing tag name does not create a duplicate; detaching a tag from one article leaves it intact/available for others

### Implementation for User Story 4

- [ ] T027 [US4] Add `TagsInput::make('tags')` to `form()` in `app/Filament/Resources/ArticleResource.php`, with `suggestions(fn () => \Spatie\Tags\Tag::pluck('name'))`, hydrated via `afterStateHydrated` from `$record?->tags->pluck('name')`
- [ ] T028 [US4] Wire tag sync in `app/Filament/Resources/ArticleResource/Pages/CreateArticle.php` (`afterCreate()`) and `app/Filament/Resources/ArticleResource/Pages/EditArticle.php` (`afterSave()`) — call `$this->record->syncTags($this->data['tags'] ?? [])`
- [ ] T029 [US4] Display tags on `resources/views/pages/artikel/show.blade.php` — render `$article->tags->pluck('name')` as a list of tag badges

**Checkpoint**: User Stories 1–4 all independently functional.

---

## Phase 7: User Story 5 - Admin mengatur featured image artikel (Priority: P2)

**Goal**: Admin can upload a single featured image per article, automatically converted to WebP on save, shown on the article card and detail page, with a helper-text dimension recommendation and no dimension-based rejection; articles without an image show a reasonable placeholder.

**Independent Test**: Upload a JPG/PNG featured image, save, verify the stored file is `.webp`; verify it renders on `/artikel` and `/artikel/{slug}`; verify an article without one shows a placeholder.

### Tests for User Story 5 ⚠️

- [ ] T030 [P] [US5] Feature test in `tests/Feature/Admin/ArticleResourceTest.php` (extend, same file as T017/T026) — uploading a JPG/PNG featured image results in a stored file with `.webp` extension; a non-image file upload is rejected (FR-014); article without an uploaded image saves successfully with `image_path` null

### Implementation for User Story 5

- [ ] T031 [US5] Add `FileUpload::make('image_path')` to `form()` in `app/Filament/Resources/ArticleResource.php` — `->image()`, `->helperText('Rekomendasi dimensi: 1200×630px (tidak wajib, tanpa validasi ukuran)')` (FR-020, no dimension validation), `->saveUploadedFileUsing(fn ($file) => \App\Support\ImageUploads::storeAsWebp($file, 'articles'))` (FR-021)
- [ ] T032 [US5] Update `resources/views/components/sections/article-card.blade.php` and `resources/views/pages/artikel/show.blade.php` — render `<img>` from `Storage::disk('public')->url($article->image_path)` when present, else a placeholder image/graphic (FR-015), closing the render gap identified in research.md §5

**Checkpoint**: All 5 user stories independently functional.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Formatting, full regression check, and manual acceptance pass.

- [ ] T033 [P] Run `vendor/bin/pint --dirty --format agent` and fix any reported style issues
- [ ] T034 Run `php artisan test --compact` (full suite) and confirm no regressions in existing Product/Contact/public-page tests
- [ ] T035 Walk through [quickstart.md](./quickstart.md) US1–US5 steps manually against a fresh `migrate:fresh --seed` to confirm end-to-end behavior

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies — can start immediately
- **Foundational (Phase 2)**: Depends on Setup (T001, so the tags migrations exist to run alongside T002/T003) — BLOCKS all user stories
- **User Stories (Phase 3–7)**: All depend on Foundational (Phase 2) completion
  - US1 has no dependency on US2–US5
  - US2 depends on US1 only for test-data convenience (needs a category to exist) — not a code dependency
  - US3 extends the same `ArticleResource` form as US2 (same file, sequential not parallel)
  - US4 and US5 both extend the same `ArticleResource` form (same file as US2/US3 — sequential, not parallel, with each other or with US2/US3)
- **Polish (Phase 8)**: Depends on all desired user stories being complete

### Within Each User Story

- Tests MUST be written and FAIL before implementation (TDD, per project convention)
- Models/migrations (Foundational) before any Filament Resource work
- Resource `form()`/`table()` before Page-level hooks (`afterCreate`/`afterSave`)
- Story complete before moving to next priority

### Parallel Opportunities

- T002, T004, T006, T007, T011 (Phase 2) can run in parallel — different files, no interdependency until T003/T005/T008/T009 which build on them
- T012 (US1 test) can be written in parallel with T017/T022/T026/T030 (other stories' tests) since they're different files/independent scenarios, but implementation tasks touching `ArticleResource.php` (T019, T020, T023, T027, T031) MUST be sequential — same file
- T033 (Pint) can run parallel to nothing meaningful — it's a final pass, listed [P] only in the sense it doesn't block on story-specific review

---

## Parallel Example: Phase 2 (Foundational)

```bash
# These can be worked on together (different files):
Task: "Create migration for article_categories table"          # T002
Task: "Create ArticleCategory model"                            # T004
Task: "Create ArticleCategoryFactory"                            # T006
Task: "Create ArticleCategorySeeder"                             # T007
Task: "Create App\Support\ImageUploads helper"                  # T011
```

---

## Implementation Strategy

### MVP First (User Stories 1 + 2)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL — blocks all stories)
3. Complete Phase 3: User Story 1 (categories — prerequisite data for writing articles)
4. Complete Phase 4: User Story 2 (write/edit articles) — **STOP and VALIDATE** independently
5. This is the deliverable MVP: admin can manage categories and articles

### Incremental Delivery

1. Setup + Foundational → foundation ready
2. US1 (categories) → test independently
3. US2 (write/edit) → test independently → MVP deployable
4. US3 (draft/publish/schedule) → test independently → closes FR-010 gap
5. US4 (tags) → test independently
6. US5 (featured image + WebP) → test independently → closes image render gap
7. Polish → full regression + manual quickstart pass

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- US2, US3, US4, US5 all extend the same `app/Filament/Resources/ArticleResource.php` file — treat their form()/table() edits as sequential within that file even though they're separate tasks
- Commit after each phase/story checkpoint, run `vendor/bin/pint --dirty --format agent` and the relevant `php artisan test --compact --filter=...` before marking tasks `[X]`
- Avoid: vague tasks, same-file conflicts without sequencing, cross-story dependencies that break independent testability
