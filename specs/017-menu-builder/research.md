# Research: Menu Builder

**Feature**: 017-menu-builder | **Date**: 2026-09-13

## 1. Struktur data lokasi menu: tabel terpisah vs enum tetap

**Decision**: Tabel `menu_locations` terpisah (bukan enum PHP/hardcoded), dengan `slug` unik dan `name` tampil. Di-seed dengan dua baris default: `navbar-utama` dan `footer`.

**Rationale**: FR-003 mensyaratkan admin dapat menambah lokasi menu baru tanpa perubahan kode. Enum PHP akan melanggar ini karena menambah lokasi baru berarti deploy kode. Tabel terpisah konsisten dengan Prinsip I (Multi-Client Reusability) — struktur navigasi berbeda per klien murni jadi data.

**Alternatives considered**:
- Enum PHP tetap (`navbar`, `footer`) — ditolak karena tidak fleksibel lintas klien (melanggar FR-003 & Prinsip I).
- Kolom `location` bertipe string bebas tanpa tabel referensi — ditolak karena rawan typo/duplikasi slug tanpa validasi, dan tidak ada tempat menyimpan `name` tampil untuk dropdown pemilihan lokasi di admin panel.

## 2. Filament Resource: satu resource gabungan vs dua resource terpisah

**Decision**: Dua Filament Resource — `MenuLocationResource` (sederhana, CRUD nama/slug lokasi, jarang diubah) dan `MenuItemResource` (CRUD utama, dengan reorder & filter per lokasi menggunakan tab/select filter, plus relasi self-referencing untuk sub-item).

**Rationale**: Memisahkan concern — pengelolaan lokasi (jarang berubah, admin-level) terpisah dari pengelolaan item (sering berubah, aktivitas harian editor). Konsisten dengan pola resource-per-model yang sudah dipakai di seluruh kodebase (Category terpisah dari Article, PortfolioCategory terpisah dari PortfolioProject).

**Alternatives considered**:
- Satu resource dengan lokasi sebagai kolom select tanpa tabel — ditolak, lihat poin 1.
- Menu builder visual kustom (non-Filament-resource, drag & drop lintas lokasi dalam satu layar) — ditolak untuk v1: menambah kompleksitas UI kustom yang signifikan tanpa kebutuhan eksplisit dari spec; drag & drop di dalam satu lokasi (reorder table Filament bawaan) sudah memenuhi FR-004. Bisa dipertimbangkan sebagai peningkatan masa depan (AMC-222-style "nice-to-have"), bukan untuk v1.

## 3. Reorder / drag & drop urutan

**Decision**: Gunakan fitur `reorderable()` bawaan Filament v3 Table Builder pada kolom `order_column` (integer), sama seperti pola yang sudah dipakai `BannerResource`.

**Rationale**: Filament v3 sudah menyediakan reorder table out-of-the-box tanpa dependency tambahan (Prinsip V — Simplicity & Dependency Discipline). Tidak perlu paket `spatie/eloquent-sortable` terpisah karena scope urutan hanya per-lokasi (dan per-parent untuk sub-item), yang bisa diimplementasikan dengan kolom integer sederhana + query `orderBy` per grup (`menu_location_id`, `parent_id`).

**Alternatives considered**:
- `spatie/eloquent-sortable` — ditolak, redundan dengan fitur reorder Filament bawaan; menambah dependency baru tanpa manfaat signifikan untuk skala kecil (puluhan item per lokasi).

## 4. Resolusi tautan internal (polymorphic vs tipe+slug manual)

**Decision**: Kolom `link_type` (enum: `internal`, `external`, `none`), `linkable_type` + `linkable_id` (nullable morph columns) untuk tautan internal, `external_url` (nullable string) untuk eksternal. URL internal dihasilkan lewat method resolver `MenuItem::resolveUrl()` yang memanggil route/URL helper sesuai tipe model target (mis. `CustomPage` → route berbasis slug), bukan menyimpan URL statis.

**Rationale**: FR-008 mensyaratkan URL tetap valid walau slug berubah — ini hanya mungkin jika URL dihitung ulang saat render (via relasi morph ke model asli), bukan disalin sebagai string beku saat item menu dibuat. Pendekatan polymorphic relation adalah pola Eloquent standar untuk "tautan ke salah satu dari beberapa jenis model", dan Laravel/Filament sudah mendukungnya secara native (MorphTo select di form) sehingga tidak melanggar Prinsip V.

