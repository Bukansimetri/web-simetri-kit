# Feature Specification: Produk CRUD Admin

**Feature Branch**: `003-produk-crud-admin`

**Created**: 2026-09-03

**Status**: Draft

**Input**: User description: "AMC-207: Modul Services/Produk — CRUD Admin. Model `Product`, migration, dan seeder sudah ada dari fitur Theme & Branding System, dipakai untuk render halaman publik /produk (list) dan /produk/{slug} (detail) dengan data seed statis. Tujuan: menambahkan CRUD admin (Filament Resource) supaya admin/operator instalasi klien bisa menambah, mengedit, menghapus, dan mengatur urutan tampil produk mereka sendiri lewat panel admin — tanpa developer perlu mengubah seeder atau kode. Field: nama, slug, kategori, deskripsi singkat, deskripsi lengkap, harga, harga coret, gambar produk, spesifikasi teknis (list key-value dinamis), fitur unggulan (list icon+judul+deskripsi dinamis), urutan tampil." Diperluas 2026-09-03 lewat klarifikasi: kategori jadi taxonomy CRUD terpisah (bukan teks bebas), gambar produk jadi galeri multi-gambar (bukan satu gambar) — lihat Clarifications.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Admin mengelola kategori produk (Priority: P1)

Admin membuka menu Kategori Produk di panel admin, membuat kategori baru (misal "Residensial", "Komersial & Industri", "Pompa Air"), mengedit nama kategori yang sudah ada, mengatur urutannya, atau menghapus kategori yang tidak dipakai — tanpa developer perlu mengubah kode.

**Why this priority**: Kategori adalah data prasyarat untuk User Story 2 (membuat produk perlu memilih kategori dari daftar yang sudah ada) — tanpa ini, admin terjebak ke daftar kategori hardcode yang tidak bisa diubah, melanggar Principle I (Multi-Client Reusability).

**Independent Test**: Login sebagai admin, buka menu Kategori Produk, buat kategori baru, verifikasi muncul di daftar. Edit nama kategori, verifikasi berubah. Coba hapus kategori yang masih dipakai produk, verifikasi ditolak dengan pesan jelas.

**Acceptance Scenarios**:

1. **Given** admin berada di form tambah kategori, **When** admin mengisi nama dan menyimpan, **Then** kategori tersimpan dan langsung tersedia sebagai pilihan saat membuat/mengedit produk.
2. **Given** admin mencoba menyimpan kategori dengan nama yang sudah dipakai kategori lain, **When** admin menyimpan, **Then** sistem menolak dengan pesan error jelas, bukan menyimpan duplikat.
3. **Given** sebuah kategori masih dipakai oleh satu atau lebih produk, **When** admin mencoba menghapus kategori tsb, **Then** sistem menolak penghapusan dan menjelaskan bahwa kategori masih dipakai (bukan menghapus paksa dan meninggalkan produk tanpa kategori).
4. **Given** admin mengubah urutan tampil kategori, **When** admin menyimpan, **Then** urutan tab filter kategori di halaman publik `/produk` mengikuti urutan baru.

---

### User Story 2 - Admin menambah & mengedit produk (Priority: P1) 🎯 MVP

Admin membuat produk baru (atau mengedit produk yang sudah ada) dengan mengisi nama, memilih kategori dari daftar yang sudah dibuat (User Story 1), harga, deskripsi — tanpa perlu meminta developer mengubah kode atau seeder. Setelah disimpan, produk langsung tampil di halaman publik `/produk` dan `/produk/{slug}`.

**Why this priority**: Ini nilai bisnis inti tiket — tanpa kemampuan tambah/edit dasar, katalog produk tetap terkunci ke data seed dan situs tidak bisa dipakai sungguhan oleh klien manapun.

**Independent Test**: (Prasyarat: minimal satu kategori sudah ada, dari seed default atau US1). Login sebagai admin, buka menu Produk, buat produk baru dengan field wajib terisi, simpan, buka `/produk` di tab baru, verifikasi produk baru muncul. Edit nama produk tsb, simpan, reload `/produk/{slug}`, verifikasi perubahan tampil.

**Acceptance Scenarios**:

