# Feature Specification: Penyesuaian Desain Website Sesuai Dokumen Klien

**Feature Branch**: `030-client-design-update`

**Created**: 2026-10-04

**Status**: Draft

**Input**: User description: "Penyesuaian desain website SUOER sesuai dokumen 'Confirm Update Design' dari klien: Beranda (Mengapa Beralih, Sederhana dan Mulus, Testimoni), footer Kontak, halaman Produk, halaman Artikel, dan halaman Portofolio."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Halaman Artikel & Portofolio dengan Hero Banner (Priority: P1)

Pengunjung yang membuka halaman Artikel atau Portofolio disambut hero banner bergambar lebar dengan jejak halaman ("Beranda / Artikel", "Beranda / Portofolio"), judul besar, dan subjudul, sama seperti halaman Produk. Gambar, judul, dan subjudul hero dapat diganti oleh admin dari panel admin tanpa bantuan developer.

**Why this priority**: Ini perubahan paling terlihat di dokumen klien dan kedua halaman saat ini hanya memiliki judul teks polos. Fondasi pengelolaan konten hero sudah ada, sehingga nilai yang dihasilkan besar dengan usaha kecil.

**Independent Test**: Buka `/artikel` dan `/portfolio`; keduanya menampilkan hero bergambar dengan breadcrumb, judul, dan subjudul. Ubah gambar dan teks hero di panel admin, lalu muat ulang halaman dan periksa bahwa perubahan tampil.

**Acceptance Scenarios**:

1. **Given** admin belum mengubah hero Artikel, **When** pengunjung membuka halaman Artikel, **Then** tampil hero bergambar dengan breadcrumb "Beranda / Artikel", judul "Wawasan & Artikel", dan subjudul bawaan.
2. **Given** admin belum mengubah hero Portofolio, **When** pengunjung membuka halaman Portofolio, **Then** tampil hero bergambar dengan breadcrumb "Beranda / Portofolio" dan judul "Portofolio Proyek" beserta subjudul bawaan.
3. **Given** admin mengganti gambar dan teks hero di panel admin, **When** pengunjung memuat ulang halaman, **Then** hero menampilkan gambar dan teks terbaru.
4. **Given** gambar hero belum diunggah atau berkasnya hilang, **When** halaman dibuka, **Then** hero tetap tampil dengan gambar bawaan dan tidak ada gambar rusak.

---

### User Story 2 - Halaman Portofolio dengan Detail Proyek Lengkap (Priority: P1)

Di bawah hero, pengunjung melihat filter kategori berbentuk pil (Semua dan tiap kategori, misalnya Residensial, Komersial & Industri, Pompa Air Tenaga Surya). Tiap kartu proyek menampilkan gambar, nama kategori, judul, ringkasan singkat, dan tautan "Lihat Detail Proyek →" menuju halaman detail proyek.

**Why this priority**: Klien secara eksplisit meminta "penambahan detail-detailnya" pada kartu portofolio. Kartu yang hanya berisi gambar dan judul kurang informatif bagi calon pelanggan.

**Independent Test**: Buka `/portfolio`, pilih tiap pil kategori, dan periksa bahwa setiap kartu menampilkan kategori, judul, ringkasan, dan tautan detail yang berfungsi.

**Acceptance Scenarios**:

1. **Given** terdapat beberapa proyek di beberapa kategori, **When** pengunjung membuka Portofolio, **Then** semua proyek tampil dan pil "Semua" berstatus aktif.
2. **Given** pengunjung memilih satu pil kategori, **When** halaman diperbarui, **Then** hanya proyek kategori itu yang tampil dan pil tersebut berstatus aktif.
3. **Given** sebuah proyek memiliki ringkasan, **When** kartunya tampil, **Then** ringkasan dipotong rapi pada maksimal tiga baris.
4. **Given** pengunjung menekan "Lihat Detail Proyek →" atau bagian mana pun dari kartu, **Then** pengunjung masuk ke halaman detail proyek tersebut.
5. **Given** sebuah proyek tidak memiliki ringkasan atau gambar sampul, **When** kartunya tampil, **Then** kartu tetap rapi tanpa ruang kosong yang mengganggu atau gambar rusak.

