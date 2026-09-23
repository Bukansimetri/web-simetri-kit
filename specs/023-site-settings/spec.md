# Feature Specification: Site Settings

**Feature Branch**: `023-site-settings`

**Created**: 2026-09-22

**Status**: Draft

**Input**: User description: "Site Settings lengkap yang benar-benar diterapkan ke halaman publik — bukan sekadar form penyimpan nilai. Empat kelompok pengaturan: Pengaturan Umum Situs (maintenance mode, identitas, informasi perusahaan, regional, legal, pesan error), SEO lanjutan (format judul, kata kunci, kanonik, kontrol pengindeksan, verifikasi situs, meta tag tambahan, robots.txt & sitemap yang dapat diatur), Scripts & Analytics (slot script head/body/footer, custom CSS, custom JavaScript, cookie consent — khusus super_admin), dan Media Sosial (URL profil, tombol berbagi, gambar berbagi default). Penekanan utama: setiap nilai wajib terbukti berpengaruh pada keluaran halaman publik yang sudah ada dan dibuktikan lewat feature test, bukan berhenti sebagai form."

## Clarifications

### Session 2026-09-22

- Q: Bagaimana nasib halaman Brand Settings yang sudah ada, mengingat sebagian isinya tumpang tindih dengan fitur ini? → A: Brand Settings dibubarkan total — seluruh pengaturannya dipindahkan ke halaman yang sesuai pada struktur baru, termasuk tema visual, favicon, dan setelan operasional; tidak ada lagi halaman Brand Settings.
- Q: Bagaimana nasib nilai yang sudah tersimpan pada instalasi yang sudah tayang saat Brand Settings dibubarkan? → A: Dipindahkan otomatis saat pembaruan dijalankan; admin tidak perlu mengisi ulang apa pun dan tampilan situs yang sudah tayang tidak berubah.
- Q: Bagaimana struktur halaman pengaturan di panel admin setelah Brand Settings dibubarkan? → A: Lima halaman — Pengaturan Umum, SEO, Scripts & Analytics, Media Sosial, dan Tampilan (warna, font, logo, favicon). Setelan operasional (WhatsApp, email notifikasi kontak, sakelar modul) berada di Pengaturan Umum.
- Q: Bagaimana bentuk pemberitahuan persetujuan cookie bagi pengunjung? → A: Bilah non-blokir dengan tombol terima dan tolak yang sama menonjol, ditambah tautan pengaturan per kategori; pengunjung tetap dapat memakai situs selama belum memutuskan.
- Q: Dari mana pengunjung membuka kembali kendali persetujuan cookie setelah pernah memilih? → A: Lewat tautan "Pengaturan Cookie" di footer, sebaris dengan tautan legal yang sudah ada, tersedia di setiap halaman publik.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Identitas dan kontak situs berhenti ditulis mati di kode (Priority: P1) 🎯 MVP

Admin membuka pengaturan situs, mengisi nama perusahaan, email, telepon, alamat, teks hak cipta, dan alamat halaman legal milik kliennya sendiri, lalu menyimpan. Footer situs publik langsung menampilkan data tersebut menggantikan data contoh bawaan.

**Why this priority**: Saat ini blok Kontak di footer memuat alamat, email, dan telepon milik klien lain yang ditulis mati di kode, begitu pula teks hak cipta dan tautan legal. Setiap instalasi klien baru harus mengedit kode untuk hal paling mendasar ini — pelanggaran langsung Prinsip I dan II konstitusi. Tanpa story ini, seluruh pengaturan lain tetap menyisakan identitas klien lain di situs yang tayang.

**Independent Test**: Login sebagai admin, isi seluruh informasi perusahaan dan legal dengan data berbeda dari bawaan, buka halaman publik mana pun, verifikasi footer menampilkan data baru dan tidak ada lagi jejak data contoh bawaan di seluruh keluaran halaman.

**Acceptance Scenarios**:

1. **Given** admin sudah mengisi nama, email, telepon, dan alamat perusahaan, **When** pengunjung membuka halaman publik mana pun, **Then** blok kontak footer menampilkan keempat data tersebut.
2. **Given** admin mengosongkan nomor telepon, **When** pengunjung membuka halaman publik, **Then** baris telepon tidak ditampilkan sama sekali, bukan tampil sebagai baris kosong atau ikon menggantung.
3. **Given** instalasi baru yang belum pernah disentuh admin, **When** pengunjung membuka halaman publik, **Then** footer tetap tampil utuh dengan nilai bawaan yang wajar dan tidak memuat data milik klien mana pun.
4. **Given** admin mengisi teks hak cipta dan alamat Syarat & Ketentuan, Kebijakan Privasi, serta Kebijakan Cookie, **When** pengunjung membuka halaman publik, **Then** footer menampilkan teks hak cipta tersebut dan ketiga tautan menuju alamat yang diisi.
5. **Given** admin mengosongkan alamat Kebijakan Cookie, **When** pengunjung membuka halaman publik, **Then** tautan Kebijakan Cookie tidak ditampilkan sementara dua tautan lain tetap tampil.
6. **Given** admin baru menyimpan perubahan, **When** admin membuka situs publik, **Then** perubahan langsung terlihat tanpa perlu membersihkan cache secara manual.

---

### User Story 2 - Ikon media sosial berhenti menjadi tautan mati (Priority: P1)

Admin mengisi alamat profil media sosial perusahaan. Ikon media sosial di header situs langsung menuju profil tersebut, dan ikon platform yang tidak diisi tidak ditampilkan.

