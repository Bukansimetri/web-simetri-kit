# Data Model: Produk CRUD Admin

**Date**: 2026-09-03 | **Feature**: [spec.md](./spec.md) | **Research**: [research.md](./research.md)

## Category (baru)

| Field | Type | Required | Notes |
|---|---|---|---|
| `id` | bigint PK | | |
| `name` | string, unique | Ya | Ditampilkan sebagai label filter di `/produk` (FR-002, FR-003) |
| `order` | integer, default 0 | Tidak | Urutan tampil tab filter kategori |

**Validasi**: `name` unique (case-insensitive disarankan, tapi minimal unique persis — FR-003).

**Relasi**: `hasMany(Product::class)`.

**Guard hapus** (FR-004): Tidak bisa dihapus jika `products()->count() > 0` — dicegah di level aplikasi (Filament `DeleteAction->before()`) dan di level DB (`restrictOnDelete()` FK di `products.category_id`).

## Product (perluasan dari 002-theme-branding-system)

| Field | Type | Required | Notes |
|---|---|---|---|
| `id` | bigint PK | | Sudah ada |
| `slug` | string, unique | Ya (auto dari `name`) | Sudah ada — admin bisa override (FR-006, FR-007) |
| `name` | string | Ya | Sudah ada |
| `category_id` | bigint FK → `categories.id` | Ya | **Baru** — menggantikan kolom `category` (string) dari 002 |
| `short_description` | text | Ya | Sudah ada |
| `description` | text | Ya | Sudah ada |
| `price` | decimal(12,2) | Ya | Sudah ada |
| `strikethrough_price` | decimal(12,2), nullable | Tidak | Sudah ada |
| `images` | json (array of string path), default `[]` | Tidak | **Baru** — menggantikan `image_path` (string tunggal) dari 002, lihat research.md §2. Index 0 = gambar sampul/cover (FR-011) |
| `specs` | json (array of `{label, value}`) | Tidak | Sudah ada — kini diisi lewat Filament `Repeater` (FR-012) |
| `features` | json (array of `{icon, title, description}`) | Tidak | Sudah ada — kini diisi lewat Filament `Repeater` (FR-012) |
| `order` | integer, default 0 | Tidak | Sudah ada (FR-013) |

**Validasi**:
- `slug`: unique antar produk (FR-007); auto-generate dari `name` saat kosong, bisa di-override manual (FR-006).
- `category_id`: wajib, harus mengacu ke `Category` yang ada (FR-005, FR-014).
- `name`, `price`: wajib (FR-014).
- Tiap file di `images`: format gambar umum, ukuran maksimum wajar (FR-015) — validasi sama seperti upload logo/favicon/OG image di Theme Settings.

**Relasi**: `belongsTo(Category::class)`.

**Migrasi dari skema 002** (lihat research.md §3): kolom `category` (string) dan `image_path` (string, tidak pernah benar-benar dirender frontend publik di 002 — lihat catatan di bawah) di-drop; data existing di-backfill ke `categories` + `category_id`.

> **Catatan ketemu saat riset**: Blade `produk/index.blade.php`, `produk/show.blade.php`, dan `home.blade.php` dari 002 ternyata **tidak pernah** merender `<img src="{{ $product->image_path }}">` — placeholder gambar (`<div class="... bg-surface-container"></div>`) dipakai apa adanya tanpa tag `<img>` yang terhubung ke data. Fitur ini (US3) sekalian memperbaiki gap tsb: Blade akan diupdate untuk benar-benar merender `images[0]` (dan galeri penuh di halaman detail) begitu datanya ada — lihat plan.md § Project Structure.

## Ringkasan perubahan skema

```text
categories (BARU)
├── id
├── name (unique)
├── order
└── timestamps

products (DIUBAH dari 002)
├── ... (kolom lain tidak berubah)
├── category_id (BARU, FK → categories.id, restrictOnDelete)
├── images (BARU, json array, default [])
├── category (DIHAPUS — string lama)
└── image_path (DIHAPUS — string lama, tidak pernah dipakai render)
```