1. **Given** admin berada di form tambah produk, **When** admin mengisi semua field wajib (termasuk memilih kategori dari daftar) dan menyimpan, **Then** produk tersimpan dan langsung muncul di `/produk` tanpa perlu deploy ulang.
2. **Given** admin mengedit produk yang sudah ada, **When** admin mengubah nama/harga/deskripsi/kategori dan menyimpan, **Then** halaman publik (`/produk`, `/produk/{slug}`, section "Produk Kami" Home) menampilkan versi terbaru pada request berikutnya.
3. **Given** admin mengisi nama produk tanpa mengisi slug, **When** admin menyimpan, **Then** sistem menghasilkan slug otomatis dari nama (dan admin tetap bisa meng-override slug secara manual).
4. **Given** admin mencoba menyimpan produk dengan slug yang sudah dipakai produk lain, **When** admin menyimpan, **Then** sistem menolak dengan pesan error yang jelas, bukan menyimpan duplikat.
5. **Given** admin mencoba menyimpan produk tanpa mengisi field wajib (nama/kategori/harga), **When** admin menyimpan, **Then** sistem menolak dengan pesan validasi per field.

---

### User Story 3 - Admin mengelola galeri gambar produk (Priority: P2)

Admin mengupload beberapa gambar untuk satu produk (bukan cuma satu), mengatur urutan tampilnya, dan menghapus gambar yang tidak diperlukan. Gambar pertama dalam urutan dipakai sebagai gambar sampul (cover) di kartu produk & daftar.

**Why this priority**: Melengkapi presentasi produk (showcase multi-sudut/varian), tapi produk tetap bisa dibuat & tampil dasar tanpa galeri lengkap (US2 sudah cukup untuk MVP).

**Independent Test**: Buka form edit produk, upload 3 gambar, ubah urutan salah satu gambar ke posisi pertama, simpan, buka `/produk/{slug}`, verifikasi gambar yang dipindah ke posisi pertama tampil sebagai gambar utama, dan seluruh galeri tampil sesuai urutan. Hapus satu gambar, verifikasi hilang dari galeri publik.

**Acceptance Scenarios**:

1. **Given** admin berada di form produk, **When** admin mengupload beberapa gambar sekaligus, **Then** seluruh gambar tersimpan dan tampil sebagai galeri di halaman detail produk.
2. **Given** produk sudah punya beberapa gambar, **When** admin mengubah urutan gambar (mis. menaruh gambar lain di posisi pertama), **Then** gambar di posisi pertama menjadi gambar sampul (cover) di kartu produk & daftar `/produk`.
3. **Given** admin menghapus salah satu gambar dari galeri, **When** admin menyimpan, **Then** gambar tsb tidak lagi tampil di manapun (galeri detail maupun kartu).
4. **Given** produk belum punya gambar sama sekali, **When** halaman publik menampilkan produk tsb, **Then** sistem menampilkan placeholder gambar yang wajar (bukan gambar rusak/kosong-mengganggu).

---

### User Story 4 - Admin mengelola spesifikasi teknis & fitur unggulan (Priority: P2)

Admin menambahkan daftar spesifikasi teknis (misal "Daya Maksimum: 550W") dan daftar fitur unggulan (icon + judul + deskripsi) untuk sebuah produk, dengan jumlah baris yang fleksibel (bisa tambah/hapus baris) — bukan field tetap yang terbatas.

**Why this priority**: Melengkapi kelengkapan halaman detail produk (tabel spesifikasi & kartu fitur), tapi tidak sekritis US1/US2 — produk tetap bisa tampil dasar tanpa ini.

**Independent Test**: Buka form edit produk, tambah 3 baris spesifikasi dan 2 baris fitur unggulan lewat UI berulang (repeater), simpan, buka `/produk/{slug}`, verifikasi seluruh baris tampil sesuai urutan yang diisi.

**Acceptance Scenarios**:

1. **Given** admin berada di form produk, **When** admin menambah beberapa baris spesifikasi (label + nilai), **Then** seluruh baris tersimpan dan tampil sebagai tabel spesifikasi di halaman detail produk.
2. **Given** admin berada di form produk, **When** admin menambah beberapa baris fitur unggulan (icon, judul, deskripsi) dan menghapus salah satu baris sebelum menyimpan, **Then** hanya baris yang tersisa yang tersimpan dan tampil.
3. **Given** produk belum punya baris spesifikasi/fitur sama sekali, **When** halaman detail produk dibuka, **Then** bagian tsb tidak tampil rusak/kosong-mengganggu (ditangani dengan wajar, bukan error).

---

### User Story 5 - Admin mengatur urutan tampil & menghapus produk (Priority: P3)

Admin mengubah urutan produk yang tampil di daftar `/produk` dan section "Produk Kami" di Home, serta menghapus produk yang sudah tidak dijual.

**Why this priority**: Nilai tambah untuk kontrol presentasi, tapi tidak memblokir penggunaan dasar.

