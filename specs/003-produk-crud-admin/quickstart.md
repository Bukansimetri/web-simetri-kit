# Quickstart: Verifikasi Manual Produk CRUD Admin

**Feature**: [spec.md](./spec.md) | **Date**: 2026-09-03

## Prasyarat

- `php artisan migrate` sudah dijalankan (migrasi `categories` + perluasan `products`, termasuk backfill data existing dari 002).
- `php artisan db:seed` sudah dijalankan ulang (atau `migrate:fresh --seed`) supaya `CategorySeeder` + `ProductSeeder` versi baru terisi.

## US1 — Kelola Kategori Produk (P1)

1. Login sebagai admin, buka `/admin/categories`. Verifikasi 3 kategori seed (Residensial, Komersial & Industri, Pompa Air) sudah ada.
2. Tambah kategori baru "Aksesoris", simpan. Verifikasi muncul di daftar.
3. Coba tambah kategori dengan nama "Residensial" (duplikat), verifikasi ditolak dengan pesan error jelas.
4. Coba hapus kategori "Residensial" (masih dipakai produk seed), verifikasi ditolak dengan notifikasi yang menjelaskan kategori masih dipakai.
5. Hapus kategori "Aksesoris" yang baru dibuat (belum dipakai produk), verifikasi berhasil terhapus.
6. Ubah urutan kategori, simpan, buka `/produk` di tab baru, verifikasi urutan tab filter berubah.

## US2 — Tambah & Edit Produk (P1) 🎯 MVP

1. Buka `/admin/products`, klik tambah produk baru. Isi nama, pilih kategori, isi harga, deskripsi singkat & lengkap. Kosongkan slug. Simpan.
2. Verifikasi slug ter-generate otomatis dari nama. Buka `/produk` di tab baru, verifikasi produk baru muncul.
3. Edit produk tsb, ubah nama & harga, simpan. Reload `/produk/{slug}`, verifikasi perubahan tampil.
4. Coba buat produk baru dengan slug yang sama persis dengan produk yang sudah ada, verifikasi ditolak.
5. Coba simpan produk tanpa mengisi nama/kategori/harga, verifikasi ditolak dengan pesan error per field.

## US3 — Galeri Gambar Produk (P2)

1. Edit sebuah produk, upload 3 gambar sekaligus di field galeri. Simpan.
2. Buka `/produk/{slug}`, verifikasi ketiga gambar tampil di galeri, dan gambar pertama tampil sebagai gambar utama.
3. Edit lagi, pindahkan gambar ketiga ke posisi pertama (reorder), simpan. Reload `/produk/{slug}` dan `/produk` (kartu produk), verifikasi gambar yang dipindah jadi cover di kedua tempat.
4. Hapus salah satu gambar dari galeri, simpan, verifikasi gambar tsb hilang dari tampilan publik.
5. Buat produk baru tanpa upload gambar sama sekali, verifikasi halaman publik menampilkan placeholder yang wajar (bukan rusak).

## US4 — Spesifikasi Teknis & Fitur Unggulan (P2)

1. Edit sebuah produk, tambah 3 baris spesifikasi (label + nilai) lewat repeater. Simpan.
2. Buka `/produk/{slug}`, verifikasi tabel spesifikasi menampilkan ketiga baris sesuai urutan.
3. Tambah 2 baris fitur unggulan (pilih icon, isi judul & deskripsi), hapus salah satu baris sebelum simpan. Simpan.
4. Verifikasi hanya 1 baris fitur unggulan yang tersisa yang tampil di halaman detail.

## US5 — Urutan Tampil & Hapus Produk (P3)

1. Buka `/admin/products`, ubah `order` dua produk supaya bertukar posisi. Simpan.
2. Buka `/produk` dan Home, verifikasi urutan tampil (termasuk section "Produk Kami") berubah sesuai.
3. Hapus salah satu produk. Verifikasi diminta konfirmasi dulu.
4. Setelah dikonfirmasi, verifikasi produk tsb hilang dari `/produk` dan Home, serta `/produk/{slug-lama}` mengembalikan 404.
