# Research: Theme & Branding System

**Date**: 2026-09-01 | **Feature**: [spec.md](./spec.md)

Fitur ini tidak punya `NEEDS CLARIFICATION` tersisa di Technical Context — dua sesi `/speckit-clarify` (2026-08-17, 2026-09-01) sudah menyelesaikan keputusan produk. Dokumen ini fokus ke keputusan teknis yang dibutuhkan untuk Phase 1, hasil membaca mockup asli di `public/mockup-html/`.

## 1. Struktur Theme Settings: extend `BrandSettings` yang sudah ada, bukan class baru

**Decision**: Tambahkan properti baru (`secondary_color`, `font_heading`, `font_body`) ke `App\Settings\BrandSettings` (Epic 2) via settings-migration, plus media collection `brand-og-image` di halaman Filament yang sama (`BrandSettingsPage`, cukup diperluas form-nya — tidak perlu halaman baru).

**Rationale**: Constitution Principle V (Simplicity) — satu grup settings (`brand`) untuk satu konsep "identitas visual instalasi" lebih sederhana daripada dua settings class + dua halaman admin yang isinya saling terkait erat. `BrandSettingsTest.php` yang sudah ada juga tinggal diperluas, bukan dibuat dari nol.

**Alternatives considered**: Class `ThemeSettings` terpisah dengan group `theme` — ditolak karena akan memaksa admin membuka dua halaman berbeda untuk satu konsep "branding" yang sama, dan tidak ada batas concern yang jelas antara "brand" vs "theme" di level user-facing.

## 2. Sumber token warna/font: port token Luminous Azure ke Tailwind v4 `@theme`, hanya sebagian yang settings-driven

**Decision**: Mockup memakai skema token ala Material 3 (`primary`, `on-primary`, `surface`, `surface-container`, `tertiary`, dst. — lihat `public/mockup-html/luminous_azure/DESIGN.md`). Semua token ini di-port apa adanya ke `resources/css/app.css` sebagai `@theme` Tailwind v4 (CSS var), sebagai **default tetap** starter kit. Hanya `primary` (sudah ada, Epic 2), `secondary`, `font-heading`, `font-body` yang di-override secara dinamis lewat inline `<style>` di `<head>` layout, dihitung dari `BrandSettings` saat request. Token turunan lain (surface, tertiary, on-*, dst.) tetap CSS statis mengikuti palet Luminous Azure — tidak diekspos ke admin.

**Rationale**: FR-002/FR-003 hanya mewajibkan warna sekunder + font + OG image sebagai settings-driven (bukan seluruh ~40 token M3). Mengekspos seluruh token M3 ke form admin akan melanggar Principle III secara halus (mendekati page-builder/theme-builder penuh) dan jauh melebihi effort yang disepakati di klarifikasi. Warna primer & sekunder tetap cukup untuk kebutuhan white-label paling umum (brand color klien), sementara nuansa surface/tertiary tetap konsisten dengan identitas visual "Luminous Azure" sebagai starter kit look-and-feel.

**Alternatives considered**: Generate seluruh tonal palette (surface, container, dst.) otomatis dari 1 warna primer pakai algoritma Material You — ditolak untuk v1: kompleksitas algoritmik tinggi, tidak diminta FR manapun, best dikerjakan sebagai peningkatan terpisah nanti kalau dibutuhkan multi-klien dengan brand color sangat berbeda.

## 3. Interaktivitas frontend: Alpine.js murni (bukan Livewire) untuk komponen client-side

**Decision**: Tambahkan `alpinejs` sebagai dependency npm untuk frontend publik (kalkulator estimasi FR-006, accordion FAQ, toggle nav mobile, validasi form Kontak FR-007). Livewire (sudah terpasang untuk Filament) TIDAK dipakai di halaman publik.

**Rationale**: FR-006 eksplisit "berjalan sepenuhnya di sisi client tanpa penyimpanan ke server" — Livewire menyiratkan round-trip server per interaksi (walau bisa di-tweak, itu bukan model defaultnya) dan menambah beban Livewire polling ke seluruh halaman publik yang seharusnya statis/cepat. Alpine.js kecil (~15kb), banyak dipakai bersama Blade + Tailwind, dan cocok untuk state lokal sederhana (kalkulator, accordion) sesuai Principle V.

**Alternatives considered**: Livewire component untuk kalkulator — ditolak (server round-trip tidak perlu, kontradiksi FR-006). Vanilla JS tanpa framework — dipertimbangkan tapi Alpine tetap dipilih karena directive `x-data`/`x-show` jauh lebih ringkas untuk state accordion/tab yang dipakai di 4+ section, dan sudah jadi pilihan umum ekosistem Laravel/Livewire (familiar bagi maintainer starter kit ini ke depan).

