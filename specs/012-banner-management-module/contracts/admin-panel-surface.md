# Contract: Admin Panel Surface & Public Rendering (Banner Management)

**Feature**: [spec.md](../spec.md) | **Date**: 2026-09-10

Fitur ini tidak mengekspos API publik. "Kontrak" di sini adalah permukaan admin panel dan perilaku rendering banner di posisi hero beranda yang harus dipenuhi implementasi, agar terverifikasi lewat feature/unit test.

## 1. Banner CRUD (User Story 1)

| Aspek | Kontrak |
|---|---|
| Route admin | `/admin/banners` (Filament Resource: index, create, edit) |
| Navigasi | Label menu "Banner" |
| Akses | Semua role dengan akses panel admin (FR-014) — tanpa policy khusus |
| Field wajib | `title`, `image_path`, `alt_text` — submit tanpa salah satunya MUST ditolak dengan error per field (FR-003) |
| Gambar | `FileUpload` gambar, WAJIB; diproses saat simpan → disimpan `.webp`, lebar ≤1600px (downscale bila lebih besar, tanpa upscale) (FR-006); form menampilkan helper rekomendasi ukuran (FR-006) |
| Link | `link_url` opsional; bila diisi MUST URL absolut http/https — nilai tak valid MUST ditolak (FR-004) |
| Periode | `starts_at`, `ends_at` opsional (date). Bila keduanya diisi dan `ends_at < starts_at` → submit MUST ditolak (FR-005) |
| Urutan / Aktif | `order` integer default 0; `is_active` boolean default true, dapat di-toggle (FR-007) |
| Kolom status | Tabel admin MUST menampilkan kolom status tayang per baris: "Tayang" / "Terjadwal" / "Kedaluwarsa" / "Nonaktif" (FR-013) sesuai `displayStatus()` |
| Hapus | MUST minta konfirmasi sebelum diproses (FR-009); setelah dihapus record hilang dari daftar admin & beranda |
| Efek simpan ke publik | Create/update/delete/toggle/ubah periode MUST tercermin di beranda pada request berikutnya tanpa deploy ulang (FR-010) |

## 2. Aturan Tayang (`scopeLive`) — Unit Contract

| Skenario banner | `is_active` | `starts_at` | `ends_at` | Tayang? |
|---|---|---|---|---|
| Tanpa periode | true | null | null | ✅ Ya |
| Periode mencakup hari ini | true | kemarin | besok | ✅ Ya |
| Mulai = hari ini | true | hari ini | null | ✅ Ya (inklusif) |
| Selesai = hari ini | true | null | hari ini | ✅ Ya (inklusif) |
| Sudah kedaluwarsa | true | −10 hari | −1 hari | ❌ Tidak |
| Belum mulai | true | +1 hari | +10 hari | ❌ Tidak |
| Nonaktif meski periode berlaku | false | kemarin | besok | ❌ Tidak |

`displayStatus()` MUST konsisten: mengembalikan `'live'` tepat untuk baris ✅ di atas, dan `'inactive'`/`'scheduled'`/`'expired'` sesuai untuk baris ❌.

## 3. Rendering di Posisi Hero Beranda (User Story 2)

| Aspek | Kontrak |
|---|---|
| Halaman | `GET /` — area banner menempati posisi section Hero (FR-015) |
| Sumber data | `Banner::live()->get()` — hanya banner tayang, urут `order` asc lalu `id` asc |
| ≥1 banner tayang | Render `<x-sections.banner-carousel :banners>` di posisi hero; Hero statis TIDAK dirender |
| >1 banner tayang | Carousel dengan kontrol navigasi (dot indicators dan/atau prev/next) + auto-rotate; giliran sesuai `order` (FR-016) |
| Tepat 1 banner tayang | Banner dirender tunggal (gambar) tanpa kontrol carousel (FR-016) |
| Konten per banner | `<img>` dengan `alt` = `alt_text`; bila `link_url` ada → dibungkus `<a href="{link_url}">` (tab yang sama); bila tidak → `<img>` biasa (FR-011) |
| Banner file hilang di storage | Slot MUST dilewati (tidak render `<img>` rusak) — edge case |
| 0 banner tayang | Beranda MUST merender `<x-sections.hero />` statis di posisi hero — TIDAK ada ruang kosong (FR-012, SC-005) |
| Section beranda lain | how-it-works, produk, CTA, dll MUST tidak berubah selain penggantian hero (FR-012, Assumptions) |
| Halaman publik lain | TIDAK menampilkan banner (FR-015) |
