# Feature Specification: Kelola Section Konten & CTA dari Panel Admin

**Feature Branch**: `029-why-choose-admin`

**Created**: 2026-09-30

**Status**: Draft

**Input**: User description: "Jadikan section beranda \"Mengapa Beralih Bersama SUOER?\" (saat ini hardcoded di resources/views/components/sections/why-choose.blade.php) dapat dikelola lewat admin panel Filament menggunakan pendekatan Model + Filament Resource per section (seperti Testimonial). Admin dapat membuat, mengubah, menghapus, mengurutkan, dan mengaktifkan/menonaktifkan kartu alasan (icon Material Symbols, judul, deskripsi, penanda kartu yang ditonjolkan/emphasized). Judul dan subjudul section juga dapat diubah dari admin. Konten yang sekarang hardcoded (Efisien & Terjangkau, Garansi Panjang, Ramah Lingkungan) menjadi data awal (seeder) sehingga tampilan beranda tidak berubah setelah migrasi. Jika tidak ada kartu aktif, section disembunyikan atau memakai fallback yang wajar."

## Konteks

Beberapa section berisi daftar item dan beberapa blok CTA (ajakan bertindak) di situs publik masih ditulis langsung di kode. Setiap perubahan teks atau ikon harus lewat developer dan deploy ulang. Klien ingin admin bisa mengubahnya sendiri dari panel admin.

### Section daftar item

| Section | Halaman | Item | Isi item | Item ditonjolkan (desain sekarang) | Subjudul section |
|---|---|---|---|---|---|
| **Mengapa Beralih** ("Mengapa Beralih Bersama SUOER?") | Beranda | 3 kartu alasan | ikon, judul, deskripsi | Ya: "Garansi Panjang" (kartu biru, miring, terangkat) | Ada |
| **Cara Kerja** ("Sederhana dan Mulus") | Beranda | 4 langkah | nomor 01–04, judul, deskripsi | Ya: "Inverter" (lingkaran nomor berwarna) | Ada |
| **Mengapa Bergabung** ("Mengapa Bergabung dengan Kami?") | Karir | 3 kartu nilai | ikon (dalam lingkaran), judul, deskripsi | Tidak ada | Ada |
| **Proses Rekrutmen** | Karir | 4 langkah | nomor 1–4, judul, deskripsi | Tidak ada | Tidak ada |

Di "Cara Kerja", kartu langkah punya kemiringan dan posisi naik-turun yang berbeda sesuai urutannya, dihubungkan garis putus-putus. Di "Proses Rekrutmen", langkah dihubungkan garis lurus di dalam kotak latar.

### Blok CTA

| Penempatan | Halaman | Teks saat ini (judul) | Isi | Tujuan tombol (tetap) |
|---|---|---|---|---|
| **Beranda** | Beranda (CTA penutup) | "Siap beralih ke / energi matahari?" | judul 2 baris, paragraf, 2 tombol | WhatsApp & halaman Kontak |
| **Produk – Kalkulator** | Daftar Produk | "Bingung pilih yang mana?" | judul, paragraf, 1 tombol | Kalkulator di beranda |
| **Produk – Penutup** | Daftar Produk | "Belum yakin kapasitas yang Anda butuhkan?" | judul, paragraf, 1 tombol | Halaman Kontak |
| **Detail Produk** | Detail Produk | "Masa Depan Energi Anda" | judul, paragraf yang memuat nama produk, 1 tautan | Halaman Kontak |
| **Daftar Artikel** | Daftar Artikel | "Punya pertanyaan seputar energi surya?" | judul, paragraf, 1 tombol | Halaman Kontak |
| **Detail Artikel** | Detail Artikel | "Siap beralih ke energi surya?" | judul, subjudul, 1 tombol | WhatsApp (atau Kontak) |
| **Tentang Kami** | Tentang Kami | "Ingin tahu lebih lanjut tentang SUOER?" | judul, subjudul, 1 tombol | WhatsApp (atau Kontak) |
| **FAQ** | FAQ | "Masih ada pertanyaan lain?" | judul, subjudul, 1 tombol | Halaman Kontak |
| **Karir** | Karir | "Tidak menemukan posisi yang cocok?" | judul, subjudul, 1 tombol | Halaman Kontak |

### Tambahan (Session 2026-10-02): Tentang Kami & Kontak

| Bagian | Halaman | Bentuk | Isi |
|---|---|---|---|
| **Misi** | Tentang Kami | Section item (5 poin, 3 kolom kiri + sisanya kolom kanan) | eyebrow, judul, subjudul; tiap poin: judul, deskripsi (ikon centang tetap) |
| **Nilai** | Tentang Kami | Section item (3 kartu berikon) + kartu besar bergambar | judul, subjudul, kartu besar (gambar, ikon, judul, deskripsi); tiap kartu: ikon, judul, deskripsi |
| **Trust Strip** | Tentang Kami | Section item (3 angka, tanpa judul section) | tiap item: ikon, angka (mis. "5.000+"), keterangan |
| **Hero** | Tentang Kami | Blok halaman | gambar latar, subjudul (judul tetap otomatis) |
| **Siapa Kami** | Tentang Kami | Blok halaman | gambar, teks badge, eyebrow, judul, isi (teks kaya), kutipan (teks kaya) |
| **Visi** | Tentang Kami | Blok halaman | eyebrow, judul, subjudul |
| **Info Kontak** | Kontak | Blok halaman | label tombol WhatsApp, jam operasional, pesan otomatis WhatsApp |

### Pendekatan

Mengikuti pola modul konten daftar yang sudah ada, seperti **Testimoni**. Setiap section daftar item menjadi satu modul di panel admin dengan daftar, form tambah/ubah, urutan, dan status aktif, serta pengaturan judul/subjudul section. CTA dikelola di satu modul berisi sembilan penempatan tetap, dan admin hanya bisa mengubah teksnya.

**Tampilan tidak berubah sama sekali.** Admin hanya mengatur isi, bukan desain. Semua data awal diisi persis sama dengan teks yang sekarang ditulis di kode.

## Clarifications

### Session 2026-09-30

