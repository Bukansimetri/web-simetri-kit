# Feature Specification: Tampil/Sembunyi Section dari Admin

**Feature Branch**: `032-section-visibility`

**Created**: 2026-10-05

**Status**: Draft

**Input**: User description: "Toggle tampil/sembunyi per section di website SUOER (opsi C): satu halaman 'Tampilan Section' di Pengaturan dengan toggle per section dikelompokkan per halaman, ditambah penanda 'Section ini sedang disembunyikan' di menu admin yang mengedit isi section. Default semua tampil; isi tidak terhapus; tanpa pratinjau; perubahan langsung tampil."

## Clarifications

### Session 2026-10-05

- Q: Apakah semua CTA tercakup, dan apakah tombol ajakan di luar section (tombol "Konsultasi Gratis" di header, tombol hero/slider Beranda) juga diberi toggle? → A: Semua 9 blok di menu CTA tercakup; tombol header dan tombol hero/slider tidak diatur oleh fitur ini.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Menyembunyikan dan Menampilkan Section (Priority: P1)

Admin membuka halaman **Tampilan Section** di grup Pengaturan. Halaman itu menampilkan semua section yang bisa disembunyikan, dikelompokkan per halaman situs (Beranda, Tentang Kami, Karir, Produk, Detail Produk, Artikel, Detail Artikel, FAQ, Kontak). Setiap section punya satu toggle **Tampilkan**. Admin mematikan toggle, menyimpan, dan section itu langsung hilang dari situs. Menyalakan kembali menampilkan section dengan isi yang sama persis.

**Why this priority**: Ini inti permintaan. Saat ini admin harus menonaktifkan item satu per satu untuk menyembunyikan sebuah section, dan beberapa section (Siapa Kami, Visi, Solusi, semua CTA) tidak bisa disembunyikan sama sekali.

**Independent Test**: Matikan toggle "Testimoni" di Beranda, simpan, buka Beranda: section hilang. Nyalakan lagi: section kembali dengan isi yang sama.

**Acceptance Scenarios**:

1. **Given** fitur baru dirilis, **When** pengunjung membuka halaman mana pun, **Then** tampilan sama persis dengan sebelum rilis (semua toggle bawaan tampil).
2. **Given** admin mematikan satu section lalu menyimpan, **When** pengunjung memuat ulang halaman terkait, **Then** section itu tidak tampil, sedangkan section lain di halaman yang sama tetap tampil.
3. **Given** section disembunyikan, **When** admin menyalakannya kembali, **Then** section tampil dengan isi, urutan item, dan teks yang sama seperti sebelum disembunyikan.
4. **Given** section disembunyikan, **When** admin mengubah isinya di menu terkait, **Then** perubahan tersimpan dan baru terlihat di situs setelah section ditampilkan lagi.
5. **Given** section ditampilkan tetapi semua itemnya nonaktif atau kosong, **Then** section tetap tersembunyi seperti perilaku sekarang.
6. **Given** section yang sama dipakai di dua halaman (mis. Testimoni di Beranda dan Tentang Kami), **Then** masing-masing halaman punya toggle sendiri dan menyembunyikan di satu halaman tidak memengaruhi halaman lain.

---

### User Story 2 - Penanda di Menu Isi Section (Priority: P2)

Saat admin membuka menu yang mengedit isi sebuah section (mis. Mengapa Beralih, Cara Kerja, Blok Halaman, CTA, Testimoni, Tim, Logo Klien, Misi, Nilai, Trust Strip, Mengapa Bergabung, Proses Rekrutmen, FAQ), muncul penanda yang jelas bila section terkait sedang disembunyikan, beserta tautan ke halaman **Tampilan Section**.

**Why this priority**: Tanpa penanda, admin bisa bingung mengapa isi yang baru diedit tidak muncul di situs.

**Independent Test**: Sembunyikan "Cara Kerja", buka menu Cara Kerja: penanda tampil. Tampilkan lagi: penanda hilang.

**Acceptance Scenarios**:

