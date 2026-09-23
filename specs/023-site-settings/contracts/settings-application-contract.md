# Kontrak: Penerapan Pengaturan ke Keluaran Publik

**Feature**: 023-site-settings | **Date**: 2026-09-23

Kontrak ini adalah penjabaran FR-059 dan FR-060: setiap pengaturan wajib punya titik penerapan yang dapat diamati pada keluaran publik, dan setiap titik wajib punya feature test.

Disusun **per permukaan keluaran**, bukan per pengaturan, karena test ditulis per permukaan. Sebuah pengaturan dianggap belum selesai selama belum punya baris di tabel ini beserta testnya.

---

## §1. Kepala dokumen — seluruh halaman publik

| Keluaran yang wajib teramati | Sumber | Requirement |
| --- | --- | --- |
| Atribut bahasa dokumen bernilai `default_language` | `SiteSettings` | FR-005 |
| `<title>` mengikuti pola jenis halaman, jatuh ke pola default bila jenisnya tidak punya pola sendiri | `SeoSettings` | FR-025, FR-026 |
| Judul SEO per konten menang atas pola mana pun | `HasSeoMetadata` | FR-028 |
| Tidak ada penanda isian mentah seperti `{page_title}` yang tersisa di judul | `PageTitle` | FR-027 |
| `meta description` memakai deskripsi default situs bila konten tidak punya sendiri | `SeoSettings` | FR-029 |
| `meta keywords` hadir bila diisi, tidak dirender bila kosong | `SeoSettings` | FR-029 |
| `link rel=canonical` memakai kanonik default bila diisi | `SeoSettings` | FR-029 |
| `meta robots` menyatakan larangan indeks saat `allow_indexing` mati | `SeoSettings` | FR-030 |
| `meta robots` menyatakan larangan ikut tautan saat `allow_following` mati | `SeoSettings` | FR-030 |
| `twitter:site` memuat `twitter_handle` bila diisi | `SeoSettings` | FR-031 |
| `og:image` dan `twitter:image` memakai gambar berbagi default | `SocialSettings` | FR-024 |
| Meta verifikasi hadir untuk tiap mesin pencari yang kodenya diisi, dan tidak hadir bila kosong | `SeoSettings` | FR-034 |
| Isi `additional_head_meta` hadir apa adanya | `SeoSettings` | FR-033 |
| JSON-LD Organization memuat data perusahaan dan daftar `sameAs` dari profil sosial terisi | `SiteSettings`, `SocialSettings` | FR-032 |
| Favicon menunjuk berkas dari `favicon_path` | `AppearanceSettings` | FR-071 |
| CSS variable warna dan font mengikuti pengaturan tampilan | `AppearanceSettings` | FR-071 |

---

## §2. Header

| Keluaran yang wajib teramati | Sumber | Requirement |
| --- | --- | --- |
| Ikon platform yang URL-nya terisi menuju URL tersebut | `SocialSettings` | FR-019 |
| Ikon platform yang URL-nya kosong tidak dirender sama sekali | `SocialSettings` | FR-020 |
| Seluruh kelompok ikon tidak dirender bila tidak ada satu pun URL terisi | `SocialSettings` | FR-020 |
| Tidak ada ikon sosial dengan tautan kosong yang tersisa di mana pun | `SocialSettings` | FR-019, SC-005 |
| Logo memakai `logo_path`, jatuh ke nama situs bila kosong | `AppearanceSettings`, `SiteSettings` | FR-071, FR-001 |

---

## §3. Footer

| Keluaran yang wajib teramati | Sumber | Requirement |
| --- | --- | --- |
| Nama, email, telepon, dan alamat perusahaan tampil di blok kontak | `SiteSettings` | FR-003 |
| Baris kontak yang nilainya kosong tidak dirender, tanpa ikon atau label menggantung | `SiteSettings` | FR-004 |
| Teks hak cipta mengikuti pengaturan | `SiteSettings` | FR-007 |
| Ketiga tautan legal menuju alamat yang diisi | `SiteSettings` | FR-007 |
| Tautan legal yang alamatnya kosong tidak dirender | `SiteSettings` | FR-007 |
| Tautan pengaturan cookie hadir hanya saat persetujuan diaktifkan | `ScriptSettings` | FR-075 |
| Tidak ada alamat, email, atau telepon milik klien tertentu yang tersisa sebagai teks tertulis di kode | — | FR-009, SC-002 |

---

## §4. Slot kode pada halaman publik

| Keluaran yang wajib teramati | Sumber | Requirement |
| --- | --- | --- |
| Isi `head_scripts` muncul di dalam `<head>` | `ScriptSettings` | FR-041 |
| Isi `body_start_scripts` muncul tepat setelah `<body>` dibuka | `ScriptSettings` | FR-041 |
| Isi `body_end_scripts` muncul tepat sebelum `</body>` | `ScriptSettings` | FR-041 |
| Isi `footer_scripts` muncul pada bagian footer | `ScriptSettings` | FR-041 |
| Isi `custom_css` dimuat sebagai gaya di `<head>` | `ScriptSettings` | FR-042 |
| Isi `custom_js` dimuat sebelum `</body>` | `ScriptSettings` | FR-043 |
| Seluruh isi slot dimuat apa adanya sebagai kode, tidak berubah menjadi teks yang di-escape | `ScriptSettings` | FR-044 |
| Slot kosong tidak meninggalkan elemen kosong | `ScriptSettings` | FR-047 |
| Tidak ada satu pun slot yang dimuat di dalam panel admin | `ScriptSettings` | FR-046 |