---

### User Story 3 - Halaman Artikel Dua Kolom dengan Pencarian dan Langganan (Priority: P1)

Pengunjung melihat daftar artikel sebagai grid kartu di kolom utama (badge kategori di atas gambar, tanggal, judul, ringkasan, "Baca Selengkapnya →"), dengan sidebar di sebelah kanan berisi kotak "Cari Artikel" dan kartu "Update Mingguan" untuk berlangganan lewat email. Pada layar kecil, sidebar berpindah ke bawah daftar artikel.

**Why this priority**: Layout dan sidebar adalah perubahan fungsional terbesar yang diminta klien pada halaman Artikel.

**Independent Test**: Buka `/artikel`; periksa grid dan sidebar. Cari kata kunci yang ada dan yang tidak ada di judul atau ringkasan artikel. Kirim formulir langganan dengan email valid dan tidak valid.

**Acceptance Scenarios**:

1. **Given** terdapat artikel terbit, **When** pengunjung membuka halaman Artikel, **Then** artikel tampil sebagai grid kartu di kolom utama dan sidebar tampil di sampingnya pada layar lebar.
2. **Given** pengunjung memasukkan kata kunci yang cocok dengan judul atau ringkasan, **When** pencarian dikirim, **Then** hanya artikel yang cocok yang tampil dan kata kunci tetap terlihat di kotak pencarian.
3. **Given** kata kunci tidak cocok dengan artikel mana pun, **When** pencarian dikirim, **Then** muncul pesan bahwa artikel tidak ditemukan beserta cara mengosongkan pencarian.
4. **Given** pengunjung memasukkan email valid di kartu "Update Mingguan", **When** menekan "Langganan", **Then** email tersimpan sebagai pelanggan dan pengunjung melihat pesan konfirmasi.
5. **Given** pengunjung memasukkan email tidak valid atau kosong, **When** menekan "Langganan", **Then** muncul pesan kesalahan yang jelas dan tidak ada data tersimpan.
6. **Given** email yang sama sudah berlangganan, **When** didaftarkan lagi, **Then** tidak ada duplikat dan pengunjung tetap melihat pesan sukses yang wajar.
7. **Given** layar berukuran ponsel, **When** halaman dibuka, **Then** grid menjadi satu kolom dan sidebar tampil di bawah grid tanpa gulir horizontal.

---

### User Story 4 - Kartu Produk Sederhana (Priority: P2)

Di halaman Produk, tiap kartu hanya menampilkan gambar produk dan nama produk (rata tengah) dalam grid rapi, tanpa badge kategori, deskripsi, harga, dan tautan tambahan. Seluruh kartu dapat diklik menuju halaman detail produk. Filter kategori yang sudah ada tetap berfungsi.

**Why this priority**: Klien meminta tampilan "hanya image dan judulnya saja". Perubahannya kecil, tetapi menyangkut halaman utama katalog.

**Independent Test**: Buka `/produk`; setiap kartu hanya berisi gambar dan judul, dan menekan kartu membuka detail produk yang benar.

**Acceptance Scenarios**:

1. **Given** terdapat beberapa produk, **When** pengunjung membuka halaman Produk, **Then** setiap kartu hanya menampilkan gambar dan nama produk.
2. **Given** pengunjung menekan sebuah kartu, **Then** pengunjung masuk ke halaman detail produk tersebut.
3. **Given** sebuah produk belum memiliki gambar, **When** kartunya tampil, **Then** tampil gambar pengganti netral dengan ukuran kartu yang tetap seragam.
4. **Given** pengunjung memilih filter kategori, **Then** hanya produk kategori itu yang tampil dengan gaya kartu yang sama.
5. **Given** nama produk panjang, **When** kartu tampil, **Then** judul dipotong atau dibungkus tanpa merusak keseragaman grid.