1. **Given** section disembunyikan, **When** admin membuka menu yang mengedit isinya, **Then** tampil penanda "Section ini sedang disembunyikan dari situs" beserta nama halaman dan tautan ke Tampilan Section.
2. **Given** satu menu mengelola isi untuk beberapa section (mis. Testimoni untuk Beranda dan Tentang Kami, CTA untuk banyak halaman, FAQ untuk Produk dan Kontak), **Then** penanda menyebut section mana saja yang sedang disembunyikan; bila tidak ada yang tersembunyi, penanda tidak tampil.
3. **Given** semua section terkait ditampilkan, **Then** tidak ada penanda.
4. **Given** menu CTA atau Blok Halaman yang mendaftar banyak entri, **Then** setiap baris yang section-nya tersembunyi diberi label "Disembunyikan".

---

### User Story 3 - Status Sekilas di Halaman Tampilan Section (Priority: P3)

Di halaman Tampilan Section, admin melihat sekilas berapa section yang tersembunyi per halaman dan bisa membuka menu isi section dari tiap baris.

**Why this priority**: Memudahkan operator mengaudit tampilan situs, tetapi tidak wajib untuk fungsi inti.

**Independent Test**: Sembunyikan dua section di Tentang Kami; judul kelompok Tentang Kami menunjukkan "2 disembunyikan" dan setiap baris punya tautan ke menu isinya.

**Acceptance Scenarios**:

1. **Given** beberapa section disembunyikan, **Then** setiap kelompok halaman menampilkan jumlah section yang tersembunyi.
2. **Given** sebuah section punya menu isi, **Then** barisnya menyertakan tautan "Edit isi" ke menu tersebut.

---

### Edge Cases

- Section yang tidak punya isi (mis. Logo Klien tanpa logo) tetap tersembunyi walau toggle menyala.
- Toggle diubah saat pengunjung sedang membuka halaman: perubahan terlihat pada muatan berikutnya.
- Menyembunyikan semua section opsional di sebuah halaman: hero, daftar utama, dan formulir tetap tampil, sehingga halaman tidak kosong.
- Admin tanpa izin pengaturan tidak dapat membuka halaman Tampilan Section.
- Section baru yang ditambahkan developer di kemudian hari otomatis tampil (default menyala) sampai admin mengubahnya.
- Pengaturan dari instalasi lama yang belum pernah disimpan berarti semua section tampil.

## Requirements *(mandatory)*

### Functional Requirements

**Halaman Tampilan Section**

- **FR-001**: Admin MUST memiliki halaman **Tampilan Section** di grup Pengaturan berisi satu toggle **Tampilkan** per section yang bisa disembunyikan, dikelompokkan per halaman situs.
- **FR-002**: Section yang bisa disembunyikan MUST mencakup tepat:
  - Beranda: Mengapa Beralih, Cara Kerja, Solusi Untuk Setiap Kebutuhan, Testimoni, CTA penutup
  - Tentang Kami: Siapa Kami, Visi, Misi, Nilai, Trust Strip, Tim, Testimoni, Logo Klien, CTA
  - Karir: Mengapa Bergabung, Proses Rekrutmen, CTA
  - Produk: CTA kalkulator, FAQ Seputar Produk, CTA penutup
  - Detail Produk: CTA
  - Artikel: CTA
  - Detail Artikel: CTA
  - FAQ: CTA
  - Kontak: FAQ Seputar Konsultasi
- **FR-003**: Hero/banner halaman (termasuk tombol di hero dan slider Beranda), tombol **Konsultasi Gratis** di header, daftar utama (produk, artikel, portofolio, lowongan), formulir kontak, kalkulator Hitung Estimasi, dan Kalkulator Detail Sistem PLTS MUST tidak memiliki toggle.
- **FR-003a**: Kesembilan blok di menu CTA (Beranda, Produk – kalkulator, Produk – penutup, Detail Produk, Daftar Artikel, Detail Artikel, Tentang Kami, FAQ, Karir) MUST masing-masing memiliki toggle sendiri; tidak ada satu toggle gabungan untuk semua CTA.
- **FR-004**: Semua toggle MUST bernilai **Tampilkan** secara bawaan, termasuk setelah rilis di instalasi yang sudah berjalan dan untuk section yang ditambahkan di masa depan.
- **FR-005**: Menyimpan halaman Tampilan Section MUST memberi konfirmasi berhasil.
- **FR-006**: Halaman Tampilan Section MUST hanya dapat diakses pengguna yang berhak mengelola pengaturan, mengikuti aturan akses halaman pengaturan lain.

