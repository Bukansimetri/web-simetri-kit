# Feature Specification: Modul Testimonials

**Feature Branch**: `008-testimonials-module`

**Created**: 2026-09-07

**Status**: Draft

**Input**: User description: "AMC-210: Modul Testimonials — CRUD admin (Filament Resource) untuk testimoni klien: nama, perusahaan/jabatan, foto (opsional), isi testimoni, rating bintang (1–5), urutan tampil, toggle aktif. Ditampilkan di section testimonials pada halaman publik (beranda / halaman terkait). Pola identik modul konten lain yang sudah ada (Artikel, Career, Custom Page): satu Filament Resource + rendering di Blade section, tanpa dependency baru. Prioritas awal: mengisi social proof di beranda yang saat ini kemungkinan masih statis/dummy."

## Clarifications

### Session 2026-09-07

- Q: Struktur field "perusahaan/jabatan" — satu field atau dua? → A: Satu field teks bebas opsional (mis. "Manajer Operasional, PT ABC").
- Q: Di halaman mana section testimoni ditampilkan? → A: Di halaman "Tentang Kami" (bukan beranda — desain beranda tidak menyediakan slot untuk ini), ditempatkan setelah section "Nilai-Nilai Kami", sebelum CTA band penutup.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Admin mengelola testimoni klien (Priority: P1) 🎯 MVP

Admin membuka menu Testimoni di panel admin, menambah testimoni baru (nama pemberi testimoni, atribusi perusahaan/jabatan, isi testimoni, rating bintang 1–5, foto opsional), mengatur urutan tampil dan status aktif, lalu menyimpannya. Admin juga bisa mengedit dan menghapus testimoni yang sudah ada.

**Why this priority**: Tanpa kemampuan CRUD dasar, tidak ada cara bagi non-developer untuk mengisi/memperbarui social proof di situs. Ini inti tiket.

**Independent Test**: Login sebagai admin, buat 3 testimoni dengan data berbeda (satu tanpa foto), atur urutan dan aktif/nonaktif, verifikasi daftar di panel menampilkan semuanya sesuai urutan; edit satu testimoni, verifikasi perubahan tersimpan; hapus satu testimoni dengan konfirmasi, verifikasi hilang dari daftar.

**Acceptance Scenarios**:

1. **Given** admin di form tambah testimoni, **When** admin mengisi nama, perusahaan/jabatan, isi testimoni, rating, dan menyimpan tanpa foto, **Then** testimoni tersimpan dan tampil di daftar panel.
2. **Given** admin di form tambah testimoni, **When** admin mengunggah foto lalu menyimpan, **Then** foto tersimpan dan tampil sebagai pratinjau di daftar/form.
3. **Given** admin mencoba menyimpan testimoni tanpa nama atau tanpa isi testimoni atau tanpa rating, **When** admin menyimpan, **Then** sistem menolak dengan pesan validasi per field.
4. **Given** admin mencoba menyimpan rating di luar rentang 1–5, **When** admin menyimpan, **Then** sistem menolak dengan pesan error.
5. **Given** admin mengedit testimoni yang sudah ada, **When** admin mengubah isi/rating/urutan/status dan menyimpan, **Then** perubahan tersimpan.
6. **Given** admin menghapus testimoni, **When** admin mengonfirmasi penghapusan, **Then** testimoni terhapus dan tidak lagi muncul di daftar maupun di halaman publik.

---

### User Story 2 - Pengunjung melihat testimoni di halaman Tentang Kami (Priority: P1)

Pengunjung yang membuka halaman "Tentang Kami" melihat section "Testimoni" berisi kutipan dari klien nyata (nama, perusahaan/jabatan, rating bintang, foto bila ada) yang dikelola admin — bukan teks dummy hardcoded.

**Why this priority**: Ini tujuan bisnis tiket — menampilkan social proof yang kredibel dan bisa diperbarui. CRUD tanpa tampilan publik tidak memberi nilai ke pengunjung.

