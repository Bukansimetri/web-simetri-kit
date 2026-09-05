# Quickstart: Verifikasi Manual Career CRUD Admin + Toggle Modul

**Feature**: [spec.md](./spec.md) | **Date**: 2026-09-05

## Prasyarat

- `php artisan migrate` sudah dijalankan (tidak ada migrasi skema tabel baru — hanya migration `database/settings/` untuk `career_module_enabled`).
- `php artisan db:seed` sudah dijalankan (memakai `JobOpeningSeeder` yang sudah ada).

## US1 — Tulis & Edit Lowongan Kerja (P1) 🎯 MVP

1. Login sebagai admin, buka `/admin/job-openings`. Verifikasi 3 lowongan seed sudah ada.
2. Tambah lowongan baru: judul "QA Engineer", lokasi "Jakarta", tipe pekerjaan "Full-time" (dari dropdown, bukan teks bebas), deskripsi, status Aktif. Simpan.
3. Buka `/karir`, verifikasi lowongan "QA Engineer" muncul.
4. Edit lowongan tsb, ubah judul jadi "Senior QA Engineer", simpan. Reload `/karir`, verifikasi perubahan tampil.
5. Nonaktifkan lowongan tsb (toggle `is_active` di tabel/form), simpan. Reload `/karir`, verifikasi TIDAK lagi muncul — tapi masih ada di `/admin/job-openings`.
6. Coba simpan lowongan baru tanpa mengisi judul/lokasi/tipe/deskripsi, verifikasi ditolak dengan pesan error per field.
7. Hapus lowongan "Senior QA Engineer", verifikasi hilang dari admin dan `/karir`.

## US2 — Toggle Modul Karir per Klien (P1)

1. Buka `/karir` sebagai pengunjung (belum login), verifikasi halaman tampil normal (200) dan link "Karir" ada di footer.
2. Login sebagai admin, buka halaman **Brand Settings**, matikan toggle "Modul Karir Aktif" (`career_module_enabled`), simpan.
3. Buka `/karir` sebagai pengunjung, verifikasi sekarang mengembalikan 404.
4. Buka halaman publik lain (mis. beranda `/`), verifikasi link "Karir" TIDAK lagi muncul di footer.
5. Buka `/admin/job-openings` sebagai admin, verifikasi menu & data lowongan TETAP bisa diakses/di-edit seperti biasa (FR-013) meskipun modul nonaktif untuk publik.
6. Nyalakan kembali toggle "Modul Karir Aktif" di Brand Settings, simpan.
7. Buka `/karir` lagi, verifikasi 200 dan seluruh data lowongan yang aktif (termasuk yang dibuat/diedit sebelumnya) tampil kembali tanpa perlu diinput ulang. Verifikasi link "Karir" muncul lagi di footer.
