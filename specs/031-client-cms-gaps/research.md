# Research: Lanjutan Penyesuaian Website dan Kelengkapan Admin CMS

Tidak ada `NEEDS CLARIFICATION`. Berikut keputusan teknisnya.

## R1. Header seragam

- **Decision**: `faq.blade.php`, `kontak.blade.php`, dan `tentang-kami.blade.php` memakai `x-sections.page-hero`. `PageBlockType::defaultImagePath()` diisi untuk `FaqHero` dan `ContactHero` (gambar mockup yang ada), `imageMaxWidth()` = 1920. `PageBannerResource` otomatis menampilkan kolom gambar bila `defaultImagePath()` tidak null, jadi tidak ada kode admin baru.
- **Dampak tes**: Fragmen `faq-hero`, `kontak-hero`, dan `tentang-kami-hero` di `LegacyMarkup` masuk `REDESIGNED`, dan fixture-nya dihapus. Render hero dijaga oleh `PageBannerRenderTest`.
- **Alternatives**: Membuat varian hero kedua (melanggar keseragaman, ditolak).

## R2. Tipografi 700

- **Decision**:
  1. Di semua view publik (`resources/views/pages`, `components/sections`, `components/layout`), ganti `font-extrabold` dan `font-black` dengan `font-bold`.
  2. Di `resources/css/app.css`, set `--text-headline-lg--font-weight` dan `--text-headline-lg-mobile--font-weight` ke 700 (xl sudah 700), sehingga judul yang hanya memakai `text-headline-*` juga 700.
  3. Teks tombol CTA (`btn-fill`, tombol CTA band/hero/sidebar) diseragamkan ke `font-bold`. Kelas `font-label-bold text-label-bold` pada tombol CTA diberi `font-bold` eksplisit.
  4. Test `TypographyConsistencyTest` merender halaman publik utama dan memastikan tidak ada `font-extrabold`/`font-black`.
- **Rationale**: Utilitas eksplisit mengalahkan aturan dasar CSS, jadi sumbernya harus diganti, bukan ditimpa dengan CSS global.
- **Dampak tes**: Fixture `LegacyMarkup` yang memuat `font-extrabold` (CTA dan section Tentang Kami/Karir) diperbarui dengan penggantian string yang sama. Ini perubahan desain yang disengaja; struktur markup lain tetap dibandingkan.
- **Alternatives**: `h1..h4 { font-weight: 700 !important }` (melawan utilitas, sulit dipelihara; ditolak).

## R3. Pilih produk beranda

- **Decision**: Kolom `products.show_on_home` (boolean, default false) dan scope `Product::forHome()`. Bila ada produk bertanda, ambil yang bertanda; bila tidak, 3 teratas menurut `order`. Hasil tetap dibatasi 3.
- **Validasi**: Toggle di form ProductResource memakai rule closure: tolak bila sudah ada 3 produk lain bertanda. Pesannya: "Maksimal 3 produk dapat ditampilkan di Beranda." Kolom tabel memakai `IconColumn` (bukan `ToggleColumn`) agar batas tidak bisa dilewati dari tabel.

## R4. FAQ admin

- **Decision**: `faq_items` ditambah `placement` (string: `faq`, `produk`, `kontak`; default `faq`) dan `is_active` (boolean, default true). Ada enum `FaqPlacement` dengan label. `FaqItemResource` memakai filter Tempat (default `faq`), urutan seret (`reorderable('order')`), kolom Kategori yang hanya tampil untuk placement `faq`, dan toggle Aktif.
- **Data bawaan**: `DefaultPageContent::faqs()` memuat 3 FAQ Produk dan 3 FAQ Kontak yang sekarang ada di Blade. `PageContentInstaller::install()` menanamnya hanya bila placement itu belum punya entri sama sekali (idempoten, tidak menimpa editan). Instalasi dipanggil lewat migrasi data baru.
- **Render**: Komponen `x-sections.faq-list` (akordeon `<details>`, item pertama terbuka) dipakai Produk dan Kontak. Section tidak dirender bila kosong. FAQ JSON-LD halaman FAQ hanya memakai entri aktif placement `faq`.

## R5. Detail lowongan

- **Decision**: Rute `GET /karir/{jobOpening}` (`karir.show`) dengan binding id. Rute ini 404 bila tidak aktif atau modul karir mati. Ada view `pages/karir/show.blade.php`: hero halaman (judul lowongan, breadcrumb "Karir"), badge lokasi dan jenis, deskripsi utuh `whitespace-pre-line`, tombol "Lamar Sekarang" ke `/kontak`. Meta title dan description diturunkan dari judul dan deskripsi.
- **Rationale**: Tanpa kolom slug baru (Principle V). Lowongan bersifat sementara, jadi URL berbasis id cukup.
- **Alternatives**: Slug unik (perlu migrasi dan form admin; ditunda).

## R6. Invalidasi cache publik

