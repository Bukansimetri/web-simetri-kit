# Quickstart: Validasi Manual Optimasi Performa

Jalankan setelah implementasi selesai (Phase Polish), di atas data seed existing.

## 1. Lazy-load gambar (User Story 1)

1. Buka `/produk`, `/artikel`, `/portfolio` di browser, buka DevTools → Network → filter Img, throttle ke "Slow 3G".
2. Reload halaman TANPA scroll — hitung jumlah request gambar yang terjadi: harus jauh lebih sedikit dibanding total gambar di halaman (hanya yang terlihat di layar awal).
3. Scroll perlahan ke bawah — gambar tambahan MUST mulai termuat saat mendekati area pandang, tanpa kotak kosong yang mengganggu lama.
4. View-source halaman apapun — cek `<img>` di dalam hero/banner TIDAK punya `loading="lazy"`; `<img>` lain (kartu produk/artikel/portfolio, testimoni, logo klien, tim) MUST punya `loading="lazy" decoding="async"`.
5. Nonaktifkan JavaScript di browser (DevTools → Settings) → reload halaman → scroll → semua gambar tetap tampil (FR-004).

## 2. Caching halaman publik (User Story 2)

1. Buka `/` dua kali berturut-turut dalam beberapa detik — kunjungan kedua terasa sama cepat atau lebih cepat.
2. Ubah judul satu produk via admin panel → buka halaman edit produk itu lagi di admin → judul baru langsung terlihat (FR-008, tanpa delay).
3. Buka halaman publik `/produk/{slug}` produk yang sama dalam ~1 menit setelah langkah 2 → judul lama MUNGKIN masih tampil (cache belum kedaluwarsa, ini perilaku yang disengaja).
4. Tunggu >5 menit, buka lagi `/produk/{slug}` yang sama → judul baru sudah tampil (SC-004, TTL kedaluwarsa).
5. Buka `/portfolio?kategori=residensial` lalu `/portfolio?kategori=komersial` (atau kategori lain yang ada) — pastikan daftar proyek yang tampil BENAR-BENAR berbeda sesuai kategori masing-masing (tidak tertukar cache).
6. Kirim form di `/kontak` dua kali berturut-turut — MUST selalu diproses baru (tidak ada perilaku aneh akibat cache).

## 3. Pemuatan non-blocking font ikon (User Story 3)

1. Buka DevTools → Network → throttle "Slow 3G" → reload halaman apa pun.
2. Amati waterfall: request `Material+Symbols` MUST tidak memblokir request/render HTML utama (teks & tata letak tampil sebelum request font ikon selesai).
3. Setelah halaman selesai dimuat, semua ikon (navigasi, tombol, kartu) MUST tampil normal — tidak ada ikon hilang/kotak persegi placeholder permanen.

## 4. Regresi

- `php artisan test --compact` — seluruh suite existing tetap hijau.
- `vendor/bin/pint --dirty --format agent` — bersih.
- Jalankan pengukuran kecepatan halaman standar pihak ketiga (mis. Lighthouse) pada `/`, `/produk`, `/artikel` — skor performa membaik dibanding sebelum optimasi (SC-002/SC-006), catat hasilnya di PR description.
