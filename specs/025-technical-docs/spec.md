# Feature Specification: Technical Documentation

**Feature Branch**: `025-technical-docs`

**Created**: 2026-09-24

**Status**: Draft

**Input**: User description: "Dokumentasi teknis (AMC-233): dokumentasi arsitektur project web-solarpanel-kit (Laravel + Filament company profile starter kit) dan panduan cara menambah section/tema baru. Target pembaca: developer yang akan mengerjakan/meng-clone kit ini untuk klien baru."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Developer baru memahami arsitektur project (Priority: P1) 🎯 MVP

Developer yang baru bergabung, atau yang baru meng-clone kit ini untuk klien baru, membaca dokumen arsitektur dan dalam satu kali baca memahami gambaran besar project: lapisan-lapisannya (halaman publik, panel admin, pengaturan situs, modul konten), di mana tiap jenis kode berada, bagaimana data mengalir dari panel admin ke halaman publik, dan konvensi yang wajib diikuti. Setelah itu developer bisa menemukan file yang relevan untuk sebuah perubahan tanpa harus bertanya ke developer lain.

**Why this priority**: Tanpa peta arsitektur, setiap developer baru harus menelusuri kode sendiri atau bergantung pada developer lama. Ini fondasi yang juga dibutuhkan panduan section/tema (US2, US3).

**Independent Test**: Beri dokumen arsitektur ke developer yang belum pernah melihat project, lalu minta ia menunjukkan lokasi kode untuk 5 skenario perubahan umum (misal: mengubah tampilan kartu produk, menambah field di pengaturan situs, menambah menu di panel admin, mengubah route halaman publik, mengubah teks email notifikasi kontak) hanya dengan bantuan dokumen.

**Acceptance Scenarios**:

1. **Given** developer belum pernah melihat project, **When** ia membaca dokumen arsitektur, **Then** ia bisa menjelaskan lapisan utama project dan hubungan antar lapisan.
2. **Given** developer perlu mengubah sebuah modul konten (misal Testimonials), **When** ia memakai dokumen arsitektur, **Then** ia bisa menemukan semua bagian yang terlibat (data, panel admin, tampilan publik, pengujian) untuk modul tersebut.
3. **Given** developer membaca bagian konvensi, **When** ia menulis kode baru, **Then** ia tahu aturan yang wajib diikuti (prinsip constitution, konvensi penamaan, kewajiban test, format kode) beserta tautan ke sumber aslinya.

---

### User Story 2 - Developer menambah section baru di halaman publik (Priority: P2)

Developer mendapat permintaan klien untuk menambah section baru (misal section "Sertifikasi" di Home) atau varian baru dari section yang sudah ada. Developer mengikuti panduan langkah demi langkah dan berhasil menambahkan section tersebut: membuat komponen tampilan, memakai token tema (warna, font) alih-alih nilai tetap, menghubungkannya ke sumber data (pengaturan situs atau modul konten), memasangnya di halaman, dan menulis test-nya.

**Why this priority**: Menambah atau memodifikasi section adalah pekerjaan kustomisasi paling sering per klien, dan constitution mewajibkan fleksibilitas visual disalurkan lewat section bernama, bukan page builder.

**Independent Test**: Developer yang belum pernah menambah section di project ini mengikuti panduan dan menambahkan satu section contoh di lingkungan lokal. Section tampil di halaman, ikut berubah warna/font saat pengaturan tema diubah di panel admin, dan test-nya lulus, tanpa bantuan developer lain.

**Acceptance Scenarios**:

1. **Given** developer mengikuti panduan menambah section, **When** ia menyelesaikan semua langkah, **Then** section baru tampil di halaman publik yang dituju.
2. **Given** section baru sudah dipasang, **When** admin mengubah warna primer atau font di pengaturan Tampilan, **Then** section baru ikut berubah tanpa perubahan kode.
3. **Given** section baru butuh konten yang bisa diubah admin, **When** developer mengikuti panduan, **Then** ia tahu kapan memakai pengaturan situs dan kapan membuat modul konten baru, dan langkah untuk masing-masing.
4. **Given** data untuk section kosong, **When** halaman dibuka, **Then** panduan sudah mengarahkan developer untuk menangani empty state (section disembunyikan atau tampil wajar, bukan error).

