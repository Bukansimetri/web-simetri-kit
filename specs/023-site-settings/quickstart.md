# Quickstart: Site Settings

**Feature**: 023-site-settings | **Date**: 2026-09-23

Cara memverifikasi fitur ini secara manual setelah diimplementasikan. Urutannya mengikuti tahap pengerjaan di [plan.md](./plan.md).

---

## Persiapan

```bash
git checkout 023-site-settings
composer install && npm install
php artisan migrate --seed
npm run dev
```

Masuk panel admin sebagai `test@example.com` (peran `super_admin` dari `DatabaseSeeder`).

Untuk menguji pembatasan akses pada tahap 1, siapkan satu pengguna admin **tanpa** peran `super_admin`.

---

## Tahap 0 — Fondasi: pembuktian bahwa tidak ada yang berubah

Tahap ini tidak menambah fitur. Keberhasilannya justru ditandai oleh **tidak adanya perubahan apa pun** yang terlihat.

1. Sebelum menjalankan migrasi, simpan keluaran halaman beranda:

   ```bash
   curl -s http://localhost:8000/ > /tmp/before.html
   ```

2. Jalankan migrasi settings, lalu ambil ulang:

   ```bash
   php artisan migrate
   curl -s http://localhost:8000/ > /tmp/after.html
   diff /tmp/before.html /tmp/after.html
   ```

   **Harapan**: tidak ada selisih (FR-069, SC-011).

3. Buka panel admin. **Harapan**: halaman Brand Settings sudah tidak ada, dan lima halaman pengaturan baru muncul (FR-063, FR-070).

4. Periksa nilai lama berpindah utuh — logo, warna, font, nomor WhatsApp, dan email notifikasi harus sudah terisi di halaman barunya tanpa pernah diisi ulang (FR-068).

5. `php artisan test --compact` **harus hijau tanpa pengecualian**. Tahap 0 yang membuat test gagal berarti ada titik pemakaian yang terlewat dari 40 berkas perujuk `BrandSettings`.

---

## Tahap 1 — P1

### US1 — Identitas dan kontak

1. Buka **Pengaturan Umum**, isi nama, email, telepon, dan alamat perusahaan dengan data yang jelas berbeda dari bawaan.
2. Isi teks hak cipta dan ketiga alamat legal, lalu simpan.
3. Buka halaman publik mana pun. **Harapan**: blok kontak dan baris hak cipta footer memakai data baru (FR-003, FR-007).
4. Kosongkan nomor telepon, simpan, muat ulang. **Harapan**: baris telepon hilang seluruhnya tanpa menyisakan ikon menggantung (FR-004).
5. Kosongkan alamat Kebijakan Cookie. **Harapan**: tautannya hilang, dua tautan legal lain tetap ada (FR-007).
6. Cari jejak data contoh bawaan di keluaran halaman:

   ```bash
   curl -s http://localhost:8000/ | grep -i "suoer\|sudirman\|555-0123"
   ```

   **Harapan**: tidak ada hasil (FR-009, SC-002).

### US2 — Ikon media sosial

1. Buka **Media Sosial**, isi alamat Instagram saja, kosongkan sisanya, simpan.
2. Buka halaman publik. **Harapan**: hanya ikon Instagram yang tampil dan menuju alamat tersebut (FR-019, FR-020).
3. Kosongkan seluruh alamat. **Harapan**: kelompok ikon hilang sepenuhnya tanpa ruang kosong janggal (FR-020).
4. Isi alamat yang bukan URL sah. **Harapan**: penyimpanan ditolak dengan pesan yang menyebut platformnya (FR-021).

### US3 — Slot skrip

1. Sebagai super admin, buka **Scripts & Analytics**. Isi keempat slot, CSS khusus, dan JS khusus dengan penanda unik, misalnya komentar `<!-- PENANDA-HEAD -->`.
2. Buka halaman publik, lihat sumber halaman. **Harapan**: setiap penanda berada tepat pada posisi yang dijanjikan labelnya (FR-041 sampai FR-043).
3. Pastikan penanda muncul sebagai kode, bukan teks yang di-escape (FR-044).
4. Buka panel admin dan lihat sumber halamannya. **Harapan**: tidak ada satu pun penanda di sana (FR-046).
5. Keluar, masuk sebagai admin **tanpa** peran super admin. **Harapan**: menu Scripts & Analytics tidak terlihat, dan membuka alamatnya langsung ditolak (FR-045).
6. Kosongkan seluruh slot. **Harapan**: halaman publik tampil normal tanpa elemen kosong tersisa (FR-047).

---

## Tahap 2 — P2

### US4 — SEO

