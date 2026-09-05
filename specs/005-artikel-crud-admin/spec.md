# Feature Specification: Artikel CRUD Admin

**Feature Branch**: `005-artikel-crud-admin`

**Created**: 2026-09-05

**Status**: Draft

**Input**: User description: "AMC-213: Modul Blog/Artikel — CRUD Admin. Model `Article`, migration, dan seeder sudah ada dari fitur Theme & Branding System, dipakai untuk render halaman publik /artikel (list) dan /artikel/{slug} (detail) dengan data seed statis. Tujuan: menambahkan CRUD admin (Filament Resource) plus melengkapi field yang diminta tiket ini (kategori, tag, featured image, draft/publish) supaya admin/operator instalasi klien bisa menulis, mengedit, menghapus, mengatur draft/publish, dan mengelola kategori & tag artikel mereka sendiri lewat panel admin."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Admin mengelola kategori artikel (Priority: P1)

Admin membuka menu Kategori Artikel di panel admin, membuat kategori baru (misal "Tips", "Berita", "Edukasi"), mengedit, mengatur urutan, atau menghapus kategori yang tidak dipakai — tanpa developer perlu mengubah kode.

**Why this priority**: Kategori adalah data prasyarat untuk User Story 2 (menulis artikel perlu memilih kategori dari daftar yang sudah ada), sama seperti pola Kategori Produk di AMC-207.

**Independent Test**: Login sebagai admin, buka menu Kategori Artikel, buat kategori baru, verifikasi muncul sebagai pilihan saat menulis artikel. Coba hapus kategori yang masih dipakai artikel, verifikasi ditolak dengan pesan jelas.

**Acceptance Scenarios**:

1. **Given** admin berada di form tambah kategori artikel, **When** admin mengisi nama dan menyimpan, **Then** kategori tersimpan dan langsung tersedia sebagai pilihan saat menulis/mengedit artikel.
2. **Given** admin mencoba menyimpan kategori dengan nama yang sudah dipakai kategori lain, **When** admin menyimpan, **Then** sistem menolak dengan pesan error jelas.
3. **Given** sebuah kategori masih dipakai satu atau lebih artikel, **When** admin mencoba menghapusnya, **Then** sistem menolak penghapusan dan menjelaskan bahwa kategori masih dipakai.

---

### User Story 2 - Admin menulis & mengedit artikel (Priority: P1) 🎯 MVP

Admin menulis artikel baru (judul, ringkasan, isi lengkap, kategori, redaksi, featured image) atau mengedit artikel yang sudah ada — tanpa perlu developer mengubah kode atau seeder. Setelah disimpan, artikel langsung tampil di halaman publik `/artikel` dan `/artikel/{slug}`.

**Why this priority**: Nilai bisnis inti tiket — tanpa kemampuan tulis/edit dasar, blog tetap terkunci ke data seed dan tidak bisa dipakai sungguhan oleh klien manapun.

**Independent Test**: (Prasyarat: minimal satu kategori sudah ada). Login sebagai admin, tulis artikel baru dengan field wajib terisi dan status "Publish", simpan, buka `/artikel`, verifikasi artikel muncul. Edit artikel tsb, verifikasi perubahan tampil di `/artikel/{slug}`.

**Acceptance Scenarios**:

1. **Given** admin berada di form tulis artikel, **When** admin mengisi field wajib (judul, ringkasan, isi, kategori) dan memilih status "Publish", **Then** artikel tersimpan dan langsung muncul di `/artikel` tanpa deploy ulang.
2. **Given** admin mengedit artikel yang sudah ada, **When** admin mengubah judul/isi/kategori dan menyimpan, **Then** halaman publik (`/artikel`, `/artikel/{slug}`) menampilkan versi terbaru pada request berikutnya.
3. **Given** admin mengisi judul tanpa mengisi slug, **When** admin menyimpan, **Then** sistem menghasilkan slug otomatis dari judul (dan admin tetap bisa meng-override slug secara manual).
4. **Given** admin mencoba menyimpan artikel dengan slug yang sudah dipakai artikel lain, **When** admin menyimpan, **Then** sistem menolak dengan pesan error jelas, bukan menyimpan duplikat.
5. **Given** admin mencoba menyimpan artikel tanpa mengisi field wajib, **When** admin menyimpan, **Then** sistem menolak dengan pesan validasi per field.

---

### User Story 3 - Admin mengatur draft/publish artikel (Priority: P1)

Admin menyimpan artikel sebagai draft (belum tampil ke publik) untuk dikerjakan bertahap, lalu mempublikasikannya saat siap — termasuk opsi menjadwalkan tanggal publish di masa depan.