**Why this priority**: Ikon Instagram, Facebook, dan YouTube saat ini tayang di header setiap halaman namun mengarah ke tautan kosong, sehingga pengunjung yang menekannya tidak ke mana-mana. Ini cacat yang terlihat langsung oleh pengunjung di setiap halaman dan berdiri sendiri dari pekerjaan lain.

**Independent Test**: Isi dua alamat profil dan kosongkan sisanya, buka halaman publik, verifikasi hanya dua ikon yang tampil dan keduanya menuju alamat yang diisi.

**Acceptance Scenarios**:

1. **Given** admin mengisi alamat profil Instagram, **When** pengunjung membuka halaman publik, **Then** ikon Instagram di header menuju alamat tersebut.
2. **Given** admin tidak mengisi alamat profil Facebook, **When** pengunjung membuka halaman publik, **Then** ikon Facebook tidak ditampilkan di header.
3. **Given** admin belum mengisi satu pun alamat profil, **When** pengunjung membuka halaman publik, **Then** seluruh kelompok ikon media sosial tidak ditampilkan tanpa menyisakan ruang kosong yang janggal.
4. **Given** admin mengisi alamat yang bukan alamat web yang sah, **When** admin menyimpan, **Then** sistem menolak dengan pesan yang menyebut platform mana yang salah.
5. **Given** admin mengisi alamat profil pada platform yang belum punya ikon di header, **When** pengunjung membuka halaman publik, **Then** ikon platform tersebut ikut ditampilkan.

---

### User Story 3 - Pemasangan kode pelacakan tanpa menyentuh kode (Priority: P1)

Super admin menempelkan potongan kode pelacakan iklan dan analitik ke slot yang sesuai, serta menambahkan gaya dan skrip khusus milik klien. Halaman publik memuat kode tersebut pada posisi yang benar tanpa perlu rilis kode baru.

**Why this priority**: Ini permintaan awal yang memicu fitur ini. Tanpa slot ini, setiap permintaan pemasangan pixel iklan, Tag Manager, atau penyesuaian tampilan kecil dari klien berubah menjadi pekerjaan pengembangan dan rilis ulang.

**Independent Test**: Login sebagai super admin, isi keempat slot skrip beserta gaya dan skrip khusus dengan penanda unik, buka halaman publik, verifikasi setiap penanda muncul tepat pada posisinya; lalu login sebagai admin biasa dan verifikasi halaman pengaturan ini tidak dapat diakses.

**Acceptance Scenarios**:

1. **Given** super admin mengisi slot skrip bagian kepala dokumen, **When** pengunjung membuka halaman publik, **Then** isi slot tersebut muncul di dalam bagian kepala dokumen.
2. **Given** super admin mengisi slot skrip awal badan, akhir badan, dan footer, **When** pengunjung membuka halaman publik, **Then** masing-masing isi muncul tepat pada posisi yang dijanjikan label slotnya.
3. **Given** super admin mengisi gaya khusus, **When** pengunjung membuka halaman publik, **Then** gaya tersebut ikut dimuat dan berlaku pada tampilan halaman.
4. **Given** super admin mengisi skrip khusus, **When** pengunjung membuka halaman publik, **Then** skrip tersebut dimuat sebelum badan dokumen ditutup.
5. **Given** admin biasa tanpa peran super admin, **When** admin tersebut membuka panel, **Then** menu pengaturan skrip tidak terlihat dan percobaan membuka alamatnya langsung ditolak.
6. **Given** seluruh slot skrip dikosongkan, **When** pengunjung membuka halaman publik, **Then** halaman tampil normal tanpa elemen kosong tersisa.
7. **Given** super admin sudah mengisi slot skrip, **When** siapa pun membuka panel admin, **Then** skrip tersebut tidak ikut dimuat di dalam panel admin.

---

### User Story 4 - SEO situs dapat disesuaikan per klien (Priority: P2)

Admin mengatur pola judul halaman, kata kunci, kontrol pengindeksan, kode verifikasi mesin pencari, dan meta tag tambahan. Halaman publik memakai pola dan nilai tersebut tanpa mengubah pengaturan SEO yang sudah diisi per konten.

**Why this priority**: Memperluas fondasi SEO yang sudah ada (spec 014 dan 015) sehingga tiap klien bisa menyesuaikan pola judul dan memverifikasi kepemilikan situs sendiri. Bernilai tinggi saat serah terima klien, tetapi situs tetap dapat tayang benar tanpanya karena dasar SEO sudah berjalan.

**Independent Test**: Ubah pemisah dan pola judul, buka beberapa jenis halaman, verifikasi judul mengikuti pola baru; isi satu kode verifikasi dan verifikasi penandanya muncul di kepala dokumen; matikan izin pengindeksan dan verifikasi halaman menyatakan dirinya tidak boleh diindeks.

**Acceptance Scenarios**:

1. **Given** admin mengubah pemisah judul dan pola judul halaman default, **When** pengunjung membuka halaman yang belum punya judul SEO sendiri, **Then** judul halaman mengikuti pola dan pemisah baru.
2. **Given** admin mengatur pola judul khusus untuk halaman artikel, **When** pengunjung membuka satu artikel, **Then** judul halaman mengikuti pola artikel, bukan pola default.
3. **Given** satu konten sudah punya judul SEO sendiri dari pengaturan per konten, **When** pengunjung membuka konten tersebut, **Then** judul konten itu yang dipakai dan pola default tidak menimpanya.
4. **Given** pola judul memuat penanda isian yang tidak dikenali, **When** pengunjung membuka halaman, **Then** penanda mentah tersebut tidak ikut tampil di judul.
5. **Given** admin mematikan izin pengindeksan, **When** mesin pencari membaca halaman publik, **Then** halaman menyatakan dirinya tidak boleh diindeks.
6. **Given** admin mengisi kode verifikasi Google Search Console, **When** mesin pencari membaca kepala dokumen, **Then** penanda verifikasi dengan kode tersebut tersedia.
7. **Given** admin mengisi meta tag tambahan, **When** pengunjung membuka halaman publik, **Then** meta tag tersebut ikut dimuat di kepala dokumen.
8. **Given** admin mengisi nama akun Twitter/X situs, **When** tautan situs dibagikan, **Then** informasi kartu berbagi menyebut akun tersebut.

