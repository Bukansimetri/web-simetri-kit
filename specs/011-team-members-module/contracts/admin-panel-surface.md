# Contract: Admin Panel Surface & Public Rendering (Team Members)

**Feature**: [spec.md](../spec.md) | **Date**: 2026-09-08

Fitur ini tidak mengekspos API publik. "Kontrak" di sini adalah permukaan admin panel dan perilaku rendering section "Tim Kami" di halaman Tentang Kami yang harus dipenuhi implementasi, agar terverifikasi lewat feature test tanpa bergantung pada detail internal Filament.

## 1. TeamMember CRUD (User Story 1)

| Aspek | Kontrak |
|---|---|
| Route admin | `/admin/team-members` (Filament Resource: index, create, edit) |
| Navigasi | Label menu "Tim" |
| Akses | Semua role dengan akses panel admin (FR-012) — tanpa policy khusus |
| Field wajib | `name`, `position`, `photo_path`, `bio` — submit tanpa salah satunya MUST ditolak dengan error per field (FR-003) |
| Foto | `FileUpload` gambar, WAJIB; diproses saat simpan → disimpan `.webp`, lebar ≤800px (downscale bila lebih besar, tanpa upscale) (FR-005); form menampilkan helper rekomendasi ukuran (FR-005) |
| LinkedIn | `linkedin_url` opsional; kosong MUST diterima tanpa error (FR-003); bila diisi MUST URL absolut http/https — nilai tak valid (`linkedin.com/...`, `javascript:...`) MUST ditolak (FR-004) |
| Bio | `bio` teks biasa (textarea), wajib |
| Urutan / Aktif | `order` integer default 0; `is_active` boolean default true, dapat di-toggle (FR-006, FR-007) |
| Hapus | MUST minta konfirmasi sebelum diproses (FR-008); setelah dihapus record hilang dari daftar admin & halaman publik |
| Efek simpan ke publik | Create/update/delete/toggle MUST tercermin di `/tentang-kami` pada request berikutnya tanpa deploy ulang (FR-009) |

## 2. Rendering Section "Tim Kami" di Halaman Tentang Kami (User Story 2)

| Aspek | Kontrak |
|---|---|
| Halaman | `GET /tentang-kami` — section "Tim Kami" disisipkan **setelah** section "Nilai-Nilai Kami" dan **sebelum** section testimoni (modul 008) (FR-013) |
| Sumber data | `TeamMember` dengan `is_active = true`, diurutkan `order` asc lalu `id` asc (FR-006, FR-007) |
| Konten per item | foto, `name`, `position`, `bio`; bila `linkedin_url` ada → tautan ikon LinkedIn `target="_blank" rel="noopener noreferrer nofollow"`; bila tidak → tanpa ikon/area LinkedIn (FR-010) |
| Anggota nonaktif | MUST tidak tampil (FR-007) |
| Foto file hilang di storage | Slot MUST tidak menampilkan gambar rusak — placeholder inisial (edge case) |
| Tidak ada anggota aktif | Section "Tim Kami" MUST tidak dirender sama sekali — tidak ada heading/area kosong; section Tentang Kami lain (hero, visi, misi, nilai, testimoni, logo klien, CTA) MUST tetap tampil (FR-011, SC-005) |
| Halaman Tentang Kami lainnya | Section eksisting MUST tidak berubah selain penyisipan section "Tim Kami" (FR-013, Assumptions) |
