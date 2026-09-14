# Research: Demo Content Seeder

**Feature**: 019-demo-content-seeder | **Date**: 2026-09-13

## 1. Cara membedakan data demo dari data asli admin saat pembersihan (FR-004, FR-006)

**Decision**: Tabel manifest polymorphic baru, `demo_seed_records` (`seedable_type`, `seedable_id`). Setiap baris yang di-insert oleh seeder demo dicatat satu baris manifest yang menunjuk ke record tsb. `demo:clean` menghapus persis record yang tercatat di manifest (lalu manifest itu sendiri), tidak menyentuh baris lain.

**Rationale**: Ini satu-satunya pendekatan yang tepat 100% membedakan "dibuat oleh demo seeder" dari "dibuat admin" tanpa bergantung pada kecocokan nilai field (nama/slug) yang berisiko salah kalau admin kebetulan memakai nilai serupa. Polymorphic manifest adalah pola Eloquent standar untuk "menandai kumpulan record lintas beberapa model", didukung penuh tanpa dependency baru.

**Alternatives considered**:
- Kolom `is_demo` boolean di setiap tabel (Product, TeamMember, Testimonial, PortfolioProject, PortfolioCategory) — ditolak: perlu migration terpisah di 4-5 tabel yang sudah ada (lebih invasif) untuk manfaat yang sama persis dengan satu tabel manifest terpusat.
- Pencocokan berdasarkan nilai field tetap (mis. hapus produk dengan `name` persis salah satu dari daftar contoh yang di-hardcode) — ditolak: rapuh (admin bisa kebetulan pakai nama yang sama), dan tidak generik untuk Testimonial/TeamMember yang tidak punya kolom unik alami (slug).
- Menyimpan daftar ID hasil seed di file JSON di `storage/` — ditolak: kurang natural untuk aplikasi Laravel dibanding tabel database, dan tidak bisa di-query/join dengan mudah bila suatu saat dibutuhkan (mis. menampilkan badge "demo" di admin panel).

## 2. Bentuk command: satu command dengan flag vs dua command terpisah

**Decision**: Dua command terpisah, `demo:seed` dan `demo:clean` (bukan satu `demo:content --clean`).

**Rationale**: Dua operasi ini punya semantik & tingkat risiko yang berbeda (isi vs hapus) — command terpisah dengan nama yang menjelaskan diri sendiri (self-descriptive) lebih aman dan lebih mudah didiskoveri lewat `php artisan list` dibanding satu command dengan flag destruktif tersembunyi. Konsisten dengan pola Laravel sendiri (`migrate` vs `migrate:rollback`, bukan satu command dengan flag).

**Alternatives considered**:
- Satu command `demo:content {--seed} {--clean}` — ditolak, menambah kompleksitas parsing tanpa manfaat, dan lebih mudah salah pakai (lupa flag, default ambigu).

## 3. Memindahkan `ProductSeeder`/`TestimonialSeeder` keluar dari `DatabaseSeeder`

**Decision**: Hapus pemanggilan `ProductSeeder::class` dan `TestimonialSeeder::class` dari `DatabaseSeeder::run()`. Keduanya tetap sebagai class Seeder yang sudah ada (isi datanya tidak berubah), tapi hanya dipanggil dari `DemoContentSeeder` (dipicu oleh `demo:seed`), bersama seeder baru `TeamMemberSeeder` dan `PortfolioDemoSeeder`.

**Rationale**: Ini memperbaiki pelanggaran standar deployment kit ini yang sudah terjadi (lihat plan.md Constitution Check) — `php artisan db:seed`/`migrate --seed` standar TIDAK BOLEH lagi menyertakan konten demo Layanan/Testimoni secara diam-diam. `CategorySeeder` (dipakai `ProductSeeder`) tetap di `DatabaseSeeder` karena kategori adalah taxonomy struktural yang wajar ada di instalasi manapun (bukan konten demo itu sendiri) — hanya `ProductSeeder`/`TestimonialSeeder` yang dipindah karena keduanya murni data contoh.

**Alternatives considered**:
- Membiarkan `ProductSeeder`/`TestimonialSeeder` tetap di `DatabaseSeeder` dan hanya menambah seeder baru untuk Tim/Portfolio ke `demo:seed` — ditolak: menyisakan ketidakkonsistenan (2 dari 4 modul yang disebut AMC-229 tetap otomatis ter-seed, melanggar FR-003 untuk modul tersebut) dan tetap melanggar standar deployment yang sudah eksplisit ditulis di konstitusi.

## 4. Menjaga idempotency (FR-005)

**Decision**: `DemoContentSeeder` (dan tiap seeder anaknya) mengecek manifest (`demo_seed_records`) di awal: bila sudah ada record manifest untuk model tsb, seeder untuk model itu dilewati (bukan insert ulang), dengan pesan info di konsol.

**Rationale**: Manifest yang sama dipakai untuk dua tujuan sekaligus (deteksi kepemilikan untuk cleanup, DAN deteksi "sudah pernah di-seed" untuk idempotency) — tidak perlu mekanisme terpisah, konsisten dengan Prinsip V (satu struktur data, dua kegunaan yang berkaitan erat).

**Alternatives considered**:
- `firstOrCreate` berbasis kombinasi field unik per model — ditolak: Testimonial/TeamMember tidak punya kombinasi field yang natural dijadikan kunci unik tanpa menambah kolom baru.

## 5. Menjaga konsistensi relasi Portfolio (kategori ↔ proyek) (FR-006)

**Decision**: `PortfolioDemoSeeder` membuat kategori contoh dan proyek contoh dalam satu seeder yang sama (kategori dibuat lebih dulu, dicatat ke manifest, baru proyek yang mereferensikannya). `demo:clean` menghapus dalam urutan terbalik: proyek portfolio dihapus lebih dulu, baru kategori portfolio — mencegah pelanggaran foreign key sekaligus mencegah "kategori yatim" bila suatu saat ada proyek non-demo yang ikut memakai kategori demo tsb (kategori demo baru dihapus bila tidak ada proyek lain, demo maupun asli, yang masih memakainya).

**Rationale**: Urutan hapus anak-lalu-induk adalah praktik standar untuk data relasional; pengecekan "masih dipakai proyek lain" sebelum menghapus kategori mencegah demo cleanup secara tidak sengaja merusak data asli admin yang kebetulan memakai kategori yang sama dengan kategori demo.

**Alternatives considered**:
- Selalu hapus kategori demo tanpa pengecekan pemakaian — ditolak, berisiko menghapus kategori yang sudah dipakai proyek asli admin (foreign key constraint akan gagal atau, lebih buruk, proyek asli kehilangan kategorinya bila FK diatur `nullOnDelete`).

## Outstanding NEEDS CLARIFICATION

Tidak ada. Seluruh keputusan teknis memakai pola yang sudah mapan di kodebase ini (seeder class, Artisan command, Eloquent relations) tanpa dependency baru.
