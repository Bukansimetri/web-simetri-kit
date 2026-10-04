# Data Model: Penyesuaian Desain Website Sesuai Dokumen Klien

Hanya satu entitas baru. Entitas lain dipakai ulang tanpa perubahan skema.

## NewsletterSubscriber (baru)

Tabel `newsletter_subscribers`.

| Kolom | Tipe | Aturan |
|---|---|---|
| id | bigint, PK | |
| email | string(255), unik | wajib, format email valid, disimpan huruf kecil |
| subscribed_at | timestamp | diisi saat pendaftaran |
| created_at / updated_at | timestamp | |

**Validasi**: `required|email|max:255`. Email dinormalisasi (`trim` + huruf kecil) sebelum disimpan. Keunikan dijaga oleh indeks unik dan `firstOrCreate`, sehingga pendaftaran ganda tidak membuat baris baru dan tidak menimbulkan galat (FR-015).

**Transisi status**: tidak ada. Berhenti berlangganan di luar cakupan. Admin dapat menghapus baris.

**Relasi**: tidak ada.

## Entitas yang dipakai ulang (tanpa perubahan skema)

| Entitas | Dipakai untuk | Catatan |
|---|---|---|
| `PageBlock` (`ArticlesHero`, `PortfolioHero`) | Judul, subjudul, gambar hero | Baris sudah ditanam migrasi `2026_10_04_135442_install_page_banner_blocks`. Gambar bawaan ditambahkan di enum (kode), bukan data. |
| `Article` | Grid dan pencarian | Pencarian pada `title` dan `excerpt`, hanya yang terbit (`published_at <= now`). |
| `PortfolioProject` | Kartu | Accessor `summary()` turunan dari `description`, tanpa kolom baru. |
| `Product` | Kartu produk | Hanya `name` dan gambar sampul yang ditampilkan. |
| `Testimonial`, `SectionItem`, `SectionHeading` | Beranda | Tidak berubah. |
| `SiteSettings` | Footer Kontak | Tidak berubah. |
