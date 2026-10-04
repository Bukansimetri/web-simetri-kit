# Feature Specification: Lanjutan Penyesuaian Website dan Kelengkapan Admin CMS

**Feature Branch**: `031-client-cms-gaps`

**Created**: 2026-10-04

**Status**: Draft

**Input**: User description: "Lanjutan penyesuaian website SUOER dari dokumen klien 'Fitur yang belum ada untuk merubah tampilan pada website' (lanjutan spec 030): header seragam, tipografi 700, pilihan produk beranda, tombol CTA ke kalkulator, perbaikan Tentang Kami, teks footer, hapus filter produk, menu FAQ, detail lowongan, bug cache portofolio, dan fitur artikel dari web-ecomm-solarpanel."

## Konteks

Spec ini melanjutkan `030-client-design-update` (PR #27) dan bergantung padanya: komponen hero halaman, menu Banner Halaman, layout Artikel baru, dan formulir langganan berasal dari spec 030.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Header Halaman Seragam (Priority: P1)

Pengunjung yang membuka halaman Tentang Kami, Produk, Artikel, Portofolio, Karir, FAQ, maupun Kontak selalu melihat header berupa banner bergambar dengan gaya yang sama: jejak halaman, judul, dan subjudul rata kiri di atas gambar. Admin mengganti gambar, judul, dan subjudul setiap banner dari menu **Banner → Banner Halaman** yang sudah ada.

**Why this priority**: Halaman FAQ dan Kontak masih memakai judul teks polos, sehingga situs terlihat tidak konsisten. Ini keluhan visual utama klien dan menyentuh banyak halaman.

**Independent Test**: Buka ketujuh halaman di atas; semuanya menampilkan banner dengan susunan sama. Ganti gambar banner FAQ dan Kontak dari admin, lalu muat ulang.

**Acceptance Scenarios**:

1. **Given** admin belum mengubah banner FAQ, **When** pengunjung membuka FAQ, **Then** tampil banner bergambar dengan jejak "Beranda / FAQ", judul, dan subjudul bawaan.
2. **Given** admin belum mengubah banner Kontak, **When** pengunjung membuka Kontak, **Then** tampil banner bergambar dengan jejak "Beranda / Kontak", judul, dan subjudul bawaan, dan formulir kontak tetap berfungsi seperti sebelumnya.
3. **Given** admin membuka Banner Halaman untuk FAQ atau Kontak, **Then** tersedia kolom gambar latar seperti halaman lain.
4. **Given** banner Tentang Kami, **When** dibandingkan dengan banner Produk, **Then** tinggi, posisi teks, gradasi, dan tipografinya sama.
5. **Given** judul banner panjang hingga dua baris, **Then** jejak halaman tidak tertutup menu navigasi.

---

### User Story 2 - FAQ Bisa Dikelola dari Admin (Priority: P1)

Admin mengelola semua pertanyaan dan jawaban dari satu menu **FAQ**. Setiap entri ditempatkan di salah satu tempat: halaman FAQ, bagian "Pertanyaan Seputar Produk" di halaman Produk, atau bagian "Pertanyaan Seputar Konsultasi" di halaman Kontak. Admin bisa menambah, mengubah, menghapus, mengurutkan, dan menyembunyikan entri.

**Why this priority**: Klien tidak bisa mengubah informasi FAQ di tiga halaman sama sekali; isinya saat ini tertulis di kode.

**Independent Test**: Ubah satu jawaban di setiap tempat dari admin, lalu periksa ketiga halaman.

**Acceptance Scenarios**:

1. **Given** fitur baru dirilis, **When** pengunjung membuka FAQ, Produk, dan Kontak, **Then** pertanyaan dan jawaban sama persis dengan sebelum rilis (dipindah sebagai data bawaan).
2. **Given** admin menambah entri dengan tempat "Produk", **Then** entri itu muncul hanya di bagian FAQ halaman Produk.
3. **Given** admin mengubah urutan entri, **Then** urutan tampil di situs mengikuti admin.
4. **Given** admin menonaktifkan entri, **Then** entri itu tidak tampil di situs.
5. **Given** semua entri di satu tempat dinonaktifkan atau dihapus, **Then** bagian FAQ di halaman itu tidak tampil sama sekali.
6. **Given** halaman FAQ memiliki kategori (tab), **Then** admin tetap bisa mengisi kategori untuk entri di halaman FAQ, dan tab mengikuti kategori yang terisi.

---

### User Story 3 - Halaman Detail Lowongan (Priority: P1)

Pengunjung halaman Karir melihat judul "Posisi Terbuka" yang bergaya sama dengan judul section lain dan rata tengah. Setiap kartu lowongan menautkan ke halaman detail yang menampilkan judul, lokasi, jenis pekerjaan, deskripsi utuh, dan tombol Lamar.

**Why this priority**: Deskripsi lowongan saat ini terpotong dua baris dan tidak ada cara membaca informasi lengkap, sehingga pelamar kehilangan informasi penting.

**Independent Test**: Buat lowongan dengan deskripsi panjang, buka Karir, klik kartunya, dan baca deskripsi lengkap.

**Acceptance Scenarios**:

1. **Given** lowongan aktif dengan deskripsi panjang, **When** pengunjung membuka Karir, **Then** kartu menampilkan ringkasan dan tautan "Lihat Detail".
2. **Given** pengunjung membuka detail lowongan, **Then** tampil deskripsi utuh dengan format paragraf terjaga dan tombol "Lamar Sekarang" yang menuju alur lamaran yang sama seperti sekarang.
3. **Given** lowongan nonaktif atau tidak ada, **When** alamat detailnya dibuka, **Then** muncul halaman tidak ditemukan.
4. **Given** modul karir dimatikan di pengaturan, **Then** halaman detail juga tidak dapat diakses.
5. **Given** halaman Karir, **Then** judul "Posisi Terbuka" rata tengah dengan gaya yang sama dengan judul "Mengapa Bergabung".

---

### User Story 4 - Artikel Lebih Lengkap (Priority: P1)

Mengadaptasi modul artikel dari proyek web-ecomm-solarpanel:

- Daftar artikel dimuat bertahap dengan tombol "Muat lebih banyak".
- Sidebar daftar menampilkan "Tag Populer" untuk memfilter artikel per tag.
- Halaman detail menampilkan jumlah dilihat dan keterangan gambar utama.
- Sidebar halaman detail berisi artikel terbaru dan formulir langganan.
- Halaman detail menampilkan "Produk Terkait" yang dipilih admin.
- Admin bisa melihat pratinjau artikel draf sebelum terbit.

**Why this priority**: Saat ini semua artikel dimuat sekaligus (berat saat artikel banyak), tag tidak bisa dipakai pengunjung, dan admin tidak bisa memeriksa draf sebelum terbit.

**Independent Test**: Isi 10 artikel dengan tag dan produk terkait; uji muat bertahap, filter tag, pratinjau draf, penghitung dilihat, dan sidebar detail.

**Acceptance Scenarios**:

1. **Given** ada lebih dari satu halaman artikel, **When** pengunjung membuka Artikel, **Then** hanya kelompok pertama yang tampil beserta tombol "Muat lebih banyak"; menekan tombol menambahkan kelompok berikutnya tanpa kehilangan posisi; tombol hilang bila semua sudah tampil.
2. **Given** pengunjung menekan sebuah tag di "Tag Populer", **Then** hanya artikel bertag itu yang tampil, tag terlihat aktif, dan tersedia cara menghapus filter.
3. **Given** pencarian atau filter kategori aktif, **When** pengunjung memuat lebih banyak, **Then** hasil tambahan tetap mengikuti pencarian dan filter yang aktif.
4. **Given** artikel draf atau terjadwal, **When** admin menekan **Preview** di halaman edit, **Then** admin melihat halaman artikel lengkap dengan penanda "Mode Preview"; **When** pengunjung yang tidak masuk membuka alamat pratinjau, **Then** akses ditolak.
5. **Given** pengunjung membuka artikel terbit, **Then** jumlah dilihat bertambah satu dan ditampilkan; pratinjau admin tidak menambah hitungan.
6. **Given** admin mengisi keterangan gambar utama, **Then** keterangan tampil di bawah gambar di detail; bila kosong tidak tampil.
7. **Given** halaman detail artikel, **Then** sidebar menampilkan beberapa artikel terbaru (selain artikel ini) dan formulir langganan yang sama dengan halaman daftar.
8. **Given** admin memilih produk terkait untuk artikel dan mengurutkannya, **Then** detail artikel menampilkan maksimal 4 produk itu dengan tautan ke detail produk; bila tidak ada, bagian ini tidak tampil.
9. **Given** fitur yang sudah ada (jadwal publikasi, SEO, waktu baca, tombol bagikan, artikel terkait, menu Langganan Newsletter), **Then** semuanya tetap berfungsi.

---

### User Story 5 - Tipografi Seragam (Priority: P2)

Semua judul (judul halaman, judul section, judul kartu) dan teks pada tombol ajakan (CTA) memakai ketebalan bold (700) secara seragam di seluruh situs publik.

**Why this priority**: Klien menilai beberapa judul terlalu tebal dan ketebalan antar bagian tidak konsisten.

**Independent Test**: Buka setiap halaman publik dan periksa bahwa semua judul dan teks tombol CTA tampil bold (700), tanpa ada yang lebih tebal atau lebih tipis.

**Acceptance Scenarios**:

1. **Given** halaman publik mana pun, **Then** tidak ada judul atau teks tombol CTA yang tampil lebih tebal dari bold (700).
2. **Given** dua elemen sejenis di halaman berbeda (mis. judul section Beranda dan Tentang Kami), **Then** ketebalannya sama.
3. **Given** admin mengganti jenis font judul di menu Tampilan, **Then** ketebalan tetap bold (700).

---

### User Story 6 - Pilih Produk di Beranda (Priority: P2)

Admin menandai produk dengan **Tampilkan di Beranda** di menu Produk untuk menentukan produk mana yang tampil di section "Solusi Untuk Setiap Kebutuhan" (maksimal 3).

**Why this priority**: Saat ini isi section ditentukan otomatis oleh urutan produk, sehingga admin tidak tahu cara mengaturnya.

**Independent Test**: Tandai dua produk, periksa beranda; tandai tiga, periksa; coba tandai produk keempat.

**Acceptance Scenarios**:

1. **Given** belum ada produk yang ditandai, **Then** beranda menampilkan 3 produk teratas menurut urutan, seperti sekarang.
2. **Given** admin menandai 1 sampai 3 produk, **Then** beranda menampilkan hanya produk yang ditandai, menurut urutan produk.
3. **Given** sudah ada 3 produk ditandai, **When** admin mencoba menandai produk keempat, **Then** admin mendapat pesan bahwa batasnya 3 dan perubahan tidak tersimpan.
4. **Given** kartu tengah section ini, **Then** tetap tampil ditonjolkan dengan badge "Terpopuler" seperti desain spec 030.

---

### User Story 7 - Perbaikan Kecil Beranda, Tentang Kami, Produk, Footer (Priority: P2)

- Tombol "Isi Form Online" pada CTA penutup Beranda mengarah ke kalkulator di Beranda.
- Di Tentang Kami, blok kutipan "Siapa Kami" disembunyikan bila kosong.
- Judul testimoni Tentang Kami menjadi label "TESTIMONI" dan judul "Partner Kami".
- Section Tim Kami tampil dengan latar penuh selebar layar dan tanpa batas atau pemisah yang janggal, termasuk saat hanya ada satu anggota.
- Halaman Produk tidak lagi memiliki filter kategori.
- Teks deskripsi footer bisa diedit di bagian baru **Footer** pada Pengaturan Umum.

**Why this priority**: Perubahan kecil yang langsung diminta klien; masing-masing berdampak terbatas.

**Independent Test**: Periksa tiap perilaku di atas di browser dan di admin.

**Acceptance Scenarios**:

1. **Given** pengunjung menekan "Isi Form Online" di CTA penutup Beranda, **Then** halaman bergulir ke kalkulator.
2. **Given** kutipan "Siapa Kami" kosong, **Then** garis dan tanda kutip tidak tampil; **Given** terisi, **Then** tampil seperti sekarang.
3. **Given** ada testimoni aktif, **When** Tentang Kami dibuka, **Then** section testimoni berlabel "TESTIMONI" dan berjudul "Partner Kami".
4. **Given** hanya ada satu anggota tim, **Then** section Tim Kami terlihat utuh dengan latar penuh dan anggota tampil rapi di tengah.
5. **Given** halaman Produk, **Then** tidak ada tombol filter kategori dan semua produk tampil.
6. **Given** admin mengubah teks deskripsi footer di Pengaturan Umum, **Then** footer semua halaman menampilkan teks baru; **Given** dikosongkan, **Then** teks bawaan tidak tampil dan tidak ada ruang kosong janggal.

---

### User Story 8 - Perubahan Admin Langsung Tampil (Priority: P2)

Saat admin menambah atau mengubah data yang tampil di halaman publik (misalnya kategori portofolio baru), perubahan itu langsung tampil di semua tampilan halaman, termasuk filter "Semua".

**Why this priority**: Klien menemukan kategori baru hanya muncul di sebagian filter Portofolio; perilaku ini membingungkan dan terlihat seperti kerusakan.

**Independent Test**: Buka Portofolio (Semua), tambah kategori dan proyek di admin, muat ulang semua filter.

**Acceptance Scenarios**:

1. **Given** pengunjung sudah membuka Portofolio "Semua", **When** admin menambah kategori dan proyek baru, **Then** memuat ulang "Semua" langsung menampilkan pil kategori baru dan proyeknya.
2. **Given** admin mengubah atau menghapus produk, artikel, proyek, testimoni, atau kategori, **Then** halaman publik terkait menampilkan data terbaru pada muatan berikutnya.

---

### Edge Cases

- Banner FAQ/Kontak tanpa gambar unggahan atau berkas hilang: memakai gambar bawaan.
- Teks banner sangat panjang: banner memanjang tanpa menutupi menu navigasi.
- Tempat FAQ tanpa entri aktif: bagian FAQ pada halaman itu tersembunyi; halaman FAQ menampilkan pesan kosong yang ramah.
- Deskripsi lowongan berisi baris baru atau teks sangat panjang: tampil utuh dan terbaca.
- Tag yang tidak dipakai artikel terbit: tidak muncul di "Tag Populer".
- Filter tag yang tidak dikenal: menampilkan semua artikel tanpa galat.
- Artikel tanpa gambar, tanpa tag, atau tanpa produk terkait: detail tetap rapi.
- Produk terkait yang kemudian dihapus: tidak tampil dan tidak menimbulkan galat.
- Penghitung dilihat tidak bertambah untuk pratinjau admin.
- Produk yang ditandai "Tampilkan di Beranda" lalu dihapus: beranda menampilkan yang tersisa atau kembali ke 3 produk teratas bila tidak ada yang tersisa.
- Teks footer berisi baris baru: ditampilkan sebagai baris baru, tanpa HTML berbahaya.
- Semua halaman tetap tampil benar di ponsel, tablet, dan desktop tanpa gulir horizontal.

## Requirements *(mandatory)*

### Functional Requirements

**Header seragam**

- **FR-001**: Halaman FAQ dan Kontak MUST menampilkan banner bergambar dengan gaya yang sama dengan Produk, Artikel, Portofolio, dan Karir.
- **FR-002**: Banner Tentang Kami MUST memakai tampilan banner yang sama dengan halaman lain (tinggi, posisi teks, gradasi, tipografi).
- **FR-003**: Admin MUST dapat mengubah gambar, judul, dan subjudul banner FAQ dan Kontak dari menu Banner Halaman yang sudah ada; bila gambar kosong atau berkas hilang, gambar bawaan dipakai.

**Tipografi**

- **FR-004**: Semua judul (judul banner, judul section, judul kartu) dan teks tombol CTA di situs publik MUST tampil dengan ketebalan bold (700).
- **FR-005**: Ketebalan MUST tetap 700 apa pun jenis font judul yang dipilih admin.

**Beranda**

- **FR-006**: Admin MUST dapat menandai produk dengan "Tampilkan di Beranda"; paling banyak 3 produk dapat ditandai sekaligus, dan percobaan menandai yang keempat ditolak dengan pesan jelas.
- **FR-007**: Section "Solusi Untuk Setiap Kebutuhan" MUST menampilkan produk yang ditandai (menurut urutan produk); bila tidak ada, MUST menampilkan 3 produk teratas menurut urutan.
- **FR-008**: Tombol kedua CTA penutup Beranda ("Isi Form Online") MUST mengarah ke kalkulator di Beranda.

**Tentang Kami**

- **FR-009**: Blok kutipan "Siapa Kami" MUST tidak tampil bila isinya kosong.
- **FR-010**: Section testimoni Tentang Kami MUST berlabel "TESTIMONI" dan berjudul "Partner Kami".
- **FR-011**: Section Tim Kami MUST tampil dengan latar selebar layar yang menyatu dengan section sekitarnya, tanpa batas atau pemisah janggal, dengan anggota tim rata tengah berapa pun jumlahnya.

**Footer**

- **FR-012**: Pengaturan Umum MUST memiliki bagian "Footer" berisi kolom teks deskripsi footer; nilai bawaannya adalah teks yang tampil sekarang.
- **FR-013**: Footer MUST menampilkan teks deskripsi dari pengaturan, menjaga baris baru, dan tidak menampilkan paragraf kosong bila dikosongkan.

**Produk**

- **FR-014**: Halaman katalog Produk MUST tidak lagi menampilkan filter kategori; semua produk tampil dalam satu grid.

**FAQ**

- **FR-015**: Admin MUST memiliki menu "FAQ" untuk menambah, mengubah, menghapus, mengurutkan, dan mengaktifkan/menonaktifkan entri tanya-jawab.
- **FR-016**: Setiap entri MUST memiliki tempat tampil: Halaman FAQ, Halaman Produk, atau Halaman Kontak; entri tampil hanya di tempatnya.
- **FR-017**: Entri untuk Halaman FAQ MUST tetap mendukung kategori (tab) seperti sekarang.
- **FR-018**: Isi FAQ Produk dan Kontak yang sekarang tertulis di kode MUST tersedia otomatis sebagai data bawaan setelah rilis, termasuk di produksi yang hanya menjalankan migrasi, tanpa menimpa entri yang sudah diubah admin.
- **FR-019**: Bagian FAQ di halaman Produk dan Kontak MUST tidak tampil bila tidak ada entri aktif untuk tempat itu.

**Karir**

- **FR-020**: Judul "Posisi Terbuka" MUST rata tengah dan bergaya sama dengan judul section lain di halaman Karir.
- **FR-021**: Setiap lowongan aktif MUST memiliki halaman detail berisi judul, lokasi, jenis pekerjaan, deskripsi utuh, dan tombol "Lamar Sekarang".
- **FR-022**: Kartu lowongan MUST menautkan ke halaman detail; halaman detail lowongan nonaktif, tidak ada, atau saat modul karir dimatikan MUST menampilkan halaman tidak ditemukan.
- **FR-023**: Halaman detail lowongan MUST memiliki judul halaman dan deskripsi untuk mesin pencari.

**Data publik segar**

- **FR-024**: Setiap perubahan data dari admin (tambah, ubah, hapus) pada konten yang ditampilkan halaman publik MUST tercermin pada muatan berikutnya di semua tampilan halaman, termasuk semua variasi filter.

**Artikel**

- **FR-025**: Daftar artikel MUST menampilkan artikel secara bertahap dengan tombol "Muat lebih banyak", dan pencarian, filter kategori, serta filter tag MUST tetap berlaku pada hasil tambahan.
- **FR-026**: Sidebar daftar artikel MUST menampilkan "Tag Populer" (tag yang paling banyak dipakai artikel terbit) yang dapat dipilih untuk memfilter, dengan cara menghapus filter.
- **FR-027**: Admin MUST dapat membuka pratinjau artikel (termasuk draf dan terjadwal) dari halaman edit; pratinjau MUST hanya dapat diakses pengguna admin yang masuk dan diberi penanda "Mode Preview".
- **FR-028**: Detail artikel terbit MUST menampilkan jumlah dilihat dan menambah hitungan satu setiap kali dibuka pengunjung; pratinjau tidak menambah hitungan.
- **FR-029**: Admin MUST dapat mengisi keterangan gambar utama; keterangan tampil di bawah gambar di halaman detail.
- **FR-030**: Halaman detail artikel MUST memiliki sidebar berisi artikel terbaru (selain artikel ini) dan formulir langganan yang sama dengan halaman daftar.
- **FR-031**: Admin MUST dapat memilih dan mengurutkan produk terkait untuk setiap artikel; detail artikel menampilkan maksimal 4 produk terkait yang masih ada.
- **FR-032**: Fitur artikel yang sudah ada (jadwal publikasi, kolom SEO, waktu baca, tombol bagikan, artikel terkait, pencarian, filter kategori, langganan) MUST tetap berfungsi.

**Lintas halaman**

- **FR-033**: Semua halaman yang diubah MUST tampil benar pada ponsel, tablet, dan desktop tanpa gulir horizontal.
- **FR-034**: Manual operator MUST diperbarui untuk menu dan kolom baru (FAQ, Tampilkan di Beranda, Footer, Preview, keterangan gambar, produk terkait).

### Key Entities *(include if feature involves data)*

- **Entri FAQ**: pertanyaan, jawaban, tempat tampil (FAQ/Produk/Kontak), kategori (untuk halaman FAQ), urutan, status aktif.
- **Produk**: ditambah penanda "Tampilkan di Beranda".
- **Lowongan Kerja**: sudah ada; mendapat halaman detail publik (alamat unik per lowongan).
- **Artikel**: ditambah jumlah dilihat, keterangan gambar utama, dan daftar produk terkait berurutan.
- **Tag Artikel**: sudah ada; dipakai sebagai filter publik.
- **Pengaturan Footer**: teks deskripsi footer.
- **Banner Halaman (FAQ, Kontak)**: sudah ada; mendapat gambar latar.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% dari 13 item ❌/⚠️ dari dokumen klien yang tercantum dalam spec ini dinyatakan selesai oleh pemangku kepentingan saat peninjauan.
- **SC-002**: Admin dapat mengubah satu jawaban FAQ di halaman mana pun dalam kurang dari 2 menit tanpa bantuan developer.
- **SC-003**: Ketujuh halaman utama (Tentang Kami, Produk, Artikel, Portofolio, Karir, FAQ, Kontak) menampilkan banner dengan susunan identik pada peninjauan visual berdampingan.
- **SC-004**: Tidak ada judul atau teks tombol CTA di situs publik yang tampil dengan ketebalan selain bold (700).
- **SC-005**: Perubahan data dari admin terlihat di semua tampilan halaman publik terkait pada muatan berikutnya (0 kasus data lama tertinggal).
- **SC-006**: Pada daftar dengan 50 artikel, muatan awal hanya menampilkan satu kelompok artikel, dan pengunjung dapat mencapai artikel mana pun lewat "Muat lebih banyak", pencarian, kategori, atau tag.
- **SC-007**: Pelamar dapat membaca deskripsi lengkap lowongan dan menekan "Lamar Sekarang" dalam 2 klik dari halaman Karir.
- **SC-008**: Seluruh pengujian otomatis yang ada tetap lulus, kecuali yang memang memeriksa tampilan lama dan diperbarui sesuai desain baru.

## Assumptions

- PR #27 (spec 030) menjadi dasar; spec ini dikerjakan di atasnya dan di-merge setelahnya.
- "Ketebalan 700" berlaku untuk judul dan teks tombol CTA di situs publik; teks isi, label kecil (eyebrow), dan panel admin tidak diubah.
- Gambar bawaan banner FAQ dan Kontak diambil dari gambar contoh yang sudah ada di proyek.
- Tombol "Lamar Sekarang" di detail lowongan tetap mengarah ke alur lamaran saat ini (halaman Kontak); formulir lamaran khusus di luar cakupan.
- Kelompok "Muat lebih banyak" berisi 6 artikel, sesuai grid dua kolom.
- "Tag Populer" menampilkan maksimal 10 tag.
- Penghitung dilihat menghitung setiap pembukaan halaman oleh pengunjung tanpa deduplikasi per orang; cukup sebagai indikator popularitas.
- Produk terkait artikel memakai produk dari katalog yang sudah ada.
- Pencarian artikel tetap dikirim saat pengunjung menekan Enter atau tombol cari; pencarian langsung saat mengetik tidak termasuk.
- Hapus sementara (soft delete) artikel seperti di web-ecomm-solarpanel tidak diadaptasi.
- Data FAQ halaman FAQ yang sudah ada tetap dipakai dan otomatis ditempatkan di "Halaman FAQ".