---

### User Story 3 - Developer menyesuaikan atau menambah tema untuk klien baru (Priority: P3)

Developer menyiapkan instalasi untuk klien baru yang identitas visualnya berbeda dari desain default. Developer memakai panduan tema untuk memahami apa yang cukup diubah admin lewat panel (logo, favicon, warna, font dari daftar kurasi), dan apa yang butuh perubahan kode (menambah font ke daftar kurasi, mengubah nilai default tema, menambah token tema baru seperti radius atau warna tambahan).

**Why this priority**: Kebanyakan kebutuhan tema klien sudah terpenuhi lewat panel admin, jadi panduan ini paling sering dibutuhkan untuk kasus di luar pengaturan standar. Tetap penting agar developer tidak menulis warna/font secara hardcode yang melanggar prinsip white-label.

**Independent Test**: Developer mengikuti panduan untuk (a) menambah satu font baru ke daftar pilihan admin, dan (b) menambah satu token tema baru. Keduanya bisa dipilih/diatur di panel admin dan tercermin di halaman publik.

**Acceptance Scenarios**:

1. **Given** klien butuh warna dan font sendiri, **When** developer membaca panduan tema, **Then** ia tahu hal ini cukup diatur admin lewat panel tanpa perubahan kode.
2. **Given** klien butuh font yang belum ada di daftar pilihan, **When** developer mengikuti panduan, **Then** font baru muncul di dropdown pengaturan Tampilan dan dipakai di halaman publik setelah dipilih.
3. **Given** klien butuh token tema baru (misal warna aksen tambahan), **When** developer mengikuti panduan, **Then** token baru bisa diatur admin, punya nilai default, dan dipakai section lewat token (bukan nilai tetap).

### Edge Cases

- Dokumen tidak sesuai lagi dengan kode karena project terus berkembang: dokumen MUST mencantumkan tanggal/versi terakhir diperbarui dan menunjuk ke file sumber (bukan menyalin isi kode panjang), agar mudah diverifikasi dan diperbarui.
- Developer mencari informasi yang sudah ada di dokumen lain (deployment, versioning klien, checklist GA4, go-live): dokumen MUST menautkan ke dokumen tersebut, bukan menduplikasi isinya.
- Developer ingin membuat varian section yang bisa dipilih admin (misal hero-v1/v2 dengan selector di panel): mekanisme pemilih varian belum tersedia (AMC-221 ditunda). Panduan MUST menyatakan batasan ini dengan jelas dan menjelaskan cara yang berlaku saat ini.
- Developer tergoda menambah fitur mirip page builder: dokumen MUST menegaskan bahwa ini di luar scope sesuai constitution.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Dokumentasi MUST menyediakan dokumen arsitektur yang menjelaskan lapisan utama project (halaman publik, panel admin, pengaturan situs, modul konten, layanan pendukung seperti SEO, sitemap, analytics, dan activity log) beserta hubungan antar lapisan.
- **FR-002**: Dokumen arsitektur MUST memuat peta direktori yang menjelaskan fungsi tiap folder penting dan jenis kode yang ditaruh di sana.
- **FR-003**: Dokumen arsitektur MUST menjelaskan alur data dari admin mengisi konten/pengaturan di panel hingga tampil di halaman publik, termasuk peran cache bila ada.
- **FR-004**: Dokumen arsitektur MUST memuat daftar modul konten yang ada, dan untuk tiap modul menunjukkan bagian yang terlibat: data, panel admin, tampilan publik, dan test.
- **FR-005**: Dokumen arsitektur MUST merangkum konvensi wajib (prinsip constitution, bahasa label panel admin, struktur grup navigasi admin, kewajiban test per modul, format kode) dan menautkan ke sumber aslinya.
- **FR-006**: Dokumentasi MUST menyediakan panduan langkah demi langkah menambah section baru di halaman publik, mencakup: membuat komponen tampilan, memakai token tema, menghubungkan ke sumber data, memasang di halaman, menangani empty state, dan menulis test.
- **FR-007**: Panduan section MUST memberi kriteria kapan konten section cukup disimpan di pengaturan situs dan kapan perlu modul konten baru, serta menautkan langkah untuk masing-masing pilihan.
- **FR-008**: Panduan section MUST menyertakan satu contoh lengkap yang bisa diikuti dari awal sampai akhir.
- **FR-009**: Dokumentasi MUST menyediakan panduan tema yang membedakan (a) kustomisasi lewat panel admin tanpa kode dan (b) perluasan lewat kode: menambah font ke daftar kurasi, mengubah nilai default tema, dan menambah token tema baru.
- **FR-010**: Panduan tema MUST menjelaskan cara token tema dari pengaturan sampai dipakai di tampilan publik, agar developer tidak menulis warna/font secara hardcode.
- **FR-011**: Dokumentasi MUST menyatakan batasan yang berlaku saat ini: belum ada pemilih varian section di panel admin (AMC-221) dan belum ada live preview tema (AMC-222); keduanya ditunda.
- **FR-012**: Dokumentasi MUST ditulis dalam Bahasa Indonesia, konsisten dengan dokumen yang sudah ada di folder dokumentasi project.
- **FR-013**: Dokumentasi MUST dapat ditemukan dari README project (ditautkan dari README) dan saling menaut dengan dokumen deployment, versioning klien, dan checklist yang sudah ada.
- **FR-014**: Setiap dokumen MUST mencantumkan tanggal terakhir diperbarui.
- **FR-015**: Semua path file, nama perintah, dan nama pengaturan yang disebut di dokumentasi MUST sesuai dengan kondisi kode saat dokumentasi ditulis.