**Alternatives considered**:
- Menyimpan URL final sebagai string saat pembuatan item — ditolak, melanggar FR-008 secara langsung (URL basi jika slug berubah).
- Kolom terpisah per jenis konten (`custom_page_id`, `article_id`, dst.) — ditolak, tidak scalable karena tiap modul publik baru (Portfolio, Career, dst.) butuh kolom baru + migrasi baru; morph relation menghindari ini.

## 5. Penanganan tautan internal ke konten yang terhapus (FR-011 / Edge Case)

**Decision**: Resolver URL memakai `optional()`/null-safe: jika model target morph sudah tidak ada (soft/hard deleted) atau relasi null, item menu tetap dirender tapi tanpa atribut `href` aktif (fallback ke `<span>` atau `href="#"` dengan indikator visual admin di panel, mis. badge "Tautan tidak valid" di kolom Filament table).

**Rationale**: Memenuhi FR-011 dan edge case terkait tanpa memerlukan cascading delete/observer kompleks. Menampilkan badge peringatan di admin panel (bukan silent fail) memudahkan admin menemukan dan memperbaiki menu yang rusak.

**Alternatives considered**:
- Cascade delete `MenuItem` saat model target dihapus — ditolak, terlalu agresif (admin bisa saja ingin mempertahankan label menu dan mengganti tautannya, bukan kehilangan seluruh entri termasuk pengaturan urutan/lokasinya).

## 6. Caching menu per lokasi

**Decision (direvisi saat implementasi)**: TIDAK menambah layer caching/observer khusus untuk Menu Builder. `MenuItem::treeForLocation()` menjalankan query langsung (sudah pakai index `(menu_location_id, parent_id, order_column)` dari data-model.md).

**Rationale**: Saat implementasi ditemukan bahwa strategi caching publik yang sudah ada di kit ini (`App\Concerns\CachesPublicPages`, AMC-225) **sengaja TTL-only tanpa invalidation** dan **sengaja hanya di layer controller, tidak pernah di model/repository** — supaya panel admin tidak pernah terpengaruh cache (lihat docblock trait tsb). Menambah observer invalidation khusus Menu Builder akan (a) menyimpang dari pola yang sudah mapan di codebase ini (melanggar konsistensi/Prinsip V), dan (b) redundan: halaman yang sudah memakai `CachesPublicPages` (Home, About, Produk, dll.) otomatis meng-cache output header/footer sebagai bagian dari HTML halaman tsb, mengikuti TTL 5 menit yang sama seperti perubahan Produk/Artikel — trade-off yang sudah diterima & diuji di `PublicPageCachingTest`. Skala data menu (puluhan item per lokasi, lihat data-model.md Scale/Scope) membuat query tanpa cache tetap murah.

**Konsekuensi**: Pada halaman yang memakai `CachesPublicPages`, perubahan Menu Builder mengikuti TTL 5 menit yang sama seperti modul lain (bukan instan) — konsisten dengan perilaku Produk/Artikel yang sudah ada, bukan regresi baru. Pada halaman tanpa caching, perubahan langsung terlihat (SC-003 terpenuhi penuh).

**Alternatives considered**:
- Observer-based invalidation per lokasi (rencana awal, lihat riwayat) — ditolak setelah implementasi karena menyimpang dari konvensi caching yang sudah mapan di codebase ini dan menambah kompleksitas tanpa manfaat nyata pada skala data yang kecil.
- Tanpa caching sama sekali, termasuk tidak mewarisi cache halaman publik — tidak realistis karena header/footer dirender sebagai bagian dari HTML yang sudah di-cache oleh controller pemanggil.

## 7. Kedalaman sub-menu

**Decision**: `parent_id` self-referencing nullable pada `menu_items`, dibatasi secara aplikatif (validasi form Filament) hanya 1 tingkat kedalaman (item dengan `parent_id` tidak boleh punya anak sendiri).

**Rationale**: FR-005 secara eksplisit membatasi "minimal satu tingkat" dan spec tidak meminta unlimited nesting; membatasi di level validasi menjaga UI dropdown navbar tetap sederhana sesuai konvensi company-profile site, sejalan dengan Prinsip V (hindari abstraksi spekulatif).

**Alternatives considered**:
- Nested set / unlimited depth — ditolak sebagai over-engineering untuk kebutuhan yang secara eksplisit dibatasi satu tingkat di spec.

## Outstanding NEEDS CLARIFICATION

Tidak ada. Seluruh keputusan teknis di atas memiliki default yang wajar dan konsisten dengan konstitusi serta konvensi kodebase yang sudah ada.
