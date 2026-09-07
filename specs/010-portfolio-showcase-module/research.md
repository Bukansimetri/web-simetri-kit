# Research: Modul Portfolio / Project Showcase

**Date**: 2026-09-07 | **Feature**: [spec.md](./spec.md)

## 1. Dua entity: `PortfolioCategory` + `PortfolioProject` — pola Artikel + Kategori Artikel

**Decision**:
- `portfolio_categories`: `id`, `name` (string, wajib, unik), `slug` (string, unik, auto dari name), `order` (integer default 0), timestamps. Meniru `ArticleCategory` + tambahan `slug` untuk URL filter yang shareable.
- `portfolio_projects`: `id`, `portfolio_category_id` (FK, wajib), `title`, `slug` (unik, auto dari title, override), `description` (longText HTML), `images` (json array path), `client_name` (string nullable), `project_url` (string nullable), `completed_at` (date nullable), `order` (integer default 0), `is_active` (boolean default true), timestamps.

**Rationale**: Struktur ini memenuhi seluruh FR-005. `is_active` (bukan `published_at`) sesuai klarifikasi Q1 — konsisten modul 006–009. `slug` di kategori memungkinkan `/portfolio?kategori=instalasi-atap` yang bersih & bisa di-bookmark (FR-017).

**Alternatives considered**: Multi-kategori (pivot) atau tag — ditolak oleh Assumptions (satu kategori per proyek). `published_at` gaya Artikel — ditolak oleh klarifikasi Q1.

## 2. Galeri gambar: `FileUpload::multiple()->reorderable()` + `images` json array (pola Produk)

**Decision**: Field `images` di form pakai `FileUpload::make('images')->image()->multiple()->reorderable()->appendFiles()->minFiles(1)->disk('public')->directory('portfolio')->saveUploadedFileUsing(fn ($file) => ImageUploads::storeAsWebp($file, 'portfolio', maxWidth: 1200))`. Kolom `images` di-cast `array`. Model punya `coverImageUrl()` (gambar pertama) dan `imageUrls()` (semua), identik `Product`.

**Rationale**: `Product` sudah membuktikan pola `FileUpload::multiple()->reorderable()` + json array. `minFiles(1)` menegakkan FR-008 (min 1 gambar). Berbeda dari `Product`, di sini dipakai `saveUploadedFileUsing` untuk resize+WebP (FR-010b).

**Alternatives considered**: Spatie Media Library dengan konversi otomatis — ditolak (dependency + kompleksitas migrasi; json array cukup dan konsisten `Product`). `Repeater` gambar — ditolak, `FileUpload::multiple` lebih ringkas untuk galeri murni.

## 3. Resize + WebP: perluas `App\Support\ImageUploads::storeAsWebp` dengan parameter `maxWidth`

**Decision**: Tambah parameter opsional `?int $maxWidth = null` ke `storeAsWebp(UploadedFile $file, string $directory, string $disk = 'public', int $quality = 80, ?int $maxWidth = null)`. Bila `$maxWidth` diisi dan lebar sumber > `$maxWidth`: hitung tinggi proporsional, buat canvas baru via `imagecreatetruecolor` + `imagecopyresampled` (dengan `imagealphablending(false)` + `imagesavealpha(true)` untuk transparansi), lalu `imagewebp`. Bila lebar ≤ `$maxWidth` atau `$maxWidth` null → perilaku lama (konversi format saja, tanpa upscale).

**Rationale**: Backward-compatible — `ArticleResource` yang memanggil tanpa `maxWidth` tidak berubah. GD (`imagecopyresampled`) sudah tersedia (dipakai di `storeAsWebp` sekarang). Nol dependency baru (Principle V). Resize on-write menghemat storage & bandwidth sesuai instruksi user + SC-006a.

**Alternatives considered**: `intervention/image` — dependency baru, ditolak (GD cukup). Resize di sisi klien (JS) sebelum upload — kompleks, rapuh, tidak menjamin hasil. Menyimpan asli + generate turunan on-the-fly — over-engineering untuk kebutuhan ini.

## 4. Helper text ukuran rekomendasi

