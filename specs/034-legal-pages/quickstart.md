# Quickstart: Verifikasi Spec 034

## Persiapan

```bash
php artisan migrate
npm run build
```

## Pemeriksaan Manual

1. Klik "Kebijakan Privasi" dan "Syarat & Ketentuan" di footer: keduanya tampil sesuai desain, tanpa label tanggal.
2. Klik butir daftar isi: halaman menggulir ke bagian itu dan butir ditandai aktif; gulir manual memindahkan penanda.
3. Di admin **Konten Halaman → Halaman**, buka "Syarat & Ketentuan":
   - ubah subjudul
   - tambah satu pasal
   - seret urutan pasal
   - hapus satu kartu garansi

   Simpan, lalu cek situs.
4. Unggah PDF: tombol unduh muncul. Hapus PDF: tombol hilang.
5. Kosongkan Nomor WhatsApp di Pengaturan Umum: tombol WhatsApp di kotak kontak hilang.
6. Buka halaman kustom lama (template Standar): tampilan tidak berubah.
7. Buka halaman FAQ: berisi 5 pertanyaan awal (bila sebelumnya kosong).
8. Cek di lebar 360 px: daftar isi di atas isi, tanpa gulir horizontal.

## Pengujian Otomatis

```bash
php artisan test --compact
vendor/bin/pint --dirty --format agent
```
