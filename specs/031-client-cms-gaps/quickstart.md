# Quickstart: Verifikasi Spec 031

## Persiapan

```bash
php artisan migrate
npm run build
```

## Pemeriksaan Manual

1. **Header**: buka Tentang Kami, Produk, Artikel, Portofolio, Karir, FAQ, Kontak. Ketujuhnya banner bergambar dengan susunan sama. Ganti gambar banner FAQ dan Kontak di **Banner → Banner Halaman**.
2. **Tipografi**: semua judul dan tombol CTA tampil bold, tidak ada yang lebih tebal.
3. **Beranda**:
   - Tandai 2 produk "Tampilkan di Beranda", lalu cek beranda.
   - Coba tandai produk keempat, harus ditolak.
   - Klik "Isi Form Online", halaman harus menggulir ke kalkulator.
4. **Tentang Kami**: kosongkan kutipan Siapa Kami (blok hilang). Testimoni berjudul "Partner Kami". Dengan 1 anggota tim, section tetap utuh dan rata tengah.
5. **Footer**: ubah Deskripsi Footer di Pengaturan Umum, lalu cek semua halaman.
6. **Produk**: tidak ada filter kategori. Ubah FAQ Produk di menu **FAQ**.
7. **Kontak / FAQ**: ubah FAQ Konsultasi dan FAQ umum, nonaktifkan satu entri.
8. **Karir**: "Posisi Terbuka" rata tengah. Klik kartu lowongan, deskripsi tampil utuh. Lowongan nonaktif harus 404.
9. **Portofolio**: buka "Semua", tambah kategori dan proyek di admin, muat ulang. Data baru langsung tampil.
10. **Artikel**:
    - Buat >6 artikel, coba "Muat lebih banyak" (dengan dan tanpa JS).
    - Uji filter tag dan kategori, juga dikombinasi dengan pencarian.
    - Klik **Preview** pada draf, lalu coba buka URL preview dalam keadaan logout.
    - Pastikan jumlah dilihat bertambah.
    - Isi caption dan produk terkait, lalu cek sidebar detail.

## Pengujian Otomatis

```bash
php artisan test --compact
vendor/bin/pint --dirty --format agent
```