---

### User Story 5 - Aturan perayapan dan peta situs dapat diatur admin (Priority: P2)

Admin menyunting isi berkas aturan perayapan dan memilih jenis konten apa saja yang masuk ke peta situs, tanpa meminta bantuan pengembang.

**Why this priority**: Aturan perayapan saat ini ditulis mati di kode sehingga klien yang ingin menutup sebagian situs dari mesin pencari harus meminta rilis baru. Berdiri sendiri dari pekerjaan SEO lain karena menyentuh keluaran yang berbeda.

**Independent Test**: Ubah isi aturan perayapan dan buka alamatnya, verifikasi isi baru tersaji; matikan penyertaan satu jenis konten lalu buka peta situs dan verifikasi konten tersebut hilang sementara jenis lain tetap ada.

**Acceptance Scenarios**:

1. **Given** admin menyunting isi aturan perayapan, **When** mesin pencari mengambil berkas tersebut, **Then** isi yang tersaji sama dengan yang disimpan admin.
2. **Given** isi aturan perayapan memuat penanda alamat situs, **When** berkas tersebut diambil, **Then** penanda itu tergantikan alamat situs yang sedang aktif.
3. **Given** admin mematikan penyertaan artikel pada peta situs, **When** mesin pencari mengambil peta situs, **Then** alamat artikel tidak lagi tercantum sementara jenis konten lain tetap tercantum.
4. **Given** admin mematikan peta situs sepenuhnya, **When** mesin pencari mengambil alamat peta situs, **Then** sistem menyatakan peta situs tidak tersedia.
5. **Given** admin mengosongkan isi aturan perayapan, **When** mesin pencari mengambil berkas tersebut, **Then** aturan bawaan yang aman tetap tersaji, bukan berkas kosong.
6. **Given** admin mengatur frekuensi perubahan dan prioritas default, **When** mesin pencari mengambil peta situs, **Then** seluruh entri mencantumkan nilai tersebut.

---

### User Story 6 - Pengunjung mengendalikan persetujuan cookie (Priority: P2)

Pengunjung yang pertama kali membuka situs melihat pemberitahuan cookie beserta pilihan kategori. Skrip analitik dan pemasaran baru berjalan setelah pengunjung menyetujui kategori terkait, dan pengunjung dapat mengubah pilihannya kapan saja.

**Why this priority**: Melengkapi pemasangan skrip pelacakan dengan kendali persetujuan yang dibutuhkan klien yang menyasar pasar dengan aturan privasi. Bergantung pada slot skrip dari Story 3 sehingga dikerjakan setelahnya.

**Independent Test**: Aktifkan persetujuan cookie dan isi skrip analitik, buka situs sebagai pengunjung baru, verifikasi skrip belum berjalan sebelum persetujuan diberikan lalu berjalan setelah disetujui, dan pilihan tetap diingat pada kunjungan berikutnya.

**Acceptance Scenarios**:

1. **Given** persetujuan cookie aktif dan pengunjung belum pernah memilih, **When** pengunjung membuka halaman publik, **Then** bilah pemberitahuan tampil dengan tombol menerima dan tombol menolak yang sama menonjol, disertai tautan menuju pengaturan per kategori.
2. **Given** bilah pemberitahuan sedang tampil, **When** pengunjung menggulir halaman dan menekan tautan atau tombol pada isi halaman, **Then** seluruh isi halaman tetap dapat digunakan tanpa pengunjung harus memutuskan persetujuan lebih dulu.
3. **Given** pengunjung belum menyetujui kategori analitik, **When** halaman dimuat, **Then** skrip yang ditandai sebagai analitik tidak dijalankan.
4. **Given** pengunjung menekan tombol menolak, **When** halaman dimuat ulang, **Then** tidak ada skrip berkategori analitik maupun pemasaran yang dijalankan dan pemberitahuan tidak tampil lagi.
5. **Given** pengunjung menyetujui kategori analitik, **When** halaman dimuat ulang, **Then** skrip analitik dijalankan dan pemberitahuan tidak tampil lagi.
6. **Given** pengunjung sudah pernah memilih, **When** pengunjung kembali pada kunjungan berikutnya, **Then** pilihan sebelumnya tetap berlaku tanpa ditanya ulang.
7. **Given** pengunjung ingin mengubah keputusannya, **When** pengunjung menekan tautan pengaturan cookie di footer, **Then** kendali persetujuan terbuka, pengunjung dapat mengubah pilihan per kategori, dan perubahan langsung berlaku.
8. **Given** kategori yang diperlukan agar situs berfungsi, **When** pengunjung membuka pilihan kategori, **Then** kategori tersebut selalu aktif dan tidak dapat dimatikan.
9. **Given** persetujuan cookie dimatikan admin, **When** pengunjung membuka halaman publik, **Then** tidak ada pemberitahuan yang tampil, tautan pengaturan cookie di footer tidak tampil, dan seluruh skrip berjalan seperti biasa.

---

### User Story 7 - Situs dapat ditutup sementara saat pemeliharaan (Priority: P3)