- Q: Apakah mengedit section ini boleh mengubah tampilan? → A: Tidak sama sekali. Tampilan, struktur, dan gaya visual section tetap persis seperti desain existing. Yang berubah hanya sumber isinya (teks & ikon kini berasal dari panel admin).
- Q: Berapa batas item aktif agar tampilan tetap sesuai desain? → A: Batas maksimal item aktif ditentukan per section sesuai desainnya: "Mengapa Beralih" = 3, "Cara Kerja" = 4. Item nonaktif tidak dibatasi.
- Q: Section beranda mana yang masuk cakupan fitur ini? → A: "Mengapa Beralih" dan "Cara Kerja". Judul/subjudul Kalkulator, Produk, dan Testimoni di beranda dikerjakan di fitur terpisah.
- Q: CTA mana yang ikut bisa diedit, dan apa yang bisa diedit? → A: CTA Penutup beranda dan CTA Band di semua halaman yang memakainya. Teks masing-masing penempatan diedit terpisah. Link, ikon, dan tampilan tombol tetap seperti sekarang.
- Q: Apakah tanya-jawab (halaman FAQ dan "Pertanyaan Seputar Konsultasi" di Kontak) digabung ke fitur ini? → A: Tidak. Dikerjakan sebagai fitur terpisah: satu modul admin FAQ.
- Q: Apakah section di halaman Karir dan CTA di halaman Produk/Artikel ikut dicakup? → A: Ya. "Mengapa Bergabung" (maks 3 aktif) dan "Proses Rekrutmen" (maks 4 aktif) di halaman Karir memakai mekanisme yang sama. Semua CTA yang masih ditulis di kode di halaman Produk, Detail Produk, dan Daftar Artikel ikut menjadi penempatan CTA. Data awal wajib sama persis dengan teks di kode saat ini, dan desain tidak berubah.
- Q: Bagaimana nama merek ("SUOER") ditulis di data awal, dan apakah teks bergantung pada Nama Situs? → A: Teks section & CTA tampil persis seperti isian admin dan **tidak** bergantung pada Nama Situs. Agar merek klien tidak tertanam di kode (constitution Principle I), nilai bawaan diisi Nama Situs **sekali** saat instalasi; setelah itu teks sepenuhnya milik admin.

### Session 2026-10-02

- Q: Apakah isi teks bergantung pada Nama Situs? → A: Tidak. Teks tampil persis seperti isian admin; nama merek di data awal hanya diisi sekali saat instalasi (FR-026).
- Q: Apakah Misi, Nilai, dan Trust Strip di Tentang Kami ikut memakai mekanisme section item? → A: Ya, demi keseragaman (sebelumnya sudah bisa diedit di halaman pengaturan "Halaman Tentang Kami").
- Q: Bagaimana Hero (gambar & subjudul), Siapa Kami, dan Visi di Tentang Kami dikelola? → A: Sebagai **blok halaman** (satu blok = satu form, tanpa daftar item), dengan pola yang sama: tampilan tetap, hanya isi yang diedit. Halaman pengaturan lama "Halaman Tentang Kami" dipensiunkan setelah isinya dipindahkan apa adanya.
- Q: Kartu besar bergambar di section Nilai dikelola di mana? → A: Bagian dari blok section Nilai (diedit bersama judul/subjudul), selalu satu, sehingga slot besar tidak pernah kosong.
- Q: Judul hero Tentang Kami "Mengenal … Lebih Dekat"? → A: Tetap otomatis mengikuti Nama Situs (tidak diedit admin).
- Q: Jam operasional & pesan otomatis WhatsApp di Kontak? → A: Bisa diedit lewat blok halaman "Kontak – Info Kontak" (bukan lewat Pengaturan Umum). Pesan WhatsApp dari blok ini juga dipakai tombol WhatsApp di CTA Beranda dan CTA Band.
- Q: Judul/hero halaman Produk, Artikel, Karir, Kontak, Portfolio? → A: Ditunda (topik terpisah).

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Admin mengubah isi kartu "Mengapa Beralih" di beranda (Priority: P1) 🎯 MVP

Admin membuka modul "Mengapa Beralih" di panel admin dan melihat tiga kartu yang sekarang tampil di beranda. Admin mengubah judul, deskripsi, atau ikon salah satu kartu lalu menyimpan. Saat beranda dibuka ulang, kartu itu menampilkan isi baru dengan tampilan yang sama persis.

**Why this priority**: Ini permintaan awal dan kebutuhan inti klien: mengubah teks tanpa developer. Setelah fitur dirilis, beranda juga harus tetap tampil persis seperti sekarang.

**Independent Test**: Ambil tangkapan layar section sebelum rilis. Jalankan migrasi dan data awal, ambil tangkapan layar lagi, dan pastikan identik. Ubah deskripsi kartu "Ramah Lingkungan" di panel admin, lalu pastikan beranda menampilkan deskripsi baru.

**Acceptance Scenarios**:

1. **Given** fitur baru dirilis dan data awal sudah dimuat, **When** pengunjung membuka beranda, **Then** section menampilkan judul "Mengapa Beralih Bersama SUOER?" (terpotong di baris yang sama), subjudul yang sama, dan tiga kartu (Efisien & Terjangkau, Garansi Panjang, Ramah Lingkungan) dengan ikon, urutan, dan penonjolan yang sama seperti sebelumnya.
2. **Given** admin membuka kartu "Efisien & Terjangkau", **When** admin mengganti judulnya menjadi "Hemat Hingga 80%" dan menyimpan, **Then** beranda menampilkan "Hemat Hingga 80%" di posisi kartu tersebut.
3. **Given** admin mengubah ikon kartu, **When** admin memilih ikon lain dan menyimpan, **Then** beranda menampilkan ikon baru di kartu tersebut.
4. **Given** admin mengosongkan judul atau deskripsi, **When** admin menyimpan, **Then** penyimpanan ditolak dengan pesan yang menjelaskan bahwa kolom wajib diisi.

---

### User Story 2 - Admin mengubah isi langkah "Cara Kerja" di beranda (Priority: P2)

