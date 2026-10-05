# Contract: Tampilan Publik Lowongan

## GET /karir/{lowongan}

- Deskripsi tampil sebagai HTML yang sudah dibersihkan, dengan judul bagian, daftar (butir/nomor), tebal/miring, tautan, dan kutipan bergaya.
- Tidak ada `<script>`, atribut `on*`, atau tautan `javascript:` dari isi admin.
- Deskripsi meta: teks polos maksimal 155 karakter, tanpa tanda format.
- Bagian lain halaman (hero, badge tipe/lokasi, tombol Lamar/Kembali) tidak berubah.

## GET /karir (kartu lowongan)

- Ringkasan: teks polos, `line-clamp-2`, tanpa tanda format; kata antar blok dipisah spasi.
- Tinggi kartu seragam untuk deskripsi pendek maupun panjang.

## Lowongan lama

- Deskripsi teks polos yang dikonversi migrasi tampil dengan paragraf dan jeda baris yang sama seperti sebelumnya.
