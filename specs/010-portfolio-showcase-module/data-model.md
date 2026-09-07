# Data Model: Modul Portfolio / Project Showcase

**Date**: 2026-09-07 | **Feature**: [spec.md](./spec.md) | **Research**: [research.md](./research.md)

## PortfolioCategory (baru)

| Field | Type | Required | Notes |
|---|---|---|---|
| `id` | bigint PK | | |
| `name` | string | Ya | Unik antar kategori portfolio (FR-002) |
| `slug` | string, unique | Ya (auto dari `name`) | Dipakai di URL filter `/portfolio?kategori={slug}` (FR-017) |
| `order` | integer, default 0 | Ya (default) | Urutan tampil chip filter |
| timestamps | | | |

**Validasi**: `name` wajib + unique; `slug` auto dari `name`, unique. Hapus dicegah bila `portfolioProjects()->exists()` (FR-003).

**Relasi**: `hasMany(PortfolioProject::class)`.

## PortfolioProject (baru)

| Field | Type | Required | Notes |
|---|---|---|---|
| `id` | bigint PK | | |
| `portfolio_category_id` | bigint FK | Ya | Satu kategori per proyek (FR-005, Assumptions) |
| `title` | string | Ya | Judul proyek (FR-005) |
| `slug` | string, unique | Ya (auto dari `title`) | Route key `/portfolio/{slug}` (FR-006, FR-007) |
| `description` | longText (HTML) | Ya | Dari `RichEditor` (FR-005) |
| `images` | json (array of path) | Ya (min 1) | Daftar path terurut; tiap file disimpan WebP lebar ≤1200px (FR-008, FR-010, FR-010b). Gambar `[0]` = sampul (FR-010) |
| `client_name` | string, nullable | Tidak | Nama klien (FR-005) |
| `project_url` | string, nullable | Tidak | URL absolut http/https bila diisi (FR-009) |
| `completed_at` | date, nullable | Tidak | Tanggal selesai, tanpa jam (FR-005, Assumptions) |
| `order` | integer, default 0 | Ya (default) | Urutan tampil listing (FR-011) |
| `is_active` | boolean, default true | Ya (default) | Hanya `true` yang tampil publik (FR-012) |
| timestamps | | | Tie-break sekunder via `id` (research.md §1) |

**Validasi**:
- `title`, `portfolio_category_id`, `description`: wajib (FR-008).
- `images`: minimal 1 file (FR-008) — `FileUpload->minFiles(1)`.
- `slug`: unique antar `portfolio_projects`, auto dari `title`, bisa override (FR-006, FR-007).
- `project_url`: opsional; bila diisi harus URL berskema `http://`/`https://` (FR-009).
- `client_name`, `completed_at`: opsional; kosong MUST tetap valid & tidak render label di detail (FR-021).

**Relasi**: `belongsTo(PortfolioCategory::class)`.

**Model casts**: `images => 'array'`, `completed_at => 'date'`, `order => 'integer'`, `is_active => 'boolean'`.

**Model fillable**: `portfolio_category_id`, `title`, `slug`, `description`, `images`, `client_name`, `project_url`, `completed_at`, `order`, `is_active`.

**Model helpers** (pola `Product`): `getRouteKeyName(): 'slug'`, `imageUrls(): array` (semua URL dari `images` di disk `public`), `coverImageUrl(): ?string` (`imageUrls()[0] ?? null`).

**Tidak ada status draft/publish** — visibilitas murni `is_active` (FR-012, klarifikasi Q1).

## Query publik

**Listing `/portfolio`** (dengan filter opsional `?kategori={slug}`):

```text
PortfolioProject::query()
    ->where('is_active', true)
    ->with('portfolioCategory')
    ->when($categorySlug (valid), fn ($q) => $q->whereRelation('portfolioCategory', 'slug', $categorySlug))
    ->orderBy('order')
    ->orderBy('id')
    ->get()
```

Slug kategori tidak dikenal → filter diabaikan (tampilkan semua). Hasil kosong → view empty-state, HTTP 200 (FR-018).

**Detail `/portfolio/{slug}`**: route-model-binding by `slug`; `abort_unless($project->is_active, 404)` (FR-020).

## Ringkasan skema baru

```text
portfolio_categories (BARU)
├── id
├── name (unique)
├── slug (unique)
├── order (integer, default 0)
└── timestamps

portfolio_projects (BARU)
├── id
├── portfolio_category_id → portfolio_categories.id
├── title
├── slug (unique)
├── description (longtext, HTML)
├── images (json array, WebP ≤1200px)
├── client_name (nullable)
├── project_url (nullable, absolut http/https)
├── completed_at (date, nullable)
├── order (integer, default 0)
├── is_active (boolean, default true)
└── timestamps
```
