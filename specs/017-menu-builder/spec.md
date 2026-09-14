# Feature Specification: Menu Builder

**Feature Branch**: `017-menu-builder`

**Created**: 2026-09-13

**Status**: Draft

**Input**: User description: "Menu Builder — navigasi navbar/footer dinamis, multi-lokasi (AMC-215). Admin dapat mengelola menu untuk navbar dan footer secara dinamis dari admin panel (Filament), termasuk menambah/mengubah/menghapus item menu, menyusun urutan (drag & drop), menentukan link (internal ke halaman/custom page/URL eksternal), dan menentukan lokasi tampil (navbar utama, footer, atau lokasi lain yang bisa dikonfigurasi). Konten Web Solarpanel Kit ini dipakai lintas klien, jadi menu builder harus fleksibel untuk berbagai struktur navigasi tanpa perlu ubah kode."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Kelola item menu navbar (Priority: P1)

Seorang admin/editor masuk ke admin panel dan ingin mengatur item apa saja yang tampil di navbar utama situs, tanpa perlu meminta developer mengubah kode. Admin menambahkan item menu baru (misalnya "Layanan"), memilih tujuan link, memberi label, dan menyimpannya sehingga langsung muncul di navbar frontend.

**Why this priority**: Ini kebutuhan inti dari fitur — tanpa kemampuan mengelola navbar secara dinamis, tujuan utama Menu Builder (fleksibilitas struktur navigasi lintas klien) tidak tercapai. Ini juga bagian navigasi yang paling sering dilihat pengunjung.

**Independent Test**: Admin dapat login ke panel, membuka Menu Builder, menambah satu item menu baru untuk lokasi navbar, dan memverifikasi item tersebut tampil di navbar halaman publik setelah disimpan — tanpa deploy kode.

**Acceptance Scenarios**:

1. **Given** admin berada di halaman Menu Builder, **When** admin menambah item menu baru dengan label dan tujuan link, lalu menyimpannya untuk lokasi "Navbar Utama", **Then** item tersebut tampil pada navbar di semua halaman publik sesuai urutan yang ditentukan.
2. **Given** item menu navbar sudah ada, **When** admin mengubah label atau tujuan link item tersebut, **Then** navbar publik menampilkan perubahan tersebut tanpa perlu tindakan tambahan.
3. **Given** item menu navbar sudah ada, **When** admin menghapus item tersebut, **Then** item tidak lagi tampil di navbar publik.

---

### User Story 2 - Susun urutan menu dengan drag & drop (Priority: P1)

Admin ingin mengubah urutan tampil item-item menu (misalnya memindahkan "Blog" ke posisi setelah "Layanan") dengan cara menyeret (drag & drop) item pada daftar di admin panel, tanpa harus mengedit angka urutan satu per satu.

**Why this priority**: Urutan tampil menu memengaruhi prioritas informasi yang dilihat pengunjung; kemampuan menyusun ulang dengan mudah adalah bagian tak terpisahkan dari "menu builder" yang disebutkan eksplisit dalam kebutuhan, dan tanpanya pengelolaan urutan menjadi tidak praktis untuk operator non-teknis.

**Independent Test**: Dengan minimal tiga item menu pada satu lokasi, admin menyeret salah satu item ke posisi baru dan menyimpan; urutan pada frontend mengikuti urutan baru tersebut.

**Acceptance Scenarios**:

1. **Given** ada tiga atau lebih item menu pada lokasi yang sama, **When** admin menyeret salah satu item ke posisi berbeda dalam daftar, **Then** urutan tampil pada admin panel dan pada frontend memperbarui sesuai posisi baru.
2. **Given** urutan menu telah diubah, **When** halaman publik dimuat ulang, **Then** item menu tampil sesuai urutan terbaru yang tersimpan.

---

### User Story 3 - Menentukan lokasi & tujuan tautan menu (Priority: P2)

Admin ingin menentukan setiap item menu akan tampil di lokasi mana (navbar utama, footer, atau lokasi lain yang tersedia) dan ke mana item tersebut mengarah — ke halaman internal yang sudah ada (mis. halaman Layanan, Portfolio, Blog, Custom Page), ke URL eksternal, atau tanpa tautan (sekadar label pemisah/grup).

