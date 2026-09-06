# Contract: Admin Panel Surface & Public Rendering (Client Logos)

**Feature**: [spec.md](../spec.md) | **Date**: 2026-09-07

Fitur ini tidak mengekspos API publik. "Kontrak" di sini adalah permukaan admin panel dan perilaku rendering logo strip di halaman Tentang Kami yang harus dipenuhi implementasi, agar terverifikasi lewat feature test tanpa bergantung pada detail internal Filament.

## 1. ClientLogo CRUD (User Story 1)

| Aspek | Kontrak |
|---|---|
| Route admin | `/admin/client-logos` (Filament Resource: index, create, edit) |
| Navigasi | Label menu "Logo Klien" |
| Akses | Semua role dengan akses panel admin (FR-011) — tanpa policy khusus |
| Field wajib | `company_name`, `logo_path` — submit tanpa salah satunya MUST ditolak dengan error per field (FR-003) |
| Logo | `FileUpload` gambar; dikonversi ke `.webp` via `ImageUploads::storeAsWebp` ke `public/client-logos/` |
| Link | `link_url` opsional; bila diisi MUST berupa URL absolut http/https — nilai tak valid (mis. `contoh.com`, `javascript:...`) MUST ditolak (FR-004) |
| Urutan | `order` integer, default 0; dapat diedit admin (FR-005) |
| Aktif | `is_active` boolean, default true; dapat di-toggle admin (FR-006) |
| Hapus | MUST minta konfirmasi sebelum diproses (FR-007); setelah dihapus record hilang dari daftar admin & halaman publik |
| Efek simpan ke publik | Create/update/delete/toggle MUST tercermin di `/tentang-kami` pada request berikutnya tanpa deploy ulang (FR-008) |

## 2. Rendering Logo Strip di Halaman Tentang Kami (User Story 2)

| Aspek | Kontrak |
|---|---|
| Halaman | `GET /tentang-kami` — logo strip disisipkan **setelah** section testimoni (modul 008) dan **sebelum** CTA band (FR-012) |
| Sumber data | `ClientLogo` dengan `is_active = true`, diurutkan `order` asc lalu `id` asc (FR-005, FR-006) |
| Konten per item | `<img>` logo dengan `alt` = `company_name`; bila `link_url` ada → dibungkus `<a href="{link_url}" target="_blank" rel="noopener noreferrer nofollow">`; bila tidak → `<img>` biasa tanpa tautan (FR-009) |
| Logo nonaktif | MUST tidak tampil (FR-006) |
| Logo dengan file hilang di storage | Slot MUST tidak menampilkan gambar rusak (disembunyikan) — edge case |
| Tidak ada logo aktif | Logo strip MUST tidak dirender sama sekali — tidak ada heading/area kosong; section Tentang Kami lain (termasuk testimoni) MUST tetap tampil (FR-010, SC-005) |
| Halaman Tentang Kami lainnya | Section eksisting (hero, visi, misi, nilai, testimoni, CTA) MUST tidak berubah selain penyisipan logo strip (FR-012, Assumptions) |
