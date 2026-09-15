# Quickstart: Verifikasi Manual Banner Hero Slider

**Feature**: [spec.md](./spec.md) | **Date**: 2026-09-14

## Prasyarat

- `php artisan migrate` sudah dijalankan (migrasi `add_hero_fields_to_banners_table`).
- `npm run dev` berjalan, atau `npm run build` sudah dijalankan — komponen slider dan animasi penanda berada di bundle.
- Login sebagai admin dengan akses panel.

## US1 — Slide hero utuh, bukan gambar polos (P1) 🎯 MVP

1. Buka `/admin/banners`. Bila sudah ada banner dari sebelumnya, verifikasi banner dengan urutan terkecil kini **sudah terisi** judul "Nyalakan Rumah & Bisnis Anda dengan Energi Matahari" beserta subjudul, dua CTA, dan trust bar hasil backfill.
2. Buka beranda. Verifikasi banner tersebut tampil sebagai hero utuh — badge, judul, subjudul, dua tombol, dan trust bar — bukan gambar polos.
3. Kembali ke admin, buat banner baru: unggah gambar, isi judul internal dan teks alt, isi Judul Slide "Promo Instalasi Oktober", subjudul bebas, CTA utama "Ambil Penawaran" → `/kontak`, biarkan CTA sekunder kosong. Simpan.
4. Buka beranda **segera** setelah menyimpan. Verifikasi perubahan langsung terlihat tanpa menunggu — ini menguji invalidasi cache (FR-018).
5. Verifikasi slide baru menampilkan satu tombol saja, tanpa celah tombol kedua.
6. Edit banner tersebut, isi label CTA sekunder "Lihat Portfolio" tetapi **kosongkan** alamatnya. Simpan → verifikasi ditolak dengan pesan pada field alamat.
7. Buat banner ketiga berisi **gambar saja** tanpa satu pun teks. Nonaktifkan dua banner lain sementara, buka beranda → verifikasi tampil gambar penuh tanpa blok teks kosong yang menggantung, dan tanpa panah karena hanya satu slide.
8. Aktifkan kembali banner lain.

## US2 — Menelusuri beberapa slide (P1)

1. Pastikan ada **tiga** banner tayang. Buka beranda.
2. Verifikasi slide dengan urutan terkecil yang tampil lebih dulu, disertai panah kiri/kanan, tiga titik navigasi, dan penanda posisi "1 / 3".
3. Gulir sehingga slider keluar layar lalu kembali masuk. Verifikasi panah memainkan animasi penanda singkat lalu **berhenti dengan sendirinya** — bukan berdenyut terus-menerus.
4. Diamkan halaman satu menit tanpa menyentuh apa pun. Verifikasi slide **tidak** berpindah sendiri.
5. Tekan panah kanan tiga kali dari slide pertama → verifikasi kembali ke slide pertama (melingkar).
6. Tekan titik navigasi ketiga → verifikasi langsung melompat ke slide ketiga dan titik ketiga ditandai aktif.
7. Klik area slider lalu tekan tombol panah kiri/kanan pada papan ketik → verifikasi slide berpindah.
8. Tekan `Tab` berulang dari header hingga melewati slider → verifikasi fokus **hanya** singgah pada tombol CTA slide yang sedang tampil, tidak pernah melompat ke tombol slide tersembunyi.
9. Perkecil jendela hingga lebar ponsel (atau buka di ponsel). Verifikasi panah tersembunyi dan muncul petunjuk "Geser untuk melihat lainnya" beranimasi.
10. Geser slide ke samping → verifikasi slide berpindah dan petunjuk geser hilang permanen. Geser ke atas/bawah → verifikasi halaman tetap menggulir normal.
11. Aktifkan pengurangan gerak di sistem operasi (macOS: Pengaturan Sistem → Aksesibilitas → Tampilan → Kurangi gerak). Muat ulang beranda → verifikasi tidak ada animasi penanda maupun transisi, tetapi panah, titik, dan geser tetap berfungsi.

## US3 — Preset tampilan (P2)

1. Siapkan satu banner bergambar **terang** dan satu bergambar **gelap**.
2. Banner bergambar terang: pilih Tampilan → lapisan gelap, posisi teks kiri. Simpan.
3. Banner bergambar gelap: pilih lapisan terang, posisi teks tengah. Simpan.
4. Buka beranda dan telusuri kedua slide. Verifikasi teks terbaca jelas pada keduanya, blok teks slide kedua berada di tengah, dan arah gradasi lapisan mengikuti posisi teks.
5. Ubah satu slide ke "Tanpa lapisan" → verifikasi teks tetap terbaca berkat bayangan teks.
6. Verifikasi dropdown Tampilan tidak menyediakan input warna bebas — hanya daftar preset.
7. Buka pengaturan tema, ganti warna primer brand, simpan. Kembali ke beranda → verifikasi warna tombol pada **seluruh** slide ikut berubah tanpa mengedit banner satu per satu.

## US4 — Trust bar (P3)

1. Edit satu banner, pada Trust Bar ketik "Dipercaya 500+ pelanggan" lalu sisipkan sebuah gambar lewat tombol gambar di editor. Simpan.
2. Verifikasi berkas gambar tersimpan di `storage/app/public/banners/trust/` berformat `.webp`.
3. Buka beranda → verifikasi teks dan gambar tampil di bawah tombol pada slide tersebut saja.
4. Kosongkan trust bar pada slide lain → verifikasi area tersebut tidak dirender dan tidak menyisakan garis pemisah atau ruang kosong.

## Kasus batas

1. **Semua banner nonaktif** → beranda menampilkan hero bawaan dengan teks default, bukan layar kosong.
2. **Berkas gambar hilang**: hapus manual satu berkas dari `storage/app/public/banners/` tanpa menghapus recordnya → muat ulang beranda, verifikasi slide tersebut dilewati, slide lain tetap tampil, dan jumlah titik navigasi menyesuaikan.
3. **Judul sangat panjang**: isi judul mendekati batas 160 karakter → verifikasi tidak menutupi tombol maupun mengubah tinggi slide.
4. **Banner warisan**: buat banner tanpa CTA namun dengan URL Tautan terisi → verifikasi seluruh area gambar dapat diklik.
5. **Tautan majemuk**: pada banner yang sama, tambahkan CTA utama → verifikasi kini hanya tombol yang dapat diklik dan gambar tidak lagi terbungkus tautan.
6. **Gambar potret**: unggah gambar berorientasi potret → verifikasi tinggi slide tetap konsisten dan gambar tidak gepeng.
7. **Tanpa JavaScript**: matikan JavaScript di peramban, muat beranda → verifikasi slide pertama tetap tampil lengkap dengan teks dan tombol.

## Verifikasi otomatis

```bash
php artisan test --compact --filter=Banner
php artisan test --compact --filter=HtmlSanitizer
php artisan test --compact            # seluruh suite
vendor/bin/pint --dirty --format agent
```

## Catatan rollback

`php artisan migrate:rollback` pada migrasi ini membuang kesepuluh kolom baru. **Seluruh konten slide — judul, subjudul, CTA, trust bar, preset tampilan — akan hilang permanen**; gambar, teks alt, periode tayang, dan urutan tetap aman. Cadangkan basis data sebelum rollback di lingkungan yang sudah berisi konten asli.