Admin membuka modul "Cara Kerja" dan melihat empat langkah yang sekarang tampil di beranda. Admin mengubah judul atau deskripsi salah satu langkah lalu menyimpan. Beranda menampilkan isi baru dengan nomor, kemiringan, posisi, dan garis penghubung yang sama persis.

**Why this priority**: Polanya sama dengan Story 1, jadi nilainya tinggi dengan usaha tambahan kecil.

**Independent Test**: Bandingkan tangkapan layar section sebelum dan sesudah rilis, pastikan identik. Ubah judul langkah "DC Power", lalu pastikan beranda menampilkan judul baru di posisi ke-2 dengan nomor "02" dan tampilan yang sama.

**Acceptance Scenarios**:

1. **Given** fitur baru dirilis dan data awal sudah dimuat, **When** pengunjung membuka beranda, **Then** section menampilkan judul "Sederhana dan Mulus", subjudul yang sama, dan empat langkah (Panel & PV Cell, DC Power, Inverter, Storage / Grid) bernomor 01–04, dengan "Inverter" ditonjolkan seperti sebelumnya.
2. **Given** admin membuka langkah "Inverter", **When** admin mengubah deskripsinya dan menyimpan, **Then** beranda menampilkan deskripsi baru di langkah ke-3.
3. **Given** admin mengosongkan judul atau deskripsi langkah, **When** admin menyimpan, **Then** penyimpanan ditolak dengan pesan yang menjelaskan bahwa kolom wajib diisi.

---

### User Story 3 - Admin mengubah section "Mengapa Bergabung" dan "Proses Rekrutmen" di halaman Karir (Priority: P3)

Admin membuka modul "Mengapa Bergabung" dan "Proses Rekrutmen", lalu mengubah isi kartu nilai (ikon, judul, deskripsi) atau langkah rekrutmen (judul, deskripsi). Halaman Karir menampilkan isi baru dengan tampilan yang sama persis.

**Why this priority**: Pola sama dengan Story 1 & 2 di halaman lain. Nilainya tinggi untuk tim HR yang sering menyesuaikan teks rekrutmen.

**Independent Test**: Bandingkan tangkapan layar kedua section di halaman Karir sebelum dan sesudah rilis, pastikan identik. Ubah judul langkah "Wawancara HR" menjadi "Wawancara Awal", lalu pastikan halaman Karir menampilkannya di posisi ke-2 dengan nomor "2".

**Acceptance Scenarios**:

1. **Given** fitur baru dirilis dan data awal sudah dimuat, **When** pengunjung membuka halaman Karir, **Then** section "Mengapa Bergabung dengan Kami?" menampilkan subjudul yang sama dan tiga kartu (Inovasi Berkelanjutan, Kolaborasi Tim, Dampak Nyata) dengan ikon yang sama, dan section "Proses Rekrutmen" menampilkan empat langkah (Lamar, Wawancara HR, Penilaian Teknis, Penawaran) bernomor 1–4, semuanya persis seperti sebelumnya.
2. **Given** admin mengganti ikon kartu "Kolaborasi Tim", **When** admin menyimpan, **Then** halaman Karir menampilkan ikon baru di dalam lingkaran yang sama.
3. **Given** admin berada di form kartu "Mengapa Bergabung" atau langkah "Proses Rekrutmen", **When** admin melihat pilihan yang tersedia, **Then** tidak ada pilihan "ditonjolkan", karena desain kedua section ini tidak memiliki item yang ditonjolkan.
4. **Given** admin membuka pengaturan section "Proses Rekrutmen", **When** admin melihat kolom yang tersedia, **Then** hanya judul section yang bisa diubah (tanpa subjudul), sesuai desainnya.

---

### User Story 4 - Admin menambah, menghapus, mengurutkan, menonaktifkan, dan menonjolkan item (Priority: P4)

Di keempat modul section, admin menambah item baru, menghapus item yang tidak relevan, mengatur urutan, dan menonaktifkan item sementara tanpa menghapusnya. Di section yang desainnya punya item ditonjolkan, admin juga menentukan item mana yang ditonjolkan. Jumlah item aktif dibatasi sesuai desain tiap section.

**Why this priority**: Membuat section benar-benar bisa dikelola, bukan hanya diedit teksnya. Tidak kritis untuk rilis pertama.

**Independent Test**: Di "Mengapa Beralih", tambah kartu ke-4 berstatus nonaktif dan pastikan tidak tampil. Coba aktifkan dan pastikan ditolak karena sudah ada 3 kartu aktif. Nonaktifkan satu kartu lama, aktifkan kartu baru, geser ke urutan pertama, dan pastikan beranda mengikuti. Ulangi di "Proses Rekrutmen" dengan batas 4 dan pastikan nomor langkah mengikuti urutan baru.

**Acceptance Scenarios**:

1. **Given** sebuah section punya item aktif di bawah batasnya, **When** admin menambah item baru berstatus aktif, **Then** halaman menampilkan item tersebut sesuai urutan yang ditentukan admin.
2. **Given** "Mengapa Beralih" atau "Mengapa Bergabung" sudah punya 3 item aktif, **When** admin mencoba menambah atau mengaktifkan item lain, **Then** panel menolak dengan pesan bahwa section ini maksimal 3 item aktif. Item tetap bisa disimpan sebagai nonaktif.
3. **Given** "Cara Kerja" atau "Proses Rekrutmen" sudah punya 4 langkah aktif, **When** admin mencoba mengaktifkan langkah ke-5, **Then** panel menolak dengan pesan bahwa section ini maksimal 4 langkah aktif.
4. **Given** ada beberapa item, **When** admin mengubah urutan, **Then** halaman menampilkan item sesuai urutan baru. Di section langkah, nomor langkah (dan di "Cara Kerja" juga kemiringan dan posisi naik-turun kartu) mengikuti posisi baru, bukan menempel pada item.
5. **Given** sebuah item aktif, **When** admin menonaktifkannya, **Then** item hilang dari halaman tetapi tetap ada di daftar admin dan bisa diaktifkan kembali.
6. **Given** sebuah item ada di daftar admin, **When** admin menghapusnya setelah konfirmasi, **Then** item hilang dari daftar admin dan dari halaman.
7. **Given** kartu "Garansi Panjang" sedang ditonjolkan, **When** admin menandai kartu lain sebagai ditonjolkan dan menyimpan, **Then** hanya kartu yang baru ditandai yang tampil ditonjolkan, dan tanda pada "Garansi Panjang" otomatis dilepas. Aturan yang sama berlaku di "Cara Kerja".
8. **Given** tidak ada item ditonjolkan di "Mengapa Beralih" atau "Cara Kerja", **When** pengunjung membuka beranda, **Then** semua item di section itu tampil dengan gaya biasa tanpa error.