**Why this priority**: Kebutuhan eksplisit tiket ("draft/publish") — tanpa ini, setiap artikel yang belum selesai ditulis akan langsung terlihat publik begitu disimpan, yang tidak wajar untuk alur kerja penulisan konten.

**Independent Test**: Buat artikel dengan status "Draft", verifikasi TIDAK muncul di `/artikel` maupun bisa diakses via `/artikel/{slug}`. Ubah status jadi "Publish", verifikasi langsung muncul. Buat artikel dengan tanggal publish di masa depan, verifikasi belum muncul sampai tanggal tsb tiba.

**Acceptance Scenarios**:

1. **Given** admin menyimpan artikel dengan status "Draft", **When** pengunjung membuka `/artikel` atau `/artikel/{slug-draft}`, **Then** artikel tsb TIDAK muncul di daftar dan `/artikel/{slug-draft}` mengembalikan 404.
2. **Given** admin mengubah status artikel dari "Draft" ke "Publish" dengan tanggal publish hari ini/masa lalu, **When** admin menyimpan, **Then** artikel langsung muncul di halaman publik.
3. **Given** admin mengatur artikel "Publish" dengan tanggal publish di masa depan, **When** pengunjung membuka `/artikel` sebelum tanggal tsb, **Then** artikel belum muncul; setelah tanggal tsb tiba, artikel muncul otomatis tanpa admin perlu melakukan apa pun lagi.

---

### User Story 4 - Admin memberi tag pada artikel (Priority: P2)

Admin menambahkan satu atau lebih tag bebas pada artikel (misal "panel-surya", "hemat-listrik") untuk membantu pengelompokan/pencarian konten, bisa memilih tag yang sudah ada atau membuat tag baru langsung saat menulis artikel.

**Why this priority**: Melengkapi kebutuhan tiket, tapi artikel tetap bisa ditulis & tampil dasar tanpa tag (US2/US3 sudah cukup untuk MVP).

**Independent Test**: Tulis artikel, tambahkan 2-3 tag (kombinasi tag baru dan yang sudah ada), simpan, buka `/artikel/{slug}`, verifikasi seluruh tag tampil. Edit artikel, hapus salah satu tag, verifikasi hilang dari tampilan.

**Acceptance Scenarios**:

1. **Given** admin menulis artikel dan mengetik nama tag baru, **When** admin menyimpan, **Then** tag baru tsb tercipta dan langsung tersedia sebagai pilihan untuk artikel lain.
2. **Given** admin memilih tag yang sudah ada dari artikel lain, **When** admin menyimpan, **Then** tag tsb terpasang ke artikel tanpa membuat duplikat tag baru.
3. **Given** artikel punya beberapa tag, **When** admin menghapus salah satu tag dari artikel (bukan menghapus tag itu sendiri secara global), **Then** tag tsb lepas dari artikel tapi tetap ada untuk dipakai artikel lain.

---

### User Story 5 - Admin mengatur featured image artikel (Priority: P2)

Admin mengupload satu gambar sampul (featured image) untuk artikel, ditampilkan di kartu artikel (`/artikel`) dan halaman detail.

**Why this priority**: Melengkapi presentasi visual blog, tapi artikel tetap bisa ditulis & tampil (teks) tanpa gambar (fallback placeholder, konsisten pola AMC-207).

**Independent Test**: Edit artikel, upload featured image, simpan, verifikasi gambar tampil di kartu `/artikel` dan halaman detail. Hapus artikel yang belum punya featured image, verifikasi halaman publik menampilkan placeholder wajar, bukan gambar rusak.

**Acceptance Scenarios**:

1. **Given** admin mengupload featured image untuk sebuah artikel, **When** admin menyimpan, **Then** gambar tsb tampil sebagai gambar sampul di kartu artikel dan halaman detail.
2. **Given** artikel belum punya featured image, **When** halaman publik menampilkannya, **Then** sistem menampilkan placeholder wajar (bukan gambar rusak/kosong-mengganggu) — konsisten dengan perilaku galeri produk di AMC-207.

### Edge Cases

