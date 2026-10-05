# Contract: Rute dan Tampilan Publik

## GET /artikel

| Param | Aturan | Perilaku |
|---|---|---|
| `q` | string, maks 100 | cari di judul/ringkasan (spec 030) |
| `kategori` | id kategori | hanya artikel kategori itu; id tak dikenal = semua |
| `tag` | slug tag | hanya artikel bertag itu; slug tak dikenal = semua |
| `halaman` | integer ≥ 1, default 1 | tampilkan 6 × halaman artikel pertama |

Dijamin ada:
- hero
- tombol kategori berupa tautan
- grid kartu dengan `id="artikel-{n}"` berurutan
- tombol "Muat lebih banyak" ke `halaman+1` (hanya bila ada sisa)
- sidebar: Cari Artikel, Tag Populer (bila ada tag terpakai), Update Mingguan
- CTA

Semua parameter aktif dipertahankan pada tautan kategori, tag, dan muat lebih banyak.

## GET /artikel/{slug}

Tambahan dari spec 030:
- "N kali dilihat"
- `<figcaption>` bila ada caption
- produk terkait (maks 4, hanya bila ada)
- sidebar Artikel Terbaru (5) dan Update Mingguan

Setiap pembukaan menambah `view_count` satu.

## GET /artikel/{slug}/preview (baru, `artikel.preview`)

- Tamu: redirect ke login admin.
- Pengguna tanpa akses panel: 403.
- Admin: 200 untuk artikel draf, terjadwal, maupun terbit. Ada banner "Mode Preview", `<meta name="robots" content="noindex">`, dan `view_count` tidak berubah.

## GET /karir/{id} (baru, `karir.show`)

- 200 untuk lowongan aktif saat modul karir hidup: judul, lokasi, jenis, deskripsi utuh, "Lamar Sekarang" → `/kontak`.
- 404 bila lowongan nonaktif, tidak ada, atau modul karir mati.

## GET /karir

- Judul "Posisi Terbuka" rata tengah, bergaya sama dengan judul "Mengapa Bergabung".
- Setiap kartu lowongan memiliki tautan ke `karir.show`.

## GET /produk

- Tanpa tombol filter kategori.
- Section "Pertanyaan Seputar Produk" dari FAQ `produk` aktif. Tidak dirender bila kosong.

## GET /kontak, GET /faq

- Memakai `page-hero` (breadcrumb "Kontak" / "FAQ").
- Kontak: "Pertanyaan Seputar Konsultasi" dari FAQ `kontak` aktif.
- FAQ: hanya FAQ `faq` aktif, dan JSON-LD hanya entri tersebut.

## GET /tentang-kami

- `page-hero`.
- Blok kutipan hanya bila terisi.
- Testimoni dengan label "Testimoni" dan judul "Partner Kami".
- Tim Kami berlatar penuh, anggota rata tengah.

## GET /

- "Solusi Untuk Setiap Kebutuhan" berisi produk `show_on_home`, atau 3 teratas bila tidak ada yang ditandai.
- CTA penutup tombol kedua → `/#kalkulator`.

## Semua halaman publik

- Tidak ada kelas `font-extrabold` atau `font-black`.
- Footer menampilkan `footer_description` dari Pengaturan Umum.

## POST /langganan

Sama seperti spec 030. Satu perbedaan: respons non-JSON redirect ke halaman asal + `#langganan` (fallback `/artikel#langganan`).