**Why this priority**: Fleksibilitas lintas klien menuntut kemampuan menautkan ke berbagai jenis tujuan dan menampilkan menu yang sama atau berbeda di beberapa lokasi situs. Ini melengkapi P1 dengan cakupan penuh terhadap jenis tautan yang mungkin dibutuhkan klien berbeda-beda.

**Independent Test**: Admin membuat satu item menu dengan tujuan URL eksternal dan satu item lain dengan tujuan halaman internal, menetapkan masing-masing ke lokasi footer, dan memverifikasi kedua tautan berfungsi benar dari halaman publik.

**Acceptance Scenarios**:

1. **Given** admin membuat item menu baru, **When** admin memilih tujuan "halaman internal" dan menentukan halaman spesifik, **Then** item menu mengarah ke URL halaman tersebut secara otomatis dan tetap benar apabila slug halaman berubah.
2. **Given** admin membuat item menu baru, **When** admin memilih tujuan "URL eksternal" dan mengisi alamat lengkap, **Then** item menu mengarah ke URL tersebut, dibuka sesuai pengaturan admin (tab sama/baru).
3. **Given** admin memiliki lebih dari satu lokasi menu (navbar dan footer), **When** admin menetapkan item menu ke lokasi footer saja, **Then** item tersebut hanya tampil di footer dan tidak tampil di navbar, begitu pula sebaliknya.

---

### Edge Cases

- Apa yang terjadi jika item menu internal menunjuk ke halaman/konten yang kemudian dihapus atau dinonaktifkan? Sistem harus tetap menampilkan menu tanpa error di frontend (mis. fallback ke label tanpa tautan aktif, atau menandai perlu perhatian admin di panel).
- Bagaimana jika suatu lokasi menu (mis. navbar utama) belum memiliki item sama sekali? Frontend harus tetap tampil wajar (navbar/footer kosong pada bagian menu, tanpa merusak tata letak).
- Bagaimana jika admin membuat menu bertingkat (item dengan sub-item/dropdown)? Struktur harus mendukung minimal satu tingkat kedalaman sub-menu.
- Bagaimana jika dua admin mengedit Menu Builder secara bersamaan? Perubahan tersimpan terakhir yang berlaku (last write wins), tanpa mekanisme locking khusus.
- Apa yang terjadi bila item menu dinonaktifkan (bukan dihapus)? Item tidak tampil di frontend tetapi konfigurasinya tetap tersimpan untuk diaktifkan kembali nanti.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Sistem MUST menyediakan antarmuka di admin panel bagi admin/editor untuk membuat, mengubah, menghapus, dan menonaktifkan item menu.
- **FR-002**: Sistem MUST mendukung lebih dari satu lokasi tampil menu (minimal "Navbar Utama" dan "Footer"), dan setiap item menu MUST ditetapkan ke satu atau lebih lokasi tersebut.
- **FR-003**: Sistem MUST memungkinkan admin menambah lokasi menu baru tanpa perubahan kode, agar struktur navigasi tetap fleksibel untuk kebutuhan klien yang berbeda-beda.
- **FR-004**: Sistem MUST memungkinkan admin mengatur urutan tampil item menu dalam satu lokasi melalui aksi seret (drag & drop), dan urutan tersebut MUST tersimpan dan diterapkan konsisten di frontend.
- **FR-005**: Sistem MUST mendukung minimal satu tingkat sub-menu (item induk dengan daftar item anak/dropdown).
- **FR-006**: Setiap item menu MUST memiliki label tampil yang dapat diedit bebas oleh admin.
- **FR-007**: Sistem MUST mendukung penentuan tujuan tautan item menu dalam bentuk: (a) tautan ke halaman/konten internal yang tersedia di situs (misalnya Custom Page, atau halaman modul lain yang sudah publik), (b) URL eksternal bebas, atau (c) tanpa tautan (label saja, mis. untuk header grup/dropdown).
- **FR-008**: Ketika item menu menautkan ke halaman internal, sistem MUST menghasilkan URL secara otomatis mengikuti slug/rute konten tersebut saat ini, sehingga tautan tetap valid apabila slug berubah.
- **FR-009**: Sistem MUST memungkinkan admin menonaktifkan (menyembunyikan) item menu tanpa menghapus datanya, dan item nonaktif MUST tidak tampil di frontend.
- **FR-010**: Sistem MUST menampilkan item menu pada frontend sesuai lokasi dan urutan yang dikonfigurasi di admin panel, dan perubahan konfigurasi MUST langsung terlihat di frontend tanpa deployment kode.
- **FR-011**: Sistem MUST menangani secara aman kondisi ketika item menu internal menunjuk ke konten yang sudah dihapus/dinonaktifkan, tanpa menyebabkan error pada halaman publik.
- **FR-012**: Sistem MUST mendukung opsi membuka tautan eksternal pada tab baru, dapat dikonfigurasi per item menu.
- **FR-013**: Konfigurasi Menu Builder (item, urutan, lokasi) MUST spesifik per instalasi/klien, konsisten dengan pola white-labeling yang sudah ada pada kit ini.

