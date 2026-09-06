# Data Model: Custom Page (Halaman Statis Bebas)

**Date**: 2026-09-07 | **Feature**: [spec.md](./spec.md) | **Research**: [research.md](./research.md)

## Custom Page (baru)

| Field | Type | Required | Notes |
|---|---|---|---|
| `id` | bigint PK | | |
| `title` | string | Ya | Judul halaman (FR-002) |
| `slug` | string, unique | Ya (auto dari `title`) | Route key untuk `/halaman/{slug}` (FR-003, FR-014); admin bisa override manual (FR-003) |
| `content` | longtext (HTML) | Ya | Diisi via `RichEditor` (FR-009, research.md §2), menyimpan HTML |

**Validasi**:
- `title`, `content`: wajib (FR-008).
- `slug`: unique antar Custom Page (FR-004, FR-005); auto-generate dari `title` saat kosong, bisa di-override manual (FR-003). TIDAK perlu divalidasi terhadap route statis lain (research.md §3 — prefix `/halaman/` menghilangkan kebutuhan ini).

**Relasi**: tidak ada — entity berdiri sendiri.

**Tidak ada status/draft-publish** (research.md §1) — begitu record dibuat, langsung dapat diakses di `/halaman/{slug}`; hapus record untuk "menyembunyikannya" (FR-007, FR-011).

**Tidak ada halaman listing publik** — setiap Custom Page murni diakses lewat URL-nya sendiri (spec.md Assumptions).

## Ringkasan skema baru

```text
custom_pages (BARU)
├── id
├── title
├── slug (unique)
├── content (longtext, HTML dari RichEditor)
└── timestamps
```
