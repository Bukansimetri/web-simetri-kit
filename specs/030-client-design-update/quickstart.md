# Quickstart: Verifikasi Penyesuaian Desain

## Persiapan

```bash
php artisan migrate
npm run build        # atau: composer run dev
```

Data bawaan hero sudah ditanam migrasi banner halaman. Untuk data contoh: `php artisan db:seed`.

## Pemeriksaan Manual

1. **Beranda** (`/`)
   - "Mengapa Beralih": judul di tengah, tiga kartu sama tinggi, kartu penekanan hanya ber-border biru, tidak ada kartu miring.
   - "Sederhana dan Mulus": empat langkah sejajar, nomor dalam lingkaran, garis putus-putus di layar lebar.
   - "Solusi Untuk Setiap Kebutuhan": tidak berubah.
   - Testimoni: label TESTIMONI, judul "Partner Kami", tiga kartu seragam, bintang bergaya garis.
2. **Footer**: alamat, email, telepon dengan ikon. Kosongkan satu di Pengaturan Situs, barisnya hilang.
3. **Produk** (`/produk`): kartu hanya gambar dan judul rata tengah, kartu dapat diklik, filter kategori masih jalan.
4. **Artikel** (`/artikel`)
   - Hero bergambar dengan "Beranda / Artikel".
   - Grid kartu di kiri, sidebar di kanan (di ponsel sidebar di bawah).
   - Cari kata yang ada dan yang tidak ada. Pesan "tidak ditemukan" dan "Hapus pencarian" muncul untuk yang tidak ada.
   - Langganan: email valid berhasil, email salah ditolak, email sama dua kali tidak membuat duplikat.
5. **Portofolio** (`/portfolio`): hero bergambar, filter pil, kartu dengan kategori, judul, ringkasan, "Lihat Detail Proyek →".
6. **Admin**: ubah gambar dan teks hero Artikel dan Portofolio di Banner Halaman Lain, muat ulang halaman publik (cache publik 5 menit, jalankan `php artisan cache:clear` bila perlu). Menu Langganan Newsletter menampilkan pendaftar.

## Pengujian Otomatis

```bash
php artisan test --compact --filter='HomePageTest|ProductPageTest|ArticlePageTest|PortfolioPageTest|NewsletterSubscribe|NewsletterSubscriberResource|PageBannerRenderTest|LegacyMarkupEquivalenceTest'
vendor/bin/pint --dirty --format agent
```
