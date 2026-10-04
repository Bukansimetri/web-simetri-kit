# Contract: Rute Publik & Tampilan

## GET /artikel

Query opsional:

| Param | Aturan | Perilaku |
|---|---|---|
| `q` | string, dipotong 100 karakter, `%` dan `_` di-escape | Menyaring artikel terbit yang judul atau ringkasannya memuat kata kunci. Kosong atau tidak ada berarti semua artikel. |

Respons 200 (HTML). Elemen yang dijamin ada:

- Hero (`x-sections.page-hero`) dengan breadcrumb "Beranda / Artikel".
- Kotak pencarian (`<form method="GET" action="/artikel">`, input `name="q"` terisi nilai saat ini).
- Grid kartu artikel. Tiap kartu menampilkan badge kategori di atas gambar, tanggal, judul, ringkasan, dan "Baca Selengkapnya".
- Pesan "tidak ditemukan" beserta tautan "Hapus pencarian" bila `q` terisi dan hasil kosong.
- Sidebar dengan kartu langganan.

Tidak ada artikel unggulan besar dan tidak ada CTA penutup.

## POST /langganan (baru)

Nama rute `newsletter.subscribe`. Middleware: `throttle:5,1`.

Body:

| Field | Aturan |
|---|---|
| `email` | wajib, email valid, maks 255 |
| `website` | honeypot, harus kosong |
| `form_token` | token waktu dari `SubmissionGuard::issueToken()` |

Respons (permintaan JSON):

| Status | Kondisi | Body |
|---|---|---|
| 201 | email baru valid | `{"message": "Terima kasih, Anda sudah berlangganan."}` |
| 201 | email sudah terdaftar | pesan identik (idempoten) |
| 201 | terdeteksi otomatis (honeypot/token) | pesan identik, tidak ada yang disimpan |
| 422 | email kosong atau tidak valid | `{"message": "...", "errors": {"email": ["..."]}}` |
| 429 | melampaui batas per email atau IP | `{"message": "Terlalu banyak percobaan..."}` |

Permintaan non-JSON (tanpa JavaScript): redirect kembali ke `/artikel#langganan` dengan flash `status` atau `errors`.

## GET /produk

Setiap kartu hanya berisi: tautan pembungkus ke `/produk/{slug}`, gambar (atau penampung gambar), dan nama produk rata tengah. Tidak ada badge kategori, deskripsi, harga, maupun tautan tambahan.

## GET /portfolio

- Hero dengan breadcrumb "Beranda / Portfolio".
- Filter pil: tautan `?kategori={slug}`, pil aktif berkelas berbeda.
- Kartu proyek: gambar, nama kategori, judul, ringkasan (maks 3 baris), "Lihat Detail Proyek →". Seluruh kartu menuju `portfolio.show`.

## GET /

- "Mengapa Beralih" dan "Sederhana dan Mulus": tidak ada kelas `rotate-*` dan `-translate-y-*`, dan struktur data tidak berubah.
- Testimoni: label `TESTIMONI`, judul "Partner Kami", tersembunyi bila tidak ada testimoni aktif.
- "Solusi Untuk Setiap Kebutuhan": markup tidak berubah.

## Admin (Filament)

`NewsletterSubscriberResource`: daftar saja (email, tanggal daftar), pencarian email, aksi hapus. Tanpa create/edit. Banner Halaman Lain (yang sudah ada) memuat entri Artikel dan Portfolio untuk mengubah hero.