---

### User Story 5 - Section Beranda Lebih Rapi: Mengapa Beralih & Sederhana dan Mulus (Priority: P2)

Di beranda, "Mengapa Beralih Bersama SUOER?" tampil dengan judul dan subjudul rata tengah, tiga kartu sama tinggi dan sejajar dengan ikon kecil. Kartu yang ditandai sebagai penekanan hanya dibedakan dengan border berwarna merek, tanpa kemiringan atau pergeseran posisi. "Sederhana dan Mulus" menampilkan empat langkah sejajar dan rata, dengan nomor dalam lingkaran yang dihubungkan garis putus-putus, tanpa kemiringan atau pergeseran vertikal antar kartu.

**Why this priority**: Klien menyatakan kedua section sudah "disesuaikan supaya lebih rapi". Isi sudah dikelola dari admin dan tidak berubah, hanya tampilannya yang dirapikan.

**Independent Test**: Buka beranda pada layar desktop dan ponsel. Tiga kartu "Mengapa Beralih" sama tinggi dan sebaris, empat langkah sebaris, dan tidak ada elemen yang miring atau bergeser.

**Acceptance Scenarios**:

1. **Given** section "Mengapa Beralih" berisi tiga item, **When** beranda dibuka di layar lebar, **Then** ketiga kartu sebaris, sama tinggi, dan sejajar.
2. **Given** satu item ditandai sebagai penekanan di admin, **Then** kartu itu hanya berbeda lewat border berwarna merek, tanpa rotasi, pergeseran, atau latar solid.
3. **Given** section "Sederhana dan Mulus" berisi empat langkah, **When** dibuka di layar lebar, **Then** keempat langkah sebaris dengan sejajar, nomor berurutan dalam lingkaran, dan garis putus-putus penghubung.
4. **Given** layar ponsel, **When** kedua section dibuka, **Then** kartu tersusun satu kolom tanpa gulir horizontal dan garis penghubung tidak mengganggu.
5. **Given** admin mengubah judul, subjudul, atau item di panel admin, **Then** perubahan tetap tampil di layout baru.

---

### User Story 6 - Testimoni "Partner Kami" (Priority: P2)

Section testimoni di beranda menampilkan label kecil "TESTIMONI" di atas judul "Partner Kami", diikuti kartu testimoni yang rata dan sama gaya (tanpa kartu yang disorot). Setiap kartu berisi bintang penilaian bergaya garis, isi testimoni, serta foto atau inisial, nama, dan jabatan.

**Why this priority**: Klien menunjukkan bentuk baru section ini, tetapi isinya tidak berubah dan sudah dikelola dari admin.

**Independent Test**: Buka beranda dengan minimal tiga testimoni aktif; periksa label, judul, dan kartu seragam. Nonaktifkan semua testimoni dan pastikan section tidak tampil.

**Acceptance Scenarios**:

1. **Given** terdapat testimoni aktif, **When** beranda dibuka, **Then** section menampilkan label "TESTIMONI" dan judul "Partner Kami".
2. **Given** tiga testimoni aktif, **When** section tampil di layar lebar, **Then** ketiga kartu sebaris dengan gaya identik, tanpa kartu yang ditonjolkan.
3. **Given** sebuah testimoni memiliki rating, **Then** jumlah bintang sesuai rating dan gaya bintang adalah garis (outline).
4. **Given** testimoni tanpa foto, **Then** tampil lingkaran inisial dari nama; **Given** tanpa jabatan, **Then** baris jabatan tidak muncul.
5. **Given** tidak ada testimoni aktif, **Then** section tidak tampil sama sekali.

---

### User Story 7 - Kontak di Footer & Pembaruan Section Produk Beranda (Priority: P3)

