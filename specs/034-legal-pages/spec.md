# Feature Specification: Halaman Legal (Kebijakan Privasi & Syarat Ketentuan) yang Dapat Diedit

**Feature Branch**: `034-legal-pages`

**Created**: 2026-10-06

**Status**: Draft

**Input**: User description: "Halaman legal Kebijakan Privasi dan Syarat & Ketentuan sesuai desain (folder stitch_suoer_premium_solar_website), dinamis dan bisa diedit dari admin lewat template 'Dokumen Legal' pada modul Halaman Kustom; isi awal dipasang otomatis; isi awal halaman FAQ; PDF opsional."

## Konteks

- Desain acuan: `kebijakan_privasi_suoer`, `syarat_ketentuan_suoer`, dan panduan gaya `luminous_azure/DESIGN.md`.
- Footer situs sudah menautkan ke `/halaman/kebijakan-privasi` dan `/halaman/syarat-ketentuan` (atau URL dari Pengaturan Umum), tetapi halaman tersebut belum ada sehingga tautan saat ini berujung "tidak ditemukan".
- Modul **Halaman** (halaman kustom) sudah ada dengan judul, isi, dan SEO, tetapi tampilannya hanya satu kolom teks.

## Clarifications

### Session 2026-10-06

- Q: Apakah badge label tanggal di hero (mis. "Berlaku Efektif: Januari 2026", "Terakhir Diperbarui: 15 Januari 2026") tetap ditampilkan dan diedit admin? → A: Tidak, label tanggal dihilangkan dari halaman dan dari form admin.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Pengunjung Membaca Halaman Legal Sesuai Desain (Priority: P1)

Pengunjung yang menekan tautan "Kebijakan Privasi" atau "Syarat & Ketentuan" di footer membuka halaman bergaya sesuai desain:

- hero bergambar dengan jejak halaman, judul, dan subjudul (tanpa label tanggal)
- paragraf pembuka dan kotak sorotan
- bagian bernomor (judul, isi, daftar berpoin, kartu)
- daftar isi di sidebar yang menandai bagian yang sedang dibaca
- kotak kontak
- tombol unduh PDF (bila tersedia)
- CTA penutup

**Why this priority**: Tautan legal di footer saat ini rusak. Halaman legal wajib untuk kepercayaan pelanggan dan kepatuhan.

**Independent Test**: Buka kedua tautan dari footer. Halaman tampil lengkap dan daftar isi berfungsi.

**Acceptance Scenarios**:

1. **Given** fitur baru dirilis, **When** pengunjung membuka tautan "Kebijakan Privasi" di footer, **Then** halaman tampil (bukan "tidak ditemukan") dengan isi dan susunan seperti desain.
2. **Given** pengunjung membuka "Syarat & Ketentuan", **Then** setiap pasal tampil berurutan dengan label kecil (mis. "PASAL 01 · PENDAHULUAN"), judul bernomor, dan isinya.
3. **Given** pengunjung menekan butir di daftar isi, **Then** halaman bergulir ke bagian tersebut dan butir itu ditandai aktif; saat menggulir manual, penanda aktif ikut berpindah.
4. **Given** layar ponsel, **Then** daftar isi tampil di atas isi (tidak berdampingan), dan halaman tidak memiliki gulir horizontal.
5. **Given** bagian yang memiliki kartu, **Then** kartu tampil dalam grid rapi (dua atau tiga per baris di desktop, satu per baris di ponsel).
6. **Given** pengunjung menekan tombol WhatsApp di kotak kontak, **Then** WhatsApp terbuka ke nomor bisnis dari Pengaturan Umum.

---

### User Story 2 - Admin Mengedit Halaman Legal (Priority: P1)

Admin membuka menu **Halaman** dan memilih template **Dokumen Legal** untuk sebuah halaman. Muncul kolom-kolom berikut, dan semuanya bisa diedit tanpa developer:

