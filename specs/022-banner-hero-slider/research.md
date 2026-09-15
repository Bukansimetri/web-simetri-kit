# Phase 0 Research: Banner Hero Slider

**Feature**: 022-banner-hero-slider | **Date**: 2026-09-14

Seluruh butir NEEDS CLARIFICATION dari Technical Context terselesaikan di bawah ini. Tidak ada yang tersisa.

---

## R1. Cara menggabungkan hero dan carousel tanpa menduplikasi markup

**Decision**: Satu komponen `x-sections.hero-slider` yang menerima koleksi banner. Di dalamnya, markup satu slide diekstrak sebagai partial yang dipakai baik pada mode slide tunggal maupun mode slider. `hero.blade.php` dipertahankan apa adanya sebagai fallback nol-banner.

**Rationale**: Perilaku "1 slide tanpa kontrol navigasi" (FR-009) dan "≥2 slide dengan kontrol" (FR-008) berbeda hanya pada pembungkus, bukan pada isi slide. Mengekstrak isi slide mencegah dua salinan markup yang akan berbeda seiring waktu — persis penyakit yang terjadi pada `banner-carousel` versi sekarang, yang isinya menyimpang jauh dari `hero`.

**Alternatives considered**:
- *Menambahkan props konten ke `banner-carousel` yang ada*: ditolak, namanya sudah tidak lagi menggambarkan isinya dan percabangan `@if(count===1)` di dalamnya sudah menduplikasi markup `<img>` empat kali.
- *Menjadikan `hero.blade.php` menerima satu banner dan memanggilnya berulang*: ditolak, `hero` memiliki nilai default hardcoded yang justru ingin dipindahkan ke basis data, dan ia masih dipakai sebagai fallback dengan default tersebut.

---

## R2. Menyimpan preset tampilan

**Decision**: Kolom `string` di basis data, di-cast ke PHP backed enum `App\Enums\BannerOverlayStyle` (`dark`, `light`, `none`) dan `App\Enums\BannerTextPosition` (`left`, `center`, `right`). Enum menyediakan `label(): string` untuk dropdown Filament berbahasa Indonesia dan `classes(): array` untuk kelas Tailwind.

**Rationale**: Enum tertutup adalah bentuk "varian section bernama" yang diwajibkan Principle III, dan mencegah nilai liar masuk basis data. Menaruh pemetaan kelas Tailwind di enum membuat penambahan preset baru menjadi satu perubahan di satu berkas.

**Alternatives considered**:
- *Kolom `enum` asli basis data*: ditolak, menambah preset berarti migration ALTER yang merepotkan di MySQL.
- *Menyimpan kelas Tailwind langsung di basis data*: ditolak keras — Tailwind 4 memindai kode sumber untuk menghasilkan kelas, sehingga kelas yang hanya ada di basis data tidak akan pernah ikut ter-build.

---

## R3. Kelas Tailwind harus statis, bukan dirangkai

**Decision**: Enum mengembalikan **string kelas lengkap yang literal**, bukan potongan yang dirangkai (`"bg-gradient-to-r from-on-surface/90 via-on-surface/65 to-transparent"`, bukan `"from-{$color}/90"`).

**Rationale**: Tailwind 4 mendeteksi kelas lewat pemindaian teks statis. Kelas hasil interpolasi tidak terdeteksi dan akan hilang di build produksi — kegagalan yang tidak terlihat saat `npm run dev` namun muncul setelah `npm run build`.

**Alternatives considered**: *Safelist di konfigurasi Tailwind*: ditolak, memperbesar bundle CSS dan menyembunyikan ketergantungan.

---

## R4. Sanitasi konten trust bar tanpa dependency baru

**Decision**: `App\Support\HtmlSanitizer::clean(?string $html): string` memakai `DOMDocument` + `DOMXPath` bawaan PHP dengan allowlist: tag `p, br, span, div, strong, b, em, i, u, a, img, ul, ol, li, small`; atribut `class, href, src, alt, title, target, rel, width, height`. Seluruh atribut `on*` dibuang, dan skema `href`/`src` dibatasi pada `http`, `https`, `mailto`, `tel`, serta path relatif. Tag di luar allowlist dibuang namun teks anaknya dipertahankan.

