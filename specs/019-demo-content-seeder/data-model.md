# Data Model: Demo Content Seeder

**Feature**: 019-demo-content-seeder | **Date**: 2026-09-13

## Entity: DemoSeedRecord (baru)

Manifest polymorphic yang mencatat setiap baris data yang di-insert oleh proses `demo:seed`, dipakai untuk idempotency (research.md #4) dan pembersihan aman (research.md #1).

| Field | Type | Constraints / Notes |
|---|---|---|
| `id` | bigint PK | |
| `seedable_type` | string | Kelas model target (morph type) — mis. `App\Models\Product`. |
| `seedable_id` | bigint | ID record target (morph id). |
| `created_at` | timestamp | Kapan record demo tsb dibuat — tidak perlu `updated_at`. |

**Relationships**:
- `DemoSeedRecord morphTo seedable` — record data aktual di salah satu dari 5 model: `Product`, `TeamMember`, `Testimonial`, `PortfolioCategory`, `PortfolioProject`.

**Validation rules**:
- Kombinasi (`seedable_type`, `seedable_id`) unik — satu record data hanya boleh tercatat sekali di manifest.

**Tidak ada perubahan skema** pada `products`, `team_members`, `testimonials`, `portfolio_categories`, `portfolio_projects` — kelimanya memakai struktur kolom yang sudah ada (lihat model masing-masing); fitur ini hanya menambah baris data + satu baris manifest per baris data.

## Entities yang dipakai (sudah ada, tidak diubah strukturnya)

- **Product** (modul "Layanan"): `slug`, `name`, `category_id`, `short_description`, `description`, `price`, `strikethrough_price`, `images`, `specs`, `features`, `order`.
- **TeamMember**: `name`, `position`, `photo_path`, `bio`, `linkedin_url`, `order`, `is_active`.
- **Testimonial**: `name`, `attribution`, `content`, `rating`, `photo_path`, `order`, `is_active`.
- **PortfolioCategory**: `name`, `slug`, `order`.
- **PortfolioProject**: `portfolio_category_id`, `title`, `slug`, `description`, `images`, `client_name`, `project_url`, `completed_at`, `order`, `is_active`.

## Urutan operasi

**Seeding** (`demo:seed`, per manifest §Rationale research.md #5):
1. `PortfolioCategory` contoh → catat manifest
2. `Product` contoh (butuh `Category` — taxonomy struktural, BUKAN bagian manifest demo, lihat research.md #3) → catat manifest
3. `TeamMember` contoh → catat manifest
4. `Testimonial` contoh → catat manifest
5. `PortfolioProject` contoh (mereferensikan `PortfolioCategory` dari langkah 1) → catat manifest

**Cleaning** (`demo:clean`, urutan kebalikan untuk menjaga FK, research.md #5):
1. Hapus `PortfolioProject` yang tercatat manifest
2. Hapus `Testimonial` yang tercatat manifest
3. Hapus `TeamMember` yang tercatat manifest
4. Hapus `Product` yang tercatat manifest
5. Hapus `PortfolioCategory` yang tercatat manifest **HANYA JIKA** tidak ada `PortfolioProject` lain (demo maupun bukan) yang masih mereferensikannya
6. Hapus seluruh baris `demo_seed_records` yang baru saja diproses