---

### User Story 5 - Admin mengubah judul dan subjudul section (Priority: P5)

Admin mengubah judul besar dan subjudul tiap section dari panel admin. Section yang desainnya tidak memiliki subjudul ("Proses Rekrutmen") hanya bisa diubah judulnya.

**Why this priority**: Judul jarang berubah dibanding isi item, tetapi klien memintanya agar seluruh section bisa dikelola tanpa developer.

**Independent Test**: Ubah judul dan subjudul "Mengapa Beralih", "Cara Kerja", dan "Mengapa Bergabung", serta judul "Proses Rekrutmen", lalu pastikan semua berubah di halamannya. Kosongkan satu subjudul dan pastikan section tetap rapi tanpa ruang kosong janggal.

**Acceptance Scenarios**:

1. **Given** admin berada di pengaturan sebuah section, **When** admin mengganti judul dan menyimpan, **Then** halaman menampilkan judul baru di section tersebut.
2. **Given** admin ingin pemenggalan baris seperti desain sekarang ("Mengapa Beralih" / "Bersama SUOER?"), **When** admin menulis judul dengan pindah baris, **Then** halaman menampilkan judul terpotong di baris yang sama. Teks admin tetap diperlakukan sebagai teks biasa, bukan kode.
3. **Given** admin mengosongkan subjudul, **When** pengunjung membuka halaman, **Then** section tampil tanpa paragraf subjudul.
4. **Given** admin mengosongkan judul, **When** admin menyimpan, **Then** penyimpanan ditolak karena judul wajib diisi.

---

### User Story 6 - Admin mengubah teks CTA (Priority: P6)

Admin membuka modul "CTA" dan melihat daftar tetap berisi sembilan penempatan CTA (lihat tabel di Konteks). Admin mengubah judul, subjudul/paragraf, atau label tombol pada salah satu penempatan lalu menyimpan. Halaman terkait menampilkan teks baru dengan tampilan, ikon, dan tujuan tombol yang sama seperti sebelumnya.

**Why this priority**: CTA jarang berubah dibanding isi section, tetapi klien ingin semua teks ajakan bertindak bisa dikelola tanpa developer.

**Independent Test**: Bandingkan tangkapan layar kesembilan CTA sebelum dan sesudah rilis, pastikan identik. Ubah judul CTA "FAQ", lalu pastikan hanya CTA di halaman FAQ yang berubah.

**Acceptance Scenarios**:

1. **Given** fitur baru dirilis dan data awal sudah dimuat, **When** pengunjung membuka Beranda, Daftar Produk, Detail Produk, Daftar Artikel, Detail Artikel, Tentang Kami, FAQ, dan Karir, **Then** setiap CTA menampilkan teks, tombol, ikon, dan tujuan tombol yang sama persis seperti sebelumnya.
2. **Given** admin membuka CTA "FAQ", **When** admin mengganti judulnya dan menyimpan, **Then** hanya CTA di halaman FAQ yang berubah.
3. **Given** admin membuka CTA "Beranda", **When** admin mengganti label tombol "Isi Form Online" menjadi "Minta Penawaran" dan menyimpan, **Then** tombol di beranda menampilkan "Minta Penawaran" dan tetap mengarah ke halaman Kontak.
4. **Given** paragraf CTA "Detail Produk" berisi penanda nama produk, **When** pengunjung membuka detail produk mana pun, **Then** penanda diganti dengan nama produk yang sedang dibuka, sama seperti perilaku sekarang.
5. **Given** admin mengubah CTA "Detail Artikel" atau "Detail Produk", **When** pengunjung membuka artikel atau produk mana pun, **Then** semua halaman detail menampilkan CTA baru yang sama.
6. **Given** admin mengosongkan judul atau label tombol, **When** admin menyimpan, **Then** penyimpanan ditolak karena kolom wajib diisi.
7. **Given** admin berada di modul CTA, **When** admin mencari tombol tambah atau hapus, **Then** tombol itu tidak tersedia karena penempatan CTA tetap mengikuti desain halaman.

---

### User Story 7 - Admin mengelola Misi, Nilai, dan Trust Strip Tentang Kami sebagai section (Priority: P7)

Admin membuka modul "Tentang Kami – Misi", "Tentang Kami – Nilai", dan "Tentang Kami – Trust Strip", lalu mengelola itemnya dengan cara yang sama seperti section lain: ubah, tambah, hapus, urutkan, dan aktif/nonaktif. Judul section (dan eyebrow Misi, serta kartu besar Nilai) diubah lewat "Ubah Judul Section".

**Why this priority**: Keseragaman cara mengelola konten. Sebelumnya bagian ini sudah bisa diedit, tetapi di halaman pengaturan terpisah dengan cara berbeda.

**Independent Test**: Setelah migrasi, halaman Tentang Kami identik dengan sebelumnya (perbandingan HTML). Isi Misi, Nilai, dan Trust Strip sama dengan yang tersimpan di halaman pengaturan lama, bukan teks bawaan. Ubah urutan Misi → poin pindah kolom sesuai posisi barunya.

**Acceptance Scenarios**:

1. **Given** admin pernah mengubah poin Misi di halaman pengaturan lama, **When** migrasi dijalankan, **Then** poin hasil editan admin itulah yang tampil, dengan urutan yang sama.
2. **Given** Misi punya 5 poin aktif, **When** admin memindahkan poin ke-5 ke urutan pertama, **Then** poin itu tampil paling atas di kolom kiri, dan poin yang tadinya ke-3 pindah ke kolom kanan.
3. **Given** Nilai sudah punya 3 kartu aktif, **When** admin mengaktifkan kartu ke-4, **Then** ditolak dengan pesan batas 3.
4. **Given** admin membuka "Ubah Judul Section" Nilai, **When** admin mengganti gambar dan judul kartu besar, **Then** kartu besar di halaman Tentang Kami berubah.
5. **Given** admin membuka modul Trust Strip, **When** melihat halaman daftar, **Then** tidak ada tombol "Ubah Judul Section", dan form item memakai label "Angka" dan "Keterangan".

---

### User Story 8 - Admin mengubah blok halaman: Hero, Siapa Kami, Visi Tentang Kami, dan Info Kontak (Priority: P8)

Admin membuka modul "Blok Halaman", memilih salah satu blok tetap, lalu mengubah isinya (gambar, teks, teks kaya). Halaman terkait menampilkan isi baru dengan tampilan yang sama persis. Halaman pengaturan lama "Halaman Tentang Kami" tidak ada lagi di panel.

**Why this priority**: Menuntaskan pemindahan halaman Tentang Kami ke pola section/blok dan membuat info kontak bisa diedit tanpa developer.

**Independent Test**: Setelah migrasi, Hero/Siapa Kami/Visi identik dengan sebelumnya dan berisi nilai yang tersimpan sebelumnya. Ubah jam operasional di "Kontak – Info Kontak" → halaman Kontak menampilkan jam baru. Ubah pesan WhatsApp → tautan WhatsApp di Kontak, CTA Beranda, dan CTA Band memakai pesan baru.

**Acceptance Scenarios**:

1. **Given** gambar Hero sudah pernah diunggah di halaman pengaturan lama, **When** migrasi dijalankan, **Then** gambar yang sama tetap tampil di Hero Tentang Kami.
2. **Given** admin mengubah isi Siapa Kami (teks kaya) dan menyisipkan skrip, **When** halaman dibuka, **Then** format teks yang diizinkan tampil, skrip tidak dijalankan.
3. **Given** admin mengosongkan gambar Siapa Kami, **When** halaman dibuka, **Then** gambar bawaan tampil.
4. **Given** nomor WhatsApp diisi di Pengaturan Umum, **When** admin mengubah pesan otomatis WhatsApp, **Then** tautan WhatsApp di Kontak, CTA Beranda, dan CTA Band memuat pesan baru.
5. **Given** admin membuka modul Blok Halaman, **When** mencari tombol tambah/hapus, **Then** tidak tersedia.
6. **Given** admin membuka menu panel, **When** mencari "Halaman Tentang Kami", **Then** menu itu sudah tidak ada.

---

### Edge Cases

- **Tidak ada item aktif di sebuah section** (semua dihapus atau dinonaktifkan): seluruh section itu, termasuk judul, subjudul, dan kotak/latar pembungkusnya, disembunyikan dari halaman. Section lain tidak terpengaruh.
- **Item aktif lebih sedikit dari batas** (mis. 2 kartu, atau 3 langkah): item tampil dengan gaya dan ukuran per item yang sama seperti desain, tanpa error. Nomor, kemiringan, dan posisi mengikuti posisi tampil.
- **Mencoba melebihi batas item aktif**: ditolak dengan pesan jelas (lihat FR-007a). Jumlah item nonaktif tidak dibatasi, jadi admin bisa menyimpan item cadangan dan menukarnya kapan saja.
- **Teks sangat panjang**: semua kolom teks punya batas panjang (FR-004, FR-009, FR-021) agar tidak merusak tata letak.
- **Admin menulis HTML atau skrip di kolom teks**: ditampilkan sebagai teks biasa, tidak dijalankan.
- **Ikon tidak dipilih atau tidak valid**: ikon wajib dipilih dari daftar ikon yang disediakan, jadi halaman tidak pernah menampilkan nama ikon mentah.
- **Item ditonjolkan sedang nonaktif**: penonjolan hanya berlaku pada item aktif. Jika item itu nonaktif, semua item aktif di section tersebut tampil dengan gaya biasa.
- **Data awal dimuat ulang** (mis. proses pengisian data awal dijalankan dua kali): tidak menimbulkan item atau CTA duplikat dan tidak menimpa isi yang sudah diubah admin.
- **Nomor WhatsApp di Pengaturan Situs kosong**: tombol WhatsApp di CTA tetap mengarah ke halaman Kontak seperti perilaku sekarang, dengan label yang diatur admin.
- **Data sebuah penempatan CTA tidak ditemukan** (mis. terhapus langsung dari basis data): halaman tetap tampil dengan teks bawaan CTA tersebut (sama dengan teks awal), bukan error atau CTA kosong.
- **Subjudul/paragraf CTA dikosongkan**: CTA tampil tanpa baris subjudul/paragraf, tanpa ruang kosong janggal.
- **Penanda nama produk dihapus admin dari paragraf CTA "Detail Produk"**: paragraf tampil apa adanya tanpa nama produk, tanpa error.
- **Pengaturan lama Tentang Kami berisi data tidak lengkap/rusak** (mis. daftar misi kosong): migrasi tetap berjalan; section tanpa item disembunyikan (FR-013) dan blok memakai nilai kosong/bawaan gambar, bukan error.
- **Misi kurang dari 5 poin aktif**: kolom kiri berisi hingga 3 poin, kolom kanan sisanya (boleh kosong), tanpa error.
- **Gambar lama yang tersimpan sudah tidak ada di disk**: gambar bawaan tampil, bukan gambar rusak.

## Requirements *(mandatory)*

### Functional Requirements

Istilah **section** berarti salah satu section daftar item: "Mengapa Beralih", "Cara Kerja", "Mengapa Bergabung", "Proses Rekrutmen", "Tentang Kami – Misi", "Tentang Kami – Nilai", dan "Tentang Kami – Trust Strip". Istilah **item** berarti kartu (alasan/nilai) atau langkah di dalam section.

#### Aturan per section

