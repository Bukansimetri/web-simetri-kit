# Contract: `demo:seed` & `demo:clean` CLI Commands

**Feature**: 019-demo-content-seeder

## `demo:seed`

**Signature**: `php artisan demo:seed`

**Perilaku**:
- Mengisi konten contoh untuk kelima entitas (PortfolioCategory, Product, TeamMember, Testimonial, PortfolioProject) sesuai urutan di [data-model.md](../data-model.md).
- Setiap entitas dicek dulu terhadap manifest (`demo_seed_records`): bila entitas tsb sudah pernah di-seed sebelumnya (ada baris manifest untuk model tsb), langkah untuk entitas itu DILEWATI dengan pesan info — **tidak** insert ulang (FR-005, idempotency).
- TIDAK PERNAH dipanggil otomatis oleh `db:seed`/`migrate --seed` standar (`DatabaseSeeder`) maupun oleh `app:setup-client` (FR-003) — hanya dijalankan lewat pemanggilan eksplisit command ini.
- Selesai dengan ringkasan: entitas mana yang diisi, mana yang dilewati (sudah ada).

**Exit code**: `0` pada kondisi normal (termasuk saat sebagian/seluruh entitas dilewati karena sudah ada) — bukan kegagalan.

## `demo:clean`

**Signature**: `php artisan demo:clean`

**Perilaku**:
- Menghapus tepat baris data yang tercatat di manifest (`demo_seed_records`), dalam urutan kebalikan seeding (lihat [data-model.md](../data-model.md) §Urutan operasi), lalu menghapus baris manifest yang bersangkutan.
- `PortfolioCategory` demo HANYA dihapus bila tidak ada `PortfolioProject` lain (demo maupun bukan) yang masih mereferensikannya (FR-006).
- Data yang BUKAN bagian manifest (ditambahkan admin lewat panel secara terpisah) TIDAK PERNAH ikut terhapus, meski kebetulan berada di modul yang sama (FR-004).
- Dijalankan pada instalasi yang tidak pernah di-`demo:seed` (manifest kosong) MUST selesai tanpa error dan tanpa melakukan perubahan apa pun.

**Exit code**: `0` pada kondisi normal, termasuk saat manifest kosong.

## Independensi dari alur setup standar

Baik `demo:seed` maupun `demo:clean` MUST TIDAK muncul/terpicu di dalam alur `php artisan migrate --seed`, `php artisan db:seed` (tanpa argumen `--class`), atau `php artisan app:setup-client` — ketiganya harus menghasilkan instalasi yang sama sekali tidak mengandung konten demo (SC-002).