**Independent Test**: Buka daftar produk di admin, ubah urutan dua produk, simpan, buka `/produk`, verifikasi urutan tampil berubah sesuai. Hapus satu produk, verifikasi produk tsb hilang dari halaman publik dan `/produk/{slug}` lamanya mengembalikan 404.

**Acceptance Scenarios**:

1. **Given** admin mengubah urutan tampil produk, **When** admin menyimpan, **Then** urutan produk di `/produk` dan section "Produk Kami" Home mengikuti urutan baru.
2. **Given** admin menghapus sebuah produk, **When** admin mengonfirmasi penghapusan, **Then** produk tsb tidak lagi muncul di halaman publik manapun, dan mengakses slug lamanya mengembalikan halaman 404.
3. **Given** admin mencoba menghapus produk, **When** admin belum mengonfirmasi, **Then** sistem meminta konfirmasi terlebih dahulu (mencegah penghapusan tidak sengaja).

### Edge Cases

- Apa yang terjadi jika admin mengupload file gambar dengan format/ukuran tidak valid? Sistem MUST menolak dengan pesan error jelas, konsisten dengan validasi upload logo/favicon/OG image di Theme Settings.
- Apa yang terjadi jika seluruh produk dihapus (katalog kosong)? Halaman `/produk` dan section "Produk Kami" Home MUST menampilkan empty state yang wajar (bukan error), konsisten dengan perilaku yang sudah ada.
- Apa yang terjadi jika admin mencoba menghapus kategori yang masih dipakai produk? Sistem MUST menolak penghapusan dengan pesan jelas (lihat US1, Acceptance Scenario 3) — bukan menghapus paksa dan meninggalkan produk tanpa kategori.
- Apa yang terjadi jika admin mengubah kategori sebuah produk sehingga tidak ada produk lain di kategori yang sama? Halaman detail produk tsb MUST tetap tampil normal, hanya bagian "Produk Terkait" kosong/disembunyikan.
- Bagaimana jika dua admin mengedit produk yang sama bersamaan? Di luar scope v1 (last-write-wins, tanpa locking) — dicatat sebagai batasan, bukan requirement.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Admin panel MUST menyediakan halaman daftar produk yang menampilkan seluruh produk, dengan kemampuan cari/filter dasar.
- **FR-002**: Admin panel MUST menyediakan halaman daftar kategori produk (CRUD terpisah dari produk) — tambah, edit, hapus, atur urutan.
- **FR-003**: Sistem MUST memvalidasi nama kategori unik — submit dengan nama yang sudah dipakai kategori lain MUST ditolak dengan pesan error jelas.
- **FR-004**: Sistem MUST menolak penghapusan kategori yang masih direferensikan oleh satu atau lebih produk, dengan pesan yang menjelaskan alasannya.
- **FR-005**: Admin MUST bisa membuat produk baru dengan mengisi: nama, kategori (dipilih dari daftar kategori yang ada), deskripsi singkat, deskripsi lengkap, harga, harga coret (opsional), galeri gambar, spesifikasi teknis, fitur unggulan, dan urutan tampil.
- **FR-006**: Sistem MUST menghasilkan slug otomatis dari nama produk saat dibuat, dan admin MUST bisa meng-override slug secara manual.
- **FR-007**: Sistem MUST memvalidasi slug produk unik antar produk — submit dengan slug yang sudah dipakai MUST ditolak dengan pesan error jelas, bukan tersimpan sebagai duplikat.
- **FR-008**: Admin MUST bisa mengedit seluruh field produk yang sudah ada, dan perubahan MUST langsung tercermin di halaman publik tanpa perlu deploy ulang.
- **FR-009**: Admin MUST bisa menghapus produk, dengan konfirmasi terlebih dahulu sebelum penghapusan diproses.
- **FR-010**: Admin MUST bisa mengupload beberapa gambar per produk (galeri), mengatur urutan tampilnya, dan menghapus gambar individual.
- **FR-011**: Gambar pertama dalam urutan galeri MUST dipakai sebagai gambar sampul (cover) di kartu produk & daftar `/produk`.
- **FR-012**: Spesifikasi teknis dan fitur unggulan MUST bisa diisi sebagai daftar baris dinamis (tambah/hapus baris sesuai kebutuhan), bukan jumlah field yang tetap/terbatas.
- **FR-013**: Admin MUST bisa mengatur urutan tampil produk, dan urutan tsb MUST konsisten dipakai di seluruh halaman publik yang menampilkan daftar produk.
- **FR-014**: Sistem MUST memvalidasi field wajib (nama, kategori, harga) — submit tanpa field wajib MUST ditolak dengan pesan error per field, bukan tersimpan sebagian.
- **FR-015**: Sistem MUST memvalidasi tipe file setiap gambar produk (format gambar umum, ukuran maksimum wajar) sebelum menyimpan.
- **FR-016**: Produk yang dihapus MUST tidak lagi muncul di halaman publik manapun, dan mengakses slug produk yang sudah dihapus MUST mengembalikan halaman 404.
- **FR-017**: CRUD Produk dan CRUD Kategori MUST terbuka untuk semua role yang memiliki akses ke panel admin (tidak dibatasi role tertentu), konsisten dengan resource admin lain yang sudah ada.
- **FR-018**: Produk tanpa gambar sama sekali MUST menampilkan gambar placeholder yang wajar di halaman publik, bukan gambar rusak/kosong.