**Rationale**: Memenuhi FR-007 dengan nol dependency baru, mengikuti preseden `ImageUploads` yang memilih GD bawaan daripada paket gambar (Principle V). `DOMDocument` sudah menjadi syarat Laravel.

**Alternatives considered**:
- *`mews/purifier` atau `ezyang/htmlpurifier`*: ditolak — `CLAUDE.md` melarang mengubah dependency tanpa persetujuan, dan Principle V menuntut audit lisensi untuk setiap paket baru.
- *Merender apa adanya dengan `{!! !!}` seperti Artikel/Custom Page*: ditolak sebagai jaring pengaman. Perbedaannya, konten trust bar kemungkinan besar ditempel dari sumber luar (potongan lencana, widget ulasan), sementara isi artikel diketik di editor. Sanitizer tetap dipasang meski pengisinya admin tepercaya.
- *`strip_tags` dengan daftar tag*: ditolak, tidak membuang atribut sehingga `onerror=` lolos.

**Catatan**: `HtmlSanitizer` ditulis generik agar modul lain dapat mengadopsinya belakangan, namun fitur ini **tidak** mengubah jalur render Artikel/Custom Page/Portfolio — di luar cakupan.

---

## R5. Invalidasi cache beranda

**Decision**: Di `Banner::booted()`, daftarkan listener `saved` dan `deleted` yang memanggil `Cache::forget('public-page:home')`.

**Rationale**: FR-018 mensyaratkan perubahan admin langsung terlihat. `CachesPublicPages` memakai TTL 5 menit, sehingga tanpa invalidasi admin akan mengira simpanannya gagal. Event model menangkap seluruh jalur perubahan — form Filament, `ToggleColumn` aktif/nonaktif, aksi massal, maupun reorder — tanpa menyentuh controller.

**Alternatives considered**:
- *Hook `afterSave` pada halaman Filament*: ditolak, terlewat pada `ToggleColumn`, bulk delete, dan reorder.
- *Observer class terpisah*: ditolak, repositori belum punya direktori `app/Observers` dan `CLAUDE.md` melarang membuat folder dasar baru tanpa persetujuan. `booted()` cukup untuk dua listener.
- *Memperpendek TTL*: ditolak, menggeser masalah tanpa menyelesaikannya dan memperberat basis data.

**Catatan**: Trait `CachesPublicPages` sengaja tidak diubah agar modul lain tidak terpengaruh.

---

## R6. Pola slider yang dapat diakses tanpa auto-rotate

**Decision**: Komponen Alpine `heroSlider` terdaftar lewat `Alpine.data()` di `resources/js/hero-slider.js`, mengikuti pola `calculatorComponent` yang sudah ada — bukan `x-data` inline panjang seperti `banner-carousel` sekarang. Markup memakai pola "carousel with tabbed slide picker": wadah ber-`aria-roledescription="carousel"`, tiap slide `role="group"` + `aria-roledescription="slide"` + `aria-label="Slide n dari m"`, titik navigasi sebagai `role="tab"` dengan `aria-selected`, slide non-aktif diberi `aria-hidden` dan `inert`.

**Rationale**: Menghapus auto-rotate (FR-011) justru **menyederhanakan** aksesibilitas — tidak perlu tombol jeda maupun `aria-live` yang wajib ada pada carousel berputar otomatis. `inert` mencegah fokus papan ketik masuk ke tombol CTA pada slide tersembunyi, yang merupakan cacat nyata pada implementasi sekarang (FR-015).

**Alternatives considered**:
- *Mempertahankan `x-data` inline*: ditolak, logikanya bertambah (geser sentuh, papan ketik, observer viewport) sehingga tidak lagi layak ditulis di atribut.
- *Menambah pustaka carousel (Swiper, Splide)*: ditolak, dependency baru untuk perilaku yang dicakup ~60 baris Alpine.

---

## R7. Penanda "bisa diklik" pada panah sebagai pengganti auto-rotate