Footer menampilkan kolom "Kontak" berisi alamat, email, dan telepon dengan ikon masing-masing, bersama kolom Solusi dan Perusahaan. Section "Solusi Untuk Setiap Kebutuhan" di beranda dipertahankan sesuai dokumen klien: kartu tengah ditonjolkan dengan badge "Terpopuler", dan ada tautan "Lihat semua produk".

**Why this priority**: Kedua bagian ini sudah sesuai dengan dokumen klien. Pekerjaannya hanya verifikasi dan penjagaan agar tidak berubah akibat perubahan lain.

**Independent Test**: Isi alamat, email, dan telepon di pengaturan situs; periksa footer. Kosongkan salah satu dan periksa bahwa barisnya tidak tampil. Periksa section produk beranda tidak berubah.

**Acceptance Scenarios**:

1. **Given** alamat, email, dan telepon terisi di pengaturan situs, **Then** footer menampilkan ketiganya dengan ikon.
2. **Given** salah satu data kontak kosong, **Then** baris itu tidak tampil dan tidak ada ruang kosong.
3. **Given** semua data kontak kosong, **Then** kolom Kontak tidak menampilkan baris kosong yang janggal.
4. **Given** perubahan pada Story 1–6 selesai, **Then** section "Solusi Untuk Setiap Kebutuhan" tampil identik dengan sebelum perubahan.

---

### Edge Cases

- Artikel atau proyek belum ada sama sekali: halaman menampilkan pesan kosong yang ramah di dalam layout baru, sidebar Artikel tetap tampil.
- Judul atau ringkasan sangat panjang pada kartu Artikel, Portofolio, atau Produk: teks dipotong agar tinggi kartu tetap seragam.
- Jumlah kartu tidak kelipatan tiga atau dua: baris terakhir tetap rapi.
- Pencarian Artikel digabung dengan filter kategori: hasil memenuhi keduanya.
- Pencarian berisi karakter khusus atau sangat panjang: ditangani tanpa galat dan tanpa menampilkan markup.
- Pengiriman formulir langganan berulang kali oleh satu pengunjung: dibatasi agar tidak menyalahgunakan halaman.
- Tampilan ponsel, tablet, dan desktop: tidak ada gulir horizontal dan teks tidak tertutup.
- Pengunjung yang menonaktifkan JavaScript tetap dapat memakai filter, pencarian, dan formulir langganan.

## Requirements *(mandatory)*

### Functional Requirements

**Beranda**

- **FR-001**: Section "Mengapa Beralih" MUST menampilkan judul dan subjudul rata tengah, serta kartu sejajar dengan tinggi sama dan ikon berukuran kecil.
- **FR-002**: Pada section "Mengapa Beralih", item yang ditandai penekanan MUST dibedakan hanya dengan border berwarna merek, tanpa rotasi, pergeseran posisi, atau perubahan skala permanen.
- **FR-003**: Section "Sederhana dan Mulus" MUST menampilkan langkah-langkah sejajar tanpa rotasi atau pergeseran vertikal, dengan nomor berurutan dalam lingkaran dan garis putus-putus penghubung pada layar lebar.
- **FR-004**: Isi section "Mengapa Beralih" dan "Sederhana dan Mulus" (judul, subjudul, item, urutan, penekanan) MUST tetap dikelola dari panel admin seperti sekarang.
- **FR-005**: Section testimoni MUST menampilkan label "TESTIMONI" dan judul "Partner Kami", kartu seragam tanpa kartu yang disorot, dan bintang penilaian bergaya garis.
- **FR-006**: Section testimoni MUST tetap tersembunyi bila tidak ada testimoni aktif, dan tetap menangani testimoni tanpa foto (inisial) dan tanpa jabatan.
- **FR-007**: Section "Solusi Untuk Setiap Kebutuhan" di beranda MUST tetap tampil seperti sebelum perubahan ini.

**Footer**

- **FR-008**: Footer MUST menampilkan kolom Kontak berisi alamat, email, dan telepon yang masing-masing diberi ikon, dan hanya menampilkan data yang terisi di pengaturan situs.

**Produk**

