# Contract: Admin Panel Surface (Artikel & Kategori Artikel)

**Feature**: [spec.md](../spec.md) | **Date**: 2026-09-05

Fitur ini tidak mengekspos API publik. "Kontrak" di sini adalah permukaan admin panel yang harus konsisten dipenuhi implementasi, supaya bisa diverifikasi lewat feature test tanpa bergantung pada detail implementasi Filament.

## 1. Kategori Artikel (User Story 1)

| Aspek | Kontrak |
|---|---|
| Route | `/admin/article-categories` (Filament Resource: index, create, edit) |
| Akses | Semua role dengan akses panel admin (FR-017) |
| Validasi nama | Submit dengan `name` yang sudah dipakai kategori lain MUST ditolak dengan pesan error jelas |
| Hapus kategori dipakai | `DeleteAction` pada kategori yang `articles_count > 0` MUST dibatalkan dengan notifikasi error — record TIDAK terhapus |
| Hapus kategori kosong | Kategori tanpa artikel MUST bisa dihapus tanpa halangan |

## 2. Artikel (User Story 2, 3, 4, 5)

| Aspek | Kontrak |
|---|---|
| Route | `/admin/articles` (Filament Resource: index, create, edit) |
| Akses | Semua role dengan akses panel admin (FR-017) |
| Field wajib | `title`, `article_category_id`, `excerpt`, `content` — submit tanpa salah satunya MUST ditolak dengan pesan error per field (FR-016) |
| Redaksi | Field teks bebas "Redaksi" (byline) MUST tersedia di form, opsional, BUKAN relasi ke akun admin/`User` yang login (FR-022) |
| Slug | Kosong → auto-generate dari `title`; diisi manual → dipakai apa adanya; duplikat dengan artikel lain MUST ditolak (FR-005, FR-006) |
| Editor konten | `content` MUST diisi lewat rich text editor (WYSIWYG), hasil tersimpan sebagai HTML (FR-019) |
| Status Draft/Publish/Jadwal | Form MUST menyediakan cara memilih Draft (published_at kosong), Publish sekarang, atau Jadwalkan (tanggal masa depan) — lihat data-model.md § Status turunan |
| Tag | `TagsInput` MUST bisa menerima tag baru (auto-create) maupun memilih tag existing (autocomplete/suggestions) tanpa duplikat (FR-011, FR-012) |
| Featured image | Upload gambar MUST menampilkan helper text rekomendasi dimensi (mis. 1200×630px) TANPA validasi/penolakan dimensi (FR-020); file yang diupload MUST dikonversi ke WebP sebelum disimpan (FR-021) |
| Validasi gambar | File bukan format gambar umum MUST ditolak dengan pesan error, tidak tersimpan (FR-014) |
| Hapus artikel | MUST minta konfirmasi sebelum diproses (FR-008); record TIDAK lagi muncul di listing admin maupun halaman publik setelahnya |
| Efek simpan ke publik | Create/update/delete artikel MUST tercermin di `/artikel` dan `/artikel/{slug}` pada request berikutnya, tanpa deploy ulang (FR-007) |

## 3. Efek ke Frontend Publik (turunan dari CRUD)

| Aspek | Kontrak |
|---|---|
| Artikel draft | `GET /artikel/{slug-draft}` MUST mengembalikan 404; `GET /artikel` MUST TIDAK menampilkannya (FR-009) |
| Artikel terjadwal (masa depan) | Sama seperti draft SAMPAI tanggal `published_at` tercapai — setelah itu otomatis tampil tanpa aksi admin (FR-010) |
| Slug dihapus | `GET /artikel/{slug-yang-sudah-dihapus}` MUST mengembalikan 404 |
| Blog kosong | `GET /artikel` MUST tetap 200 dengan empty state wajar saat 0 artikel published (perilaku sudah ada dari 002) |
| Artikel tanpa featured image | Halaman publik MUST menampilkan placeholder gambar yang wajar, bukan `<img>` kosong/rusak (FR-015) |
| Konten HTML | Halaman detail artikel MUST merender `content` sebagai HTML (bukan `explode("\n")` seperti sebelumnya) |
