# Data Model: Modul Banner Management

**Date**: 2026-09-10 | **Feature**: [spec.md](./spec.md) | **Research**: [research.md](./research.md)

## Banner (baru)

| Field | Type | Required | Notes |
|---|---|---|---|
| `id` | bigint PK | | |
| `title` | string | Ya | Judul internal untuk identifikasi admin — TIDAK dirender publik (FR-002) |
| `image_path` | string | Ya | Path relatif hasil `ImageUploads::storeAsWebp(..., maxWidth: 1600)` di disk `public`, direktori `banners/` (FR-002, FR-006) |
| `alt_text` | string | Ya | Teks alternatif gambar untuk aksesibilitas (FR-002, FR-003, FR-011) |
| `link_url` | string, nullable | Tidak | URL absolut http/https bila diisi (FR-004); banner jadi tautan (FR-011) |
| `starts_at` | date, nullable | Tidak | Tanggal mulai tayang; kosong = tanpa batas awal (FR-002, FR-008) |
| `ends_at` | date, nullable | Tidak | Tanggal selesai tayang; kosong = tanpa batas akhir (FR-002, FR-008); MUST ≥ `starts_at` bila keduanya diisi (FR-005) |
| `order` | integer, default 0 | Ya (default) | Urutan tampil / giliran carousel menaik (FR-007, FR-016) |
| `is_active` | boolean, default true | Ya (default) | Prasyarat tayang (FR-008) |
| `created_at` / `updated_at` | timestamps | | Tie-break sekunder via `id` (research.md §2) |

**Validasi**:

- `title`, `image_path`, `alt_text`: wajib (FR-003).
- `link_url`: opsional; bila diisi harus URL berskema `http://`/`https://` (FR-004).
- `ends_at`: `after_or_equal:starts_at` — hanya dievaluasi bila `starts_at` juga diisi (FR-005).
- `image_path`: diisi otomatis oleh handler upload; di-downscale ≤1600px + WebP saat simpan (FR-006).

**Relasi**: tidak ada — entity berdiri sendiri.

**Model casts**: `starts_at => 'date'`, `ends_at => 'date'`, `order => 'integer'`, `is_active => 'boolean'`.

**Model fillable**: `title`, `image_path`, `alt_text`, `link_url`, `starts_at`, `ends_at`, `order`, `is_active`.

## Aturan tayang (FR-008) — `scopeLive()`

Sebuah banner **tayang** bila SEMUA benar:

1. `is_active === true`
2. `starts_at` IS NULL **atau** `starts_at <= today()`
3. `ends_at` IS NULL **atau** `ends_at >= today()`

Perbandingan tanggal inklusif (banner pada hari `starts_at`/`ends_at` dianggap tayang). Hasil diurutkan `order` asc lalu `id` asc.

```text
Banner::query()
    ->where('is_active', true)
    ->where(fn ($q) => $q->whereNull('starts_at')->orWhereDate('starts_at', '<=', today()))
    ->where(fn ($q) => $q->whereNull('ends_at')->orWhereDate('ends_at', '>=', today()))
    ->orderBy('order')->orderBy('id')
```

## Status turunan (FR-013) — `displayStatus(): string`

| Kondisi | Nilai | Label admin | Warna badge |
|---|---|---|---|
| `! is_active` | `inactive` | Nonaktif | gray |
| aktif & `starts_at` > today | `scheduled` | Terjadwal | warning |
| aktif & `ends_at` < today | `expired` | Kedaluwarsa | danger |
| selain di atas | `live` | Tayang | success |

Invariant: `displayStatus() === 'live'` ⟺ banner termasuk hasil `scopeLive()` (diverifikasi unit test).

## Query publik (beranda)

`HomeController` mengirim `$banners = Banner::live()->get()` ke `pages.home` (di samping `$products`). `home.blade.php`: `$banners->isNotEmpty()` → `<x-sections.banner-carousel :banners="$banners" />`; else → `<x-sections.hero />` (FR-012, FR-015).

## Ringkasan skema baru

```text
banners (BARU)
├── id
├── title (internal)
├── image_path (WebP ≤1600px di disk public)
├── alt_text
├── link_url (nullable, absolut http/https)
├── starts_at (date, nullable)
├── ends_at (date, nullable, ≥ starts_at)
├── order (integer, default 0)
├── is_active (boolean, default true)
└── timestamps
```