**Situs publik**

- **FR-007**: Section yang dimatikan MUST tidak dirender sama sekali di halaman terkait; section lain tidak terpengaruh.
- **FR-008**: Menyembunyikan section MUST tidak mengubah atau menghapus isinya; menampilkan kembali MUST menampilkan isi yang sama.
- **FR-009**: Section yang ditampilkan MUST tetap mengikuti aturan yang ada: tersembunyi bila tidak punya item aktif.
- **FR-010**: Perubahan toggle MUST terlihat di situs pada muatan halaman berikutnya, tanpa menunggu cache kedaluwarsa.
- **FR-011**: Toggle untuk section yang sama di halaman berbeda (Testimoni Beranda vs Tentang Kami) MUST independen.

**Penanda di menu isi**

- **FR-012**: Menu admin yang mengedit isi section MUST menampilkan penanda saat satu atau lebih section terkait disembunyikan, menyebut section dan halamannya, serta menautkan ke halaman Tampilan Section.
- **FR-013**: Menu yang mendaftar banyak entri per section (CTA, Blok Halaman, FAQ per tempat tampil) MUST memberi label "Disembunyikan" pada baris yang section-nya tersembunyi.
- **FR-014**: Penanda MUST tidak tampil bila semua section terkait ditampilkan.

**Kelengkapan**

- **FR-015**: Halaman Tampilan Section MUST menampilkan jumlah section tersembunyi per kelompok halaman dan tautan "Edit isi" ke menu isi section bila tersedia.
- **FR-016**: Manual operator MUST diperbarui untuk halaman Tampilan Section dan penandanya.

### Key Entities *(include if feature involves data)*

- **Visibilitas Section**: daftar section yang bisa disembunyikan beserta status tampil/sembunyi; setiap section memiliki halaman induk, label, status (bawaan tampil), dan menu isi terkait (bila ada).

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin dapat menyembunyikan atau menampilkan section mana pun dalam daftar kurang dari 30 detik, dari satu halaman, tanpa mengubah isi section.
- **SC-002**: Setelah rilis, 0 perubahan tampilan di seluruh halaman publik sampai admin mengubah toggle.
- **SC-003**: 100% dari 25 section dalam cakupan bisa disembunyikan dan ditampilkan kembali dengan isi identik.
- **SC-004**: Perubahan toggle terlihat di situs pada muatan halaman berikutnya (0 kasus tampilan lama tertinggal).
- **SC-005**: Untuk setiap section tersembunyi, menu isinya menampilkan penanda; untuk section yang tampil, tidak ada penanda yang keliru.
- **SC-006**: Seluruh pengujian otomatis yang ada tetap lulus.

## Assumptions

- Kalkulator Hitung Estimasi dan Kalkulator Detail Sistem PLTS tetap diatur di kode (keputusan klien), di luar cakupan.
- Tidak ada pratinjau section tersembunyi (keputusan klien).
- Hak akses mengikuti halaman pengaturan lain yang sudah ada (pengguna dengan izin pengaturan).
- Penonaktifan item individual (mis. testimoni nonaktif) tetap berfungsi seperti sekarang dan independen dari toggle section.
- Toggle berlaku untuk semua pengunjung; tidak ada penjadwalan tampil/sembunyi.
- Jumlah section dalam cakupan adalah 25 (Beranda 5, Tentang Kami 9, Karir 3, Produk 3, Detail Produk 1, Artikel 1, Detail Artikel 1, FAQ 1, Kontak 1); daftar dapat bertambah di masa depan tanpa mengubah perilaku section yang ada.
