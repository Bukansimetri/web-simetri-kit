# Contract: Admin Panel Surface (Produk & Kategori)

**Feature**: [spec.md](../spec.md) | **Date**: 2026-09-03

Fitur ini tidak mengekspos API publik. "Kontrak" di sini adalah permukaan admin panel (route, permission, dan perilaku UI) yang harus konsisten dipenuhi implementasi, supaya bisa diverifikasi lewat feature test tanpa bergantung pada detail implementasi Filament.

## 1. Kategori Produk (User Story 1)

| Aspek | Kontrak |
|---|---|
| Route | `/admin/categories` (Filament Resource standar: index, create, edit) |
| Akses | Semua role dengan akses panel admin (FR-017) — tidak dibatasi role tertentu |
| Validasi nama | Submit dengan `name` yang sudah dipakai kategori lain MUST ditolak, form tetap di halaman dengan pesan error di field `name` |
| Hapus kategori dipakai | `DeleteAction` pada kategori yang `products_count > 0` MUST dibatalkan dengan notifikasi error yang menyebutkan kategori masih dipakai — record TIDAK terhapus |
| Hapus kategori kosong | Kategori tanpa produk MUST bisa dihapus tanpa halangan |
| Urutan | Perubahan `order` MUST langsung mempengaruhi urutan tab filter di `/produk` pada request publik berikutnya |

## 2. Produk (User Story 2, 3, 4, 5)

| Aspek | Kontrak |
|---|---|
| Route | `/admin/products` (Filament Resource standar: index, create, edit) |
| Akses | Semua role dengan akses panel admin (FR-017) |
| Field wajib | `name`, `category_id`, `price` — submit tanpa salah satunya MUST ditolak dengan pesan error per field (FR-014), tidak ada data tersimpan sebagian |
| Slug | Kosong → auto-generate dari `name`; diisi manual → dipakai apa adanya; duplikat dengan produk lain MUST ditolak (FR-006, FR-007) |
| Galeri gambar | `FileUpload` multi-file, reorderable; urutan tersimpan MUST konsisten dengan urutan tampil di frontend publik; gambar pertama MUST jadi cover (FR-010, FR-011) |
| Validasi gambar | File bukan format gambar umum / melebihi ukuran maksimum MUST ditolak dengan pesan error, tidak tersimpan (FR-015) |
| Specs & Features | `Repeater` — baris bisa ditambah/dihapus bebas sebelum simpan; hanya baris yang tersisa saat submit yang tersimpan (FR-012) |
| Hapus produk | MUST minta konfirmasi sebelum diproses (FR-009); setelah terhapus, record TIDAK lagi muncul di listing admin maupun halaman publik |
| Efek simpan ke publik | Create/update/delete produk MUST tercermin di `/produk`, `/produk/{slug}`, dan section "Produk Kami" Home pada request berikutnya, tanpa deploy ulang (FR-008) |

## 3. Efek ke Frontend Publik (turunan dari CRUD)

| Aspek | Kontrak |
|---|---|
| Slug dihapus | `GET /produk/{slug-yang-sudah-dihapus}` MUST mengembalikan 404 (FR-016) |
| Katalog kosong | `GET /produk` dan section "Produk Kami" Home MUST tetap 200 dengan empty state wajar saat 0 produk (edge case) |
| Produk tanpa gambar | Halaman publik MUST menampilkan placeholder gambar yang wajar, bukan `<img>` kosong/rusak (FR-018) |
| Produk tanpa related (kategori sendirian) | Bagian "Produk Terkait" di halaman detail MUST kosong/disembunyikan dengan wajar, bukan error (edge case) |
