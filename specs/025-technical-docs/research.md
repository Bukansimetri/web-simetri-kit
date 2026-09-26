# Research: Technical Documentation (AMC-233)

**Feature**: [spec.md](./spec.md) | **Plan**: [plan.md](./plan.md) | **Date**: 2026-09-24

Fakta kode di bawah dikumpulkan langsung dari repo pada branch `025-technical-docs` (setelah commit `7c9817f`) dan menjadi bahan isi dokumen. Setiap keputusan mengikuti format Decision / Rationale / Alternatives.

## 1. Pembagian dokumen

- **Decision**: Tiga file terpisah di `docs/`: `docs/arsitektur.md` (US1), `docs/panduan-section.md` (US2), `docs/panduan-tema.md` (US3). README menautkan ketiganya di bagian baru "Dokumentasi Developer".
- **Rationale**: Tiap user story punya pembaca dan momen baca berbeda (onboarding vs. tugas spesifik). File terpisah bisa diuji dan diperbarui sendiri-sendiri, dan konsisten dengan pola `docs/` yang sudah ada (satu topik per file, nama Bahasa Indonesia berhuruf kecil dengan tanda hubung).
- **Alternatives considered**: Satu file besar `docs/teknis.md` (ditolak: terlalu panjang, pembaca panduan section harus menggulir melewati arsitektur); wiki eksternal (ditolak: tidak ikut ter-clone ke repo klien, tidak ter-versioning bersama kode).

## 2. Verifikasi path otomatis (FR-015, SC-004)

- **Decision**: Tambah satu feature test `tests/Feature/Docs/TechnicalDocsPathsTest.php` yang membaca ketiga dokumen, mengambil setiap path repo di inline code (bukan di dalam fenced code block) yang diawali `app/`, `resources/`, `routes/`, `database/`, `config/`, `tests/`, `docs/`, atau file root seperti `vite.config.js`, lalu memastikan path tersebut ada. Test juga memastikan setiap tautan relatif Markdown antar-dokumen mengarah ke file yang ada.
- **Rationale**: SC-004 menuntut 100% path valid. Pemeriksaan manual mudah terlewat, dan dokumen teknis cepat basi. Test murah (hanya baca file), berjalan di suite yang sudah ada, dan menangkap path yang berubah akibat refactor di kemudian hari.
- **Alternatives considered**: Hanya review manual (ditolak: tidak tahan terhadap perubahan kode berikutnya); link checker eksternal seperti `markdown-link-check` (ditolak: menambah dependency npm, melanggar Principle V untuk kebutuhan sekecil ini).
- **Konsekuensi penulisan**: Path file *hipotetis* pada contoh (misal file section contoh yang belum ada) MUST ditulis di dalam fenced code block, bukan inline code, supaya tidak ikut diperiksa.

## 3. Contoh lengkap panduan section (FR-008)

- **Decision**: Contoh utama adalah section hipotetis "Sertifikasi" di Home yang kontennya berasal dari pengaturan situs (jalur paling ringan). Untuk jalur modul konten, panduan merujuk modul nyata **Testimonials** sebagai contoh yang sudah jadi (model, resource admin, section, test, invalidasi cache) daripada membuat contoh hipotetis kedua.
- **Rationale**: Contoh hipotetis bisa diikuti dari nol tanpa mengubah kode produksi. Testimonials adalah modul lengkap terkecil yang sudah memakai semua pola (section dengan `@props`, empty state, cache, test admin).
- **Alternatives considered**: Benar-benar menambahkan section contoh ke kode (ditolak: menambah fitur yang tidak diminta klien, melanggar Principle V "avoid speculative"); hanya menjelaskan Testimonials tanpa contoh baru (ditolak: tidak memenuhi US2 "dari awal sampai akhir").

## 4. Fakta arsitektur (bahan `docs/arsitektur.md`)

