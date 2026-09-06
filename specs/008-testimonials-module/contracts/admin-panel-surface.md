# Contract: Admin Panel Surface & Public Rendering (Testimonials)

**Feature**: [spec.md](../spec.md) | **Date**: 2026-09-07

Fitur ini tidak mengekspos API publik. "Kontrak" di sini adalah permukaan admin panel dan perilaku rendering di halaman Tentang Kami yang harus dipenuhi implementasi, agar terverifikasi lewat feature test tanpa bergantung pada detail internal Filament.

## 1. Testimonial CRUD (User Story 1)

| Aspek | Kontrak |
|---|---|
| Route admin | `/admin/testimonials` (Filament Resource: index, create, edit) |
| Navigasi | Label menu "Testimoni" |
| Akses | Semua role dengan akses panel admin (FR-012) — tanpa policy khusus |
| Field wajib | `name`, `content`, `rating` — submit tanpa salah satunya MUST ditolak dengan error per field (FR-003) |
| Rating | Hanya menerima bilangan bulat 1–5; nilai 0/6/non-integer MUST ditolak (FR-004) |
| Atribusi | `attribution` opsional (satu field teks bebas) — kosong MUST tetap valid (FR-002) |
| Foto | `photo_path` opsional; upload dikonversi ke `.webp` via `ImageUploads::storeAsWebp` ke `public/testimonials/`; tanpa foto MUST tetap valid (FR-005) |
| Urutan | `order` integer, default 0; dapat diedit admin (FR-006) |
| Aktif | `is_active` boolean, default true; dapat di-toggle admin (FR-007) |
| Hapus | MUST minta konfirmasi sebelum diproses (FR-008); setelah dihapus record hilang dari daftar admin & halaman publik |
| Efek simpan ke publik | Create/update/delete/toggle MUST tercermin di `/tentang-kami` pada request berikutnya tanpa deploy ulang (FR-009) |

## 2. Rendering Section di Halaman Tentang Kami (User Story 2)

| Aspek | Kontrak |
|---|---|
| Halaman | `GET /tentang-kami` — section testimoni disisipkan setelah "Nilai-Nilai Kami", sebelum CTA band (FR-013) |
| Sumber data | `Testimonial` dengan `is_active = true`, diurutkan `order` asc lalu `id` asc (FR-006, FR-007) |
| Konten per item | nama, atribusi (bila ada), isi testimoni, rating sebagai bintang terisi dari 5, foto (bila ada) atau placeholder inisial (FR-010) |
| Testimoni nonaktif | MUST tidak tampil (FR-007) |
| Testimoni tanpa foto | MUST tampil rapi dengan inisial, tanpa `<img>` rusak (FR-005, SC-006) |
| Tidak ada testimoni aktif | Section testimoni MUST tidak dirender sama sekali — tidak ada heading/area kosong; section Tentang Kami lain (hero, visi, misi, nilai, CTA) MUST tetap tampil (FR-011, SC-005) |
| Halaman Tentang Kami lainnya | Section eksisting MUST tidak berubah selain penyisipan section testimoni (FR-013, Assumptions) |
