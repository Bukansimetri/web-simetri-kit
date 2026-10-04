# Data Model: Lanjutan Penyesuaian Website dan Kelengkapan Admin CMS

## Perubahan skema

### faq_items (ubah)

| Kolom | Tipe | Aturan |
|---|---|---|
| placement | string(20), default `faq`, indeks | salah satu `faq`, `produk`, `kontak` (enum `FaqPlacement`) |
| is_active | boolean, default true | entri nonaktif tidak tampil |

Baris lama otomatis `placement = faq`, `is_active = true`. Validasi admin: `question` wajib (maks 255), `answer` wajib, `category` opsional (maks 100, hanya untuk `faq`), `order` ≥ 0.

### products (ubah)

| Kolom | Tipe | Aturan |
|---|---|---|
| show_on_home | boolean, default false | maksimal 3 baris bernilai true (divalidasi di form admin) |

### articles (ubah)

| Kolom | Tipe | Aturan |
|---|---|---|
| view_count | unsignedInteger, default 0 | bertambah tanpa event model |
| image_caption | string(255), nullable | keterangan gambar utama |

### article_product (baru)

| Kolom | Tipe | Aturan |
|---|---|---|
| id | bigint PK | |
| article_id | FK articles, cascade delete | |
| product_id | FK products, cascade delete | |
| sort_order | unsignedInteger, default 0 | urutan tampil |
| timestamps | | |

Unik (`article_id`, `product_id`). Relasi: `Article::relatedProductRows()` hasMany `ArticleRelatedProduct` (untuk Repeater) dan `Article::relatedProducts()` belongsToMany `Product` lewat `article_product`, diurut `sort_order`.

### settings `site` (ubah)

| Properti | Tipe | Default |
|---|---|---|
| footer_description | ?string | "Menginspirasi masa depan berkelanjutan melalui inovasi tenaga surya yang elegan dan presisi tinggi untuk masyarakat Indonesia." |

## Tanpa perubahan skema

| Entitas | Perubahan perilaku |
|---|---|
| JobOpening | Halaman detail publik `/karir/{id}` |
| PageBlock (`FaqHero`, `ContactHero`) | Gambar bawaan dari enum, kolom gambar muncul di admin |
| Tag (Spatie) | Dipakai sebagai filter publik dan Tag Populer |
| Cache | Kunci `public-page:version` (integer) dinaikkan saat model publik disimpan/dihapus |

## Data bawaan (installer, idempoten)

- FAQ `produk`: 3 entri dari `produk/index.blade.php` saat ini.
- FAQ `kontak`: 3 entri dari `kontak.blade.php` saat ini.
- Ditanam hanya bila placement tersebut belum memiliki entri.