- Apa yang terjadi jika admin mengupload featured image dengan format/ukuran tidak valid? Sistem MUST menolak dengan pesan error jelas, konsisten dengan validasi upload di modul lain.
- Apa yang terjadi jika seluruh artikel published dihapus (blog kosong)? Halaman `/artikel` MUST menampilkan empty state yang wajar (sudah ada dari 002), bukan error.
- Apa yang terjadi jika admin mencoba menghapus kategori artikel yang masih dipakai? Sistem MUST menolak dengan pesan jelas (lihat US1, Acceptance Scenario 3).
- Apa yang terjadi jika admin menghapus artikel yang sudah published? Artikel MUST hilang dari `/artikel` dan `/artikel/{slug-lama}` MUST mengembalikan 404.
- Bagaimana urutan tampil artikel di `/artikel` diatur? Tetap berdasarkan tanggal publish terbaru dulu (perilaku yang sudah ada dari 002) — tidak ada pengurutan manual tambahan di fitur ini.
- Apa yang terjadi jika file yang diupload valid sebagai gambar tapi gagal dikonversi ke WebP (mis. file korup)? Sistem MUST menolak upload dengan pesan error, bukan menyimpan file asli tanpa konversi (FR-021 tetap harus terpenuhi untuk file yang tersimpan).

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Admin panel MUST menyediakan halaman daftar kategori artikel (CRUD terpisah dari artikel) — tambah, edit, hapus.
- **FR-002**: Sistem MUST memvalidasi nama kategori artikel unik — submit dengan nama yang sudah dipakai kategori lain MUST ditolak dengan pesan error jelas.
- **FR-003**: Sistem MUST menolak penghapusan kategori artikel yang masih direferensikan satu atau lebih artikel, dengan pesan yang menjelaskan alasannya.
- **FR-004**: Admin MUST bisa menulis artikel baru dengan mengisi: judul, ringkasan, isi lengkap, kategori (dipilih dari daftar), featured image (opsional), tag (opsional), dan status draft/publish.
- **FR-005**: Sistem MUST menghasilkan slug otomatis dari judul artikel saat dibuat, dan admin MUST bisa meng-override slug secara manual.
- **FR-006**: Sistem MUST memvalidasi slug artikel unik antar artikel — submit dengan slug yang sudah dipakai MUST ditolak dengan pesan error jelas.
- **FR-007**: Admin MUST bisa mengedit seluruh field artikel yang sudah ada, dan perubahan MUST langsung tercermin di halaman publik tanpa deploy ulang.
- **FR-008**: Admin MUST bisa menghapus artikel, dengan konfirmasi terlebih dahulu sebelum penghapusan diproses.
- **FR-009**: Artikel berstatus draft MUST TIDAK tampil di halaman publik manapun (`/artikel` maupun akses langsung `/artikel/{slug}` MUST mengembalikan 404).
- **FR-010**: Artikel berstatus publish dengan tanggal publish di masa depan MUST TIDAK tampil di halaman publik sampai tanggal tsb tiba, tanpa perlu aksi admin tambahan saat tanggal tercapai.
- **FR-011**: Admin MUST bisa memberi satu atau lebih tag bebas ke artikel — mengetik tag baru MUST membuat tag tsb tersedia untuk artikel lain, memilih tag yang sudah ada MUST TIDAK membuat duplikat.
- **FR-012**: Melepas tag dari sebuah artikel MUST TIDAK menghapus tag tsb secara global (tag tetap tersedia untuk artikel lain).
- **FR-013**: Admin MUST bisa mengupload, mengganti, dan menghapus featured image satu artikel.
- **FR-014**: Sistem MUST memvalidasi tipe file featured image (format gambar umum) sebelum menyimpan; TIDAK ADA validasi dimensi/ukuran gambar (hanya rekomendasi, lihat FR-020).
- **FR-015**: Artikel tanpa featured image MUST menampilkan placeholder gambar yang wajar di halaman publik, bukan gambar rusak/kosong.
- **FR-016**: Sistem MUST memvalidasi field wajib (judul, ringkasan, isi, kategori) — submit tanpa field wajib MUST ditolak dengan pesan error per field.
- **FR-017**: CRUD Artikel dan CRUD Kategori Artikel MUST terbuka untuk semua role yang memiliki akses panel admin, konsisten dengan resource admin lain yang sudah ada.
- **FR-018**: Kategori artikel MUST berupa entity (`Article Category`) dan CRUD yang terpisah sepenuhnya dari Kategori Produk (AMC-207) — tidak berbagi tabel/daftar, supaya pilihan kategori pada satu modul tidak muncul di modul lain yang semantiknya berbeda.
- **FR-019**: Isi artikel (`content`) MUST ditulis lewat rich text editor (WYSIWYG) yang mendukung format dasar (bold/italic, heading, link, gambar inline) — bukan textarea teks polos — dan disimpan sebagai HTML.
- **FR-020**: Form upload featured image MUST menampilkan teks bantuan yang merekomendasikan dimensi gambar ideal (mis. 1200×630px), TANPA menolak/validasi file berdasarkan dimensi aktualnya.
- **FR-021**: Setiap gambar yang diupload lewat form artikel (featured image) MUST dikonversi otomatis ke format WebP (kompresi) saat disimpan, terlepas dari format asli yang diupload admin (JPG/PNG/dll) — pengunjung/kartu artikel MUST menampilkan versi WebP tsb.
- **FR-022**: Admin MUST bisa mengisi field "Redaksi" (nama penulis/tim penulis, teks bebas) saat menulis/mengedit artikel — field ini BUKAN relasi ke akun admin/User yang login (tidak ada konsep "author" terautentikasi di fitur ini), murni teks bebas yang ditampilkan sebagai byline di halaman publik.

