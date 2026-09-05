# Feature Specification: Career/Lowongan Kerja CRUD Admin + Toggle Modul

**Feature Branch**: `006-career-crud-admin`

**Created**: 2026-09-05

**Status**: Draft

**Input**: User description: "AMC-212: Modul Career/Lowongan Kerja — CRUD Admin + Toggle Modul per Klien. Model `JobOpening`, migration, dan seeder sudah ada dari fitur Theme & Branding System, dipakai untuk render halaman publik /karir dengan data seed statis. Tujuan: (1) menambahkan CRUD admin (Filament Resource) untuk model JobOpening; (2) menambahkan kemampuan menonaktifkan SELURUH MODUL Karir per instalasi klien — toggle global yang, ketika dimatikan, menyembunyikan route/halaman publik /karir DAN link 'Karir' di navigasi footer."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Admin menulis & mengedit lowongan kerja (Priority: P1) 🎯 MVP

Admin membuka menu Lowongan Kerja di panel admin, menulis lowongan baru (judul posisi, lokasi, tipe pekerjaan, deskripsi), mengedit lowongan yang sudah ada, mengaktifkan/menonaktifkan lowongan tertentu, atau menghapusnya — tanpa developer perlu mengubah seeder atau kode.

**Why this priority**: Nilai bisnis inti tiket — tanpa kemampuan tulis/edit dasar, halaman Karir tetap terkunci ke data seed statis dan tidak bisa dipakai sungguhan oleh klien manapun.

**Independent Test**: Login sebagai admin, tulis lowongan baru dengan field wajib terisi dan status aktif, simpan, buka `/karir`, verifikasi lowongan muncul. Edit lowongan tsb, verifikasi perubahan tampil. Nonaktifkan lowongan (toggle aktif per-item), verifikasi hilang dari `/karir` tanpa dihapus dari admin.

**Acceptance Scenarios**:

1. **Given** admin berada di form tambah lowongan, **When** admin mengisi field wajib (judul, lokasi, tipe pekerjaan, deskripsi) dan menyimpan, **Then** lowongan tersimpan dan langsung muncul di `/karir` (jika aktif) tanpa deploy ulang.
2. **Given** admin mengedit lowongan yang sudah ada, **When** admin mengubah judul/lokasi/deskripsi dan menyimpan, **Then** halaman publik `/karir` menampilkan versi terbaru pada request berikutnya.
3. **Given** sebuah lowongan berstatus aktif, **When** admin menonaktifkannya (toggle "aktif" di form/tabel), **Then** lowongan tsb TIDAK lagi tampil di `/karir`, tapi tetap ada di daftar admin untuk diaktifkan lagi nanti.
4. **Given** admin mencoba menyimpan lowongan tanpa mengisi field wajib, **When** admin menyimpan, **Then** sistem menolak dengan pesan validasi per field.
5. **Given** admin menghapus sebuah lowongan, **When** admin mengonfirmasi penghapusan, **Then** lowongan tsb hilang dari admin maupun `/karir` secara permanen.

---

### User Story 2 - Admin menonaktifkan seluruh modul Karir untuk instalasi yang tidak membutuhkannya (Priority: P1)

Admin/operator instalasi klien yang tidak membutuhkan halaman karir sama sekali (mis. bisnis kecil tanpa rencana rekrutmen terbuka) bisa mematikan seluruh modul Karir lewat satu pengaturan, sehingga halaman publik `/karir` dan link "Karir" di navigasi tidak lagi muncul di situs — tanpa perlu developer menghapus kode atau route.

**Why this priority**: Kebutuhan eksplisit tiket ("opsional, toggle aktif per klien") — starter kit ini dipakai lintas klien dengan kebutuhan berbeda-beda (Principle I: Multi-Client Reusability); memaksa semua klien menampilkan halaman Karir walau tidak relevan bagi mereka bertentangan dengan prinsip white-label/reusable ini.

**Independent Test**: Sebagai admin, matikan toggle modul Karir di pengaturan. Buka `/karir` sebagai pengunjung, verifikasi halaman tidak lagi bisa diakses (404). Buka halaman mana pun yang menampilkan footer, verifikasi link "Karir" tidak muncul. Nyalakan kembali toggle, verifikasi `/karir` dan link "Karir" muncul kembali seperti semula.

