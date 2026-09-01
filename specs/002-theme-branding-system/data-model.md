# Data Model: Theme & Branding System

**Date**: 2026-09-01 | **Feature**: [spec.md](./spec.md) | **Research**: [research.md](./research.md)

## Theme Settings (perluasan `BrandSettings`)

Perluasan dari `App\Settings\BrandSettings` (Epic 2 — group `brand`, disimpan via `spatie/laravel-settings`). Bukan class/tabel baru — lihat [research.md §1](./research.md#1-struktur-theme-settings-extend-brandsettings-yang-sudah-ada-bukan-class-baru).

| Field | Type | Required | Default (fallback) | Notes |
|---|---|---|---|---|
| `app_name` | string, nullable | Tidak | `config('app.name')` | Sudah ada (Epic 2) |
| `logo_path` | media path, nullable | Tidak | Logo default Filament | Sudah ada (Epic 2) |
| `favicon_path` | media path, nullable | Tidak | Favicon default | Sudah ada (Epic 2) |
| `primary_color` | string (hex), nullable | Tidak | `#006397` (Luminous Azure primary) | Sudah ada (Epic 2); default diisi ulang ke token Luminous Azure (FR-005) |
| `secondary_color` | string (hex), nullable | Tidak | `#3a5f94` (Luminous Azure secondary) | **Baru** (FR-003) |
| `font_heading` | string (enum kurasi), nullable | Tidak | `Manrope` | **Baru** (FR-004) — nilai harus salah satu dari daftar kurasi, lihat §Validasi |
| `font_body` | string (enum kurasi), nullable | Tidak | `Be Vietnam Pro` | **Baru** (FR-004) |
| `og_image` (media collection `brand-og-image`) | media path, nullable | Tidak | Asset default Luminous Azure (`public/images/og-default.jpg` atau setara) | **Baru** (FR-009/FR-010) |

**Daftar font kurasi** (dropdown, FR-004 — lihat [research.md §5](./research.md#5-font-kurasi-preload-semua-kandidat-lewat-bunny-vite-plugin-yang-sudah-ada)): Manrope, Be Vietnam Pro, Inter, Poppins, Plus Jakarta Sans, Nunito Sans, Work Sans, Lato.

**Validasi**:
- `secondary_color`: harus hex valid (format sama dengan validasi `primary_color` yang sudah ada).
- `font_heading` / `font_body`: harus salah satu nilai di daftar kurasi (server-side reject nilai di luar daftar — tidak ada free text, FR-004).
- `og_image`: tipe file dibatasi ke gambar umum (png/jpg/webp), ukuran maksimum wajar untuk asset share sosial — konsisten dengan validasi upload logo/favicon Epic 2.

**Relasi**: Tidak ada — satu baris konfigurasi berlaku global untuk instalasi (bukan per-user), sama seperti `BrandSettings` yang sudah ada.

**State**: Tidak ada state transition — berlaku langsung begitu disimpan (FR-002).

## Product (seed)

Representasi produk solar panel untuk halaman Produk (list) & Produk Detail. Struktur minimal siap digantikan modul CRUD Epic 3 (AMC-207) tanpa mengubah Blade (FR-008).

| Field | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `slug` | string, unique | Untuk route `/produk/{slug}` |
| `name` | string | Mis. "Panel Surya Monokristalin 550W" |
| `category` | string | Mis. `residensial`, `komersial`, `pompa-air` — dipakai untuk related products & filter |
| `short_description` | text | Ditampilkan di card list |
| `description` | text | Ditampilkan di halaman detail |
| `price` | decimal | Harga jual |
| `strikethrough_price` | decimal, nullable | Harga coret (diskon), null jika tidak ada promo |
| `image_path` | string | Path gambar produk (disk `public`) |
| `specs` | json | Array key-value spesifikasi teknis (mis. `{"Daya Maksimum (Pmax)": "550W", "Efisiensi Modul": "21.3%"}`) — bebas urutan, dirender sebagai tabel |
| `features` | json | Array objek `{icon, title, description}` untuk list fitur unggulan |
| `order` | integer, default 0 | Urutan tampil di list/related products |

**Validasi**: `slug` unique; `price`/`strikethrough_price` non-negatif; `specs`/`features` harus array valid (divalidasi di seeder, bukan form — belum ada form CRUD di fitur ini).

**Relasi**: Tidak ada relasi antar model di fitur ini (related products dipilih dari `category` yang sama, bukan foreign key).

## Article (seed)

Representasi artikel/blog untuk halaman Artikel. Siap digantikan modul CRUD Epic 3 (AMC-213).

| Field | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `slug` | string, unique | Route `/artikel/{slug}` |
| `title` | string | |
| `excerpt` | text | Ringkasan untuk card & featured article |
| `content` | longtext | Isi artikel lengkap |
| `image_path` | string | Gambar sampul |
| `category` | string | Untuk filter di listing |
| `published_at` | datetime | Untuk urutan & tampilan tanggal terbit |

**Validasi**: `slug` unique; `published_at` tidak boleh di masa depan untuk artikel yang dianggap tayang (edge case empty state jika belum ada artikel published).

## Job Opening (seed)

Representasi lowongan kerja untuk halaman Karir. Siap digantikan modul CRUD Epic 3 (AMC-212).

| Field | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `title` | string | Nama posisi |
| `location` | string | Mis. "Jakarta / Remote" |
| `employment_type` | string | Mis. `full-time`, `internship` |
| `description` | text | Deskripsi & kualifikasi singkat |
| `is_active` | boolean, default true | Untuk toggle tampil/tidak tanpa hapus data |

**Validasi**: Hanya `is_active = true` yang tampil di listing publik (mendukung modul Career opsional/toggle per Epic 3 AMC-212).

## FAQ Item (seed)

Representasi pertanyaan & jawaban untuk halaman FAQ.

| Field | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `question` | string | |
| `answer` | text | |
| `category` | string, nullable | Untuk sidebar kategori (opsional, fallback "Umum" jika kosong) |
| `order` | integer, default 0 | Urutan tampil dalam accordion |

**Validasi**: Tidak ada validasi khusus — data seed murni tampilan.

## Contact Inquiry (bukan entity tersimpan)

Form Kontak (FR-007) tidak menyimpan data ke tabel apa pun di fitur ini (lihat [research.md §7](./research.md#7-form-kontak-tanpa-submit-sungguhan-validasi-client-side-saja-tanpa-route-post)). Field form (`nama`, `phone`, `kebutuhan`, `pesan`) hanya divalidasi di sisi client; tidak ada model/migration untuk ini di fitur ini — akan ditambahkan saat AMC-216 dikerjakan.