- **FR-009**: Kartu produk di halaman Produk MUST hanya menampilkan gambar dan nama produk (rata tengah); badge kategori, deskripsi, harga, dan tautan tambahan MUST tidak tampil.
- **FR-010**: Seluruh area kartu produk MUST dapat diklik menuju halaman detail produk, dan filter kategori yang ada MUST tetap berfungsi.
- **FR-011**: Kartu produk tanpa gambar MUST menampilkan gambar pengganti dengan ukuran sama dengan kartu lain.

**Artikel**

- **FR-012**: Halaman Artikel MUST menampilkan hero banner bergambar dengan breadcrumb "Beranda / Artikel", judul, dan subjudul; teks bawaan judul "Wawasan & Artikel".
- **FR-013**: Daftar artikel MUST tampil sebagai grid kartu di kolom utama dengan badge kategori di atas gambar, tanggal terbit, judul, ringkasan, dan tautan "Baca Selengkapnya →", ditemani sidebar pada layar lebar.
- **FR-014**: Sidebar MUST memuat kotak "Cari Artikel" yang menyaring artikel terbit berdasarkan kata kunci pada judul atau ringkasan, dan MUST menampilkan pesan bila hasil kosong.
- **FR-015**: Sidebar MUST memuat kartu "Update Mingguan" dengan kolom email dan tombol "Langganan" yang menyimpan email valid sebagai pelanggan, menolak email tidak valid dengan pesan jelas, dan tidak membuat duplikat.
- **FR-016**: Filter kategori artikel yang sudah ada MUST tetap berfungsi dan dapat dikombinasikan dengan pencarian.
- **FR-017**: Pelanggan baru MUST terlihat oleh admin dalam panel admin, sehingga dapat dipakai untuk kampanye email.
- **FR-018**: Pada layar kecil, sidebar MUST pindah ke bawah daftar artikel dan layout MUST satu kolom.

**Portofolio**

- **FR-019**: Halaman Portofolio MUST menampilkan hero banner bergambar dengan breadcrumb "Beranda / Portofolio", judul bawaan "Portofolio Proyek", dan subjudul.
- **FR-020**: Filter kategori portofolio MUST berbentuk pil dengan "Semua" dan setiap kategori, serta pil aktif tampil berbeda.
- **FR-021**: Kartu proyek MUST menampilkan gambar, nama kategori, judul, ringkasan singkat (maksimal tiga baris), dan tautan "Lihat Detail Proyek →" ke halaman detail.
- **FR-022**: Kartu proyek tanpa ringkasan atau gambar sampul MUST tetap tampil rapi.

**Hero & Konten Admin**

- **FR-023**: Hero Artikel dan Portofolio MUST memakai gaya hero yang sama dengan halaman Produk, dan gambarnya MUST jatuh ke gambar bawaan bila belum diunggah atau berkasnya hilang.
- **FR-024**: Admin MUST dapat mengubah gambar, judul, dan subjudul hero Artikel dan Portofolio dari panel admin tanpa developer.
- **FR-025**: Data awal hero (judul, subjudul, gambar bawaan) MUST tersedia otomatis setelah rilis, termasuk di lingkungan produksi yang hanya menjalankan migrasi.

**Lintas halaman**

- **FR-026**: Semua halaman yang diubah MUST tampil benar pada ponsel, tablet, dan desktop tanpa gulir horizontal.
- **FR-027**: Perubahan MUST tidak mengubah halaman lain yang tidak disebut dalam dokumen klien (Tentang Kami, FAQ, Karir, Kontak, detail produk, detail artikel, detail proyek), kecuali bagian bersama yang memang diubah.
- **FR-028**: Seluruh teks tampil dalam bahasa Indonesia sesuai dokumen klien.

### Key Entities *(include if feature involves data)*