**Acceptance Scenarios**:

1. **Given** modul Karir dalam keadaan aktif (default), **When** pengunjung membuka `/karir`, **Then** halaman tampil normal dan link "Karir" muncul di footer.
2. **Given** admin menonaktifkan modul Karir lewat pengaturan, **When** pengunjung membuka `/karir`, **Then** sistem mengembalikan halaman "tidak ditemukan" (404), bukan halaman kosong/error lain.
3. **Given** modul Karir nonaktif, **When** pengunjung membuka halaman publik mana pun (mis. beranda), **Then** link "Karir" TIDAK muncul di navigasi footer.
4. **Given** modul Karir sebelumnya nonaktif, **When** admin mengaktifkannya kembali, **Then** `/karir` dan link "Karir" di footer kembali muncul tanpa kehilangan data lowongan yang sudah ada sebelumnya.

### Edge Cases

- Apa yang terjadi jika modul Karir dinonaktifkan sementara ada lowongan aktif tersimpan? Data lowongan TIDAK hilang/terhapus — hanya halaman publiknya yang disembunyikan; begitu modul diaktifkan lagi, lowongan yang masih aktif langsung tampil kembali.
- Apa yang terjadi jika modul Karir aktif tapi tidak ada satupun lowongan yang berstatus aktif? Halaman `/karir` MUST tetap 200 dengan empty state yang wajar (perilaku yang sudah ada), bukan error.
- Apa yang terjadi jika admin mencoba menghapus lowongan yang sedang ditampilkan publik? Lowongan MUST langsung hilang dari `/karir` pada request berikutnya, konsisten dengan pola hapus di modul Produk/Artikel.
- Apa yang terjadi jika admin mengakses langsung menu admin "Lowongan Kerja" saat modul Karir nonaktif? CRUD admin MUST tetap bisa diakses dan dipakai seperti biasa — toggle modul hanya memengaruhi visibilitas halaman PUBLIK, bukan kemampuan admin mengelola datanya (supaya admin bisa menyiapkan konten karir dulu sebelum modul diaktifkan).

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Admin panel MUST menyediakan halaman daftar lowongan kerja (CRUD) — tambah, edit, hapus.
- **FR-002**: Admin MUST bisa menulis lowongan baru dengan mengisi: judul posisi, lokasi, tipe pekerjaan (dipilih dari daftar tetap), deskripsi, dan status aktif/nonaktif per-lowongan.
- **FR-003**: Sistem MUST memvalidasi field wajib (judul, lokasi, tipe pekerjaan, deskripsi) — submit tanpa field wajib MUST ditolak dengan pesan error per field.
- **FR-004**: Admin MUST bisa mengedit seluruh field lowongan yang sudah ada, dan perubahan MUST langsung tercermin di halaman publik `/karir` tanpa deploy ulang.
- **FR-005**: Admin MUST bisa menghapus lowongan, dengan konfirmasi terlebih dahulu sebelum penghapusan diproses.
- **FR-006**: Admin MUST bisa mengaktifkan/menonaktifkan sebuah lowongan tanpa menghapusnya — lowongan nonaktif TIDAK tampil di `/karir` tapi tetap ada di daftar admin.
- **FR-007**: Tipe pekerjaan MUST dipilih dari daftar pilihan tetap (mis. Full-time, Part-time, Kontrak, Magang) — bukan teks bebas — supaya tampilan di halaman publik konsisten.
- **FR-008**: CRUD Lowongan Kerja MUST terbuka untuk semua role yang memiliki akses panel admin, konsisten dengan resource admin lain yang sudah ada.
- **FR-009**: Sistem MUST menyediakan satu pengaturan global ("modul Karir aktif/nonaktif") yang independen dari status aktif/nonaktif masing-masing lowongan individual.
- **FR-010**: Ketika modul Karir dinonaktifkan, halaman publik `/karir` MUST mengembalikan respons "tidak ditemukan" (404) untuk semua pengunjung.
- **FR-011**: Ketika modul Karir dinonaktifkan, link "Karir" di navigasi footer publik MUST TIDAK ditampilkan di halaman manapun.
- **FR-012**: Menonaktifkan modul Karir MUST TIDAK menghapus data lowongan kerja yang sudah tersimpan — mengaktifkan modul kembali MUST langsung menampilkan kembali lowongan-lowongan yang berstatus aktif tanpa perlu input ulang.
- **FR-013**: Menonaktifkan modul Karir MUST TIDAK membatasi akses admin ke CRUD Lowongan Kerja di panel admin — admin tetap bisa menulis/mengedit/menghapus lowongan seperti biasa terlepas dari status modul.
- **FR-014**: Pengaturan toggle modul Karir MUST dapat diubah oleh admin lewat panel admin, tanpa perlu mengedit file konfigurasi atau kode.

