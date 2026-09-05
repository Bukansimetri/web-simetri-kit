# Data Model: Artikel CRUD Admin

**Date**: 2026-09-05 | **Feature**: [spec.md](./spec.md) | **Research**: [research.md](./research.md)

## Article Category (baru)

| Field | Type | Required | Notes |
|---|---|---|---|
| `id` | bigint PK | | |
| `name` | string, unique | Ya | Ditampilkan sebagai pilihan kategori saat menulis artikel (FR-001, FR-002) |
| `order` | integer, default 0 | Tidak | Urutan tampil (kalau dipakai sebagai filter di frontend nanti) |

**Validasi**: `name` unique (FR-002).

**Relasi**: `hasMany(Article::class)`.

**Guard hapus** (FR-003): Tidak bisa dihapus jika `articles()->count() > 0` — dicegah di level aplikasi (Filament `DeleteAction->before()`, pola identik `CategoryResource` Produk) dan di level DB (`restrictOnDelete()` FK).

## Article (perluasan dari 002-theme-branding-system)

| Field | Type | Required | Notes |
|---|---|---|---|
| `id` | bigint PK | | Sudah ada |
| `slug` | string, unique | Ya (auto dari `title`) | Sudah ada — admin bisa override (FR-005, FR-006) |
| `title` | string | Ya | Sudah ada |
| `excerpt` | text | Ya | Sudah ada |
| `content` | longtext (HTML) | Ya | Sudah ada — kini diisi via `RichEditor` (FR-019, research.md §4), menyimpan HTML bukan plain text |
| `article_category_id` | bigint FK → `article_categories.id` | Ya | **Baru** — menggantikan kolom `category` (string) dari 002 |
| `image_path` | string, **nullable** | Tidak | Sudah ada, kini nullable (FR-013 opsional) — path hasil konversi WebP (FR-021) |
| `published_at` | datetime, nullable | Tidak | Sudah ada — `null` = Draft, masa depan = Terjadwal, `<= now()` = Published (FR-009, FR-010; research.md §2) |
| `redaksi` | string, nullable | Tidak | **Baru** — byline teks bebas (nama penulis/tim), BUKAN relasi ke `User` (FR-022) |

**Validasi**:
- `slug`: unique antar artikel (FR-006); auto-generate dari `title` saat kosong, bisa di-override manual (FR-005).
- `article_category_id`: wajib, harus mengacu ke `ArticleCategory` yang ada (FR-004, FR-016).
- `title`, `excerpt`, `content`: wajib (FR-016).
- `image_path` (saat file diupload): format gambar umum (FR-014) — TIDAK ADA validasi dimensi (FR-020), hanya helper text rekomendasi.
- `redaksi`: opsional, teks bebas, tidak ada format khusus (FR-022).

**Relasi**:
- `belongsTo(ArticleCategory::class)`.
- `tags()` — disediakan trait `Spatie\Tags\HasTags` (many-to-many polymorphic ke tabel `tags`/`taggables` bawaan package, research.md §3). Bukan kolom biasa.

**Status turunan (tidak disimpan, dihitung on-the-fly)**:

```text
published_at === null           → Draft
published_at > now()            → Terjadwal
published_at <= now()           → Published
```

**Migrasi dari skema 002**: kolom `category` (string) di-drop, data existing di-backfill ke `article_categories` + `article_category_id` (pola sama seperti research.md 003-produk-crud-admin §3); `image_path` diubah jadi nullable.

## Tag (disediakan package, bukan model custom)

Tabel `tags` dan `taggables` dibuat oleh migration bawaan `spatie/laravel-tags` (`php artisan vendor:publish --tag=tags-migrations`) — bukan tabel/model yang kita desain sendiri. Satu tag (`name`) bisa dipasang ke banyak artikel; melepas tag dari satu artikel tidak menghapus baris tag itu sendiri (FR-011, FR-012).

## Ringkasan perubahan skema

```text
article_categories (BARU)
├── id
├── name (unique)
├── order
└── timestamps

tags, taggables (BARU — dari package spatie/laravel-tags, bukan didesain manual)

articles (DIUBAH dari 002)
├── ... (title, slug, excerpt, published_at tidak berubah)
├── article_category_id (BARU, FK → article_categories.id, restrictOnDelete)
├── image_path (DIUBAH — jadi nullable)
├── content (DIUBAH secara semantik — kini HTML dari RichEditor, tipe kolom tetap longtext)
├── redaksi (BARU, string nullable — byline teks bebas, bukan relasi User)
└── category (DIHAPUS — string lama)
```