**Independent Test**: Dengan beberapa testimoni aktif dan nonaktif dibuat via User Story 1, buka halaman Tentang Kami sebagai pengunjung, verifikasi hanya testimoni aktif yang tampil, dalam urutan yang ditetapkan admin, lengkap dengan nama/perusahaan/rating; nonaktifkan semua testimoni, verifikasi section testimoni tidak muncul sama sekali (tidak ada blok kosong).

**Acceptance Scenarios**:

1. **Given** ada 3 testimoni aktif dengan urutan 1/2/3, **When** pengunjung membuka halaman Tentang Kami, **Then** ketiganya tampil di section testimoni sesuai urutan tersebut.
2. **Given** sebuah testimoni berstatus nonaktif, **When** pengunjung membuka halaman Tentang Kami, **Then** testimoni tersebut tidak tampil.
3. **Given** sebuah testimoni aktif tanpa foto, **When** pengunjung membuka halaman Tentang Kami, **Then** testimoni tetap tampil rapi dengan placeholder/inisial, tanpa gambar rusak.
4. **Given** tidak ada testimoni aktif sama sekali, **When** pengunjung membuka halaman Tentang Kami, **Then** section testimoni tidak dirender (halaman tetap rapi tanpa area kosong) dan section lain Tentang Kami tetap utuh.
5. **Given** rating sebuah testimoni bernilai 4, **When** pengunjung melihat testimoni tersebut, **Then** tampil 4 bintang terisi dari 5.

---

### Edge Cases

- Dua testimoni memiliki nilai urutan yang sama → ditampilkan berdampingan dengan urutan sekunder yang stabil (mis. berdasarkan waktu dibuat), tanpa error.
- Isi testimoni sangat panjang → ditampilkan apa adanya sebagai teks; tidak ada pemotongan otomatis di v1 (admin bertanggung jawab menjaga panjang wajar).
- Foto dengan rasio/ukuran tidak ideal → ditampilkan dalam bingkai konsisten (di-crop/di-fit oleh layout), tidak merusak tata letak.
- Foto testimoni yang dihapus dari storage secara manual → tampilan publik jatuh ke placeholder/inisial, bukan gambar rusak.
- Nilai rating kosong tidak diperbolehkan; rating hanya menerima bilangan bulat 1–5.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Admin panel MUST menyediakan menu CRUD Testimoni — tambah, daftar, edit, hapus.
- **FR-002**: Setiap testimoni MUST memiliki: nama pemberi testimoni (wajib), atribusi perusahaan/jabatan sebagai satu field teks bebas opsional (mis. "Manajer Operasional, PT ABC"), isi testimoni (wajib), rating bintang bilangan bulat 1–5 (wajib), foto (opsional), nilai urutan tampil, dan status aktif/nonaktif.
- **FR-003**: Sistem MUST memvalidasi field wajib (nama, isi testimoni, rating) — submit tanpa salah satunya MUST ditolak dengan pesan error per field.
- **FR-004**: Sistem MUST memvalidasi rating berada dalam rentang 1–5 (bilangan bulat) — nilai di luar itu MUST ditolak.
- **FR-005**: Admin MUST bisa mengunggah foto testimoni; foto bersifat opsional dan testimoni tanpa foto MUST tetap valid dan tampil rapi (placeholder/inisial) di halaman publik.
- **FR-006**: Admin MUST bisa menetapkan nilai urutan tampil per testimoni; halaman publik MUST menampilkan testimoni aktif diurutkan berdasarkan nilai urutan tersebut (menaik), dengan urutan sekunder yang deterministik saat nilai urutan sama.
- **FR-007**: Admin MUST bisa mengaktifkan/menonaktifkan tiap testimoni; hanya testimoni berstatus aktif yang tampil di halaman publik.
- **FR-008**: Admin MUST bisa menghapus testimoni dengan konfirmasi terlebih dahulu; setelah dihapus, testimoni MUST hilang dari daftar admin dan halaman publik.
- **FR-009**: Perubahan testimoni (tambah/edit/hapus/aktif/urutan) MUST tercermin di halaman Tentang Kami pada request berikutnya tanpa deploy ulang.
- **FR-010**: Section testimoni MUST menampilkan, per testimoni: nama, atribusi perusahaan/jabatan (bila ada), isi testimoni, rating sebagai bintang terisi dari 5, dan foto (bila ada).
- **FR-011**: Jika tidak ada testimoni aktif, halaman Tentang Kami MUST tidak merender section testimoni sama sekali (tanpa area/heading kosong), konsisten dengan pola modul opsional lain; section Tentang Kami lain (hero, visi, misi, nilai, CTA) MUST tidak terpengaruh.
- **FR-012**: CRUD Testimoni MUST terbuka untuk semua role yang memiliki akses panel admin, konsisten dengan resource admin lain yang sudah ada.
- **FR-013**: Section testimoni MUST ditempatkan di halaman "Tentang Kami" (`/tentang-kami`), setelah section "Nilai-Nilai Kami" dan sebelum CTA band penutup. Beranda dan halaman lain TIDAK menampilkan testimoni di v1 (desain beranda tidak menyediakan slot untuk section ini).