### Key Entities

- **Dokumen Arsitektur**: gambaran besar project untuk developer: lapisan, peta direktori, alur data, daftar modul, dan konvensi.
- **Panduan Section**: langkah menambah atau memodifikasi section halaman publik, dengan satu contoh lengkap.
- **Panduan Tema**: cara kustomisasi tema lewat panel admin dan cara memperluas sistem tema lewat kode.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Developer yang belum pernah melihat project dapat menunjukkan lokasi kode yang benar untuk minimal 4 dari 5 skenario perubahan umum hanya dengan bantuan dokumen arsitektur, dalam waktu kurang dari 30 menit.
- **SC-002**: Developer yang mengikuti panduan section berhasil menambahkan satu section baru (tampil, mengikuti tema, dengan test yang lulus) dalam waktu kurang dari 2 jam tanpa bertanya ke developer lain.
- **SC-003**: Developer yang mengikuti panduan tema berhasil menambah satu font ke daftar pilihan admin dalam waktu kurang dari 30 menit.
- **SC-004**: 100% path file, perintah, dan nama pengaturan yang disebut di dokumentasi valid terhadap kode saat dokumentasi dirilis.
- **SC-005**: Tidak ada isi dokumen deployment, versioning, atau checklist yang disalin ulang; informasi tersebut hanya dirujuk lewat tautan.

## Assumptions

- Pembaca adalah developer yang sudah familiar dengan framework yang dipakai project secara umum, sehingga dokumentasi tidak mengajarkan dasar framework, hanya cara project ini memakainya.
- Dokumentasi disimpan sebagai file di repositori (folder dokumentasi yang sudah ada), bukan di wiki atau layanan eksternal, agar ikut ter-versioning bersama kode dan ikut ter-clone ke repo klien.
- Karena pemilih varian section (AMC-221) ditunda, "menambah section/tema baru" berarti mekanisme yang berlaku saat ini: menambah komponen section baru dan memperluas pengaturan tema. Panduan akan diperbarui saat AMC-221 dikerjakan.
- User manual untuk operator/admin panel (AMC-234) dan dokumentasi deployment (sudah ada) berada di luar scope dokumen ini; dokumen ini hanya menautkannya.
- Pengukuran success criteria dilakukan secara manual (review oleh developer lain atau uji baca), bukan otomatis.
