# Research: Artikel CRUD Admin

**Date**: 2026-09-05 | **Feature**: [spec.md](./spec.md)

## 1. Kategori artikel: entity `ArticleCategory` terpisah, bukan berbagi `Category` Produk

**Decision**: Tabel `article_categories` baru (`id`, `name` unique, `order`), `articles.category` (string) diganti `articles.article_category_id` (FK, `restrictOnDelete()`) — pola identik dengan `categories`/`category_id` di Produk (003-produk-crud-admin), tapi tabel & model terpisah sepenuhnya.

**Rationale**: Keputusan eksplisit dari klarifikasi (Q1=A). Kategori Produk (Residensial/Komersial/Pompa Air) dan kategori Artikel (Tips/Berita/Edukasi) adalah dua domain berbeda — menyatukan jadi satu tabel akan membuat dropdown kategori di satu modul menampilkan pilihan yang tidak relevan dari modul lain.

**Alternatives considered**: Reuse `Category` model Produk dengan kolom `type` pembeda (`product`/`article`) — ditolak, menambah kompleksitas query (filter `type` di mana-mana) untuk manfaat yang tidak diminta; dua tabel kecil terpisah jauh lebih sederhana (Principle V).

## 2. Draft/Publish/Terjadwal: dari `published_at` nullable, tanpa kolom `status` terpisah

**Decision**: Tetap satu sumber kebenaran — `articles.published_at` (datetime, nullable). Semantik: `null` = Draft (FR-009), `published_at` di masa depan = Terjadwal (FR-010), `published_at <= now()` = Published. Status "Draft/Terjadwal/Published" untuk tampilan admin dihitung on-the-fly (accessor), TIDAK disimpan sebagai kolom baru. Form Filament menyediakan pilihan mudah: toggle "Publish sekarang" (set `published_at = now()`) vs "Jadwalkan" (date picker) vs kosongkan untuk draft.

**Rationale**: Menghindari dua sumber kebenaran yang bisa tidak sinkron (kolom `status` terpisah vs `published_at` — apa yang terjadi kalau admin set status=published tapi lupa isi tanggal, atau sebaliknya?). Pola `published_at` nullable + filter `<= now()` adalah pendekatan standar CMS (WordPress, dll.) dan sudah SEBAGIAN diimplementasikan di 002 (`whereNotNull('published_at')`) — tinggal ditambah filter `<= now()` yang sebelumnya belum ada (celah FR-010 yang baru ketahuan saat riset ini, lihat §5).

**Alternatives considered**: Kolom `status` enum (`draft`/`scheduled`/`published`) terpisah dari `published_at` — ditolak, redundan dan berisiko out-of-sync; nilai tambahnya (query sedikit lebih eksplisit) tidak sebanding dengan risiko bug data tidak konsisten.

## 3. Tag: pakai `spatie/laravel-tags` yang sudah terpasang tapi belum pernah dipakai

**Decision**: Tambahkan trait `Spatie\Tags\HasTags` ke model `Article`. Publish migration package (`php artisan vendor:publish --provider="Spatie\Tags\TagsServiceProvider" --tag="tags-migrations"`) untuk membuat tabel `tags` dan `taggables` bawaan package. Form Filament pakai `TagsInput::make('tags')` dengan `suggestions()` dari `Spatie\Tags\Tag::pluck('name')`, di-hydrate dari `$record->tags->pluck('name')` dan disinkronkan lewat `$record->syncTags(...)` di hook `afterSave`/`afterCreate` Resource page.

**Rationale**: `spatie/laravel-tags` SUDAH ada di `composer.json` sejak Epic 1 (AMC-199 "Install dependensi wajib: ... Tags") tapi belum pernah benar-benar dipakai di model manapun — persis seperti temuan Spatie Media Library di research.md 003-produk-crud-admin §2. Memakainya sekarang memenuhi Principle V (pakai dependency yang SUDAH ada, jangan bikin sistem tag sendiri dari nol) sekaligus akhirnya "melunasi" instalasi yang sempat idle.