---

## §5. `/robots.txt`

| Keluaran yang wajib teramati | Sumber | Requirement |
| --- | --- | --- |
| Isi yang tersaji sama dengan yang disimpan admin | `SeoSettings` | FR-035 |
| Penanda `{site_url}` tergantikan alamat situs yang sedang aktif | `SeoSettings` | FR-036 |
| Aturan bawaan yang aman tersaji saat isi dikosongkan, bukan berkas kosong | `SeoSettings` | FR-037 |
| Baris `Sitemap:` tidak tersaji saat peta situs dimatikan, meski admin menuliskannya | `SeoSettings` | FR-040 |

---

## §6. `/sitemap.xml`

| Keluaran yang wajib teramati | Sumber | Requirement |
| --- | --- | --- |
| Alamat jenis konten yang sakelarnya mati tidak tercantum, sementara jenis lain tetap tercantum | `SeoSettings` | FR-038 |
| Peta situs menyatakan diri tidak tersedia saat dimatikan | `SeoSettings` | FR-038 |
| Setiap entri mencantumkan frekuensi perubahan dan prioritas default | `SeoSettings` | FR-039 |
| Alamat `/karir` mengikuti sakelar modul karir | `SiteSettings` | FR-072 |

---

## §7. Halaman pemeliharaan

| Keluaran yang wajib teramati | Sumber | Requirement |
| --- | --- | --- |
| Pengunjung anonim pada halaman publik mana pun menerima halaman pemeliharaan | `SiteSettings` | FR-011 |
| Status yang dikembalikan menyatakan kondisi sementara | `SiteSettings` | FR-012 |
| Panel admin tetap dapat diakses | `SiteSettings` | FR-013 |
| Pengguna yang terautentikasi tetap melihat isi situs yang sebenarnya | `SiteSettings` | FR-013 |
| Situs kembali normal segera setelah sakelar dimatikan | `SiteSettings` | FR-011 |

---

## §8. Halaman kesalahan

| Keluaran yang wajib teramati | Sumber | Requirement |
| --- | --- | --- |
| Alamat yang tidak ada menampilkan `error_404_message` | `SiteSettings` | FR-016 |
| Gangguan sistem menampilkan `error_500_message` | `SiteSettings` | FR-016 |
| Pesan bawaan tampil saat pengaturan kosong | `SiteSettings` | FR-015 |
| Halaman memuat identitas situs dan jalan kembali ke halaman utama | `SiteSettings` | FR-016 |
| Tidak ada rincian teknis yang bocor ke pengunjung | — | FR-017 |
| Halaman `500` tetap tampil meski pengaturan tidak dapat dibaca | — | FR-017, research.md R6 |

---

## §9. Tombol berbagi

| Keluaran yang wajib teramati | Sumber | Requirement |
| --- | --- | --- |
| Hanya platform terpilih yang dirender pada halaman artikel dan produk | `SocialSettings` | FR-022, FR-023 |
| Setiap tombol membawa alamat konten yang sedang dibuka | `SocialSettings` | FR-023 |
| Tidak ada tombol dirender saat sakelar dimatikan | `SocialSettings` | FR-022 |

---

## §10. Panel admin

| Perilaku yang wajib teramati | Requirement |
| --- | --- |
| Lima halaman pengaturan tersedia dan masing-masing dapat disimpan sendiri | FR-070 |
| Menyimpan satu halaman tidak mengubah nilai halaman lain | FR-070 |
| Halaman Scripts & Analytics tidak terlihat bagi admin tanpa peran super admin | FR-045 |
| Membuka alamat halaman Scripts & Analytics langsung sebagai admin biasa ditolak | FR-045 |
| Tidak ada dua field di halaman mana pun yang mengatur nilai yang sama | FR-064, SC-012 |
| Panel menampilkan penanda jelas selama mode pemeliharaan aktif | FR-014 |

---

## §11. Migrasi dari Brand Settings

| Perilaku yang wajib teramati | Requirement |
| --- | --- |
| Setiap nilai `brand.*` yang terisi berpindah utuh ke properti tujuannya | FR-068 |
| Tidak ada nilai yang hilang maupun berubah isinya setelah perpindahan | FR-068 |
| Keluaran halaman publik sebelum dan sesudah perpindahan identik selama pengaturan tidak diubah | FR-069, SC-011 |
| Grup `brand` tidak lagi menyimpan properti apa pun setelah migrasi | FR-063 |
| Seluruh titik pemakaian lama tetap berfungsi: logo, favicon, warna, font, gambar berbagi, deskripsi meta, tautan WhatsApp, email notifikasi, sakelar karir | FR-067 |