**Decision**: Tiga lapis penanda, seluruhnya tunduk pada `prefers-reduced-motion`:
1. Animasi *nudge* CSS pada ikon panah — bergeser 4px maju-mundur, 3 siklus, dipicu saat slider memasuki viewport lewat `IntersectionObserver`, lalu berhenti permanen.
2. Penanda posisi tekstual "1 / 3" di dekat titik navigasi, agar jumlah slide terbaca tanpa bergantung pada animasi.
3. Di layar kecil, panah disembunyikan dan digantikan petunjuk "Geser untuk melihat lainnya" beserta ikon beranimasi yang hilang permanen setelah interaksi pertama.

**Rationale**: Tanpa perpindahan otomatis, tidak ada yang memberi tahu pengunjung bahwa slide kedua ada — persis kekhawatiran yang diangkat pemilik produk. Animasi yang berhenti sendiri memberi petunjuk tanpa menjadi gangguan permanen, dan penanda tekstual memastikan informasinya tetap sampai ketika animasi dimatikan.

**Alternatives considered**:
- *Animasi berulang tanpa henti*: ditolak, mengganggu dan bersaing dengan CTA sebagai titik perhatian.
- *Hanya mengandalkan hover*: ditolak, tidak ada hover di layar sentuh.
- *Mengintip sebagian slide berikutnya di tepi layar*: ditolak, merusak komposisi hero lebar penuh dan memotong gambar secara tidak terduga.

---

## R8. Perilaku tautan majemuk (link_url lama vs CTA baru)

**Decision**: Bila slide memiliki minimal satu CTA, gambar **tidak** dibungkus tautan dan `link_url` diabaikan. Bila tidak ada CTA sama sekali dan `link_url` terisi, seluruh area slide dibungkus tautan seperti perilaku sekarang.

**Rationale**: Memenuhi FR-017 (kompatibilitas banner lama) sekaligus menghindari tautan bersarang — `<a>` di dalam `<a>` adalah HTML tidak valid yang membuat perilaku klik dan pembacaan teknologi bantu tidak dapat diprediksi.

**Alternatives considered**: *Menghapus `link_url`*: ditolak, merusak banner yang sudah ada dan melanggar FR-016/FR-017.

---

## R9. Strategi pemuatan gambar antar slide

**Decision**: Slide pertama: `fetchpriority="high"`, tanpa `loading="lazy"`, tanpa `decoding="async"`. Slide berikutnya: `loading="lazy"` + `decoding="async"`. Seluruh slide dirender di HTML awal dan disembunyikan lewat `x-show`.

**Rationale**: Slide pertama adalah elemen LCP beranda (SC-007). `banner-carousel` sekarang sudah memakai pola `@if ($i > 0)` ini; fitur ini mempertahankannya dan menambahkan `fetchpriority` yang belum ada. Merender seluruh slide di awal membuat perpindahan slide instan tanpa permintaan jaringan.

**Alternatives considered**: *Memuat slide lewat permintaan terpisah*: ditolak, berlebihan untuk kurang dari sepuluh slide dan merusak fungsi tanpa JavaScript.

---

## R10. Backfill konten hero ke banner yang sudah ada

**Decision**: Migration `up()` menyalin teks hero yang berlaku sekarang ke banner dengan `order` terkecil (pemecah seri: `id` terkecil), hanya bila `heading` masih kosong. Teks disalin sebagai **literal** di dalam migration, bukan dibaca dari berkas Blade.

**Rationale**: FR-016. Migration harus deterministik dan dapat dijalankan ulang bertahun-tahun kemudian; membaca berkas Blade saat migrate akan membuat hasilnya bergantung pada isi berkas pada saat itu, dan gagal bila berkasnya sudah berubah.

**Alternatives considered**:
- *Backfill seluruh banner dengan teks yang sama*: ditolak, menghasilkan slide yang identik berulang.
- *Perintah artisan terpisah*: ditolak, mudah terlewat saat deploy sehingga situs klien tampil tanpa teks.
- *Tanpa backfill*: ditolak, bertentangan langsung dengan keputusan klarifikasi.

**Catatan**: `down()` hanya membuang kolom. Data teks hilang saat rollback — konsekuensi yang diterima dan didokumentasikan di quickstart.