Admin menyalakan mode pemeliharaan sebelum melakukan perubahan besar. Pengunjung melihat halaman pemberitahuan pemeliharaan, sementara admin tetap dapat masuk panel dan memeriksa situs.

**Why this priority**: Berguna saat pergantian konten besar atau migrasi, namun situs tetap dapat beroperasi penuh tanpanya. Nilainya muncul sesekali, bukan harian.

**Independent Test**: Nyalakan mode pemeliharaan, buka halaman publik sebagai pengunjung biasa dan verifikasi halaman pemeliharaan tampil, lalu buka panel admin dan verifikasi tetap dapat diakses.

**Acceptance Scenarios**:

1. **Given** mode pemeliharaan aktif, **When** pengunjung membuka halaman publik mana pun, **Then** halaman pemberitahuan pemeliharaan tampil menggantikan isi halaman.
2. **Given** mode pemeliharaan aktif, **When** mesin pencari mengambil halaman publik, **Then** sistem menyatakan kondisi ini sementara sehingga halaman pemeliharaan tidak menggantikan halaman asli di hasil pencarian.
3. **Given** mode pemeliharaan aktif, **When** admin membuka panel admin, **Then** panel tetap dapat diakses dan digunakan seperti biasa.
4. **Given** mode pemeliharaan aktif dan admin sedang masuk, **When** admin membuka halaman publik, **Then** admin melihat isi situs yang sebenarnya, bukan halaman pemeliharaan.
5. **Given** mode pemeliharaan dimatikan kembali, **When** pengunjung membuka halaman publik, **Then** situs tampil normal tanpa langkah tambahan.

---

### User Story 8 - Halaman kesalahan berbicara dengan bahasa klien (Priority: P3)

Admin menulis pesan yang tampil saat pengunjung membuka alamat yang tidak ada atau saat terjadi gangguan sistem, sehingga nada bicaranya sesuai merek klien.

**Why this priority**: Memperhalus pengalaman pada kondisi yang jarang terjadi. Saat ini belum ada halaman kesalahan khusus sama sekali, namun ketiadaannya tidak menghalangi situs beroperasi.

**Independent Test**: Isi pesan kesalahan khusus, buka alamat yang tidak ada, verifikasi pesan tersebut tampil beserta jalan kembali ke halaman utama.

**Acceptance Scenarios**:

1. **Given** admin mengisi pesan halaman tidak ditemukan, **When** pengunjung membuka alamat yang tidak ada, **Then** pesan tersebut tampil pada halaman kesalahan.
2. **Given** admin mengisi pesan gangguan sistem, **When** terjadi gangguan pada sisi sistem, **Then** pesan tersebut tampil tanpa membocorkan rincian teknis apa pun.
3. **Given** admin belum mengisi pesan kesalahan, **When** pengunjung membuka alamat yang tidak ada, **Then** pesan bawaan yang wajar tetap tampil.
4. **Given** pengunjung berada di halaman kesalahan, **When** pengunjung ingin melanjutkan, **Then** tersedia jalan kembali ke halaman utama beserta identitas situs.

---

### User Story 9 - Pengunjung membagikan konten ke media sosial (Priority: P3)

Admin menyalakan tombol berbagi dan memilih platform yang relevan bagi kliennya. Pengunjung yang membaca artikel atau melihat produk dapat membagikannya lewat tombol tersebut.

**Why this priority**: Menambah jangkauan konten, namun konten tetap dapat dibagikan manual tanpa tombol ini. Bergantung pada gambar berbagi default agar hasil bagikan terlihat rapi.

**Independent Test**: Nyalakan tombol berbagi dan pilih dua platform, buka satu artikel, verifikasi hanya dua tombol tersebut tampil dan masing-masing membawa alamat artikel yang sedang dibuka.

**Acceptance Scenarios**:

1. **Given** admin menyalakan tombol berbagi dan memilih tiga platform, **When** pengunjung membuka satu artikel, **Then** tepat tiga tombol berbagi tampil.
2. **Given** pengunjung menekan satu tombol berbagi, **When** halaman berbagi terbuka, **Then** alamat yang dibagikan adalah alamat konten yang sedang dibuka.
3. **Given** admin mematikan tombol berbagi, **When** pengunjung membuka artikel, **Then** tidak ada tombol berbagi yang tampil.
4. **Given** satu konten belum punya gambar berbagi sendiri, **When** tautannya dibagikan, **Then** gambar berbagi default situs yang dipakai.

---

### Edge Cases

- Apa yang terjadi bila admin mengisi alamat halaman legal yang menunjuk ke halaman yang kemudian dihapus? Tautan tetap tampil sesuai isian admin; sistem tidak boleh gagal memuat footer karenanya.
- Bagaimana sistem menangani gaya khusus yang ditulis dengan sintaks salah? Halaman tetap tampil dengan gaya bawaan; kesalahan gaya tidak boleh menjatuhkan halaman.
- Bagaimana sistem menangani skrip khusus yang gagal berjalan? Isi halaman tetap tampil dan dapat dinavigasi tanpa bergantung pada keberhasilan skrip tersebut.
- Apa yang terjadi bila pola judul memuat penanda isian yang tidak berlaku untuk jenis halaman tersebut? Penanda diganti nilai kosong lalu spasi dan pemisah berlebih dirapikan, bukan tampil mentah.
- Apa yang terjadi bila admin mengisi teks yang sangat panjang pada pesan kesalahan atau deskripsi situs? Sistem membatasi panjang saat menyimpan dan memberi tahu admin, bukan merusak tata letak halaman.
- Apa yang terjadi pada mode pemeliharaan bila admin lupa mematikannya? Panel admin menampilkan penanda jelas bahwa situs sedang tertutup bagi pengunjung.
- Bagaimana bila pengunjung menolak seluruh kategori cookie yang bisa ditolak? Situs tetap berfungsi penuh dan hanya kehilangan pengukuran, bukan kehilangan fungsi.
- Bagaimana bila peta situs dimatikan sementara isi aturan perayapan masih menyebut alamat peta situs? Sistem menjaga keduanya tetap selaras sehingga tidak menunjuk ke berkas yang tidak tersedia.
- Apa yang terjadi bila super admin menyimpan skrip berukuran sangat besar? Sistem membatasi ukuran yang dapat disimpan dan memberi tahu batasnya, bukan memperlambat setiap halaman tanpa peringatan.