- **Hero Halaman**: judul, subjudul, dan gambar latar untuk halaman Artikel dan Portofolio; dikelola admin; memiliki nilai bawaan.
- **Pelanggan Newsletter**: alamat email unik dan waktu mendaftar; dibuat dari formulir "Update Mingguan" dan dapat dilihat admin.
- **Artikel**: judul, ringkasan, kategori, gambar, tanggal terbit; dipakai sebagai bahan pencarian dan grid.
- **Proyek Portofolio**: judul, kategori, gambar sampul, ringkasan; dipakai pada kartu dan filter.
- **Produk**: nama, gambar sampul, kategori; kartu hanya memakai nama dan gambar.
- **Item Section & Testimoni**: konten beranda yang sudah ada dan tetap dikelola dari admin.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Dalam peninjauan berdampingan dengan dokumen klien, 100% dari delapan area (Mengapa Beralih, Sederhana dan Mulus, Solusi, Testimoni, Footer Kontak, Produk, Artikel, Portofolio) dinilai sesuai oleh pemangku kepentingan, atau penyimpangannya disetujui secara tertulis.
- **SC-002**: Pengunjung dapat menemukan artikel tertentu lewat pencarian dalam kurang dari 15 detik dari membuka halaman Artikel.
- **SC-003**: Pengunjung dapat mendaftar "Update Mingguan" dalam kurang dari 20 detik, dan 100% pendaftaran valid tercatat tanpa duplikat.
- **SC-004**: Admin dapat mengganti gambar dan teks hero Artikel atau Portofolio dalam kurang dari 3 menit tanpa bantuan developer.
- **SC-005**: Pada lebar layar 360 px hingga 1440 px, tidak ada gulir horizontal dan tidak ada teks atau tombol yang terpotong pada semua halaman yang diubah.
- **SC-006**: Semua kartu dalam satu baris di Produk, Artikel, Portofolio, Mengapa Beralih, dan Testimoni memiliki tinggi yang sama pada layar lebar.
- **SC-007**: Tidak ada galat atau gambar rusak saat halaman dibuka dengan data kosong (tanpa artikel, proyek, produk, testimoni, atau gambar).
- **SC-008**: Seluruh pengujian otomatis yang sudah ada tetap lulus setelah pembaruan, kecuali pengujian yang memang memeriksa tampilan lama dan diperbarui sesuai desain baru.

## Assumptions

- Dokumen "Confirm Update Design" dari klien adalah acuan visual utama; tampilan yang sama dengan dokumen dianggap benar walaupun data contoh (judul artikel, nama proyek, foto) berbeda dari data yang tersimpan.
- Isi dan struktur data yang sudah dikelola admin (item section, testimoni, CTA, pengaturan kontak) tidak berubah; hanya presentasi yang disesuaikan.
- Pekerjaan hero admin yang belum di-commit (pengelolaan banner halaman beserta data awalnya) menjadi dasar fitur hero Artikel dan Portofolio, dan diselesaikan di dalam cakupan ini bila belum lengkap.
- Pencarian artikel cukup berupa pencocokan kata kunci sederhana pada judul dan ringkasan; pencarian teks penuh tidak diperlukan.
- Formulir "Update Mingguan" hanya mengumpulkan alamat email dan mencatatnya untuk dikelola admin; pengiriman email otomatis, konfirmasi dua langkah, dan berhenti berlangganan berada di luar cakupan.
- Artikel unggulan besar pada halaman Artikel digantikan oleh layout grid dan sidebar sesuai dokumen. CTA penutup yang dapat diedit admin tetap tampil di bawah grid (bagian di luar cuplikan dokumen klien).
- Pada halaman Produk, filter kategori dipertahankan dan harga tetap tersimpan di data tetapi tidak ditampilkan di daftar.
- Kartu Portofolio memakai ringkasan proyek yang sudah ada; bila belum ada, ringkasan diturunkan dari deskripsi proyek.
- Warna, tipografi, dan gaya umum mengikuti sistem desain situs yang sudah ada.
- Judul "Partner Kami" menggantikan judul testimoni lama di beranda; teks bawaan dapat diubah lagi jika klien meminta.
