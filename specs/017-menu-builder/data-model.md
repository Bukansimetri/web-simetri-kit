# Data Model: Menu Builder

**Feature**: 017-menu-builder | **Date**: 2026-09-13

## Entity: MenuLocation

Mewakili tempat menu ditampilkan (navbar, footer, atau lokasi lain yang ditambahkan admin).

| Field | Type | Constraints / Notes |
|---|---|---|
| `id` | bigint PK | |
| `name` | string | Nama tampil di admin panel, mis. "Navbar Utama" |
| `slug` | string | Unik, dipakai kode/Blade untuk memanggil lokasi (`<x-layout.menu location="navbar-utama" />`) |
| `description` | string, nullable | Bantuan konteks bagi admin, mis. "Tampil di header seluruh halaman publik" |
| `created_at` / `updated_at` | timestamp | |

**Validation rules**:
- `slug` unik, format kebab-case, di-generate otomatis dari `name` saat dibuat (dapat diedit manual).
- `name` wajib diisi, tidak boleh kosong.

**Seed default** (via `MenuSeeder`, dijalankan sekali per instalasi klien, bukan demo-only):
- `Navbar Utama` (`navbar-utama`)
- `Footer` (`footer`)

## Entity: MenuItem

Mewakili satu entri navigasi pada suatu lokasi.

| Field | Type | Constraints / Notes |
|---|---|---|
| `id` | bigint PK | |
| `menu_location_id` | bigint FK → `menu_locations.id` | Wajib. Menentukan lokasi tampil (FR-002). |
| `parent_id` | bigint FK → `menu_items.id`, nullable | Untuk sub-menu 1 tingkat (FR-005). Item dengan `parent_id` terisi tidak boleh punya anak sendiri (divalidasi di form, bukan di DB). |
| `label` | string | Teks tampil menu (FR-006). Wajib diisi. |
| `link_type` | enum(`internal`,`external`,`none`) | Menentukan sumber tujuan tautan (FR-007). |
| `linkable_type` | string, nullable | Kelas model target saat `link_type = internal` (morph type), mis. `App\Models\CustomPage`. |
| `linkable_id` | bigint, nullable | ID model target saat `link_type = internal` (morph id). |
| `external_url` | string, nullable | URL tujuan saat `link_type = external`. Wajib diisi & valid URL jika `link_type = external`. |
| `open_in_new_tab` | boolean, default `false` | Berlaku terutama untuk `external`, tapi tersedia untuk semua jenis (FR-012). |
| `order_column` | integer | Urutan tampil dalam grup (`menu_location_id` + `parent_id`) — dipakai fitur reorder Filament (FR-004). |
| `is_active` | boolean, default `true` | Nonaktifkan tanpa hapus (FR-009). |
| `created_at` / `updated_at` | timestamp | |

**Relationships**:
- `MenuItem belongsTo MenuLocation`
- `MenuItem belongsTo MenuItem` (self, `parent_id`) — parent
- `MenuItem hasMany MenuItem` (self, `parent_id`) — children, `orderBy('order_column')`
- `MenuItem morphTo linkable` — target konten internal (mis. `CustomPage`, atau model publik lain yang mengimplementasikan kontrak "punya URL publik", lihat Contracts)

**Validation rules**:
- Jika `link_type = internal`: `linkable_type` + `linkable_id` wajib terisi dan mengarah ke record yang ada saat penyimpanan (validasi existence saat create/update; tautan yang kemudian terhapus ditangani di render time, bukan dicegah retroaktif — lihat research.md #5).
- Jika `link_type = external`: `external_url` wajib diisi, format URL valid (skema `http(s)://`).
- Jika `link_type = none`: `linkable_type`, `linkable_id`, `external_url` semua null — item berfungsi sebagai label grup/dropdown header saja.
- `order_column` di-manage otomatis oleh Filament reorder handle, bukan diinput manual oleh admin.

**State transitions**:
- `is_active`: `true` ⇄ `false` (toggle), tidak menghapus data lain.
- Tidak ada status lain (tidak ada draft/publish workflow — di luar scope spec ini).

## Ringkasan Migrasi

1. `create_menu_locations_table` — kolom sesuai tabel di atas.
2. `create_menu_items_table` — kolom sesuai tabel di atas, dengan index pada (`menu_location_id`, `parent_id`, `order_column`) untuk query render yang efisien, dan index pada (`linkable_type`, `linkable_id`) untuk morph lookup.

## Referensi ke Requirements

- FR-001–FR-002, FR-006, FR-009 → `MenuItem` CRUD + `menu_location_id` + `is_active`.
- FR-003 → `MenuLocation` sebagai data, bukan enum kode.
- FR-004 → `order_column` + Filament reorder.
- FR-005 → `parent_id` self-relation, dibatasi 1 tingkat via validasi form.
- FR-007, FR-008 → `link_type` + morph `linkable` + resolver URL dinamis (lihat contracts/menu-rendering-contract.md).
- FR-011 → penanganan `linkable` null/terhapus saat render (lihat contracts).
- FR-012 → `open_in_new_tab`.
- FR-013 → seluruh data berbasis tabel per-instalasi (tidak ada data lintas klien yang di-share).