## Requirements *(mandatory)*

### Functional Requirements

#### Pengaturan umum situs

- **FR-001**: Admin MUST dapat mengatur nama situs, tagline, dan deskripsi situs dari halaman Pengaturan Umum. Logo dan favicon diatur dari halaman Tampilan (lihat FR-071).
- **FR-002**: Admin MUST dapat mengatur informasi perusahaan berupa nama, email, nomor telepon, dan alamat.
- **FR-003**: Blok kontak pada footer situs publik MUST menampilkan informasi perusahaan dari FR-002, menggantikan seluruh teks yang saat ini ditulis mati di kode.
- **FR-004**: Setiap bagian informasi perusahaan yang dikosongkan admin MUST tidak ditampilkan sama sekali pada halaman publik, tanpa menyisakan ikon, label, atau baris kosong.
- **FR-005**: Admin MUST dapat mengatur bahasa default dan zona waktu situs; bahasa default menentukan bahasa yang dinyatakan dokumen halaman dan zona waktu menjadi acuan tampilan tanggal pada halaman publik.
- **FR-006**: Admin MUST dapat mengatur teks hak cipta serta alamat halaman Syarat & Ketentuan, Kebijakan Privasi, dan Kebijakan Cookie.
- **FR-007**: Footer situs publik MUST memakai teks hak cipta dan ketiga alamat legal dari FR-006, dan MUST menyembunyikan tautan legal yang alamatnya dikosongkan.
- **FR-008**: Sistem MUST menyediakan nilai bawaan yang wajar dan netral untuk seluruh pengaturan pada FR-001 sampai FR-006, sehingga instalasi baru tampil utuh sebelum admin mengisi apa pun.
- **FR-009**: Sistem MUST tidak lagi memuat data identitas milik klien mana pun sebagai nilai tertulis di kode halaman publik.

#### Mode pemeliharaan

- **FR-010**: Admin MUST dapat menyalakan dan mematikan mode pemeliharaan.
- **FR-011**: Saat mode pemeliharaan aktif, pengunjung yang membuka halaman publik MUST melihat halaman pemberitahuan pemeliharaan menggantikan isi halaman.
- **FR-012**: Saat mode pemeliharaan aktif, sistem MUST menyatakan kondisi tersebut sebagai keadaan sementara kepada mesin pencari sehingga halaman asli tidak tergantikan di hasil pencarian.
- **FR-013**: Saat mode pemeliharaan aktif, panel admin MUST tetap dapat diakses, dan pengguna yang sedang masuk sebagai admin MUST tetap melihat isi situs publik yang sebenarnya.
- **FR-014**: Panel admin MUST menampilkan penanda yang jelas selama mode pemeliharaan masih aktif.

#### Halaman kesalahan

- **FR-015**: Admin MUST dapat mengatur pesan yang tampil saat halaman tidak ditemukan dan saat terjadi gangguan sistem.
- **FR-016**: Halaman kesalahan MUST menampilkan pesan dari FR-015, identitas situs, dan jalan kembali ke halaman utama.
- **FR-017**: Halaman kesalahan MUST tidak menampilkan rincian teknis apa pun kepada pengunjung.

#### Media sosial

- **FR-018**: Admin MUST dapat mengisi alamat profil untuk Facebook, Twitter/X, Instagram, LinkedIn, YouTube, Pinterest, dan TikTok.
- **FR-019**: Ikon media sosial pada halaman publik MUST menuju alamat dari FR-018, menggantikan tautan kosong yang dipakai saat ini.
- **FR-020**: Ikon platform yang alamatnya dikosongkan MUST tidak ditampilkan, dan seluruh kelompok ikon MUST tidak ditampilkan bila tidak ada satu pun alamat terisi.
- **FR-021**: Sistem MUST menolak penyimpanan alamat profil yang bukan alamat web sah, dengan pesan yang menyebut platform bersangkutan.
- **FR-022**: Admin MUST dapat menyalakan tombol berbagi dan memilih platform yang ditampilkan dari Facebook, Twitter/X, LinkedIn, Pinterest, Reddit, WhatsApp, Telegram, dan Email.
- **FR-023**: Tombol berbagi MUST tampil pada halaman detail artikel dan produk, dan MUST membawa alamat konten yang sedang dibuka.
- **FR-024**: Admin MUST dapat mengatur gambar berbagi default yang dipakai konten yang belum punya gambar berbagi sendiri.

#### SEO lanjutan