### Key Entities

- **Category**: Taxonomy baru — nama (unik), urutan tampil. Dipakai sebagai filter kategori di `/produk` dan pengelompokan "Produk Terkait" di halaman detail. Satu kategori bisa dipakai banyak produk; produk tidak bisa dihapus kategorinya jika masih ada produk yang memakainya (FR-004).
- **Product**: Entity yang sudah ada dari fitur Theme & Branding System — kini berelasi ke `Category` (bukan string bebas), plus galeri gambar (lihat entity Product Image), spesifikasi teknis (list key-value), fitur unggulan (list icon+judul+deskripsi), urutan tampil.
- **Product Image**: Entity baru — satu produk punya banyak gambar, masing-masing punya urutan tampil. Gambar dengan urutan pertama otomatis jadi cover/sampul (FR-011) — tidak ada flag "cover" terpisah.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin dapat menambahkan produk baru (lengkap dengan kategori & minimal satu gambar) dan melihatnya tampil di halaman publik dalam satu kali proses simpan, tanpa bantuan developer atau deploy ulang.
- **SC-002**: Admin dapat menyelesaikan perubahan data satu produk (edit field + simpan) dalam waktu kurang dari 2 menit.
- **SC-003**: 100% percobaan submit dengan data tidak valid (slug/nama kategori duplikat, field wajib kosong, file bukan gambar, hapus kategori yang masih dipakai) ditolak dengan pesan error yang jelas — tidak ada data rusak/parsial yang tersimpan.
- **SC-004**: Setelah produk dihapus, 0% halaman publik yang masih menampilkan produk tsb, dan URL detail lamanya mengembalikan 404.
- **SC-005**: Admin dapat mengelola galeri gambar (upload, urutkan, hapus) sebuah produk dalam satu sesi edit tanpa reload halaman berulang kali.

## Clarifications

### Session 2026-09-03

- Q: Apakah CRUD Produk/Kategori dibatasi ke role tertentu atau terbuka untuk semua role dengan akses panel admin? → A: Terbuka untuk semua role dengan akses panel admin (FR-017), konsisten dengan resource lain yang sudah ada.
- Q: Apakah gambar produk satu saja (sesuai skema lama) atau galeri multi-gambar? → A: Galeri multi-gambar per produk (FR-010, FR-011; User Story 3 baru) — gambar pertama dalam urutan jadi cover otomatis.
- Q: Apakah kategori produk teks bebas, dropdown kurasi, atau entity taxonomy terpisah dengan CRUD sendiri? → A: Entity taxonomy terpisah dengan CRUD sendiri (FR-002–FR-004; User Story 1 baru) — produk berelasi ke Category, bukan string bebas.

## Assumptions

- Kategori bersifat flat (tidak berjenjang/nested) — tidak ada sub-kategori di v1.
- Cover/gambar sampul produk otomatis mengikuti gambar pertama dalam urutan galeri — tidak ada flag "jadikan sampul" terpisah, cukup atur urutan.
- `ProductSeeder` (dan seeder kategori baru yang menyertainya) tetap dipertahankan untuk kebutuhan demo/dev (sesuai constitution "Deployment & Client Setup Standards": demo content harus seedable, tidak wajib dijalankan di produksi) — CRUD admin menjadi sumber data utama pasca go-live.
- Halaman publik (`/produk`, `/produk/{slug}`, Home) yang sudah dibangun di 002-theme-branding-system MUST tetap berfungsi dengan struktur data baru (kategori sebagai relasi, gambar sebagai galeri) — bukan breaking change bagi pengunjung situs.
- Tidak ada validasi khusus jumlah minimum/maksimum gambar per produk selain "boleh kosong → tampil placeholder" (edge case) dan "boleh banyak" — tidak ada batas atas eksplisit di v1.