**Decision**: `FileUpload::make('images')->helperText('Rekomendasi 1200×900px (rasio bebas). Gambar lebih besar otomatis dikecilkan ke lebar 1200px & dikonversi ke WebP. Gambar pertama = sampul.')` (FR-010a).

**Rationale**: Instruksi eksplisit user. Konsisten gaya helperText modul lain (`ArticleResource` featured image menyebut "Rekomendasi dimensi: 1200×630px").

## 5. Routing publik: `/portfolio` (index + filter) & `/portfolio/{portfolioProject:slug}` (detail)

**Decision**: Dua route di `routes/web.php`:
- `Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio.index');`
- `Route::get('/portfolio/{portfolioProject:slug}', [PortfolioController::class, 'show'])->name('portfolio.show');`

`index(Request $request)`: ambil `$categories = PortfolioCategory::orderBy('order')->get()`; base query `PortfolioProject::where('is_active', true)->with('portfolioCategory')->orderBy('order')->orderBy('id')`; bila `$request->query('kategori')` cocok slug kategori → filter `whereHas`/`where portfolio_category_id`; bila slug kategori tidak dikenal → abaikan filter (tampilkan semua, FR edge case). `show(PortfolioProject $portfolioProject)`: `abort_unless($portfolioProject->is_active, 404)` lalu load kategori.

**Rationale**: Pola identik `ArticleController` (index + show, route-model-binding by slug, `abort_unless` untuk visibilitas). Filter via query string (`?kategori=`) memenuhi FR-017 (tercermin di URL, shareable) tanpa route tambahan.

**Alternatives considered**: Route `/portfolio/kategori/{slug}` terpisah — lebih banyak route, dan query param lebih natural untuk filter opsional. Filter via POST/session — tidak shareable, ditolak.

## 6. Halaman publik: listing grid + filter chips, detail galeri

**Decision**:
- `pages/portfolio/index.blade.php`: heading + baris chip kategori (link ke `?kategori={slug}`, chip "Semua" tanpa query; chip aktif ditandai); grid kartu (sampul `coverImageUrl()` atau placeholder, judul, nama kategori); `@forelse` dengan empty-state "Belum ada proyek" (FR-018).
- `pages/portfolio/show.blade.php`: breadcrumb, judul + kategori; galeri (semua `imageUrls()` sesuai urutan) — grid/stacked, gambar pertama besar; blok metadata opsional (`@if($project->client_name)`, `@if($project->completed_at)`, `@if($project->project_url)` → `<a target="_blank" rel="noopener noreferrer nofollow">`); `{!! $project->description !!}` dalam wrapper prose. Tidak ada label untuk field kosong (FR-021).

**Rationale**: Meniru `pages/artikel/index.blade.php` (grid + `@forelse` empty-state) dan `pages/produk/show.blade.php` (galeri). Chip filter = link biasa (server-render), tanpa JS — sederhana, shareable, konsisten Principle III/V.

**Alternatives considered**: Filter JS/Livewire reaktif — tidak perlu, link server-render cukup dan lebih shareable. Lightbox galeri — nice-to-have, bukan requirement; bisa ditambah sebagai peningkatan.

## 7. Cegah hapus kategori yang dipakai (FR-003)

**Decision**: Di `PortfolioCategoryResource` table `DeleteAction`, pakai `->before(function (PortfolioCategory $record, DeleteAction $action) { if ($record->portfolioProjects()->exists()) { Notification::make()->danger()->title('Kategori masih dipakai proyek')->send(); $action->cancel(); } })` — pola sama `ArticleCategoryResource` (yang sudah pakai `Notification` + cancel untuk kasus serupa).

**Rationale**: `ArticleCategoryResource` sudah punya guard identik (terlihat di import `Notification` + `Collection`). Konsisten, nol dependency.

**Alternatives considered**: FK `onDelete('restrict')` + tangkap QueryException — lebih kasar (pesan error mentah). Soft-reassign ke "Uncategorized" — menambah konsep yang tidak diminta.

## 8. Akses CRUD: tanpa policy (semua role panel)

**Decision**: Kedua resource tanpa policy/`canAccess()` — mengikuti seluruh resource konten (FR-015).

**Rationale**: Konsistensi; spec eksplisit. Pembatasan role = AMC-203 terpisah.