- **FR-025**: Admin MUST dapat mengatur pemisah judul dan pola judul halaman default memakai penanda isian judul halaman, nama situs, dan pemisah.
- **FR-026**: Admin MUST dapat mengatur pola judul khusus per jenis halaman untuk jenis halaman yang benar-benar punya alamat publik di situs ini: beranda, daftar artikel, detail artikel, daftar produk, detail produk, daftar portfolio, detail proyek portfolio, halaman kustom, FAQ, karir, kontak, dan tentang kami.
- **FR-027**: Sistem MUST membuang penanda isian yang tidak dikenali atau tidak berlaku dari judul yang dihasilkan, lalu merapikan pemisah dan spasi berlebih.
- **FR-028**: Pengaturan SEO per konten yang sudah ada MUST tetap menang atas pola dan nilai default situs.
- **FR-029**: Admin MUST dapat mengatur kata kunci meta dan alamat kanonik default situs.
- **FR-030**: Admin MUST dapat menyalakan atau mematikan izin pengindeksan dan izin penelusuran tautan, dan pilihan tersebut MUST dinyatakan pada setiap halaman publik.
- **FR-031**: Admin MUST dapat mengatur nilai berbagi Open Graph dan Twitter Card, termasuk nama akun Twitter/X situs, tanpa merombak keluaran berbagi yang sudah berjalan.
- **FR-032**: Admin MUST dapat mengatur data terstruktur organisasi yang sudah dimuat halaman publik saat ini.
- **FR-033**: Admin MUST dapat menambahkan meta tag bebas yang ikut dimuat pada kepala dokumen halaman publik.
- **FR-034**: Admin MUST dapat mengisi kode verifikasi kepemilikan situs untuk Google Search Console, Bing Webmaster Tools, Yandex Webmaster, dan Baidu Webmaster Tools, dan setiap kode yang terisi MUST dinyatakan pada kepala dokumen.

#### Aturan perayapan dan peta situs

- **FR-035**: Admin MUST dapat menyunting isi berkas aturan perayapan, menggantikan isi yang saat ini ditulis mati di kode.
- **FR-036**: Sistem MUST mengganti penanda alamat situs di dalam isi aturan perayapan dengan alamat situs yang sedang aktif.
- **FR-037**: Sistem MUST menyajikan aturan perayapan bawaan yang aman bila admin mengosongkan isinya.
- **FR-038**: Admin MUST dapat menyalakan atau mematikan peta situs, dan memilih jenis konten yang disertakan di antara halaman kustom, artikel, produk, dan proyek portfolio.
- **FR-039**: Admin MUST dapat mengatur frekuensi perubahan dan prioritas default yang dicantumkan pada entri peta situs.
- **FR-040**: Sistem MUST menjaga aturan perayapan, peta situs, dan kontrol pengindeksan tetap selaras sehingga tidak saling bertentangan atau menunjuk berkas yang tidak tersedia.

#### Skrip, gaya khusus, dan analitik

- **FR-041**: Super admin MUST dapat mengisi empat slot kode terpisah yang dimuat pada kepala dokumen, tepat setelah badan dokumen dibuka, tepat sebelum badan dokumen ditutup, dan pada bagian footer.
- **FR-042**: Super admin MUST dapat mengisi gaya khusus yang dimuat pada kepala dokumen halaman publik.
- **FR-043**: Super admin MUST dapat mengisi skrip khusus yang dimuat sebelum badan dokumen halaman publik ditutup.
- **FR-044**: Isi seluruh slot pada FR-041 sampai FR-043 MUST dimuat apa adanya sebagai kode, tidak diubah menjadi teks biasa.
- **FR-045**: Hanya pengguna dengan peran super admin yang MUST dapat melihat dan menyimpan pengaturan pada FR-041 sampai FR-043; pengguna admin lain MUST tidak melihat menunya dan MUST ditolak bila membuka alamatnya langsung.
- **FR-046**: Isi slot kode MUST hanya dimuat pada halaman publik dan MUST tidak dimuat di dalam panel admin.
- **FR-047**: Slot yang dikosongkan MUST tidak meninggalkan elemen kosong pada halaman.
- **FR-048**: Sistem MUST membatasi ukuran isi yang dapat disimpan per slot dan memberi tahu admin batas tersebut saat terlampaui.
- **FR-049**: Kegagalan gaya atau skrip khusus MUST tidak menghalangi isi utama halaman tampil dan dinavigasi.

#### Persetujuan cookie

- **FR-050**: Admin MUST dapat menyalakan dan mematikan pemberitahuan persetujuan cookie.
- **FR-051**: Saat aktif, pengunjung yang belum pernah memilih MUST melihat pemberitahuan beserta pilihan kategori persetujuan.
- **FR-052**: Sistem MUST menyediakan kategori yang diperlukan agar situs berfungsi yang selalu aktif dan tidak dapat dimatikan pengunjung, serta kategori analitik dan pemasaran yang dapat ditolak.
- **FR-053**: Super admin MUST dapat menandai setiap slot kode pada FR-041 sampai FR-043 dengan kategori persetujuan yang mengikatnya.
- **FR-054**: Kode yang terikat kategori analitik atau pemasaran MUST tidak dijalankan sebelum pengunjung menyetujui kategori tersebut.
- **FR-055**: Pilihan pengunjung MUST diingat pada kunjungan berikutnya sehingga pemberitahuan tidak tampil berulang.
- **FR-056**: Pengunjung MUST dapat membuka kembali kendali persetujuan lewat tautan pengaturan cookie di footer yang tersedia pada setiap halaman publik, mengubah pilihannya per kategori kapan saja, dan perubahan MUST langsung berlaku.
- **FR-057**: Saat pemberitahuan persetujuan dimatikan admin, seluruh kode MUST dijalankan seperti biasa tanpa pemberitahuan apa pun.
- **FR-058**: Sistem MUST menyimpan pilihan persetujuan pada perangkat pengunjung itu sendiri, dan MUST tidak menyimpan catatan persetujuan di sisi sistem. Fitur ini tidak menyediakan riwayat persetujuan yang dapat ditinjau admin, sehingga tidak ada data pribadi pengunjung yang terkumpul dari proses ini.