| Section | Batas item aktif | Ikon | Bisa ditonjolkan | Subjudul section | Nomor otomatis |
|---|---|---|---|---|---|
| Mengapa Beralih | 3 | Wajib | Ya (maks 1) | Opsional | Tidak |
| Cara Kerja | 4 | Tidak ada | Ya (maks 1) | Opsional | Ya, format "01" |
| Mengapa Bergabung | 3 | Wajib | Tidak | Opsional | Tidak |
| Proses Rekrutmen | 4 | Tidak ada | Tidak | Tidak ada | Ya, format "1" |
| Tentang Kami – Misi | 5 | Tidak ada (ikon centang tetap) | Tidak | Opsional + eyebrow | Tidak; 3 item pertama kolom kiri, sisanya kolom kanan |
| Tentang Kami – Nilai | 3 | Wajib | Tidak | Opsional + kartu besar | Tidak |
| Tentang Kami – Trust Strip | 3 | Wajib | Tidak | Tidak ada judul section | Tidak; kolom judul = "Angka", deskripsi = "Keterangan" |

#### Pengelolaan item

- **FR-001**: Admin MUST dapat melihat daftar semua item tiap section (aktif dan nonaktif) di panel admin, berisi judul, status aktif, dan urutan. Ikon ikut ditampilkan di section yang punya ikon, dan status ditonjolkan ikut ditampilkan di section yang mendukung penonjolan.
- **FR-002**: Admin MUST dapat menambah, mengubah, dan menghapus item. Penghapusan butuh konfirmasi.
- **FR-003**: Setiap item MUST memiliki judul (wajib), deskripsi (wajib), status aktif (bawaan: aktif), dan urutan tampil. Item di section berikon MUST memiliki ikon (wajib). Item di section yang mendukung penonjolan MUST memiliki penanda ditonjolkan (bawaan: tidak). Kolom yang tidak berlaku untuk sebuah section MUST NOT ditampilkan di form admin.
- **FR-004**: Batas panjang item MUST per section: judul 60 / deskripsi 200 untuk section Beranda & Karir; judul 120 / deskripsi 500 untuk Misi & Nilai; angka 60 / keterangan 120 untuk Trust Strip (mengikuti batas halaman pengaturan lama).
- **FR-005**: Ikon MUST dipilih dari daftar ikon yang disediakan (bagian dari keluarga ikon yang sudah dipakai situs), yang minimal mencakup semua ikon yang dipakai data awal. Admin MUST dapat melihat pratinjau ikon saat memilih.
- **FR-006**: Admin MUST dapat mengatur urutan item langsung dari daftar, misalnya dengan menyeret baris, dan urutan ini MUST menentukan urutan tampil di halaman.
- **FR-007**: Admin MUST dapat mengaktifkan/menonaktifkan item langsung dari daftar tanpa membuka form.
- **FR-007a**: Jumlah item aktif MUST dibatasi sesuai tabel aturan per section. Menambah, mengubah, atau mengaktifkan item yang akan membuat jumlah item aktif melebihi batas MUST ditolak dengan pesan yang menyebut batasnya, baik dari form maupun dari tombol aktif/nonaktif di daftar. Item nonaktif tidak dibatasi jumlahnya.
- **FR-007b**: Batas item aktif MUST ditentukan per section mengikuti desainnya dan MUST NOT dapat diubah admin.
- **FR-008**: Di section yang mendukung penonjolan, paling banyak satu item MUST berstatus ditonjolkan. Saat admin menandai sebuah item sebagai ditonjolkan, tanda pada item lain di section yang sama MUST otomatis dilepas.

#### Judul & subjudul section

- **FR-009**: Admin MUST dapat mengubah judul (wajib, maksimal 80 karakter) tiap section, dan subjudul (opsional, maksimal 250 karakter) untuk section yang desainnya memiliki subjudul, melalui tombol "Ubah Judul Section" di halaman daftar modul section tersebut.
- **FR-010**: Pindah baris yang ditulis admin di judul MUST ditampilkan sebagai pindah baris di halaman. Isi lain MUST ditampilkan sebagai teks biasa (tidak menjalankan HTML/skrip).

#### Tampilan halaman

- **FR-011**: Halaman MUST hanya menampilkan item berstatus aktif, sesuai urutan yang ditentukan admin.
- **FR-012**: Item yang ditonjolkan MUST tampil dengan gaya penonjolan yang sama seperti desain sekarang: kartu biru untuk "Mengapa Beralih", lingkaran nomor berwarna untuk "Cara Kerja". Item lainnya MUST tampil dengan gaya biasa yang sama seperti sekarang.
- **FR-012a**: Struktur tampilan, tata letak, warna, tipografi, ukuran, efek hover, garis penghubung, kotak latar, dan animasi semua section dan CTA MUST identik dengan desain existing. Fitur ini hanya mengganti sumber isi, bukan tampilannya.
- **FR-012b**: Panel admin MUST NOT menyediakan pengaturan yang mengubah tampilan (warna, ukuran, posisi, kemiringan, gaya, atau kelas tampilan). Satu-satunya pengaruh visual yang bisa diatur admin adalah memilih item yang ditonjolkan di section yang mendukungnya.
- **FR-012c**: Di section langkah ("Cara Kerja" dan "Proses Rekrutmen"), nomor langkah MUST ditentukan otomatis oleh posisi tampil dengan format sesuai desain masing-masing ("01" dan "1"). Di "Cara Kerja", kemiringan dan posisi naik-turun kartu juga MUST ditentukan oleh posisi tampil (ke-1 s/d ke-4), sama persis dengan pola desain sekarang. Admin tidak mengisi nomor secara manual.
- **FR-013**: Jika sebuah section tidak punya item aktif, seluruh section itu MUST disembunyikan dari halaman.
- **FR-014**: Perubahan yang disimpan admin MUST terlihat di halaman paling lambat pada muatan halaman berikutnya.

#### CTA