- gambar hero dan subjudul
- paragraf pembuka
- kotak sorotan
- daftar bagian yang bisa ditambah, dihapus, dan diurutkan; setiap bagian berisi label kecil, judul, isi teks berformat, kartu opsional, dan catatan kecil opsional
- kotak kontak
- berkas PDF
- CTA penutup

**Why this priority**: Isi legal sering diperbarui (pasal baru, perubahan ketentuan) dan harus bisa dikelola klien sendiri.

**Independent Test**: Ubah subjudul, tambah satu pasal, ubah urutan dua pasal, hapus satu kartu, lalu periksa halaman publik.

**Acceptance Scenarios**:

1. **Given** admin membuat halaman baru dengan template Dokumen Legal, **When** mengisi judul dan minimal satu bagian lalu menyimpan, **Then** halaman tampil dengan tata letak legal di `/halaman/{slug}`.
2. **Given** admin mengubah urutan bagian, **Then** urutan, penomoran, dan daftar isi di situs mengikuti urutan baru.
3. **Given** admin mengosongkan kotak sorotan, kotak kontak, atau CTA penutup, **Then** elemen itu tidak tampil dan tidak meninggalkan ruang kosong.
4. **Given** admin menambahkan kartu ke sebuah bagian, **Then** kartu tampil di bawah isi bagian itu; tanpa kartu, grid tidak tampil.
5. **Given** admin mengganti template halaman dari Dokumen Legal ke Standar, **Then** halaman tampil dengan tata letak standar memakai judul dan isi standar, dan data legal tetap tersimpan bila admin kembali ke template Dokumen Legal.
6. **Given** halaman kustom lama yang tidak diubah, **Then** tetap memakai template Standar dan tampilannya tidak berubah.

---

### User Story 3 - PDF Opsional (Priority: P2)

Admin dapat mengunggah salinan PDF dokumen. Bila ada, sidebar menampilkan tombol unduh; bila tidak, tombolnya tidak tampil.

**Why this priority**: Klien ingin menyediakan versi resmi yang bisa diunduh, tetapi tidak selalu tersedia.

**Independent Test**: Unggah PDF lalu cek tombol; hapus PDF lalu cek tombol hilang.

**Acceptance Scenarios**:

1. **Given** admin mengunggah PDF, **Then** tombol "Unduh … (PDF)" tampil dan mengunduh berkas tersebut.
2. **Given** tidak ada PDF atau berkasnya hilang dari penyimpanan, **Then** tombol unduh tidak tampil.
3. **Given** admin mencoba mengunggah berkas bukan PDF atau melebihi batas ukuran, **Then** unggahan ditolak dengan pesan jelas.

---

### User Story 4 - Isi Awal Otomatis untuk Halaman Legal dan FAQ (Priority: P2)

Setelah rilis (cukup menjalankan migrasi), dua halaman legal sudah ada dengan teks dari desain, dan halaman FAQ sudah berisi pertanyaan awal. Admin tinggal meninjau dan mengubahnya.

**Why this priority**: Produksi hanya menjalankan migrasi; tanpa isi awal, halaman legal dan FAQ akan kosong.

**Independent Test**: Instalasi baru tanpa seeder, jalankan migrasi, lalu buka kedua halaman legal dan halaman FAQ.

**Acceptance Scenarios**:

1. **Given** instalasi tanpa halaman bertautan `kebijakan-privasi` dan `syarat-ketentuan`, **When** migrasi dijalankan, **Then** kedua halaman dibuat dengan template Dokumen Legal dan teks persis dari desain.
2. **Given** teks bawaan menyebut nama merek, email, telepon, atau alamat, **Then** nilai tersebut diambil dari Nama Situs dan data perusahaan di Pengaturan Umum saat pemasangan.
3. **Given** halaman dengan salah satu tautan tersebut sudah ada, **When** migrasi dijalankan lagi, **Then** halaman itu tidak diubah atau ditimpa.
4. **Given** halaman FAQ belum punya entri sama sekali, **When** migrasi dijalankan, **Then** pertanyaan awal FAQ (yang selama ini menjadi data contoh) dipasang sebagai entri aktif Halaman FAQ; **Given** halaman FAQ sudah punya entri, **Then** tidak ada yang ditambahkan.
5. **Given** developer menjalankan seeder halaman legal atau FAQ secara manual, **Then** hasilnya sama dengan pemasangan otomatis dan tidak menduplikasi data.