#### Penerapan nyata dan pembuktian

- **FR-059**: Setiap pengaturan pada fitur ini MUST punya titik penerapan nyata pada keluaran yang sudah ada — footer, header, kepala dokumen, berkas aturan perayapan, peta situs, halaman kesalahan, atau halaman pemeliharaan. Pengaturan yang hanya tersimpan tanpa pengaruh MUST tidak dianggap selesai.
- **FR-060**: Setiap titik penerapan pada FR-059 MUST dibuktikan lewat feature test yang memeriksa keluaran halaman publik, bukan hanya lewat pemeriksaan manual, sesuai Prinsip IV konstitusi.
- **FR-061**: Perubahan pengaturan MUST langsung terlihat pada halaman publik tanpa langkah pembersihan cache manual oleh admin.
- **FR-062**: Seluruh pengaturan MUST dapat diatur per instalasi klien tanpa perubahan kode, sesuai Prinsip I konstitusi.

#### Konsolidasi pengaturan yang sudah ada

- **FR-063**: Halaman Brand Settings yang ada saat ini MUST dibubarkan, dan seluruh pengaturan di dalamnya MUST dipindahkan ke halaman yang sesuai pada struktur pengaturan baru.
- **FR-064**: Setelah pemindahan, setiap pengaturan MUST hanya dapat diubah dari satu tempat. Sistem MUST tidak menyediakan dua tempat berbeda yang mengatur hal yang sama.
- **FR-065**: Pengaturan lama yang tidak tercakup fitur ini — warna dan font tema, favicon, nomor WhatsApp bisnis, email penerima notifikasi kontak, dan sakelar modul opsional — MUST tetap tersedia setelah pembubaran pada halaman yang sesuai, tidak boleh hilang.
- **FR-066**: Nomor WhatsApp bisnis dan email penerima notifikasi kontak MUST tetap diperlakukan sebagai setelan operasional form kontak yang terpisah dari informasi kontak perusahaan yang tampil di footer, dan MUST tidak digabungkan menjadi satu nilai meskipun terlihat serupa.
- **FR-067**: Seluruh titik pemakaian pengaturan lama yang sudah berjalan — logo di header dan footer, favicon, warna dan font halaman publik, gambar berbagi default, deskripsi meta default, tautan WhatsApp pada form kontak, pengiriman email notifikasi kontak, dan sakelar modul Karir — MUST tetap berfungsi persis seperti sebelumnya setelah pemindahan.
- **FR-068**: Nilai pengaturan yang sudah tersimpan pada instalasi yang sedang berjalan MUST berpindah otomatis ke tempat barunya saat pembaruan dijalankan, tanpa admin perlu mengisi ulang satu pun nilai.
- **FR-069**: Setelah pemindahan otomatis pada FR-068, halaman publik instalasi yang sudah tayang MUST tampil dan berperilaku persis sama seperti sebelum pembaruan selama admin tidak mengubah pengaturan apa pun.
- **FR-070**: Panel admin MUST menyediakan lima halaman pengaturan situs: Pengaturan Umum, SEO, Scripts & Analytics, Media Sosial, dan Tampilan; masing-masing MUST dapat disimpan sendiri tanpa memengaruhi isi halaman lain.
- **FR-071**: Halaman Tampilan MUST memuat pengaturan tema visual berupa warna, font, logo, dan favicon.
- **FR-072**: Halaman Pengaturan Umum MUST memuat setelan operasional berupa nomor WhatsApp bisnis, email penerima notifikasi kontak, dan sakelar modul opsional.
- **FR-073**: Pemberitahuan persetujuan cookie MUST berbentuk bilah yang tidak menghalangi interaksi; pengunjung MUST tetap dapat membaca dan memakai seluruh isi halaman selama belum memutuskan.
- **FR-074**: Bilah persetujuan MUST menyediakan tombol menerima seluruh kategori dan tombol menolak seluruh kategori yang dapat ditolak dengan penonjolan visual setara, ditambah tautan menuju pengaturan per kategori. Tombol menolak MUST tidak lebih sulit dijangkau daripada tombol menerima.
- **FR-075**: Tautan pengaturan cookie di footer MUST hanya ditampilkan saat pemberitahuan persetujuan diaktifkan admin, dan MUST ditempatkan sebaris dengan tautan legal yang sudah ada.

### Key Entities