### Key Entities

- **Job Opening** (`JobOpening`, entity yang sudah ada dari 002-theme-branding-system): judul posisi, lokasi, tipe pekerjaan (dari daftar tetap), deskripsi, status aktif/nonaktif per-lowongan (FR-006). Tidak berelasi ke entity lain.
- **Pengaturan Modul Karir**: satu nilai boolean global (bukan per-lowongan) yang menentukan apakah seluruh fitur Karir (halaman publik + link navigasi) ditampilkan untuk instalasi ini (FR-009 s.d. FR-014).

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin dapat menulis lowongan baru dan melihatnya tampil di halaman publik `/karir` dalam satu kali proses simpan (status aktif), tanpa bantuan developer atau deploy ulang.
- **SC-002**: Admin dapat menyelesaikan perubahan data satu lowongan (edit field + simpan) dalam waktu kurang dari 2 menit.
- **SC-003**: 100% percobaan submit dengan data tidak valid (field wajib kosong) ditolak dengan pesan error yang jelas.
- **SC-004**: Setelah lowongan dihapus atau dinonaktifkan, 0% halaman publik yang masih menampilkannya.
- **SC-005**: Admin dapat menonaktifkan seluruh modul Karir untuk instalasinya dalam satu langkah pengaturan, dan perubahan berlaku langsung (halaman `/karir` 404, link navigasi hilang) tanpa deploy ulang atau bantuan developer.
- **SC-006**: 100% instalasi yang menonaktifkan modul Karir tidak kehilangan data lowongan yang sudah tersimpan — data langsung tersedia kembali begitu modul diaktifkan ulang.

## Assumptions

- Fitur ini memperluas model `JobOpening` yang sudah ada dari 002-theme-branding-system, bukan membuat entity terpisah untuk data lowongan itu sendiri.
- `JobOpeningSeeder` tetap dipertahankan untuk kebutuhan demo/dev (konsisten pola AMC-207/AMC-213) — CRUD admin jadi sumber data utama pasca go-live.
- Daftar pilihan tetap untuk tipe pekerjaan (FR-007) mengikuti istilah umum industri: Full-time, Part-time, Kontrak, Magang — daftar ini cukup ditentukan sekali saat implementasi, tidak perlu dikelola dinamis oleh admin (bukan entity taxonomy terpisah seperti Kategori Produk/Artikel), karena jumlah opsinya kecil dan jarang berubah.
- Pengaturan toggle modul Karir (FR-009) disimpan sebagai satu boolean baru pada mekanisme pengaturan global yang sudah ada di project ini (tempat pengaturan lain seperti nama aplikasi, logo, dan warna brand disimpan) — bukan tabel/entity baru, dan bukan sistem toggle modul generik untuk seluruh fitur starter kit (Principle V: Simplicity).
- Default nilai toggle modul Karir untuk instalasi baru adalah AKTIF — konsisten dengan perilaku saat ini (halaman `/karir` sudah ada dan berfungsi sejak 002-theme-branding-system) sehingga tidak ada perubahan perilaku mendadak bagi instalasi yang sudah berjalan.
- Toggle modul Karir hanya memengaruhi visibilitas halaman publik dan link navigasi — tidak memengaruhi akses CRUD admin (lihat FR-013), supaya admin tetap bisa menyiapkan konten karir kapan saja.
