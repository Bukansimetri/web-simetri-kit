# Data Model: Deskripsi Lowongan Berformat

## job_openings (tanpa perubahan skema)

| Kolom | Tipe | Perubahan |
|---|---|---|
| description | text | Isi kini HTML hasil editor (sebelumnya teks polos). Wajib; tampak kosong ditolak. |

Kolom lain (`title`, `location`, `employment_type`, `is_active`) tidak berubah.

## Aturan isi

- Disimpan apa adanya dari editor; dibersihkan `HtmlSanitizer` setiap kali ditampilkan.
- Tag yang dipertahankan: paragraf, `br`, judul `h2`–`h4`, tebal, miring, garis bawah, daftar, tautan (http/https/mailto/tel), kutipan.

## Konversi data lama (migrasi)

Untuk tiap baris `description` yang belum berupa HTML:

1. Normalisasi newline (`\r\n` → `\n`), trim.
2. Escape HTML pada seluruh teks.
3. Pecah paragraf pada baris kosong → `<p>…</p>`; newline tunggal di dalam paragraf → `<br>`.
4. Baris yang sudah mengandung tag blok (`<p`, `<ul`, `<ol`, `<h2`–`<h4`, `<blockquote`, `<br`) dilewati.

Migrasi tidak dapat dibalik secara berarti (`down()` kosong); data HTML tetap valid.
