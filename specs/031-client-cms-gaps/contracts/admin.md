# Contract: Panel Admin

## Menu FAQ (baru) — `FaqItemResource`

- Grup navigasi: Konten Halaman. Label "FAQ".
- Tabel:
  - Kolom: Pertanyaan, Tempat (badge), Kategori, Aktif (toggle).
  - Filter **Tempat** (default Halaman FAQ). Urutan bisa diseret (`order`) dalam satu tempat.
  - Aksi: edit, hapus, hapus massal.
- Form:
  - Field: Tempat (select: Halaman FAQ / Halaman Produk / Halaman Kontak), Pertanyaan, Jawaban (textarea), Kategori (hanya untuk Halaman FAQ), Aktif.
  - Entri baru diletakkan di urutan terakhir dalam tempatnya.

## Produk (ubah)

- Form: toggle **Tampilkan di Beranda**. Ditolak bila sudah ada 3 produk lain bertanda, dengan pesan "Maksimal 3 produk dapat ditampilkan di Beranda."
- Tabel: kolom ikon "Beranda" (baca saja).

## Artikel (ubah)

- Section Featured Image: field **Keterangan Gambar** (opsional, maks 255).
- Section baru **Produk Terkait**: repeater (pilih produk, dapat diurutkan, tanpa duplikat).
- Halaman edit: aksi header **Preview** (tab baru → `artikel.preview`).
- Tabel: kolom "Dilihat" (view_count, dapat diurutkan).

## Pengaturan Umum (ubah)

- Section baru **Footer**: **Deskripsi Footer** (textarea, opsional, maks 500).

## Banner Halaman (perilaku)

- FAQ dan Kontak kini memiliki kolom **Gambar Latar**.
