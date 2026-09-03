# Research: Produk CRUD Admin

**Date**: 2026-09-03 | **Feature**: [spec.md](./spec.md)

## 1. Kategori sebagai entity terpisah + FK, bukan string bebas

**Decision**: Tabel `categories` baru (`id`, `name` unique, `order`), `products.category` (string) diganti `products.category_id` (foreign key, `restrictOnDelete()`).

**Rationale**: Keputusan eksplisit dari klarifikasi (Q3=C). FK + `restrictOnDelete()` di level DB jadi *safety net* untuk FR-004 (tolak hapus kategori yang masih dipakai), dilengkapi pengecekan level aplikasi di Filament `DeleteAction` supaya pesan errornya ramah (bukan exception DB mentah).

**Alternatives considered**: Kolom string tetap + validasi `in:` dari daftar dinamis — ditolak, tidak memberi admin kemampuan CRUD kategori sungguhan (tidak ada tempat menyimpan "daftar kategori" itu sendiri). Kategori berjenjang (nested) — ditolak untuk v1, tidak diminta dan menambah kompleksitas tanpa kebutuhan jelas (Assumptions: kategori flat).

## 2. Galeri gambar produk: kolom JSON array, bukan tabel `product_images` / Spatie Media Library

**Decision**: `products.images` (json, array path gambar terurut — index 0 = cover). Diisi lewat Filament `FileUpload::make('images')->multiple()->reorderable()->image()`, disk `public`, direktori `products`.

**Rationale**: `spatie/laravel-medialibrary` sudah terpasang (dependency `tomatophp/filament-media-manager`) tapi TIDAK dipakai di model manapun di aplikasi ini — seluruh upload gambar yang sudah ada (`logo_path`, `favicon_path`, `og_image_path` di `BrandSettings`) memakai pola sederhana: kolom string/JSON + `FileUpload` biasa, disk `public`. `products.specs` dan `products.features` (dari 002) juga sudah JSON array. Memakai kolom JSON array untuk `images` konsisten dengan pola yang sudah ada di codebase ini, dan Filament `FileUpload::multiple()->reorderable()` sudah cukup untuk kebutuhan FR-010/FR-011 (upload banyak, urutkan, hapus, cover = urutan pertama) tanpa perlu tabel/relasi/Eloquent event tambahan. Principle V (Simplicity & Dependency Discipline) — jangan tambah pola penyimpanan media baru kalau yang sudah ada cukup.

**Alternatives considered**: Tabel `product_images` terpisah (relasi `hasMany`) — ditolak, over-engineering untuk kebutuhan "daftar path gambar terurut" yang tidak butuh query independen di luar konteks satu produk. Spatie Media Library (`HasMedia`/`InteractsWithMedia` + `SpatieMediaLibraryFileUpload`) — ditolak, menambah API/konsep baru (media collection, conversions) yang belum dipakai sama sekali di project ini, padahal kebutuhan riil (banyak gambar + urutan) sudah terpenuhi pola JSON yang sudah ada.

## 3. Migrasi data existing (seed 002) saat menambah `category_id`

**Decision**: Satu migration terurut yang: (a) buat tabel `categories`, (b) backfill baris `Category` dari nilai unik `products.category` yang sudah ada (mis. `residensial` → "Residensial"), (c) tambah kolom `products.category_id` nullable dulu, isi berdasarkan pemetaan nama, lalu ubah jadi `NOT NULL` + FK, (d) drop kolom `products.category` (string lama) dan `products.image_path` (kolom lama yang ternyata tidak pernah benar-benar dipakai render di frontend publik — lihat data-model.md).

**Rationale**: `ProductSeeder` dari 002 sudah pernah dijalankan di banyak environment (dev lokal, mungkin sudah di-seed sebelumnya) — migration harus aman dijalankan baik di database yang sudah ada data (auto-migrasi ke struktur baru) maupun database kosong/fresh (tabel `categories` tetap perlu diisi minimal, lewat `CategorySeeder` baru yang didaftarkan bareng `ProductSeeder`).

**Alternatives considered**: `migrate:fresh` manual tiap kali — ditolak, tidak realistis untuk instalasi yang sudah live/punya data admin lain (users, roles, dst.) yang tidak boleh hilang.

## 4. Spesifikasi teknis & fitur unggulan: Filament `Repeater`, bukan field terpisah

**Decision**: `Repeater::make('specs')` (sub-field `label`, `value`) dan `Repeater::make('features')` (sub-field `icon`, `title`, `description`) di form `ProductResource`, disimpan sebagai array JSON — struktur identik dengan yang sudah dikonsumsi Blade `produk/show.blade.php` dari 002 (tidak perlu ubah frontend).

**Rationale**: `Repeater` adalah komponen bawaan Filament 3.3 (sudah terpasang) untuk tepat kasus "daftar baris dinamis" (FR-012) — tidak perlu package tambahan.

**Alternatives considered**: Key-value editor bawaan Filament (`KeyValue::make`) untuk specs — dipertimbangkan tapi ditolak untuk `features` karena butuh 3 sub-field terstruktur (icon+title+description) yang tidak didukung `KeyValue` (cuma key→value datar); `Repeater` dipakai konsisten untuk keduanya supaya UX admin seragam.

## 5. Guard hapus kategori yang masih dipakai: cek aplikasi + FK sebagai safety net

**Decision**: Filament `DeleteAction` (dan `DeleteBulkAction`) di `CategoryResource` memakai `->before()` untuk cek `Category::withCount('products')`, batalkan + tampilkan `Notification` error kalau `products_count > 0`. Kolom `category_id` tetap `restrictOnDelete()` di migration sebagai lapisan pertahanan kedua (kalau ada jalur hapus lain di luar UI, mis. tinker/query langsung, DB tetap menolak).

**Rationale**: FR-004 minta pesan error yang jelas ke admin (bukan exception mentah) — cek di level aplikasi memberi UX yang tepat, FK constraint menjamin integritas data tetap terjaga meski ada jalur lain.

**Alternatives considered**: Cuma andalkan FK constraint (biarkan Filament menampilkan error dari exception DB) — ditolak, pesannya biasanya generik/teknis, tidak ramah admin non-teknis (bertentangan dengan SC-003 "pesan error yang jelas").
