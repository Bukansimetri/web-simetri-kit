# Contract: Panel Admin

## Halaman Tampilan Section (baru)

- Grup navigasi **Pengaturan Situs**, label "Tampilan Section", URL `/admin/section-visibility-settings-page` (slug Filament bawaan), dengan tombol Simpan.
- Satu blok per halaman situs, sesuai urutan [section-catalog.md](section-catalog.md).
  - Judul blok = nama halaman. Bila ada section tersembunyi, judul diberi tambahan "· N disembunyikan".
  - Satu toggle **Tampilkan** per section (label = nama section), bawaan menyala.
  - Tautan "Edit isi" di samping toggle menuju menu isi section (bila ada).
- Simpan menampilkan notifikasi "Tampilan section tersimpan".
- Akses sama dengan halaman pengaturan lain.

## Penanda di menu isi

- Halaman daftar menu isi (lihat tabel R6 di research.md) menampilkan subjudul peringatan bila satu atau lebih section terkait tersembunyi:
  "Section ini sedang disembunyikan dari situs: {Halaman} – {Section}[, …]. Atur di Tampilan Section."
  ("Tampilan Section" berupa tautan.)
- Tidak ada subjudul bila semua section terkait tampil.
- Tabel CTA, Blok Halaman, dan FAQ memiliki kolom **Tayang**: badge "Disembunyikan" (warna peringatan) untuk baris yang section-nya tersembunyi, "—" untuk lainnya.
