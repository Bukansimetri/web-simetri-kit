# Quickstart: Verifikasi Manual Artikel CRUD Admin

**Feature**: [spec.md](./spec.md) | **Date**: 2026-09-05

## Prasyarat

- `php artisan migrate` sudah dijalankan (migrasi `article_categories`, `tags`/`taggables` dari package, perluasan `articles`).
- `php artisan db:seed` (atau `migrate:fresh --seed`) sudah dijalankan ulang supaya `ArticleCategorySeeder` + `ArticleSeeder` versi baru terisi.

## US1 — Kelola Kategori Artikel (P1)

1. Login sebagai admin, buka `/admin/article-categories`. Verifikasi 3 kategori seed (Tips, Berita, Edukasi) sudah ada.
2. Tambah kategori baru "Studi Kasus", simpan. Verifikasi muncul di daftar.
3. Coba tambah kategori dengan nama "Tips" (duplikat), verifikasi ditolak.
4. Coba hapus kategori "Tips" (masih dipakai artikel seed), verifikasi ditolak dengan notifikasi jelas.
5. Hapus kategori "Studi Kasus" yang baru dibuat (belum dipakai), verifikasi berhasil.

## US2 — Tulis & Edit Artikel (P1) 🎯 MVP

1. Buka `/admin/articles`, klik tambah artikel baru. Isi judul, pilih kategori, isi ringkasan, isi "Redaksi" (mis. "Tim Redaksi SUOER"), tulis isi lewat rich text editor (coba bold/heading/link). Kosongkan slug. Simpan sebagai Publish (tanggal hari ini).
2. Verifikasi slug ter-generate otomatis. Buka `/artikel`, verifikasi artikel baru muncul; buka detailnya, verifikasi format teks (bold/heading/link) tampil sesuai yang ditulis, dan "Tim Redaksi SUOER" tampil sebagai byline.
3. Edit artikel tsb, ubah judul, simpan. Reload `/artikel/{slug}`, verifikasi perubahan tampil.
4. Coba buat artikel baru dengan slug yang sama persis dengan artikel yang sudah ada, verifikasi ditolak.
5. Coba simpan artikel tanpa mengisi judul/kategori/ringkasan/isi, verifikasi ditolak dengan pesan error per field.

## US3 — Draft, Publish, dan Jadwal (P1)

1. Buat artikel baru, pilih "Draft" (jangan isi tanggal publish). Simpan.
2. Buka `/artikel`, verifikasi artikel draft TIDAK muncul. Coba akses `/artikel/{slug-draft}` langsung, verifikasi 404.
3. Edit artikel tsb, ubah jadi "Publish" dengan tanggal hari ini. Simpan. Reload `/artikel`, verifikasi sekarang muncul.
4. Buat artikel baru lagi, pilih "Publish" dengan tanggal publish BESOK (masa depan). Simpan.
5. Buka `/artikel`, verifikasi artikel tsb belum muncul (karena tanggalnya belum tiba). (Verifikasi otomatis-muncul saat tanggal tiba bisa disimulasikan lewat test otomatis, bukan manual real-time.)

## US4 — Tag Artikel (P2)

1. Edit sebuah artikel, tambahkan 2 tag baru (mis. "panel-surya", "hemat-listrik") lewat input tag. Simpan.
2. Buka `/admin/articles` → edit artikel lain, verifikasi kedua tag tsb muncul sebagai saran/pilihan (bukan perlu diketik ulang dari nol).
3. Pasang salah satu tag yang sudah ada ke artikel kedua ini, simpan. Verifikasi tidak ada duplikat tag baru tercipta.
4. Kembali ke artikel pertama, lepas satu tag, simpan. Verifikasi tag tsb hilang dari artikel pertama tapi masih ada/tersedia untuk artikel lain.

## US5 — Featured Image (P2)

1. Edit sebuah artikel, upload gambar (JPG/PNG apa saja). Verifikasi ada teks rekomendasi dimensi (mis. "1200×630px") di dekat field upload, dan file BUKAN dimensi tsb tetap diterima (tidak ada penolakan).
2. Simpan, lalu cek file yang tersimpan di storage — verifikasi file berformat `.webp` (bukan `.jpg`/`.png` asli).
3. Buka `/artikel` dan `/artikel/{slug}`, verifikasi gambar tampil.
4. Buat artikel baru TANPA upload featured image, verifikasi halaman publik menampilkan placeholder wajar (bukan gambar rusak).