**Alternatives considered**: Kolom JSON `tags` di tabel `articles` (mirip pola `specs`/`features` Produk) — ditolak, tag butuh query "artikel dengan tag X" dua arah dan berbagi antar artikel (many-to-many sungguhan), yang tidak natural direpresentasikan sebagai JSON array per baris; `spatie/laravel-tags` sudah menyediakan tepat semantik ini tanpa kerja tambahan.

## 4. Rich text editor: `Filament\Forms\Components\RichEditor` bawaan, simpan HTML

**Decision**: Field `content` di form `ArticleResource` pakai `RichEditor::make('content')` (bawaan Filament 3.3, tidak perlu package tambahan). Kolom `articles.content` (sudah `longText`) tetap dipakai apa adanya untuk menyimpan HTML hasil editor. Blade `artikel/show.blade.php` diubah dari `@foreach (explode("\n", $article->content))` jadi render HTML langsung (`{!! $article->content !!}`).

**Rationale**: Keputusan eksplisit dari klarifikasi (Q2=A). `RichEditor` sudah termasuk paket inti Filament (dipakai TipTap di baliknya) — nol dependency baru (Principle V).

**Alternatives considered**: Markdown editor (`MarkdownEditor` Filament, simpan Markdown mentah lalu di-parse ke HTML saat render) — dipertimbangkan (juga bawaan Filament tanpa dependency baru), tapi ditolak karena butuh parser Markdown tambahan di sisi Blade (`League\CommonMark` atau sejenis, belum terpasang) hanya untuk manfaat "sumber tetap plain text" yang tidak diminta; `RichEditor` → simpan HTML → render langsung adalah jalur paling pendek.

## 5. Temuan implementasi: `Article.image_path` tidak pernah dirender (gap sama seperti Product di 003)

**Decision**: Perbaiki `resources/views/components/sections/article-card.blade.php` dan `resources/views/pages/artikel/show.blade.php` supaya benar-benar merender `<img>` dari `image_path` (dikonversi ke URL via `Storage::disk('public')->url()`), dengan placeholder wajar saat kosong (FR-015) — persis pola `Product::coverImageUrl()` dari 003.

**Rationale**: `grep` pada codebase mengonfirmasi tidak ada satu pun `<img>` yang mereferensikan `$article->image_path` di Blade manapun — gap identik dengan `Product.image_path` yang ditemukan saat riset 003-produk-crud-admin. Fitur ini sekalian menutup gap tsb, bukan cuma menambah CRUD.

**Alternatives considered**: Biarkan gap ini dan tangani di tiket terpisah — ditolak, fitur ini sudah menyentuh `image_path` (mengubahnya nullable + menambah konversi WebP), jadi memperbaiki render sekalian adalah biaya tambahan yang sangat kecil dibanding membuka tiket baru untuk hal yang sama persis.

## 6. Konversi WebP: GD (PHP extension bawaan), bukan `intervention/image`

**Decision**: Helper kecil `App\Support\ImageUploads::storeAsWebp()` memakai fungsi GD (`imagecreatefromstring`, `imagewebp`) untuk mengonversi file upload ke `.webp` sebelum disimpan ke disk, dipanggil lewat `FileUpload::saveUploadedFileUsing()` di form `ArticleResource`.

**Rationale**: GD sudah aktif di environment ini (dipakai untuk generate `public/images/og-default.jpg` di 002-theme-branding-system) dan mendukung `imagewebp()` secara native — tidak perlu dependency baru untuk kebutuhan konversi satu arah (upload → WebP) yang cukup sederhana (Principle V). Requirement eksplisit dari user: HANYA konversi format, TIDAK ada validasi/penolakan berdasarkan dimensi (FR-014, FR-020) — helper text di form cukup memberi rekomendasi ukuran, tidak menegakkannya.

**Alternatives considered**: `intervention/image` (package populer untuk manipulasi gambar) — ditolak untuk v1, GD sudah cukup untuk operasi tunggal "decode gambar apapun → encode sebagai WebP" tanpa butuh API fluent Intervention (resize, crop, dll. yang tidak diminta). Kalau kebutuhan manipulasi gambar berkembang di modul lain nanti, evaluasi ulang saat itu — bukan sekarang untuk satu use case sederhana.