1. Buka **SEO**, ubah pemisah judul dan pola judul default, simpan.
2. Buka halaman yang belum punya judul SEO sendiri. **Harapan**: judul mengikuti pola baru (FR-025).
3. Isi pola judul khusus untuk detail artikel, buka satu artikel. **Harapan**: pola artikel yang dipakai (FR-026).
4. Isi judul SEO pada satu artikel lewat form artikel. **Harapan**: judul konten itu menang atas pola mana pun (FR-028).
5. Tulis pola yang memuat penanda ngawur seperti `{tidak_dikenal}`. **Harapan**: penanda mentah tidak ikut tampil, dan tidak ada pemisah menggantung (FR-027).
6. Matikan izin pengindeksan. **Harapan**: halaman menyatakan dirinya tidak boleh diindeks (FR-030).
7. Isi kode verifikasi Google. **Harapan**: penandanya hadir di kepala dokumen; kosongkan lagi dan penandanya hilang (FR-034).

### US5 — Aturan perayapan dan peta situs

1. Ubah isi aturan perayapan, sertakan penanda `{site_url}`.

   ```bash
   curl -s http://localhost:8000/robots.txt
   ```

   **Harapan**: isi sesuai yang disimpan dan penanda tergantikan alamat aktif (FR-035, FR-036).
2. Kosongkan isinya. **Harapan**: aturan bawaan yang aman tersaji, bukan berkas kosong (FR-037).
3. Matikan penyertaan artikel pada peta situs.

   ```bash
   curl -s http://localhost:8000/sitemap.xml | grep -c "/artikel/"
   ```

   **Harapan**: `0`, sementara produk dan portfolio tetap tercantum (FR-038).
4. Matikan peta situs sepenuhnya. **Harapan**: `/sitemap.xml` menyatakan tidak tersedia, dan baris `Sitemap:` hilang dari `robots.txt` meski masih tertulis di pengaturan (FR-040).

### US6 — Persetujuan cookie

1. Tandai slot skrip footer dengan kategori analitik, aktifkan persetujuan cookie, simpan.
2. Buka situs pada jendela penyamaran. **Harapan**: bilah tampil dengan tombol terima dan tolak sama menonjol (FR-073, FR-074).
3. Sebelum memilih apa pun, gulir dan tekan tautan di halaman. **Harapan**: situs tetap dapat dipakai (FR-073).
4. Periksa skrip analitik belum berjalan sebelum persetujuan diberikan (FR-054).
5. Tekan terima. **Harapan**: skrip berjalan dan bilah tidak tampil lagi; muat ulang dan bilah tetap tidak tampil (FR-055).
6. Tekan tautan pengaturan cookie di footer, ubah pilihan. **Harapan**: perubahan langsung berlaku (FR-056).
7. Matikan persetujuan dari panel. **Harapan**: bilah dan tautan footer hilang, seluruh skrip berjalan seperti biasa (FR-057, FR-075).

---

## Tahap 3 — P3

### US7 — Mode pemeliharaan

1. Nyalakan mode pemeliharaan, simpan.
2. Buka halaman publik pada jendela penyamaran. **Harapan**: halaman pemeliharaan tampil (FR-011).

   ```bash
   curl -s -o /dev/null -w "%{http_code}\n" http://localhost:8000/
   ```

   **Harapan**: `503` (FR-012).
3. Pada jendela yang masih login sebagai admin, buka halaman publik. **Harapan**: isi situs yang sebenarnya tampil (FR-013).
4. Buka panel admin. **Harapan**: tetap dapat diakses, dan ada penanda jelas bahwa situs sedang tertutup (FR-013, FR-014).
5. Matikan lagi. **Harapan**: situs normal tanpa langkah tambahan.

### US8 — Halaman kesalahan

1. Isi pesan 404, simpan, buka alamat ngawur seperti `/tidak-ada`. **Harapan**: pesan tersebut tampil beserta identitas situs dan jalan kembali (FR-016).
2. Kosongkan pesannya. **Harapan**: pesan bawaan yang wajar tetap tampil (FR-015).

### US9 — Tombol berbagi

1. Nyalakan tombol berbagi, pilih dua platform, simpan.
2. Buka satu artikel. **Harapan**: tepat dua tombol tampil dan masing-masing membawa alamat artikel tersebut (FR-022, FR-023).
3. Matikan sakelarnya. **Harapan**: tidak ada tombol berbagi yang tampil (FR-022).

---

## Penutup

```bash
vendor/bin/pint --dirty --format agent
php artisan test --compact
```

Sebelum fitur dianggap selesai, perbarui dokumentasi deployment dan checklist go-live dari spec 021 agar memuat lima halaman pengaturan baru — Deployment Standards konstitusi menganggap modul tak terdokumentasi sebagai belum selesai.
