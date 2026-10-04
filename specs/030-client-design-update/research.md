# Research: Penyesuaian Desain Website Sesuai Dokumen Klien

Tidak ada `NEEDS CLARIFICATION` di Technical Context. Berikut keputusan teknis yang perlu dicatat.

## R1. Sumber hero Artikel & Portofolio

- **Decision**: Pakai `x-sections.page-hero` dengan data `PageBlock` bertipe `ArticlesHero` dan `PortfolioHero` (sudah ada di `PageBlockType` dan terdaftar sebagai `pageBanners()`). Tambahkan `defaultImagePath()` untuk keduanya agar tidak jatuh ke gambar Produk. Jejak "Beranda / Artikel" sudah dirender oleh komponen lewat prop `breadcrumb`.
- **Rationale**: Komponen sudah dipakai Produk dan Karir. Admin sudah punya menu Banner Halaman Lain, jadi FR-024 terpenuhi tanpa kode admin baru.
- **Alternatives**: Membuat komponen hero baru (duplikasi, ditolak). Hero statis di Blade (melanggar Principle I).

## R2. Gambar bawaan hero

- **Decision**: Pakai gambar mockup yang sudah ada di `public/images/mockup` (pola sama dengan `ProductsHero`). Artikel memakai `home-2.jpg` atau yang paling sesuai, Portofolio memakai `home-4.jpg`. Pemilihan akhir saat implementasi setelah melihat gambarnya. `PageContent::imageUrl()` sudah menangani berkas hilang dengan jatuh ke gambar bawaan (FR-023).
- **Rationale**: Tanpa aset baru dan tanpa dependency.
- **Alternatives**: Menunggu gambar dari klien (memblokir). Gambar dari dokumen PDF klien tidak dapat diambil ulang dengan kualitas cukup.

## R3. Pencarian artikel

- **Decision**: Server-side lewat query string `?q=`. `ArticleController::index` menambahkan `where` (judul LIKE atau excerpt LIKE) pada artikel terbit. Kunci cache menjadi `public-page:artikel.index:` + hash dari `q` ternormalisasi. Input dipotong maksimal 100 karakter dan karakter wildcard `%`/`_` di-escape.
- **Rationale**: Bekerja tanpa JavaScript (edge case spec), URL dapat dibagikan, dan sederhana (Principle V). Filter kategori sebelumnya client-side Alpine; tetap client-side dan otomatis berlaku pada hasil pencarian yang sudah dimuat (kombinasi FR-016).
- **Alternatives**: Pencarian client-side Alpine (tidak bekerja tanpa JS dan memuat semua artikel). Laravel Scout atau mesin pencari (dependency baru, berlebihan).
- **Catatan cache**: Tanpa `q`, kunci tetap `...:all` sehingga perilaku cache yang ada tidak berubah. Kunci dengan `q` bebas terbentuk tak terbatas, jadi TTL pendek yang ada (5 menit) menjadi batas. Hanya `q` tidak kosong yang di-cache, dan panjangnya dibatasi.

## R4. Penyimpanan langganan newsletter

- **Decision**: Tabel `newsletter_subscribers` (`email` unik, `subscribed_at`, timestamps). Endpoint `POST /langganan` dengan `throttle:5,1`, honeypot dan token waktu dari `SubmissionGuard`, dan pembatas per-email lewat `RateLimiter` seperti `ContactController`. Email yang sudah ada dibalas sukses yang sama (idempoten, tanpa membocorkan keberadaan email).
- **Response**: Form dikirim lewat `fetch` (JSON) bila JS aktif, dan jatuh ke redirect dengan flash status bila tidak. Satu controller mendukung keduanya lewat `expectsJson()`.
- **Rationale**: Memakai ulang pola yang sudah teruji. Idempoten menjawab edge case duplikat.
- **Alternatives**: Memakai tabel `contact_submissions` (mencampur lead dengan langganan, ditolak). Integrasi layanan email eksternal (di luar cakupan sesuai asumsi spec).

## R5. Pengelolaan pelanggan oleh admin

