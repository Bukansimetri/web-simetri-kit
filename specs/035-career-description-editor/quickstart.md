# Quickstart: Verifikasi Spec 035

## Persiapan

```bash
php artisan migrate
npm run build
```

## Pemeriksaan Manual

1. Admin **Karir → Lowongan**: buka lowongan lama; teksnya utuh dengan baris dan paragraf yang sama di editor.
2. Ubah deskripsi: tambah judul "Kualifikasi", daftar berbutir, teks tebal, dan tautan. Simpan.
3. Buka halaman detail lowongan: format tampil rapi dan tautan bisa diklik.
4. Buka halaman Karir: kartu menampilkan ringkasan dua baris tanpa tanda format.
5. Kosongkan deskripsi dan simpan: form menolak.
6. Lowongan lama (belum diubah) di halaman detail tampil seperti sebelumnya.
7. Cek lebar 360 px: tanpa gulir horizontal.

## Pengujian Otomatis

```bash
php artisan test --compact
vendor/bin/pint --dirty --format agent
```