## 4. Grafik kalkulator estimasi: SVG manual, bukan library charting

**Decision**: Bagian "Investment Line" & "Savings Curve" pada kalkulator Home (`home_suoer_html_calculator_results/code.html`) diimplementasikan sebagai elemen `<svg>`/`<path>` yang di-render ulang lewat Alpine (`x-bind:d` dihitung dari input pengguna), bukan library chart pihak ketiga.

**Rationale**: Grafiknya sederhana — dua garis linear/kurva pada satu grid — dan mockup sudah menyediakan markup SVG statis sebagai referensi. Menambah Chart.js/ApexCharts hanya untuk satu grafik kecil ini melanggar Principle V (dependency discipline) untuk manfaat yang tidak sepadan.

**Alternatives considered**: Chart.js — ditolak, overkill untuk 1 grafik 2-garis dan menambah ~60kb dependency baru untuk kasus yang bisa diselesaikan dengan segelintir baris JS/SVG.

## 5. Font kurasi: preload semua kandidat lewat `bunny()` Vite plugin yang sudah ada

**Decision**: Daftar font kurasi (FR-004) — minimal Manrope & Be Vietnam Pro (dari mockup) plus beberapa pasangan populer lain (mis. Inter, Poppins, Plus Jakarta Sans, Nunito Sans, Work Sans, Lato) — semuanya didaftarkan sekaligus di `vite.config.js` via `bunny()` (`laravel-vite-plugin/fonts`, sudah dipakai untuk "Instrument Sans"). Saat admin ganti pilihan font di Theme Settings, yang berubah hanya CSS variable `--font-heading`/`--font-body`, bukan `<link>` baru — tidak ada request jaringan tambahan saat runtime.

**Rationale**: `bunny()` sudah jadi konvensi project (self-hosted Google Fonts, tanpa call ke domain Google langsung — lebih baik untuk privasi & performa). Preload semua kandidat kurasi di build time menghindari FOUC/flash saat admin ganti font, dan tetap konsisten dengan Principle V (pakai yang sudah ada, bukan tambah sistem font-loading baru).

**Alternatives considered**: Lazy-load `<link>` Google Fonts sesuai pilihan admin — ditolak, menambah request eksternal langsung ke `fonts.googleapis.com` (bertentangan dengan pola `bunny()` self-hosted yang sudah dipakai) dan berisiko FOUC.

## 6. Data seed Produk/Artikel/Karir/FAQ: Eloquent model + migration sederhana, bukan hardcode di Blade

**Decision**: Empat model baru (`Product`, `Article`, `JobOpening`, `FaqItem`) dengan migration dasar + seeder berisi konten dari mockup (nama produk, harga, spesifikasi dari `produk_detail_suoer_header_aligned/code.html`, dst.), diquery biasa di controller — bukan array hardcoded di Blade/controller.

**Rationale**: FR-008 eksplisit meminta struktur ini siap digantikan modul CRUD Epic 3 tanpa mengubah Blade. Memakai Eloquent model dari awal (walau tanpa Filament Resource) berarti nanti tinggal menambah `Filament\Resources\ProductResource` dkk. yang menunjuk model yang sama, tanpa migrasi data ulang.

**Alternatives considered**: Config array / JSON file statis untuk data seed — ditolak, karena tidak ada jalur upgrade halus ke CRUD (FR-008 secara eksplisit minta struktur yang bisa "digantikan", bukan "ditulis ulang").

## 7. Form Kontak tanpa submit sungguhan: validasi client-side saja, tanpa route POST

**Decision**: Form Kontak dirender statis dengan atribut validasi HTML5 + Alpine untuk pesan error yang lebih ramah, `<form>` TIDAK attach ke route POST sungguhan (tombol submit menampilkan state "terkirim" secara optimistic di client, tanpa network call), sesuai FR-007/Q5.

**Rationale**: Menghindari kesan "form rusak" (submit yang gagal diam-diam atau error 419/404) sambil tetap jujur bahwa backend-nya belum ada — dan tidak membangun setengah endpoint yang harus dibongkar lagi saat AMC-216 dikerjakan.

**Alternatives considered**: Route POST yang menyimpan ke tabel sementara tanpa notifikasi — ditolak, itu sudah masuk wilayah "backend Kontak" yang eksplisit ditunda ke AMC-216 oleh Q5.
