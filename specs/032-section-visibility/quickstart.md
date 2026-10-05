# Quickstart: Verifikasi Spec 032

## Persiapan

```bash
php artisan migrate
npm run build
```

## Pemeriksaan Manual

1. Buka **Pengaturan Situs → Tampilan Section**. Semua toggle menyala, situs tidak berubah.
2. Matikan **Beranda → Testimoni**, lalu simpan.
   - Beranda tanpa testimoni.
   - Tentang Kami masih menampilkan testimoni.
   - Menu **Testimoni** menampilkan penanda.
3. Matikan **Tentang Kami → Siapa Kami** dan **Produk → CTA Kalkulator**.
   - Kedua section hilang.
   - Menu **Blok Halaman** dan **CTA** memberi badge "Disembunyikan" pada baris terkait.
4. Nyalakan kembali semuanya. Isi tampil sama persis dan penanda hilang.
5. Klik "Edit isi" di salah satu baris. Menu isi section terbuka (FAQ terfilter ke tempat yang benar).

## Pengujian Otomatis

```bash
php artisan test --compact
vendor/bin/pint --dirty --format agent
```