- **Decision**: Satu Filament Resource baca-saja (`NewsletterSubscriberResource`): daftar dengan kolom email dan tanggal, pencarian, hapus, dan ekspor CSV bila `ExportAction` Filament tersedia tanpa paket tambahan. Tanpa halaman create/edit. Dikelompokkan di grup navigasi yang sama dengan Kontak/Lead. Izin lewat Shield seperti resource lain.
- **Rationale**: FR-017 hanya meminta pelanggan terlihat oleh admin.
- **Alternatives**: Tanpa UI admin (melanggar FR-017).

## R6. Ringkasan kartu Portofolio

- **Decision**: Tambahkan accessor `summary()` pada `PortfolioProject` yang mengembalikan `Str::limit(strip_tags(description), 140)`. Tidak ada kolom baru. Kartu memakai `line-clamp-3`.
- **Rationale**: Asumsi spec sudah menetapkan ringkasan diturunkan dari deskripsi. Menghindari migrasi dan perubahan form admin.
- **Alternatives**: Kolom `excerpt` baru dengan field admin (lebih fleksibel, tetapi menambah migrasi dan tugas isi data, dapat dilakukan nanti bila diminta).

## R7. Tampilan "Mengapa Beralih" dan "Sederhana dan Mulus"

- **Decision**: Hanya ubah class Blade. Hilangkan `rotate-*`, `-translate-y-*`, dan `scale` permanen. Kartu penekanan memakai `border-2 border-primary-container` dengan latar putih. Judul rata tengah dan lebar penuh. Ikon dikecilkan (`w-10 h-10`, ikon `text-xl`). Kartu `h-full` pada grid agar sama tinggi. `how-it-works` menghapus larik `$rotations` dan `$offsets`.
- **Rationale**: Data, `PageContent::section()`, dan admin tidak disentuh (FR-004).
- **Dampak tes**: `LegacyMarkupEquivalenceTest` membandingkan markup dengan fixture lama. Untuk dua fragmen ini, fixture sengaja diganti. Aturan "jangan buat ulang fixture" di test berlaku untuk konversi CMS tanpa perubahan desain, sedangkan fitur ini justru mengubah desain. Fragmen lama dilepas dari `LegacyMarkup::FRAGMENTS` dan diganti test render biasa yang memeriksa ketiadaan class rotasi dan keberadaan struktur baru. Ini sejalan dengan catatan memori proyek: jaga kesetaraan hanya saat tujuannya konversi.

## R8. Testimoni "Partner Kami"

- **Decision**: Ubah blok testimoni di `home.blade.php`: label `TESTIMONI` (kecil, kapital, spasi huruf), judul "Partner Kami", hapus subjudul dan sorotan kartu tengah, bintang memakai ikon `star_border`/`star` bergaya garis sesuai rating seperti komponen `testimonials.blade.php` yang sudah ada. Kerangka kartu boleh meniru komponen itu, tetapi blok beranda tidak dipindah ke komponen agar perubahan tetap kecil.
- **Rationale**: Komponen testimoni halaman Tentang Kami tidak berubah (FR-027).
- **Alternatives**: Menyatukan ke komponen (menyentuh halaman Tentang Kami, di luar cakupan).

## R9. Kartu produk gambar dan judul saja

- **Decision**: `product-card.blade.php` dirombak menjadi tautan pembungkus penuh: gambar `aspect-[4/3]`, judul rata tengah dengan `line-clamp-2`. Gambar pengganti tetap memakai `data-product-image-placeholder` agar test lama masih relevan. Komponen ini hanya dipakai halaman Produk. Dipastikan lewat `grep` sebelum diubah, dan bila dipakai di tempat lain (mis. produk terkait) dibuat varian `compact` agar halaman lain tidak berubah.
- **Rationale**: FR-009 sampai FR-011.

## R10. Layout Artikel

- **Decision**: `grid lg:grid-cols-[1fr_320px]`; kolom utama berisi filter kategori dan grid kartu 2 kolom; sidebar `lg:sticky`. Pada layar kecil sidebar berada di bawah. `featured` dan CTA `ArticleIndex` dihapus dari halaman daftar (asumsi spec). Placement CTA `ArticleIndex` tetap ada di data dan admin (tanpa tampilan) sehingga tidak ada migrasi data; hal ini dicatat sebagai sisa yang dapat dibersihkan nanti.
- **Rationale**: Sesuai dokumen klien.
- **Alternatives**: Mempertahankan artikel unggulan (tidak ada di desain klien).
