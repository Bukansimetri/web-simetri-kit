# Quickstart: Memverifikasi Dokumentasi Teknis

Cara memverifikasi fitur ini setelah diimplementasikan.

## 1. Verifikasi otomatis

```bash
php artisan test --compact tests/Feature/Docs/TechnicalDocsPathsTest.php
```

Harus lulus: semua path inline di `docs/arsitektur.md`, `docs/panduan-section.md`, `docs/panduan-tema.md` ada di repo, dan semua tautan relatif antar dokumen valid.

## 2. Uji baca arsitektur (US1, SC-001)

Minta developer yang belum mengenal project membaca `docs/arsitektur.md`, lalu menunjukkan lokasi kode untuk:

1. Mengubah tampilan kartu produk.
2. Menambah field di pengaturan situs.
3. Menambah menu di panel admin.
4. Mengubah route halaman publik.
5. Mengubah teks email notifikasi kontak.

Lulus jika minimal 4 dari 5 benar dalam waktu kurang dari 30 menit.

## 3. Uji panduan section (US2, SC-002)

Developer mengikuti `docs/panduan-section.md` untuk menambah section contoh di lokal:

- Section tampil di halaman tujuan.
- Ubah warna primer di admin (**Pengaturan Situs → Tampilan**), section ikut berubah.
- Kosongkan data section, halaman tidak error.
- Test section lulus.

Lulus jika selesai dalam waktu kurang dari 2 jam tanpa bertanya. Buang perubahan contoh setelah uji (`git checkout .`).

## 4. Uji panduan tema (US3, SC-003)

Developer mengikuti `docs/panduan-tema.md` untuk menambah satu font:

- Font muncul di dropdown halaman Tampilan.
- Setelah dipilih dan `npm run build`, halaman publik memakai font tersebut (cek di DevTools, tab Network/Computed).

Lulus jika selesai dalam waktu kurang dari 30 menit. Buang perubahan setelah uji.

## 5. Cek tautan & duplikasi (FR-013, SC-005)

- README memuat bagian "Dokumentasi Developer" yang menautkan ketiga dokumen.
- Tidak ada paragraf dari `docs/deployment.md` atau dokumen `docs/` lain yang disalin ulang.
