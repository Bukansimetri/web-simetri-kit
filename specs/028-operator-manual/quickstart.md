# Quickstart: Memverifikasi Manual Operator

## 1. Test otomatis

```bash
php artisan test --compact tests/Feature/Docs
```

Mencakup: semua label grup/menu panel ada di manual (tebal), tanpa kode/path/perintah terminal, tanpa nama starter kit atau vendor, tautan dari README dan `docs/arsitektur.md`, tanggal terakhir diperbarui, dan tautan relatif valid.

## 2. Cocokkan label dengan panel (SC-004)

1. Login ke `/admin` sebagai super admin.
2. Untuk setiap subbagian di bagian "Mengelola konten" dan "Pengaturan situs", buka menu terkait dan pastikan nama kolom, tombol, dan status yang disebut manual tampil persis sama di form dan tabel.
3. Pastikan label login, "Profil", "Keluar", dan pita mode pemeliharaan sesuai.

## 3. Uji baca non-teknis (SC-001, SC-002)

1. Siapkan instalasi lokal dengan data contoh (seeder demo).
2. Beri manual ke orang non-teknis yang belum pernah memakai panel.
3. Minta ia menyelesaikan: ganti banner, tambah produk, terbitkan artikel, tambah testimoni, buka lowongan. Target: minimal 4 dari 5 dalam total < 45 menit.
4. Minta ia mencari 3 tugas acak lewat daftar isi. Target: masing-masing < 1 menit.
5. Catat bagian yang membuatnya bingung dan perbaiki manual.

## 4. Cek netral klien (FR-014)

Baca ulang manual: tidak ada nama klien, nama starter kit, data kontak nyata, atau URL domain tertentu.
