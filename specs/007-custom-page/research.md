# Research: Custom Page (Halaman Statis Bebas)

**Date**: 2026-09-07 | **Feature**: [spec.md](./spec.md)

## 1. Entity `CustomPage` baru, tanpa draft/publish, tanpa listing publik

**Decision**: Tabel `custom_pages` baru (`id`, `title`, `slug` unique, `content` longText, timestamps). Tidak ada kolom `published_at`/`status` (berbeda dari Artikel) — begitu record ada, langsung dapat diakses; menghapus record adalah satu-satunya cara "menyembunyikannya" lagi. Tidak ada route index/listing publik — setiap halaman murni diakses lewat `/halaman/{slug}`.

**Rationale**: Keputusan eksplisit di spec.md (Assumptions) — use-case utama (Kebijakan Privasi, Syarat & Ketentuan, dan halaman legal/informasi statis lain) tidak butuh alur draft/schedule seperti Artikel; menambah kolom itu adalah kompleksitas yang tidak diminta (Principle V).

**Alternatives considered**: Meniru penuh pola Artikel (`published_at` nullable + status turunan) — ditolak, tidak ada kebutuhan bisnis untuk menjadwalkan publish halaman legal statis; menambah field yang tidak dipakai adalah over-engineering untuk kasus ini.

## 2. Rich text: `Filament\Forms\Components\RichEditor` (identik pola Artikel)

**Decision**: Field `content` di form `CustomPageResource` pakai `RichEditor::make('content')` — komponen bawaan Filament 3.3, sama persis dengan yang sudah dipakai `ArticleResource` (005-artikel-crud-admin). Kolom `content` (longText) menyimpan HTML apa adanya, dirender di Blade lewat `{!! $customPage->content !!}`.

**Rationale**: Nol dependency baru, pola sudah terbukti bekerja & teruji di fitur sebelumnya (Principle V). Konsisten UX admin — editor yang sama dipakai di dua modul berbeda (Artikel, Custom Page).

**Alternatives considered**: Tidak ada — ini keputusan yang sudah dibuat dan divalidasi di 005-artikel-crud-admin, tidak ada alasan mengevaluasi ulang untuk kasus yang identik.

## 3. Routing: prefix `/halaman/{slug}`, satu route dinamis untuk semua Custom Page

**Decision**: Satu route baru: `Route::get('/halaman/{customPage:slug}', CustomPageController::class)->name('halaman.show');` di `routes/web.php`, memakai route-model-binding by slug (pola sama seperti `Article`/`Product`). Model `CustomPage::getRouteKeyName()` mengembalikan `'slug'`.

**Rationale**: Keputusan eksplisit dari klarifikasi (Q2=A). Prefix path terpisah membuat slug Custom Page TIDAK PERNAH bisa bentrok dengan route statis manapun (`/produk`, `/karir`, dst) — tidak perlu daftar "reserved slugs" yang harus dirawat manual setiap kali ada route statis baru ditambahkan (FR-005, FR-014). Route-model-binding otomatis 404 kalau slug tidak ditemukan (FR-011) tanpa kode tambahan, identik pola `Article`/`Product`.

**Alternatives considered**: Route di root (`/{slug}`) dengan middleware/validasi terhadap daftar reserved slug — ditolak eksplisit oleh klarifikasi (Q2=B), karena daftar reserved slug adalah beban perawatan manual yang mudah terlupa dan berisiko konflik silent.

## 4. Update link footer: langsung ke slug tetap, bukan dinamis dipilih admin

**Decision**: Link "Kebijakan Privasi" dan "Syarat & Ketentuan" di `resources/views/components/layout/footer.blade.php` diarahkan ke `url('/halaman/kebijakan-privasi')` dan `url('/halaman/syarat-ketentuan')` — slug tetap yang HARUS dipakai admin saat membuat kedua halaman tsb (didokumentasikan di quickstart.md) supaya link berfungsi.

**Rationale**: Sesuai FR-012 — link footer perlu diarahkan ke Custom Page yang sesuai. Karena Custom Page tidak punya mekanisme "halaman mana yang berperan sebagai kebijakan privasi" (tidak ada field/flag semacam itu diminta di spec — Principle V, jangan menambah abstraksi yang tidak diminta), pendekatan paling sederhana adalah link footer mengarah ke slug konvensi tetap, dan admin diberi tahu (lewat quickstart/dokumentasi) untuk memakai slug tsb persis saat membuat kedua halaman. Jika halaman belum dibuat, link tetap ada di footer tapi akan 404 sampai admin membuatnya (FR-011, US2 Acceptance Scenario 2) — perilaku ini eksplisit diterima di spec (bukan disembunyikan otomatis, berbeda dari toggle modul Karir di 006 yang punya flag on/off).

**Alternatives considered**: Field pengaturan di Brand Settings untuk memilih Custom Page mana yang jadi "Kebijakan Privasi"/"Syarat & Ketentuan" (mirip pola toggle modul Karir) — dipertimbangkan, tapi ditolak sebagai over-engineering untuk kebutuhan ini; slug konvensi tetap + dokumentasi cukup sederhana dan tidak diminta spec untuk bisa berubah-ubah link footernya secara dinamis.