- **Pengaturan Umum Situs**: Identitas situs, informasi perusahaan, pengaturan regional, informasi legal, pesan halaman kesalahan, sakelar mode pemeliharaan, dan setelan operasional form kontak beserta sakelar modul opsional. Satu set nilai per instalasi.
- **Pengaturan Tampilan**: Warna tema, font heading dan body, logo, dan favicon. Menampung pengaturan visual yang sebelumnya berada di halaman Brand Settings.
- **Pengaturan SEO Situs**: Pola judul dan pemisah, pola judul per jenis halaman, kata kunci, alamat kanonik default, kontrol pengindeksan, nilai berbagi, data terstruktur, meta tag tambahan, dan kode verifikasi mesin pencari. Menjadi lapisan default di bawah pengaturan SEO per konten yang sudah ada.
- **Pengaturan Perayapan dan Peta Situs**: Isi aturan perayapan, sakelar peta situs, pilihan jenis konten yang disertakan, frekuensi perubahan dan prioritas default.
- **Pengaturan Skrip dan Analitik**: Empat slot kode berdasarkan posisi muat, gaya khusus, skrip khusus, dan kategori persetujuan yang mengikat tiap slot. Hanya dapat diubah super admin.
- **Pengaturan Media Sosial**: Alamat profil per platform, sakelar dan pilihan platform tombol berbagi, serta gambar berbagi default.
- **Persetujuan Cookie Pengunjung**: Pilihan per kategori milik satu pengunjung, tersimpan pada perangkat pengunjung tersebut dan dipakai untuk menentukan kode mana yang boleh berjalan. Tidak tersimpan di sisi sistem.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Seluruh identitas, kontak, dan tautan legal yang tampil di situs publik dapat diganti sepenuhnya lewat panel admin tanpa satu pun perubahan kode.
- **SC-002**: Pencarian menyeluruh pada keluaran halaman publik instalasi baru tidak menemukan satu pun data identitas milik klien tertentu yang tertinggal sebagai nilai tertulis di kode.
- **SC-003**: Admin dapat memasang kode pelacakan baru dan melihatnya aktif di situs publik dalam waktu kurang dari 5 menit tanpa melibatkan pengembang maupun rilis kode.
- **SC-004**: Setiap pengaturan yang disediakan fitur ini memiliki setidaknya satu feature test yang membuktikan pengaruhnya pada keluaran halaman publik; tidak ada pengaturan tanpa pembuktian.
- **SC-005**: Ikon media sosial yang tampil di situs publik seluruhnya menuju alamat yang benar; tidak ada ikon bertautan kosong yang tersisa.
- **SC-006**: Instalasi baru yang belum disentuh admin tetap menampilkan seluruh halaman publik secara utuh, tanpa bagian kosong, tautan menggantung, maupun teks penampung.
- **SC-007**: Admin dapat menutup dan membuka kembali situs untuk pemeliharaan sepenuhnya dari panel admin, dengan panel tetap dapat diakses selama situs tertutup.
- **SC-008**: Sebelum pengunjung menyetujui kategori analitik, tidak ada satu pun kode berkategori analitik atau pemasaran yang berjalan pada halaman publik.
- **SC-009**: Waktu muat halaman publik tidak memburuk secara terukur dibanding sebelum fitur ini, pada kondisi seluruh pengaturan terisi wajar.
- **SC-010**: Serah terima instalasi baru ke klien tidak lagi membutuhkan penyuntingan berkas tampilan untuk hal-hal yang dicakup fitur ini.
- **SC-011**: Instalasi klien yang sudah tayang dapat menerima pembaruan ini tanpa satu pun langkah pengisian ulang oleh admin, dan tanpa perubahan tampilan yang terlihat pengunjung.
- **SC-012**: Setiap pengaturan hanya memiliki satu tempat pengubahan di panel admin; penelusuran seluruh halaman pengaturan tidak menemukan dua field berbeda yang mengatur nilai yang sama.

## Assumptions

- Pengaturan bahasa default hanya menentukan bahasa yang dinyatakan dokumen halaman dan acuan penulisan tanggal. Fitur ini **tidak** mencakup sistem penerjemahan konten maupun situs multibahasa — itu pekerjaan tersendiri yang jauh lebih besar dan berada di luar cakupan.
- Pola judul per jenis halaman hanya disediakan untuk jenis halaman yang benar-benar punya alamat publik di situs ini. Penelusuran daftar rute publik saat perencanaan memastikan situs ini **tidak** memiliki halaman hasil pencarian, halaman penulis, halaman tag, maupun halaman daftar per kategori — karena itu keempatnya tidak disediakan, baik pada pola judul maupun pada pilihan isi peta situs, sejalan dengan Prinsip V konstitusi yang melarang abstraksi spekulatif. Bila kelak halaman kategori dibuat, penambahannya menjadi pekerjaan tersendiri.
- Pengaturan SEO yang sudah berjalan dari spec 014-seo-management dan 015-sitemap-robots tidak dirombak. Fitur ini memperluasnya sebagai lapisan default dan tetap memberi kemenangan pada pengaturan per konten yang sudah ada.
- Slot kode dan gaya khusus ditujukan bagi pemasangan alat pihak ketiga yang lazim seperti pengelola tag, pixel iklan, dan penyesuaian tampilan kecil. Isinya sengaja dimuat apa adanya sebagai kode; pembatasan keamanannya bertumpu pada pembatasan peran super admin, bukan pada penyaringan isi.
- Mode pemeliharaan menutup halaman publik saja. Alamat berkas aturan perayapan dan peta situs mengikuti perilaku halaman publik selama pemeliharaan.
- Kategori persetujuan cookie mengikuti pembagian lazim: kategori yang diperlukan agar situs berfungsi, kategori analitik, dan kategori pemasaran.
- Persetujuan cookie hanya diingat pada perangkat pengunjung. Pencatatan persetujuan di sisi sistem sebagai bukti kepatuhan yang dapat ditinjau — beserta kewajiban retensi dan penghapusan data yang menyertainya — berada di luar cakupan dan merupakan batas yang diambil sadar, bukan kelalaian. Klien yang kelak menyasar pasar dengan kewajiban pembuktian persetujuan memerlukan pekerjaan tersendiri.
- Pengaturan disimpan sebagai pengaturan tingkat instalasi, bukan per pengguna, dan berlaku untuk seluruh pengunjung situs tersebut.
- Panel admin sudah memiliki sistem peran yang dapat membedakan super admin dari admin lain, sehingga pembatasan akses pada slot kode dapat bertumpu padanya.
- Nilai bawaan seluruh pengaturan bersifat netral tanpa identitas klien mana pun, sehingga aman dipakai instalasi baru maupun demo penjualan.
