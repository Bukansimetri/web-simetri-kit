# Contract: Admin Panel Surface (Lowongan Kerja & Toggle Modul Karir)

**Feature**: [spec.md](../spec.md) | **Date**: 2026-09-05

Fitur ini tidak mengekspos API publik. "Kontrak" di sini adalah permukaan admin panel yang harus konsisten dipenuhi implementasi, supaya bisa diverifikasi lewat feature test tanpa bergantung pada detail implementasi Filament.

## 1. Lowongan Kerja (User Story 1)

| Aspek | Kontrak |
|---|---|
| Route | `/admin/job-openings` (Filament Resource: index, create, edit) |
| Akses | Semua role dengan akses panel admin (FR-008) — SELALU dapat diakses terlepas dari status toggle modul Karir (FR-013) |
| Field wajib | `title`, `location`, `employment_type`, `description` — submit tanpa salah satunya MUST ditolak dengan pesan error per field (FR-003) |
| Tipe pekerjaan | `employment_type` MUST berupa `Select` dengan pilihan tetap (Full-time/Part-time/Kontrak/Magang) — bukan input teks bebas (FR-007) |
| Status aktif | `is_active` MUST bisa diubah lewat form maupun aksi cepat (toggle) di tabel, tanpa harus menghapus/membuat ulang record (FR-006) |
| Hapus lowongan | MUST minta konfirmasi sebelum diproses (FR-005); record TIDAK lagi muncul di listing admin maupun `/karir` setelahnya |
| Efek simpan ke publik | Create/update/delete/toggle `is_active` MUST tercermin di `/karir` pada request berikutnya, tanpa deploy ulang (FR-004) |

## 2. Toggle Modul Karir (User Story 2)

| Aspek | Kontrak |
|---|---|
| Lokasi | Field baru di halaman **Brand Settings** yang sudah ada (`/admin/brand-settings-page` atau route setara) — BUKAN halaman admin baru (FR-014, Clarifications) |
| Field | `Toggle::make('career_module_enabled')`, default `true` |
| Efek simpan | Mengubah nilai dan menyimpan MUST langsung berlaku untuk request publik berikutnya (FR-009 s.d. FR-011), tanpa cache yang perlu di-clear manual |
| Independensi data | Menonaktifkan/mengaktifkan toggle ini MUST TIDAK mengubah/menghapus baris `job_openings` manapun (FR-012) |

## 3. Efek ke Frontend Publik (turunan dari toggle)

| Aspek | Kontrak |
|---|---|
| Modul aktif (default) | `GET /karir` MUST 200 dengan daftar lowongan `is_active = true`; link "Karir" MUST tampil di footer setiap halaman publik |
| Modul nonaktif | `GET /karir` MUST 404; link "Karir" MUST TIDAK muncul di footer halaman publik manapun (FR-010, FR-011) |
| Lowongan nonaktif per-item (`is_active = false`) | TIDAK tampil di `/karir` terlepas dari status toggle modul — independen (FR-006 vs FR-009) |
| Blog/halaman karir kosong (modul aktif, 0 lowongan aktif) | `GET /karir` MUST tetap 200 dengan empty state wajar (perilaku sudah ada dari 002) |
