# Contract: Public Render & Slider Behaviour (Banner Hero Slider)

**Feature**: [spec.md](../spec.md) | **Date**: 2026-09-14

Kontrak perilaku render beranda yang harus dipenuhi implementasi, terverifikasi lewat feature test dan verifikasi manual pada quickstart.

## 1. Pemilihan mode render

| Kondisi | Yang dirender |
|---|---|
| 0 banner lolos syarat tayang **atau** seluruh berkas gambarnya hilang | `x-sections.hero` (hero bawaan, teks default) |
| Tepat 1 banner tayang dengan berkas gambar ada | `x-sections.hero-slider` mode slide tunggal — tanpa panah, tanpa titik navigasi, tanpa penanda posisi |
| ≥2 banner tayang | `x-sections.hero-slider` mode slider — dengan panah, titik navigasi, dan penanda posisi |

Banner yang recordnya ada namun berkas gambarnya tidak ditemukan MUST dilewati dan tidak ikut dihitung dalam penentuan mode (FR-020).

## 2. Kontrak isi satu slide

Setiap elemen dirender **hanya bila** datanya terisi (FR-002):

| Elemen | Syarat render |
|---|---|
| Gambar latar | Selalu (slide tanpa gambar tidak pernah sampai ke view) |
| Badge | `badge_text` terisi |
| Judul | `heading` terisi |
| Subjudul | `subheading` terisi |
| Tombol utama | `cta_primary_label` dan `cta_primary_url` keduanya terisi |
| Tombol sekunder | `cta_secondary_label` dan `cta_secondary_url` keduanya terisi |
| Trust bar | `trust_html` terisi setelah sanitasi |
| Seluruh blok konten | Minimal satu elemen di atas terisi; bila tidak, slide dirender sebagai gambar penuh tanpa pembungkus konten kosong |

Judul slide pertama MUST dirender sebagai `<h1>`; judul slide berikutnya sebagai `<h2>`, agar beranda hanya punya satu `<h1>`.

## 3. Kontrak tautan

| Kondisi slide | Perilaku |
|---|---|
| Punya minimal satu CTA | Gambar **tidak** dibungkus tautan; `link_url` diabaikan; tombol CTA yang menjadi jalur klik |
| Tanpa CTA, `link_url` terisi | Seluruh area slide dibungkus satu tautan menuju `link_url` |
| Tanpa CTA, tanpa `link_url` | Slide tidak dapat diklik |

Tautan bersarang (`<a>` di dalam `<a>`) MUST tidak pernah terjadi.

## 4. Kontrak preset tampilan

| `overlay_style` | Lapisan | Warna teks |
|---|---|---|
| `dark` | Gradasi gelap dari sisi teks menuju transparan | Putih |
| `light` | Gradasi putih dari sisi teks menuju transparan | Gelap sesuai brand |
| `none` | Tanpa lapisan | Putih dengan bayangan teks |

| `text_position` | Perataan blok konten | Arah gradasi lapisan |
|---|---|---|
| `left` | Kiri | Kiri → kanan |
| `center` | Tengah | Dari kedua sisi ke tengah |
| `right` | Kanan | Kanan → kiri |

Warna tombol dan aksen MUST bersumber dari variabel tema brand, bukan dari nilai yang tersimpan di banner (FR-006).

Seluruh kelas utilitas MUST ditulis literal di kode sumber, tidak dirangkai secara dinamis, agar terdeteksi pemindai CSS saat build.

## 5. Kontrak perilaku slider

| Aspek | Kontrak |
|---|---|
| Perpindahan otomatis | MUST TIDAK ADA dalam kondisi apa pun (FR-011) |
| Panah | Slide sebelumnya / berikutnya, melingkar di kedua ujung |
| Titik navigasi | Satu titik per slide, melompat langsung ke slide tersebut, menandai slide aktif |
| Penanda posisi | Menampilkan posisi slide aktif dan jumlah total, mis. "1 / 3" |
| Papan ketik | Panah kiri/kanan berpindah slide saat fokus berada di dalam slider |
| Sentuh | Geser mendatar berpindah slide; geser menegak tetap menggulir halaman |
| Penanda animasi panah | Dimainkan saat slider pertama kali memasuki area pandang, berhenti sendiri setelah beberapa siklus, dan tidak diulang (FR-012) |
| Petunjuk geser di layar kecil | Tampil menggantikan panah, hilang permanen setelah interaksi pertama |
| Pengurangan gerak | Bila perangkat meminta pengurangan gerak, seluruh animasi penanda dan transisi MUST dimatikan; navigasi tetap berfungsi penuh (FR-014) |
| Tanpa JavaScript | Slide pertama tetap tampil lengkap dengan teks dan tombol |

## 6. Kontrak aksesibilitas

| Aspek | Kontrak |
|---|---|
| Wadah slider | Menyandang peran carousel beserta nama yang menjelaskan isinya |
| Tiap slide | Menyandang peran grup dengan nama "Slide n dari m" |
| Slide non-aktif | MUST disembunyikan dari teknologi bantu **dan** dari urutan fokus papan ketik — tombol CTA pada slide tersembunyi MUST tidak dapat dijangkau `Tab` (FR-015) |
| Panah dan titik | Memiliki nama yang dapat dibaca; titik aktif menandai keadaan terpilihnya |
| Gambar | Memakai `alt_text` banner |
| Kontras | Setiap preset lapisan MUST memenuhi kontras teks minimum pada gambar yang sesuai peruntukannya |

## 7. Kontrak performa

| Aspek | Kontrak |
|---|---|
| Slide pertama | Prioritas muat tinggi, tanpa pemuatan tertunda |
| Slide berikutnya | Pemuatan tertunda dan dekode asinkron |
| Jumlah permintaan | Berpindah slide MUST tidak memicu permintaan jaringan tambahan |
| Tinggi slide | Konsisten antar slide sehingga tidak terjadi pergeseran tata letak saat berpindah |
