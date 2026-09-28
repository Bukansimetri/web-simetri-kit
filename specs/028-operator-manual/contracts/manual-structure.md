# Contract: Struktur Manual Operator

Kontrak isi `docs/manual-operator.md`. Test `tests/Feature/Docs/OperatorManualTest.php` memeriksa bagian yang bisa diotomatisasi; sisanya diperiksa lewat [quickstart.md](../quickstart.md).

## Aturan penulisan

- Bahasa Indonesia sehari-hari, sapaan "Anda", situs disebut "situs Anda".
- Label panel ditulis **tebal** persis seperti di layar. Jalur menu memakai pola `**Grup** → **Menu**`, misal **Konten Halaman** → **Banner**.
- Langkah ditulis sebagai daftar bernomor, satu tindakan per langkah.
- Tanpa fenced code block, path file, perintah terminal, screenshot, nama klien, nama starter kit, atau nama vendor panel.
- Istilah teknis yang tak terhindarkan (slug, SEO, meta description, cache) dijelaskan sekali di bagian Istilah.
- Peringatan ditulis sebagai blockquote diawali **Perhatian:**.

## Urutan bagian

| # | Bagian | Isi wajib | FR |
|---|---|---|---|
| 0 | Judul + `**Terakhir diperbarui**: YYYY-MM-DD` | Satu paragraf: untuk siapa manual ini | FR-016 |
| 1 | Daftar isi | Tautan ke setiap bagian dan subbagian tugas | FR-017 |
| 2 | Masuk, keluar, dan akun Anda | Login, Keluar, Profil (ganti nama dan kata sandi), lupa kata sandi | FR-003 |
| 3 | Mengenal panel | Peta menu: **Dasbor** lalu setiap grup dan menunya, satu kalimat per menu; catatan bahwa menu yang tampil bisa berbeda per peran | FR-004 |
| 4 | Dasbor dan prospek | Kartu angka, tabel "Perlu ditindaklanjuti" dan tanda "Terlambat", grafik mingguan; alur Pesan Masuk dan Lead Kalkulator, arti status, cara mengubah status dan mencatat follow-up | FR-007, FR-008 |
| 5 | Mengelola konten | Satu subbagian per menu konten (FR-005), dikelompokkan per grup. Tiap subbagian: tampil di mana, kolom penting/wajib, status tayang, batasan gambar, langkah tambah/ubah/hapus | FR-005, FR-006 |
| 6 | Pengaturan situs | Satu subbagian per halaman di **Pengaturan Situs**; mode pemeliharaan di bawah **Pengaturan Umum** | FR-009, FR-010 |
| 7 | Pengguna dan peran | Membuat akun, memilih peran, mereset kata sandi staf, mencabut akses, **Peran**, **Log Aktivitas**; tandai "khusus admin utama" | FR-011 |
| 8 | Kapan perubahan tampil di situs | Jeda hingga 5 menit, halaman yang langsung berubah, cara memeriksa | Edge case |
| 9 | Masalah umum | Minimal: lupa kata sandi, menu tidak terlihat, perubahan belum tampil, data terhapus, kapan menghubungi developer | FR-012 |
| 10 | Istilah | Slug, SEO, meta description, cache, draft/terbit | FR-002 |

Peringatan **Perhatian:** wajib ada di: hapus data (bagian 5), Scripts & Analytics dan Kalkulator Estimasi (bagian 6), mengubah slug halaman yang sudah tayang (bagian 5), dan mode pemeliharaan (bagian 6). (FR-013)

## Tautan

- `README.md` menautkan manual di bagian dokumentasi, dengan keterangan "untuk operator/admin klien".
- `docs/arsitektur.md` menautkan manual di "Dokumen terkait" sebagai dokumen untuk operator.
- Manual tidak menautkan dokumen developer (arsitektur, panduan section/tema, deployment), karena bukan untuk pembaca operator.
