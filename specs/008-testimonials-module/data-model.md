# Data Model: Modul Testimonials

**Date**: 2026-09-07 | **Feature**: [spec.md](./spec.md) | **Research**: [research.md](./research.md)

## Testimonial (baru)

| Field | Type | Required | Notes |
|---|---|---|---|
| `id` | bigint PK | | |
| `name` | string | Ya | Nama pemberi testimoni (FR-002) |
| `attribution` | string, nullable | Tidak | Satu field teks bebas perusahaan/jabatan, mis. "Manajer Operasional, PT ABC" (FR-002, klarifikasi Q1) |
| `content` | text | Ya | Isi testimoni (FR-002) |
| `rating` | unsignedTinyInteger | Ya | Bilangan bulat 1–5 (FR-002, FR-004) |
| `photo_path` | string, nullable | Tidak | Path relatif hasil `ImageUploads::storeAsWebp` di disk `public`, direktori `testimonials/` (FR-005, research.md §3) |
| `order` | integer, default 0 | Ya (default) | Urutan tampil menaik di halaman publik (FR-006) |
| `is_active` | boolean, default true | Ya (default) | Hanya `true` yang tampil di halaman publik (FR-007) |
| `created_at` / `updated_at` | timestamps | | Tie-break sekunder via `id` (research.md §4) |

**Validasi**:

- `name`, `content`, `rating`: wajib (FR-003).
- `rating`: bilangan bulat dalam himpunan {1,2,3,4,5} — ditegakkan via `Select` options + `Rule::in([1,2,3,4,5])` (FR-004).
- `attribution`, `photo_path`: opsional; record tanpa keduanya tetap valid (FR-005).
- `photo_path`: diisi otomatis oleh handler upload (bukan input teks manual).

**Relasi**: tidak ada — entity berdiri sendiri.

**Tidak ada status draft/publish** — visibilitas publik murni dari `is_active` (FR-007). Menghapus record menghilangkannya permanen (FR-008).

**Model casts**: `rating => 'integer'`, `order => 'integer'`, `is_active => 'boolean'`.

**Model fillable**: `name`, `attribution`, `content`, `rating`, `photo_path`, `order`, `is_active`.

## Query publik (halaman Tentang Kami)

```text
Testimonial::query()
    ->where('is_active', true)
    ->orderBy('order')
    ->orderBy('id')
    ->get()
```

Dikirim `AboutController` sebagai `$testimonials` ke `pages.tentang-kami`; section `x-sections.testimonials` tidak merender apa pun bila koleksi kosong (FR-011).

## Ringkasan skema baru

```text
testimonials (BARU)
├── id
├── name
├── attribution (nullable)
├── content (text)
├── rating (unsignedTinyInteger, 1–5)
├── photo_path (nullable, WebP di disk public)
├── order (integer, default 0)
├── is_active (boolean, default true)
└── timestamps
```