| Area | Fakta dari kode |
|---|---|
| Versi | Laravel 13.23, Filament 3.3, Livewire 3.8, Spatie Laravel Settings 3.9, Filament Shield 3.9, Tailwind CSS v4 (konfigurasi CSS-first lewat `@theme` di `resources/css/app.css`, tanpa `tailwind.config.js`), Vite 8, PHP 8.3. README masih menyebut Laravel 11; dokumen arsitektur memakai versi aktual. |
| Routing publik | `routes/web.php`, nama route berbahasa Indonesia (`produk.index`, `tentang-kami`, `kontak`, `halaman.show`). Custom Page memakai prefix `/halaman/`. |
| Controller publik | `app/Http/Controllers/Public/*Controller.php`, satu per halaman. |
| View publik | `resources/views/pages/*` extend `resources/views/layouts/public.blade.php`; section di `resources/views/components/sections/*` dipanggil sebagai `<x-sections.nama />`; header/footer/menu di `resources/views/components/layout/`; partial head (tema, OG, JSON-LD) di `resources/views/layouts/partials/`. |
| Pengaturan situs | `app/Settings/*Settings.php` (Site, Appearance, Seo, Social, Script, AboutPage, Calculator); migrasi nilai di `database/settings/`; halaman admin di `app/Filament/Pages/*SettingsPage.php`. |
| Panel admin | Resource di `app/Filament/Resources/`; grup navigasi didefinisikan urut di `app/Providers/Filament/AdminPanelProvider.php` (Konten Halaman, Katalog, Prospek & Pesan, Blog, Portfolio, Karir, Menu Builder, Pengaturan Situs, Sistem). |
| Cache | Trait `app/Concerns/CachesPublicPages.php`, TTL 5 menit, key `public-page:{halaman}`, hanya di controller publik. Invalidasi lewat hook `saved`/`deleted` di model (misal `app/Models/Banner.php`) atau saat halaman pengaturan disimpan (misal `app/Filament/Pages/AboutPageSettingsPage.php`). |
| SEO | Trait `app/Concerns/HasSeoMetadata.php`, helper `app/Support/Seo/`, sitemap/robots di `SitemapController`. |
| Test | `tests/Feature/{Admin,Public,Settings,Console,Database,Dashboard,ActivityLog,Pages}`. `tests/Feature/Public/NoHardcodedClientDataTest.php` menjaga prinsip white-label. |
| Seeder demo | `database/seeders/DemoContentSeeder.php` dan seeder per modul. |

**Temuan sampingan** (dicatat di dokumen sebagai hal yang perlu diperhatikan, tidak diperbaiki di fitur ini): `app/Models/Testimonial.php` hanya menghapus cache `public-page:tentang-kami`, padahal Home (`HomeController`) juga menampilkan testimoni dengan key `public-page:home`. Akibatnya perubahan testimoni bisa terlambat tampil di Home sampai 5 menit. Disarankan tiket perbaikan terpisah.

## 5. Fakta alur tema (bahan `docs/panduan-tema.md`)

Alur token dari admin ke tampilan:

1. Admin mengisi halaman **Tampilan** (`app/Filament/Pages/AppearanceSettingsPage.php`), nilai tersimpan di `app/Settings/AppearanceSettings.php` (grup `appearance`).
2. `resources/views/layouts/partials/theme-vars.blade.php` meng-echo CSS variable `--brand-color-primary`, `--brand-color-secondary`, `--brand-font-heading`, `--brand-font-body` per request, dengan fallback ke konstanta `DEFAULT_*` di `AppearanceSettings`.
3. `resources/css/app.css` memetakan variable tersebut ke token Tailwind di blok `@theme` (`--color-primary`, `--color-secondary`, `--font-headline-*`, `--font-body-*`) sehingga class seperti `text-primary`, `bg-secondary`, `font-headline-xl` otomatis mengikuti pengaturan.
4. Token lain (surface, tertiary, `on-*`, dst.) tetap di `@theme` dan tidak diekspos ke admin.

Hal yang wajib dijelaskan di panduan:

- **Menambah font**: harus di dua tempat sekaligus, yaitu konstanta `FONT_OPTIONS` di `AppearanceSettings` **dan** entri `bunny()` di `vite.config.js`, lalu build ulang aset. Jika hanya salah satu, font muncul di dropdown tapi tidak ter-load (jatuh ke fallback). Ini jebakan utama.
- **Mengubah default tema**: konstanta `DEFAULT_*` di `AppearanceSettings` dan blok `:root` fallback di `resources/css/app.css` harus disamakan.
- **Menambah token baru**: properti baru di `AppearanceSettings` + migrasi di `database/settings/` + field di `AppearanceSettingsPage` + echo di `theme-vars.blade.php` + pemetaan di `@theme` + test di `tests/Feature/Settings/AppearanceSettingsTest.php`.

## 6. Batasan yang harus dinyatakan (FR-011)

- **Decision**: Kedua panduan memuat kotak "Batasan saat ini" yang menyebut: belum ada pemilih varian section di panel admin (AMC-221, ditunda) dan belum ada live preview tema (AMC-222, ditunda). Cara yang berlaku sekarang: varian dibuat sebagai komponen bernama terpisah (misal `hero` dan `hero-slider`) dan pemilihannya dilakukan di kode halaman.
- **Rationale**: Constitution Principle III menyebut varian section sebagai mekanisme resmi, sehingga developer akan mencarinya. Menyatakan batasan mencegah developer membangun pemilih sendiri tanpa spec.

## 7. Bahasa, gaya, dan pemeliharaan

- **Decision**: Bahasa Indonesia, gaya mengikuti `docs/deployment.md` (paragraf pembuka yang menyebut untuk siapa dokumen ini, tabel untuk data terstruktur, tautan relatif antar dokumen). Tiap dokumen diawali baris `**Terakhir diperbarui**: YYYY-MM-DD`. Isi kode panjang tidak disalin; cukup path file dan potongan singkat.
- **Rationale**: FR-012, FR-014, dan edge case "dokumen basi". Merujuk path, bukan menyalin kode, membuat dokumen tetap benar lebih lama dan terlindungi test di §2.