- **FR-019**: Sistem MUST menyediakan sembilan penempatan CTA tetap sesuai tabel di Konteks: Beranda, Produk – Kalkulator, Produk – Penutup, Detail Produk, Daftar Artikel, Detail Artikel, Tentang Kami, FAQ, dan Karir. Admin MUST NOT dapat menambah atau menghapus penempatan.
- **FR-020**: Setiap penempatan CTA MUST dapat diubah: judul (wajib), subjudul/paragraf (opsional), dan label tombol utama (wajib). CTA "Beranda" juga MUST memiliki label tombol kedua (wajib). Form admin hanya menampilkan kolom yang dipakai penempatan tersebut.
- **FR-021**: Batas panjang teks CTA: judul maksimal 80 karakter (pindah baris dipertahankan), subjudul/paragraf maksimal 300 karakter, dan label tombol maksimal 40 karakter.
- **FR-022**: Tujuan, ikon, dan gaya tombol CTA MUST tetap seperti perilaku sekarang dan MUST NOT dapat diubah admin. Tombol WhatsApp memakai nomor dari Pengaturan Situs (atau halaman Kontak bila nomor kosong). Tombol lain mengarah ke halaman yang sama seperti sekarang.
- **FR-023**: Tampilan setiap CTA MUST identik dengan desain existing (FR-012a berlaku). Perubahan teks satu penempatan MUST NOT memengaruhi penempatan lain.
- **FR-024**: Jika data sebuah penempatan CTA tidak tersedia, halaman MUST menampilkan teks bawaan CTA tersebut, bukan error atau CTA kosong.
- **FR-025**: Paragraf CTA "Detail Produk" MUST mendukung penanda nama produk yang saat tampil diganti dengan nama produk yang sedang dibuka, dalam huruf kecil seperti perilaku sekarang. Form admin MUST menjelaskan penanda ini.
- **FR-026**: Teks section dan CTA MUST ditampilkan persis seperti yang disimpan admin, tanpa penggantian otomatis apa pun saat halaman dibuka (kecuali penanda nama produk di FR-025). Nama merek pada data awal MUST diisi dari Nama Situs satu kali saat data awal ditanam, bukan ditulis literal di kode.

#### Tentang Kami & Kontak (Session 2026-10-02)

- **FR-027**: Judul section Misi MUST memiliki eyebrow (teks kecil di atas judul, maks 60 karakter) selain judul dan subjudul. Tombol "Ubah Judul Section" hanya menampilkan kolom yang dipakai desain section tersebut.
- **FR-028**: Section Misi MUST menampilkan 3 item aktif pertama di kolom kiri dan sisanya di kolom kanan, ditentukan oleh urutan tampil.
- **FR-029**: Section Nilai MUST memiliki kartu besar bergambar yang diedit bersama judul section: gambar (opsional; bila kosong memakai gambar bawaan seperti sekarang), ikon (wajib), judul (wajib, maks 160), deskripsi (wajib, maks 500). Kartu besar selalu tampil selama section tampil.
- **FR-030**: Trust Strip tidak memiliki judul section; modulnya MUST NOT menampilkan tombol "Ubah Judul Section". Kolom item diberi label "Angka" dan "Keterangan".
- **FR-031**: Sistem MUST menyediakan **blok halaman** tetap: "Tentang Kami – Hero", "Tentang Kami – Siapa Kami", "Tentang Kami – Visi", dan "Kontak – Info Kontak", dikelola di satu modul **Blok Halaman** (daftar tetap, hanya ubah; tanpa tambah/hapus). Setiap blok hanya menampilkan kolom yang dipakai desainnya:
  - Hero: gambar latar (opsional, gambar bawaan bila kosong), subjudul (maks 500). Judul hero tetap otomatis "Mengenal {Nama Situs} Lebih Dekat".
  - Siapa Kami: gambar (opsional, gambar bawaan bila kosong), teks badge (maks 120), eyebrow (maks 60), judul (maks 160), isi (teks kaya), kutipan (teks kaya). Teks kaya dibersihkan dari HTML berbahaya seperti sekarang.
  - Visi: eyebrow (maks 60), judul (maks 500), subjudul (maks 500).
  - Info Kontak: label tombol WhatsApp (maks 40), jam operasional (maks 80), pesan otomatis WhatsApp (maks 300).
- **FR-032**: Pesan otomatis WhatsApp dari blok "Kontak – Info Kontak" MUST dipakai oleh tombol WhatsApp di halaman Kontak, CTA Beranda, dan CTA Band. Notifikasi/email yang memakai pesan WhatsApp lain tidak berubah.
- **FR-033**: Isi halaman pengaturan lama "Halaman Tentang Kami" MUST dipindahkan **apa adanya** (nilai yang tersimpan saat ini, termasuk gambar yang sudah diunggah, bukan teks bawaan) ke section dan blok baru saat migrasi. Penanda `{app_name}` di isi Siapa Kami diisi Nama Situs sekali saat migrasi (FR-026). Setelah itu halaman pengaturan lama MUST dihapus dari panel admin, dan halaman Tentang Kami hanya membaca sumber baru.
- **FR-034**: Tampilan halaman Tentang Kami (Hero, Siapa Kami, Visi, Misi, Nilai, Trust Strip) dan blok Info Kontak MUST identik dengan sebelumnya (FR-012a berlaku), diverifikasi dengan perbandingan HTML seperti section lain.

#### Data awal & akses

- **FR-015**: Sistem MUST menyediakan data awal yang **sama persis** (karakter per karakter, termasuk tanda baca, pemenggalan baris judul, ikon, urutan, dan penanda ditonjolkan) dengan teks yang sekarang ditulis di kode, untuk keempat section (judul, subjudul, semua item) dan kesembilan penempatan CTA. Pengecualiannya, nama merek klien diambil dari Nama Situs saat data awal ditanam (FR-026). Tidak ada halaman yang berubah setelah fitur dirilis, dengan syarat Nama Situs = nama merek yang sekarang tampil saat migrasi dijalankan.
- **FR-016**: Data awal MUST aman dijalankan ulang: tidak membuat duplikat dan tidak menimpa isi yang sudah diubah admin.
- **FR-017**: Akses ke semua modul fitur ini MUST mengikuti aturan hak akses yang sama dengan modul konten lain di panel admin (mis. Testimoni).
- **FR-018**: Menu panel admin MUST dikelompokkan per halaman dengan label berbahasa Indonesia: grup **Beranda** (Banner, Mengapa Beralih, Cara Kerja), **Tentang Kami** (Misi, Nilai, Trust Strip, Tim, Logo Klien, Testimoni), **Karir** (Lowongan Kerja, Mengapa Bergabung, Proses Rekrutmen), dan **Konten Halaman** (Halaman, Blok Halaman, CTA). Label menu section cukup nama section karena nama grup sudah menyebut halamannya; judul halaman admin tetap memakai nama lengkap, mis. "Beranda – Mengapa Beralih".