### Key Entities

- **Article Category** (baru): nama (unik), urutan tampil. Dipakai untuk mengelompokkan artikel; satu kategori bisa dipakai banyak artikel, tidak bisa dihapus jika masih dipakai (FR-003).
- **Article**: entity yang sudah ada dari 002-theme-branding-system — kini berelasi ke `Article Category` (bukan string bebas), plus status draft/publish, tag (many-to-many), featured image, dan field teks bebas `redaksi` (byline, FR-022).
- **Tag**: label bebas yang bisa dipasang ke banyak artikel (many-to-many) — satu tag bisa dipakai banyak artikel, satu artikel bisa punya banyak tag.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin dapat menulis artikel baru (lengkap dengan kategori) dan melihatnya tampil di halaman publik dalam satu kali proses simpan (status publish, tanggal hari ini), tanpa bantuan developer atau deploy ulang.
- **SC-002**: Admin dapat menyelesaikan perubahan data satu artikel (edit field + simpan) dalam waktu kurang dari 2 menit.
- **SC-003**: 100% artikel berstatus draft atau terjadwal masa depan TIDAK PERNAH terlihat pengunjung sebelum waktunya.
- **SC-004**: 100% percobaan submit dengan data tidak valid (slug/nama kategori duplikat, field wajib kosong, file bukan gambar, hapus kategori yang masih dipakai) ditolak dengan pesan error yang jelas.
- **SC-005**: Setelah artikel dihapus, 0% halaman publik yang masih menampilkannya, dan URL detail lamanya mengembalikan 404.
- **SC-006**: 100% featured image yang tersimpan berformat WebP, terlepas dari format file asli yang diupload admin.

## Clarifications

### Session 2026-09-05

- Q: Apakah kategori artikel entity terpisah dari Kategori Produk (AMC-207) atau memakai taxonomy yang sama? → A: Entity `Article Category` terpisah sepenuhnya (FR-018) — tidak berbagi tabel dengan Kategori Produk.
- Q: Apakah isi artikel ditulis lewat rich text editor atau tetap teks polos? → A: Rich text editor (WYSIWYG), disimpan sebagai HTML (FR-019) — menggantikan textarea/`explode("\n")` yang ada sekarang.
- Tambahan dari user (di luar 2 pertanyaan di atas): form upload featured image MUST menampilkan rekomendasi dimensi ideal tanpa memvalidasi dimensi aktual (FR-020), dan setiap gambar yang diupload MUST dikonversi otomatis ke WebP saat disimpan (FR-021, SC-006).

### Session 2026-09-05 (lanjutan, sebelum `/speckit-plan` ulang)

- Tambahan dari user: artikel TIDAK memakai konsep "author" (relasi ke akun admin/User) — cukup field teks bebas "Redaksi" (FR-022) untuk mencantumkan nama penulis/tim sebagai byline.

## Assumptions

- Fitur ini memperluas model `Article` yang sudah ada dari 002-theme-branding-system, bukan membuat entity terpisah untuk data artikel itu sendiri.
- `ArticleSeeder` tetap dipertahankan untuk kebutuhan demo/dev (konsisten pola AMC-207) — CRUD admin jadi sumber data utama pasca go-live.
- Halaman publik (`/artikel`, `/artikel/{slug}`) yang sudah dibangun di 002-theme-branding-system tetap berfungsi dengan struktur data baru (kategori relasi, status draft/publish, tag, featured image) — bukan breaking change bagi pengunjung.
- Urutan tampil artikel di `/artikel` tetap berdasarkan tanggal publish (bukan field `order` manual seperti Produk) — konsisten perilaku yang sudah ada.
- Tidak ada validasi kontras/SEO khusus untuk konten artikel di v1 — di luar scope fitur ini.
- Dimensi gambar yang direkomendasikan (FR-020) memakai rasio umum untuk kartu artikel/OG image (mis. 1200×630px) — nilai pasti ditentukan saat implementasi, bukan bagian keputusan bisnis.
- Konversi WebP (FR-021) berlaku untuk featured image artikel di fitur ini; menerapkannya ke upload gambar modul lain (Produk, Brand Settings, dst.) di luar scope — bisa jadi peningkatan terpisah nanti kalau diinginkan konsisten di seluruh aplikasi.
- Field "Redaksi" (FR-022) opsional (boleh dikosongkan) kecuali diminta lain — tidak ada validasi format khusus, murni teks bebas pendek (mis. "Tim Redaksi SUOER" atau nama individu).
