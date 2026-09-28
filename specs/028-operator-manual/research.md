# Research: Manual Operator Panel Admin

**Feature**: [spec.md](./spec.md) | **Plan**: [plan.md](./plan.md) | **Date**: 2026-09-26

Fakta di bawah diambil dari branch `028-operator-manual` (turunan `main` di commit `e480e31`, sudah berisi Dashboard prospek dari PR #24), dengan panel dirender untuk user `super_admin` yang punya semua izin.

## 1. Lokasi dan nama dokumen

- **Decision**: Satu file `docs/manual-operator.md`.
- **Rationale**: Folder `docs/` sudah menjadi tempat semua dokumentasi dan ikut ter-clone ke repo klien. Satu file dengan daftar isi memudahkan operator melompat ke tugas (FR-017) dan memudahkan developer mengekspornya ke PDF bila perlu. Nama berbahasa Indonesia mengikuti `panduan-section.md`, `panduan-tema.md`.
- **Alternatives considered**: Beberapa file per grup menu (ditolak: operator harus berpindah-pindah file, dan ekspor ke PDF jadi lebih repot); halaman bantuan di panel (ditolak oleh user saat memilih format).

## 2. Menu yang benar-benar tampil di panel

Navigasi yang dirender untuk super admin (urutan sesuai panel):

| Grup | Menu |
|---|---|
| (tanpa grup) | Dasbor |
| Content | Media Manager |
| Konten Halaman | Halaman, Banner, Testimoni, Tim, Logo Klien, Halaman Tentang Kami |
| Katalog | Produk, Kategori Produk, Peralatan Listrik |
| Prospek & Pesan | Lead Kalkulator, Pesan Masuk |
| Blog | Artikel, Kategori Artikel |
| Portfolio | Portfolio, Kategori Portfolio |
| Karir | Lowongan Kerja |
| Menu Builder | Lokasi Menu, Item Menu |
| Pengaturan Situs | Pengaturan Umum, Tampilan, SEO, Media Sosial, Scripts & Analytics, Kalkulator Estimasi |
| Sistem | Pengguna, Peran, Log Aktivitas |

- **Decision**: Manual mengikuti daftar ini persis, termasuk grup **Content** dan **Media Manager** yang berlabel Inggris (dari plugin media manager, tidak didaftarkan di `navigationGroups()`). Spec FR-004/FR-005 sudah diperbarui untuk mencakupnya.
- **Rationale**: FR-015 mewajibkan label sama persis dengan panel. Menerjemahkan label di manual padahal panel masih berbahasa Inggris akan membingungkan operator.
- **Temuan sampingan**: Grup "Content" melanggar konvensi label panel berbahasa Indonesia (`docs/arsitektur.md`, bagian Konvensi). Diusulkan sebagai tiket Linear terpisah; tidak diubah di fitur ini. Bila kelak diubah, test di §6 akan gagal dan manual ikut diperbarui.

## 3. Label di luar menu

| Tempat | Label |
|---|---|
| Halaman login | "Masuk ke akun Anda", kolom "Alamat email", "Kata sandi", centang "Ingat saya", tombol "Masuk" |
| Menu pengguna (kanan atas) | "Profil" (halaman berjudul "Profil saya"), "Keluar" |
| Mode pemeliharaan aktif | Pita merah di atas panel: "Mode Pemeliharaan aktif — pengunjung publik melihat halaman pemeliharaan." |

- **Decision**: Label kolom, tombol, dan status di tiap menu diambil langsung dari definisi form/tabel resource dan halaman pengaturan saat manual ditulis (misal status Pesan Masuk: Baru, Sudah Dihubungi, Selesai; status Lead Kalkulator: Baru, Sudah Dihubungi, Qualified, Deal, Batal).
- **Rationale**: Label kolom tidak bisa diverifikasi otomatis dengan murah (§6), jadi diverifikasi manual sesuai [quickstart.md](./quickstart.md).

## 4. Fakta perilaku yang harus dijelaskan ke operator

- **Lupa password**: Panel hanya punya login, tanpa reset password mandiri. Admin mereset password staf lewat **Sistem → Pengguna** (kolom "Kata Sandi" di form edit). Bila super admin sendiri lupa, hubungi developer.
- **Hapus data**: Tidak ada model yang memakai soft delete, jadi penghapusan permanen. Manual menyarankan memakai status nonaktif/draft bila ragu.
- **Jeda tampil di situs**: Halaman publik di-cache 5 menit. Perubahan Banner (Home), Tim, Testimoni, Logo Klien, dan Halaman Tentang Kami (halaman Tentang Kami) langsung menghapus cache terkait. Produk, Artikel, Portfolio, dan FAQ bisa terlambat hingga 5 menit. Sumber: `docs/arsitektur.md` bagian "Cache halaman publik". Testimoni di Home punya keterlambatan yang sudah tercatat sebagai masalah diketahui; manual cukup menyebut "sebagian halaman hingga 5 menit".
- **Mode pemeliharaan**: Diatur di **Pengaturan Umum**. Pengunjung anonim melihat halaman pemeliharaan; pengguna yang sedang login dan panel admin tidak terpengaruh (lihat `tests/Feature/Public/MaintenanceModeTest.php`). Karena admin yang login tetap melihat situs normal, manual menyarankan memeriksa lewat jendela penyamaran (incognito).
- **Peran dan menu yang terlihat**: Seeder membuat peran `super_admin`, `Editor`, dan `Viewer`. Saat ini hanya menu Peran, Media Manager, dan Log Aktivitas yang dibatasi izin; menu konten lain terlihat oleh semua pengguna yang punya peran. Manual tidak menjanjikan pembatasan per peran yang belum ada; cukup menjelaskan bahwa menu yang tampil bisa berbeda per peran dan pengaturan izin dilakukan admin utama atau developer.

## 5. Tanpa screenshot

- **Decision**: Tidak ada gambar; label ditulis tebal persis seperti di panel, dengan pola jalur `**Grup** → **Menu**`.
- **Rationale**: Asumsi di spec. Logo dan warna panel berbeda per klien, dan screenshot cepat usang. Label tebal membuat teks mudah dicocokkan dengan layar dan bisa diverifikasi otomatis (§6).

## 6. Verifikasi otomatis

- **Decision**: Test baru `tests/Feature/Docs/OperatorManualTest.php` yang memeriksa:
  1. Setiap label grup dan menu di navigasi panel (dirender untuk super admin dengan semua izin) muncul di manual sebagai teks tebal `**Label**` (FR-004, FR-015, SC-003).
  2. Tidak ada fenced code block, path repo, atau perintah terminal (`php artisan`, `composer`, `npm`, `git `) di manual (FR-002, SC-005).
  3. Tidak ada nama starter kit atau vendor panel (`Simetri`, `Solarpanel`, `Filament`, `Laravel`) di manual (FR-014).
  4. README dan `docs/arsitektur.md` menautkan manual (FR-016).

  Tanggal terakhir diperbarui dan validitas tautan relatif memakai test yang sudah ada dengan menambahkan `docs/manual-operator.md` ke data provider `tests/Feature/Docs/TechnicalDocsPathsTest.php`.
- **Rationale**: Navigasi diambil dari panel saat test berjalan, bukan daftar tetap, sehingga menu baru atau label yang berganti otomatis membuat test gagal sampai manual diperbarui. Ini menjawab edge case "manual tertinggal dari panel".
- **Alternatives considered**: Daftar label tetap di test (ditolak: tidak mendeteksi menu baru); tidak ada test (ditolak: SC-003/SC-004 tidak terjaga setelah rilis).
- **Catatan implementasi test**: Izin dipenuhi dengan `Gate::before(fn () => true)` di dalam test, dan user diberi peran `super_admin` karena Log Aktivitas memeriksa nama peran langsung.