---

### Edge Cases

- Bagian tanpa isi teks tetapi berkartu: tetap tampil dengan judul dan kartu.
- Halaman legal tanpa bagian sama sekali: tampil dengan hero dan pembuka; daftar isi tidak tampil.
- Judul bagian yang sangat panjang: daftar isi membungkus teks tanpa merusak sidebar.
- Bagian lebih dari 15: daftar isi tetap dapat digulir di sidebar.
- Nomor WhatsApp di Pengaturan Umum kosong: tombol WhatsApp di kotak kontak tidak tampil; email tetap tampil bila diisi.
- Gambar hero belum diunggah atau berkas hilang: memakai gambar bawaan.
- Isi teks berformat berisi skrip berbahaya: dibersihkan sebelum ditampilkan.
- Admin mengubah tautan (slug) halaman legal: tautan footer bawaan tidak lagi mengarah ke halaman itu kecuali admin mengisi URL di Pengaturan Umum (perilaku saat ini dipertahankan).
- Bagian "Tampilan Section" (spec 032) tidak mencakup halaman legal; halaman legal tidak memiliki toggle section.

## Requirements *(mandatory)*

### Functional Requirements

**Template dan tampilan**

- **FR-001**: Modul Halaman MUST memiliki pilihan template **Standar** dan **Dokumen Legal**; halaman yang sudah ada otomatis bertemplate Standar.
- **FR-002**: Halaman bertemplate Standar MUST tampil persis seperti sekarang.
- **FR-003**: Halaman bertemplate Dokumen Legal MUST tampil sesuai desain acuan:
  - hero bergambar (jejak halaman, judul, subjudul); badge label tanggal dari desain acuan tidak ditampilkan
  - paragraf pembuka
  - kotak sorotan opsional
  - bagian bernomor otomatis dengan label kecil opsional, judul, isi teks berformat, kartu opsional, dan catatan kecil opsional
  - sidebar berisi daftar isi, tombol unduh PDF (bila ada), dan kotak kontak (bila diisi)
  - CTA penutup opsional
- **FR-004**: Daftar isi MUST dibuat otomatis dari judul bagian, dapat diklik untuk menggulir ke bagian, dan menandai bagian yang sedang terlihat.
- **FR-005**: Pada layar kecil, sidebar MUST pindah ke atas isi dan halaman tidak boleh memiliki gulir horizontal.
- **FR-006**: Header dan footer MUST memakai milik situs (bukan dari desain acuan).
- **FR-007**: Teks berformat dari admin MUST dibersihkan dari HTML berbahaya sebelum ditampilkan.

**Admin**

- **FR-008**: Admin MUST dapat mengedit semua elemen halaman legal: gambar hero, subjudul, pembuka, kotak sorotan (judul, teks), bagian (tambah, hapus, urutkan; label, judul, isi, kartu dengan ikon/judul/teks, catatan), kotak kontak (judul, teks, label tombol WhatsApp, pesan WhatsApp, email), PDF, dan CTA penutup (judul, teks, label tombol, tautan).
- **FR-009**: Kolom khusus Dokumen Legal MUST hanya tampil saat template Dokumen Legal dipilih; data legal MUST tetap tersimpan bila template diganti.
- **FR-010**: Admin MUST dapat mengunggah satu berkas PDF (maksimal 10 MB) per halaman legal; berkas non-PDF ditolak.
- **FR-011**: Ikon kartu MUST dipilih dari daftar ikon kurasi yang sudah dipakai menu lain, tanpa input bebas.

**PDF**

- **FR-012**: Tombol unduh MUST tampil hanya bila PDF diunggah dan berkasnya tersedia; bila tidak, tombol tidak tampil.

**Isi awal**