### Key Entities *(include if feature involves data)*

- **Testimoni**: satu kutipan dari klien. Atribut: nama pemberi (wajib), atribusi perusahaan/jabatan (satu field teks bebas, opsional), isi testimoni (wajib), rating 1–5 (wajib), foto (opsional), urutan tampil (angka), status aktif (boolean). Tidak berelasi ke entity lain.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin dapat menambahkan satu testimoni lengkap (termasuk foto) dan melihatnya tampil di halaman Tentang Kami dalam satu kali proses simpan, tanpa bantuan developer atau deploy ulang.
- **SC-002**: Admin dapat menyusun ulang urutan tampil testimoni dan perubahannya terlihat di halaman Tentang Kami pada refresh berikutnya.
- **SC-003**: 100% percobaan submit dengan data tidak valid (field wajib kosong, rating di luar 1–5) ditolak dengan pesan error yang jelas.
- **SC-004**: 100% testimoni berstatus nonaktif atau terhapus tidak muncul di halaman publik.
- **SC-005**: Saat tidak ada testimoni aktif, halaman Tentang Kami tetap tampil rapi tanpa section/area testimoni kosong, dan section lainnya tetap utuh.
- **SC-006**: Testimoni tanpa foto tampil tanpa gambar rusak pada 100% kasus.

## Assumptions

- Section testimoni v1 hanya tampil di halaman **Tentang Kami** (`/tentang-kami`). Menampilkannya di beranda atau halaman lain di luar scope tiket ini (desain beranda tidak menyediakan slot untuk section ini — hasil klarifikasi 2026-09-07).
- Tidak ada toggle modul tingkat-atas (aktif/nonaktif seluruh modul) seperti modul Karir — mekanisme "sembunyikan" cukup lewat menonaktifkan/menghapus tiap testimoni; bila semua nonaktif, section otomatis tidak dirender (FR-011). Menambah toggle modul dianggap kompleksitas yang tidak diminta.
- Rating adalah bilangan bulat 1–5 (tanpa setengah bintang) untuk kesederhanaan tampilan.
- Foto testimoni mengikuti perilaku upload gambar yang sudah dipakai modul lain di project ini (mis. konversi/penyimpanan gambar seperti pada featured image Artikel); tidak ada persyaratan dimensi/rasio khusus yang divalidasi keras di v1.
- Tidak ada moderasi/alur persetujuan — testimoni dibuat langsung oleh admin (bukan submission dari publik). Form testimoni dari pengunjung di luar scope.
- Tidak ada paginasi/carousel wajib; menampilkan seluruh testimoni aktif dalam satu grid/daftar sudah memadai untuk volume yang diharapkan (puluhan, bukan ratusan). Perilaku carousel bersifat pilihan implementasi tampilan, bukan requirement.
- Tidak ada field tanggal/tautan sumber testimoni di v1.
- Halaman "Tentang Kami" hanya diubah dengan menyisipkan satu section testimoni baru pada posisi yang ditentukan (FR-013); seluruh section eksisting (hero, siapa kami, visi, misi, nilai, CTA) tidak diubah. Beranda tidak disentuh sama sekali.
