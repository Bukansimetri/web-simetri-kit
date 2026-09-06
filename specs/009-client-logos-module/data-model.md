# Data Model: Modul Client Logos

**Date**: 2026-09-07 | **Feature**: [spec.md](./spec.md) | **Research**: [research.md](./research.md)

## ClientLogo (baru)

| Field | Type | Required | Notes |
|---|---|---|---|
| `id` | bigint PK | | |
| `company_name` | string | Ya | Nama perusahaan partner/klien; dipakai sebagai `alt` gambar (FR-002, FR-009) |
| `logo_path` | string | Ya | Path relatif hasil `ImageUploads::storeAsWebp` di disk `public`, direktori `client-logos/` (FR-003, research.md §2) |
| `link_url` | string, nullable | Tidak | URL absolut http/https bila diisi (FR-004); dirender sebagai tautan `target=_blank` (FR-009) |
| `order` | integer, default 0 | Ya (default) | Urutan tampil menaik di logo strip (FR-005) |
| `is_active` | boolean, default true | Ya (default) | Hanya `true` yang tampil di halaman publik (FR-006) |
| `created_at` / `updated_at` | timestamps | | Tie-break sekunder via `id` (research.md §4) |

**Validasi**:

- `company_name`, `logo_path`: wajib (FR-003).
- `link_url`: opsional; bila diisi harus URL valid berskema `http://` atau `https://` (FR-004) — ditegakkan via `TextInput->url()` + guard skema.
- `logo_path`: diisi otomatis oleh handler upload (bukan input teks manual).

**Relasi**: tidak ada — entity berdiri sendiri.

**Tidak ada status draft/publish** — visibilitas publik murni dari `is_active` (FR-006). Menghapus record menghilangkannya permanen (FR-007).

**Model casts**: `order => 'integer'`, `is_active => 'boolean'`.

**Model fillable**: `company_name`, `logo_path`, `link_url`, `order`, `is_active`.

## Query publik (halaman Tentang Kami)

```text
ClientLogo::query()
    ->where('is_active', true)
    ->orderBy('order')
    ->orderBy('id')
    ->get()
```

Dikirim `AboutController` sebagai `$clientLogos` ke `pages.tentang-kami` (di samping `$testimonials` dari modul 008); section `x-sections.client-logos` tidak merender apa pun bila koleksi kosong (FR-010).

## Ringkasan skema baru

```text
client_logos (BARU)
├── id
├── company_name
├── logo_path (WebP di disk public)
├── link_url (nullable, absolut http/https)
├── order (integer, default 0)
├── is_active (boolean, default true)
└── timestamps
```
