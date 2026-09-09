# Data Model: Modul Team Members

**Date**: 2026-09-08 | **Feature**: [spec.md](./spec.md) | **Research**: [research.md](./research.md)

## TeamMember (baru)

| Field | Type | Required | Notes |
|---|---|---|---|
| `id` | bigint PK | | |
| `name` | string | Ya | Nama anggota (FR-002) |
| `position` | string | Ya | Jabatan (FR-002) |
| `photo_path` | string | Ya | Path relatif hasil `ImageUploads::storeAsWebp(..., maxWidth: 800)` di disk `public`, direktori `team/` (FR-002, FR-005, research.md §2) |
| `bio` | text | Ya | Bio singkat, teks biasa (FR-002, Assumptions) |
| `linkedin_url` | string, nullable | Tidak | URL absolut http/https bila diisi; boleh kosong (FR-004, klarifikasi Q1) |
| `order` | integer, default 0 | Ya (default) | Urutan tampil menaik di section (FR-006) |
| `is_active` | boolean, default true | Ya (default) | Hanya `true` yang tampil di halaman publik (FR-007) |
| `created_at` / `updated_at` | timestamps | | Tie-break sekunder via `id` (research.md §4) |

**Validasi**:

- `name`, `position`, `photo_path`, `bio`: wajib (FR-003).
- `linkedin_url`: opsional; kosong → diterima tanpa error (FR-003); bila diisi harus URL berskema `http://`/`https://` (FR-004).
- `photo_path`: diisi otomatis oleh handler upload (bukan input teks); foto di-downscale ≤800px lebar + WebP saat simpan (FR-005).

**Relasi**: tidak ada — entity berdiri sendiri.

**Tidak ada status draft/publish** — visibilitas publik murni dari `is_active` (FR-007). Menghapus record menghilangkannya permanen (FR-008).

**Model casts**: `order => 'integer'`, `is_active => 'boolean'`.

**Model fillable**: `name`, `position`, `photo_path`, `bio`, `linkedin_url`, `order`, `is_active`.

## Query publik (halaman Tentang Kami)

```text
TeamMember::query()
    ->where('is_active', true)
    ->orderBy('order')
    ->orderBy('id')
    ->get()
```

Dikirim `AboutController` sebagai `$teamMembers` ke `pages.tentang-kami` (di samping `$testimonials` dari modul 008 & `$clientLogos` dari modul 009); section `x-sections.team-members` tidak merender apa pun bila koleksi kosong (FR-011).

## Ringkasan skema baru

```text
team_members (BARU)
├── id
├── name
├── position
├── photo_path (WebP ≤800px di disk public)
├── bio (text)
├── linkedin_url (nullable, absolut http/https)
├── order (integer, default 0)
├── is_active (boolean, default true)
└── timestamps
```