- **Decision**: `CachesPublicPages::rememberPublicPage()` membungkus kunci menjadi `"{$key}:v".Cache::get('public-page:version', 1)`. Trait `FlushesPublicPageCache` dipasang pada model yang tampil di halaman publik (Product, Category, Article, ArticleCategory, PortfolioProject, PortfolioCategory, Testimonial, FaqItem, JobOpening, TeamMember, ClientLogo, Banner). Trait ini menaikkan `public-page:version` (`Cache::forever` + 1) pada event `saved` dan `deleted`. Entri lama kedaluwarsa sendiri lewat TTL 5 menit.
- **Relasi**: Sinkronisasi tag dan produk terkait dari Filament terjadi setelah `saved`, jadi `EditArticle`/`CreateArticle::afterSave()` juga menaikkan versi.
- **Penghitung dilihat**: memakai `incrementQuietly('view_count')`, sehingga tidak memicu event dan tidak membanjiri invalidasi.
- **Alternatives**: Cache tags (tidak didukung driver file/database). `Cache::flush()` (menghapus cache non-publik, termasuk rate limiter; ditolak).

## R7. Artikel: paginasi kumulatif dan filter server-side

- **Decision**: `ArticleController::index` membaca `q`, `kategori` (slug kategori), `tag` (slug tag), dan `halaman` (≥1). Query mengambil `6 × halaman + 1` baris untuk mengetahui apakah masih ada sisa, lalu menampilkan `6 × halaman`. Kunci cache memuat semua parameter.
- **"Muat lebih banyak"**: Tombol berupa `<a href="?…&halaman=N+1#artikel-{6N+1}">`, sehingga tanpa JS pengunjung diarahkan ke kartu pertama yang baru. Dengan JS (Alpine), klik melakukan `fetch` URL itu, mengambil kartu baru dari HTML (`DOMParser`), menambahkannya ke grid, lalu memperbarui href tombol atau menghapusnya. Posisi gulir tetap.
- **Filter kategori**: Pindah dari Alpine (client-side) ke tautan `?kategori=`, karena filter client-side tidak benar lagi saat artikel dimuat bertahap. Tampilan tombol tetap sama.
- **Tag Populer**: Query `Spatie\Tags\Tag` join `taggables` (taggable_type Article) dan join `articles` terbit, group by tag, urut jumlah menurun, limit 10, di-cache. Tag aktif ditandai, dan ada tautan "Hapus filter".
- **Rationale**: Cocok dengan pola portofolio yang sudah server-side, bekerja tanpa JS, dan cache-friendly.
- **Alternatives**: Livewire seperti web-ecomm (komponen Livewire publik pertama di proyek ini, menambah kompleksitas; ditolak).

## R8. Artikel: preview, view count, caption, produk terkait, sidebar detail

- **Preview**: Rute `GET /artikel/{article:slug}/preview` (`artikel.preview`) dengan middleware `auth`, plus cek `$request->user()->canAccessPanel(admin)`, sehingga user tanpa peran mendapat 403. View sama dengan detail, ditambah banner "Mode Preview" dan `noindex`. Aksi header `Preview` di `EditArticle` membuka tab baru.
- **View count**: Kolom `articles.view_count` (unsigned int, default 0). `show()` memanggil `incrementQuietly` sebelum render, tidak dipanggil di preview. Ditampilkan sebagai "N kali dilihat".
- **Caption**: Kolom `articles.image_caption` (string 255, nullable), field di section Featured Image, ditampilkan sebagai `<figcaption>` bila terisi.
- **Produk terkait**: Tabel `article_product` (id, article_id, product_id, sort_order, unik article+product, cascade delete) dan model `ArticleRelatedProduct`. Admin memakai `Repeater::make('relatedProductRows')->relationship()->orderColumn('sort_order')` berisi `Select product_id` (searchable, distinct). Detail menampilkan maksimal 4 lewat `Article::relatedProducts()` (belongsToMany berurutan), memakai `product-card` versi `simple`.
- **Sidebar detail**: Layout `lg:grid-cols-[minmax(0,1fr)_320px]`. Sidebar berisi "Artikel Terbaru" (5 terbit terbaru selain artikel ini, di-cache) dan `x-sections.newsletter-card`. Artikel terkait dan tombol bagikan tetap ada.
- **Langganan dari detail**: `NewsletterController` mengembalikan redirect ke `url()->previous()` + `#langganan` (fallback `/artikel`) untuk permintaan non-JSON.

## R9. Perbaikan kecil

- **CTA beranda**: tombol kedua `href` → `url('/#kalkulator')`. Pastikan section kalkulator memiliki `id="kalkulator"` (sudah dipakai CTA Produk).
- **Kutipan kosong**: Blok kutipan dibungkus `@if (filled(strip_tags((string) $whoWeAre->value('quote'))))`.
- **Testimoni Tentang Kami**: `testimonials.blade.php` judul diganti "Partner Kami" (label tetap "Testimoni").
- **Tim Kami**: `<section>` luar menjadi full-width (`w-full`, latar sama dengan section tetangga), isi dibungkus `max-w-7xl mx-auto`. Grid diganti `flex flex-wrap justify-center` dengan lebar kartu tetap, agar 1–N anggota rata tengah.
- **Filter Produk**: Blok tombol kategori dan `x-data` dihapus dari `produk/index.blade.php`. `$categories` tidak lagi dikirim.
- **Footer**: Settings `site.footer_description` (nullable, default teks sekarang), section "Footer" di halaman Pengaturan Umum (Textarea, maks 500). Footer menampilkan `nl2br(e())` bila terisi.
