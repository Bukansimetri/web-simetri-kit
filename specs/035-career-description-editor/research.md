# Research: Deskripsi Lowongan Karir Berformat

## R1 — Sanitizer membuang judul

- **Temuan**: `HtmlSanitizer` mengizinkan `p, br, span, div, strong, b, em, i, u, a, img, ul, ol, li, small`. Tag lain di-unwrap (isinya dipertahankan, tag dibuang). Toolbar `RichEditor` Filament menghasilkan `h2`, `h3`, `blockquote`, `s`, `pre/code`.
- **Keputusan**: Tambahkan `h2`, `h3`, `h4`, `blockquote` ke daftar yang diizinkan. Tag lain (mis. `s`, `pre`) tidak ditambah; `RichEditor` dibatasi tombolnya agar tidak menawarkan format yang akan dibuang.
- **Alasan**: Judul bagian adalah inti permintaan. Penambahan bersifat aditif dan aman (tanpa atribut berbahaya; atribut tetap disaring).
- **Alternatif**: Mengganti sanitizer dengan pustaka eksternal (ditolak: dependency baru). Hanya memakai `strong` sebagai pengganti judul (ditolak: tidak memenuhi spec).

## R2 — Lowongan lama (teks polos)

- **Temuan**: Detail merender `{{ $job->description }}` dengan `whitespace-pre-line`. Trix/RichEditor akan menggabungkan baris bila diberi teks polos bernewline, sehingga isi lama tampak rusak di editor.
- **Keputusan**: Migrasi data mengonversi setiap deskripsi tersimpan: `e()` pada teks, pecah paragraf di baris kosong → `<p>`, newline tunggal → `<br>`. Baris yang sudah berupa HTML (mengandung tag blok yang dikenal) dilewati, sehingga aman dijalankan ulang.
- **Alasan**: Tampilan publik dan editor sama-sama benar tanpa langkah manual (FR-004, FR-005, FR-011).
- **Alternatif**: Konversi saat render (ditolak: editor tetap menampilkan teks polos). Kolom kedua untuk HTML (ditolak: kompleksitas, dua sumber kebenaran).

## R3 — Ringkasan kartu dan meta

- **Keputusan**: `JobOpening::descriptionExcerpt(?int $limit = null)` memberi jarak antar blok (`</p>`, `</li>`, `<br>`, tutup judul → spasi), `strip_tags`, dekode entitas, rapatkan spasi, lalu `Str::limit` bila perlu. Kartu memakai ini dengan `line-clamp-2`; meta memakai limit 155.
- **Alasan**: Tanpa jarak antar blok, kata dari paragraf berbeda menempel.

## R4 — Gaya tampil di detail

- **Keputusan**: Pakai kelas varian Tailwind pada kontainer (`[&_p]:mt-4`, `[&_ul]:list-disc`, `[&_h2]:…`, `[&_a]:text-primary`) seperti `legal.blade.php`; tanpa plugin typography. Judul `font-bold` mengikuti keseragaman tipografi.
- **Alasan**: Konsisten dengan halaman lain; tanpa dependency baru. Perlu `npm run build`.

## R5 — Toolbar editor

- **Keputusan**: `RichEditor` dengan tombol: `h2`, `h3`, `bold`, `italic`, `underline`, `link`, `bulletList`, `orderedList`, `blockquote`, `undo`, `redo`. Tanpa `attachFiles`, `strike`, `codeBlock`.
- **Alasan**: Hanya format yang dipertahankan sanitizer dan tidak ada lampiran tersembunyi (edge case spec).

## R6 — Validasi "tampak kosong"

- **Keputusan**: Aturan validasi kustom pada field: `strip_tags` + dekode entitas + `trim` kosong → pesan "Deskripsi wajib diisi."
- **Alasan**: Editor menyimpan `<p></p>` bila dikosongkan, yang lolos `required` biasa.

## R7 — Cache

- **Temuan**: `JobOpening` memakai `FlushesPublicPageCache`; perubahan otomatis membuang cache publik. Tidak perlu kerja tambahan (FR-009). Migrasi data memakai query builder langsung, jadi tidak memicu event; cache dibuang sekali di akhir migrasi.