- **FR-013**: Migrasi MUST membuat halaman `kebijakan-privasi` dan `syarat-ketentuan` bertemplate Dokumen Legal dengan teks dari desain bila tautan tersebut belum ada, termasuk di produksi yang hanya menjalankan migrasi.
- **FR-014**: Nama merek, email, telepon, dan alamat dalam teks bawaan MUST diambil dari Pengaturan Umum saat pemasangan; bila data perusahaan kosong, bagian teks yang memerlukannya memakai nilai dari desain sebagai cadangan.
- **FR-015**: Pemasangan MUST idempoten dan tidak menimpa halaman yang sudah ada.
- **FR-016**: Migrasi MUST memasang entri awal Halaman FAQ (isi yang selama ini dipakai sebagai data contoh FAQ) hanya bila Halaman FAQ belum punya entri; seeder FAQ dan halaman legal MUST dapat dijalankan manual dengan hasil yang sama tanpa duplikasi.

**Lintas fitur**

- **FR-017**: Halaman legal MUST tetap muncul di sitemap dan memiliki judul serta deskripsi untuk mesin pencari seperti halaman kustom lain.
- **FR-018**: Manual operator MUST menjelaskan template Dokumen Legal, kolomnya, dan PDF.

### Key Entities *(include if feature involves data)*

- **Halaman (Halaman Kustom)**: sudah ada; ditambah pilihan template dan data dokumen legal.
- **Data Dokumen Legal** (bagian dari Halaman): gambar hero, subjudul, pembuka, kotak sorotan, daftar bagian (label, judul, isi, kartu[ikon, judul, teks], catatan), kotak kontak, berkas PDF, CTA penutup.
- **Entri FAQ**: sudah ada (spec 031); entri awal Halaman FAQ dipasang otomatis.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Setelah rilis, 0 tautan legal di footer berujung "tidak ditemukan".
- **SC-002**: Admin dapat mengubah subjudul dan menambah satu pasal dalam kurang dari 3 menit tanpa bantuan developer.
- **SC-003**: Pada peninjauan berdampingan, kedua halaman legal sesuai desain acuan untuk semua elemen di tabel konteks (hero, pembuka, sorotan, bagian, kartu, daftar isi, kontak, PDF, CTA).
- **SC-004**: Pada lebar 360–1440 px, kedua halaman legal tidak memiliki gulir horizontal dan daftar isi dapat digunakan.
- **SC-005**: Instalasi baru yang hanya menjalankan migrasi memiliki kedua halaman legal dan minimal 5 entri FAQ di Halaman FAQ.
- **SC-006**: Menjalankan migrasi atau seeder berulang kali tidak menghasilkan halaman atau entri FAQ ganda.
- **SC-007**: Halaman kustom yang sudah ada tampil identik dengan sebelum rilis.
- **SC-008**: Seluruh pengujian otomatis yang ada tetap lulus.

## Assumptions

- Teks bawaan diambil dari desain acuan apa adanya; klien atau tim legal bertanggung jawab meninjau akurasi hukumnya (mis. rujukan UU PDP, data DPO, masa garansi) sebelum tayang.
- Badge label tanggal pada hero desain acuan sengaja tidak dibuat (keputusan klien); bila tanggal berlaku perlu disebut, admin dapat menuliskannya di subjudul atau paragraf pembuka.
- CTA penutup memakai gaya banner biru yang sudah ada; tautannya default ke halaman Kontak.
- Gambar hero bawaan memakai gambar contoh yang sudah ada di proyek sampai admin mengunggah sendiri.
- Kotak kontak memakai nomor WhatsApp bisnis dari Pengaturan Umum; tidak ada nomor terpisah per halaman.
- Daftar isi tidak bisa diedit terpisah; selalu mengikuti judul bagian.
- Entri FAQ awal memakai lima pertanyaan yang sudah ada di data contoh FAQ (instalasi, cuaca, penghematan, garansi, perawatan), dengan nama merek mengikuti Nama Situs.
- Tidak ada pembuatan PDF otomatis; PDF hanya dari unggahan admin.