### Key Entities *(include if feature involves data)*

- **Item Section**: Satu kartu atau langkah di salah satu dari empat section. Atribut: section asal, judul, deskripsi, status aktif, urutan tampil, dan (bila berlaku) ikon serta penanda ditonjolkan. Nomor langkah tidak disimpan karena diturunkan dari posisi tampil. Batas item aktif dan item ditonjolkan mengikuti aturan per section.
- **Pengaturan Section**: Teks pembuka tiap section. Atribut: judul, dan subjudul untuk section yang memilikinya. Satu set nilai per section.
- **CTA**: Teks ajakan bertindak untuk satu penempatan tetap. Atribut: penempatan (unik, tidak bisa diubah), judul, subjudul/paragraf, label tombol utama, dan label tombol kedua (hanya CTA Beranda). Jumlahnya tetap sembilan.
- **Blok Halaman**: Isi satu blok tetap di sebuah halaman (Hero, Siapa Kami, Visi Tentang Kami; Info Kontak). Atribut: blok (unik, tidak bisa diubah) dan kolom isi sesuai blok. Jumlahnya tetap empat.
- **Pengaturan Section** (tambahan): eyebrow (Misi) dan kartu besar (Nilai: gambar, ikon, judul, deskripsi).

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Setelah fitur dirilis dan data awal dimuat, semua section, blok halaman, dan kesembilan CTA (termasuk seluruh halaman Tentang Kami dan Info Kontak) tampil identik secara piksel dengan tampilan sebelumnya pada lebar layar mobile dan desktop (perbandingan tangkapan layar sebelum/sesudah tanpa selisih), termasuk 100% teks, ikon, nomor, urutan, dan penonjolan.
- **SC-002**: Admin non-teknis dapat mengubah teks satu item atau satu CTA dan melihat hasilnya di halaman dalam waktu kurang dari 2 menit, tanpa bantuan developer.
- **SC-003**: 0 perubahan teks di keempat section maupun kesembilan CTA yang membutuhkan developer atau deploy ulang setelah fitur dirilis.
- **SC-004**: Dalam semua kombinasi yang diuji (0 s/d batas item aktif per section; dengan dan tanpa item ditonjolkan), halaman tampil tanpa error dan tanpa tata letak rusak, dan 100% upaya melebihi batas item aktif ditolak.
- **SC-005**: Teks berisi HTML/skrip yang dimasukkan admin tidak pernah dijalankan di halaman publik.
- **SC-006**: 100% teks data awal cocok dengan teks yang sekarang ditulis di kode (diverifikasi otomatis sebelum rilis).

## Assumptions

- **Pola modul**: Mengikuti pola modul konten daftar yang sudah ada (Testimoni): daftar, form, status aktif, dan urutan. Satu modul per section, ditambah satu modul CTA. Tidak ada page builder atau pengaturan tata letak oleh admin.
- **Section kosong**: Jika tidak ada item aktif, section disembunyikan seluruhnya. Menampilkan konten bawaan akan membingungkan admin karena item yang sudah dinonaktifkan tetap muncul.
- **Penonjolan hanya di section yang desainnya punya**: "Mengapa Bergabung" dan "Proses Rekrutmen" tidak punya item ditonjolkan. Border biru pada kartu "Mengapa Bergabung" hanyalah efek saat kursor diarahkan ke kartu, bukan penonjolan.
- **Ikon**: Memakai daftar ikon yang sudah ada di project (dipakai juga halaman pengaturan Tentang Kami & Peralatan Listrik), ditambah ikon yang dipakai data awal (mis. `groups`). Daftar ikon adalah kumpulan ikon relevan (energi, hemat, garansi, lingkungan, inovasi, tim, dampak, layanan, dll.) dari keluarga ikon yang sudah dipakai situs, bukan seluruh katalog. Daftar bisa diperluas developer bila perlu.
- **Satu bahasa**: Konten hanya dalam Bahasa Indonesia, seperti modul konten lainnya.
- **Riwayat perubahan**: Jika modul konten lain mencatat log aktivitas, modul ini mengikuti pola yang sama. Tidak ada fitur versi atau pratinjau khusus.
- **CTA per penempatan, bukan per halaman bebas**: Semua halaman detail artikel berbagi satu CTA, begitu juga semua halaman detail produk. CTA baru di halaman lain membutuhkan developer untuk menambah penempatan.
- **Tujuan tombol CTA tidak bisa diedit**: Supaya admin tidak memasang link salah dan nomor WhatsApp tetap dikelola di satu tempat (Pengaturan Situs).
- **Data pengaturan lama tidak dihapus**: Nilai lama "Halaman Tentang Kami" tetap tersimpan di basis data (tidak dipakai lagi) sebagai cadangan rollback; hanya halaman adminnya yang dihapus.
- **Di luar cakupan**:
  - Judul/subjudul section Kalkulator, Produk, dan Testimoni di beranda. Direncanakan sebagai fitur "Pengaturan Beranda" terpisah. Hero beranda sudah dikelola lewat modul Banner.
  - Tanya-jawab: halaman FAQ, "Pertanyaan Seputar Konsultasi" di Kontak, dan "Pertanyaan Seputar Produk" di Daftar Produk. Dikerjakan sebagai fitur FAQ terpisah.
  - Judul/hero halaman Produk, Artikel, Karir, Kontak, Portfolio; judul section Tim, Testimoni, Logo Klien, "Posisi Terbuka", dan judul "Produk Terkait". Ditunda sebagai topik terpisah.
  - Judul hero Tentang Kami tetap otomatis dari Nama Situs.