### Key Entities *(include if feature involves data)*

- **Menu Location**: Mewakili tempat menu ditampilkan di situs (mis. Navbar Utama, Footer). Memiliki nama/identitas unik dan dapat ditambah oleh admin tanpa ubah kode.
- **Menu Item**: Mewakili satu entri navigasi. Atribut kunci: label, tujuan tautan (internal/eksternal/tanpa tautan), referensi konten internal (jika berlaku), URL eksternal (jika berlaku), lokasi menu yang ditempati, urutan tampil, status aktif/nonaktif, opsi buka tab baru, dan relasi ke item induk (untuk mendukung sub-menu).

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin dapat menambahkan item menu baru dan melihatnya tampil di frontend dalam waktu kurang dari 2 menit, tanpa bantuan developer.
- **SC-002**: Admin dapat mengubah urutan minimal 5 item menu dalam satu lokasi menggunakan drag & drop dalam waktu kurang dari 1 menit, dan urutan tersebut akurat 100% saat diverifikasi di frontend.
- **SC-003**: 100% perubahan pada Menu Builder (tambah/ubah/hapus/urutan/nonaktifkan) tercermin di halaman publik tanpa perlu deployment kode atau intervensi teknis.
- **SC-004**: Struktur navigasi (navbar & footer) untuk klien baru dapat disesuaikan sepenuhnya melalui admin panel, tanpa satu pun perubahan kode pada template/tema.
- **SC-005**: Ketika sebuah item menu menunjuk ke konten yang telah dihapus, halaman publik tetap dapat dimuat tanpa error (uji fungsional, bukan sekadar tidak crash di 100% kasus).

## Assumptions

- Klien akhir (pengunjung situs publik) bukan pengguna Menu Builder; hanya admin/editor melalui admin panel yang mengelola konfigurasi menu.
- Lokasi menu default yang disediakan sejak awal adalah "Navbar Utama" dan "Footer", mengikuti struktur navigasi yang sudah ada di frontend saat ini (lihat komponen header dan footer layout).
- "Halaman/konten internal" yang dapat dijadikan tujuan tautan mencakup Custom Page dan halaman-halaman modul publik yang sudah ada di kit ini (mis. Layanan, Portfolio, Blog, Karir, Kontak) sesuai modul yang aktif untuk klien tersebut.
- Tidak ada kebutuhan pembatasan hak akses granular baru untuk Menu Builder di luar peran admin/editor yang sudah ada pada sistem otorisasi panel saat ini.
- Riwayat versi/audit trail perubahan menu memanfaatkan mekanisme activity log yang sudah tersedia di kit ini, tanpa kebutuhan sistem versioning terpisah.
- Tidak ada batas maksimum jumlah item menu atau kedalaman sub-menu di luar satu tingkat dropdown yang disebutkan secara eksplisit; batas praktis mengikuti kewajaran desain navigasi web.
