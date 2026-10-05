# Feature Specification: Deskripsi Lowongan Karir dengan Editor Teks Berformat

**Feature Branch**: `035-career-description-editor`

**Created**: 2026-10-06

**Status**: Draft

**Input**: User description: "Bagian Karir: deskripsi lowongan bisa diedit dengan editor teks berformat (WYSIWYG) di admin agar bisa dirapikan (judul, daftar, tebal, tautan), dan tampil rapi di halaman Karir/detail lowongan. Data lowongan lama yang berupa teks biasa tetap tampil benar tanpa perubahan visual. Konten dibersihkan dari HTML berbahaya."

## Konteks

- Lowongan karir saat ini hanya punya satu kolom teks panjang, **Deskripsi**, yang diisi sebagai teks polos. Admin tidak bisa membuat judul bagian, daftar kualifikasi, atau penekanan; semuanya tampil sebagai paragraf datar.
- Deskripsi tampil di dua tempat: ringkasan dua baris pada kartu lowongan di halaman Karir, dan isi lengkap di halaman detail lowongan.
- Modul lain (Artikel, Halaman) sudah memakai editor teks berformat, sehingga admin sudah terbiasa dengan tampilannya.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Admin Merapikan Deskripsi Lowongan (Priority: P1)

Admin membuka form lowongan dan menulis deskripsi dengan editor teks berformat: judul bagian (mis. "Tanggung Jawab", "Kualifikasi"), daftar berbutir atau bernomor, huruf tebal atau miring, dan tautan. Setelah disimpan, halaman detail lowongan menampilkan deskripsi dengan format yang sama dan rapi.

**Why this priority**: Inilah permintaan utama; tanpanya admin tetap tidak bisa menyusun deskripsi lowongan yang terbaca.

**Independent Test**: Buat lowongan dengan deskripsi berisi judul, daftar, dan teks tebal; buka halaman detail dan periksa format tampil.

**Acceptance Scenarios**:

1. **Given** admin di form lowongan, **When** menulis deskripsi dengan judul bagian, daftar, dan teks tebal lalu menyimpan, **Then** halaman detail lowongan menampilkan judul, daftar, dan teks tebal itu dengan gaya yang selaras dengan halaman lain.
2. **Given** deskripsi berisi tautan, **When** pengunjung membuka detail lowongan, **Then** tautan tampil dan dapat diklik.
3. **Given** admin mengosongkan deskripsi, **When** menyimpan, **Then** form menolak dengan pesan bahwa deskripsi wajib diisi (perilaku sekarang dipertahankan).
4. **Given** deskripsi diubah di admin, **When** halaman Karir dan detail dibuka kembali, **Then** perubahan langsung terlihat.

---

### User Story 2 - Lowongan Lama Tetap Tampil Benar (Priority: P1)

Lowongan yang sudah ada berisi teks polos (dengan baris baru). Setelah fitur ini aktif, lowongan itu tampil sama seperti sebelumnya di halaman Karir maupun detail, tanpa admin harus mengedit ulang, dan dapat dibuka di editor tanpa isi rusak.

**Why this priority**: Situs sudah dipakai klien; perubahan tidak boleh merusak konten yang sudah tayang.

**Independent Test**: Siapkan lowongan lama berisi teks polos dengan beberapa baris, buka halaman detail dan editor admin.

**Acceptance Scenarios**:

1. **Given** lowongan lama berisi teks polos dengan beberapa baris, **When** halaman detail dibuka, **Then** paragraf dan jeda barisnya tampil seperti sebelum fitur ini.
2. **Given** lowongan lama, **When** admin membukanya di form, **Then** teks lama muncul utuh di editor dan dapat langsung diformat ulang.
3. **Given** teks lama mengandung karakter seperti `<`, `>`, atau `&`, **When** ditampilkan, **Then** karakter itu tampil sebagai teks biasa, bukan hilang atau dianggap kode.

---

### User Story 3 - Ringkasan di Kartu Lowongan Tetap Rapi (Priority: P2)

Kartu lowongan di halaman Karir menampilkan ringkasan dua baris dari deskripsi. Ringkasan itu berupa teks bersih tanpa tanda format, sehingga kartu tetap seragam walau deskripsinya berisi judul dan daftar.

**Why this priority**: Mencegah kartu berantakan atau menampilkan kode format; dampaknya lebih kecil dari dua cerita di atas.

**Independent Test**: Buat lowongan dengan deskripsi berformat panjang dan periksa kartunya di halaman Karir.

**Acceptance Scenarios**:

1. **Given** deskripsi berformat (judul, daftar, tebal), **When** halaman Karir dibuka, **Then** kartu menampilkan ringkasan teks polos maksimal dua baris tanpa tanda format.
2. **Given** deskripsi sangat panjang, **When** kartu ditampilkan, **Then** tinggi kartu tetap seragam dengan kartu lain.
3. **Given** halaman detail lowongan, **When** dibagikan atau dicari, **Then** deskripsi meta berupa teks polos ringkas tanpa tanda format.

---

### User Story 4 - Konten Aman dari Skrip Berbahaya (Priority: P1)

Apa pun yang tersimpan di deskripsi tidak boleh menjalankan skrip atau memuat elemen berbahaya di halaman publik, termasuk bila data diubah di luar editor.

**Why this priority**: Konten berformat dari admin ditampilkan ke publik; kelalaian di sini membuka celah keamanan.

**Independent Test**: Simpan deskripsi berisi tag skrip, atribut peristiwa, dan tautan `javascript:`; buka halaman detail.

**Acceptance Scenarios**:

1. **Given** deskripsi berisi tag skrip atau atribut seperti `onclick`/`onerror`, **When** halaman detail dibuka, **Then** elemen dan atribut itu tidak muncul di halaman.
2. **Given** tautan dengan alamat `javascript:`, **When** halaman dirender, **Then** alamat berbahaya itu dibuang.
3. **Given** format yang wajar (judul, daftar, tebal, miring, tautan, kutipan), **When** dirender, **Then** semuanya dipertahankan.

---

### Edge Cases

- Deskripsi lama yang hanya satu baris tanpa baris baru tetap tampil sebagai satu paragraf.
- Deskripsi lama dengan baris kosong ganda tidak menghasilkan jarak yang berlebihan.
- Deskripsi berformat yang hanya berisi tag kosong (tampak kosong) dianggap tidak diisi dan ditolak form.
- Gambar yang ditempel di editor tidak disimpan sebagai berkas tersembunyi yang membengkakkan data; bila editor mengizinkan lampiran, perilakunya sama dengan editor di modul Artikel.
- Ringkasan kartu untuk deskripsi yang diawali judul tetap terbaca (judul dan paragraf digabung sebagai teks polos).
- Lowongan nonaktif tetap tidak tampil publik, tidak berubah oleh fitur ini.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Kolom **Deskripsi** pada form lowongan MUST memakai editor teks berformat yang mendukung judul bagian, daftar berbutir dan bernomor, tebal, miring, tautan, dan kutipan, selaras dengan editor di modul lain.
- **FR-002**: Deskripsi MUST tetap wajib diisi; isi yang tampak kosong (hanya tag kosong atau spasi) MUST ditolak.
- **FR-003**: Halaman detail lowongan MUST menampilkan deskripsi berformat dengan gaya yang rapi dan konsisten (jarak antarparagraf, judul bagian, daftar dengan butir/nomor, tautan terbedakan), tanpa mengubah bagian lain halaman.
- **FR-004**: Lowongan yang sudah ada dengan deskripsi teks polos MUST tampil di halaman detail dan kartu tanpa perubahan visual yang berarti (paragraf dan jeda baris dipertahankan), tanpa admin perlu mengedit ulang.
- **FR-005**: Saat dibuka di form, deskripsi lama MUST muncul utuh di editor dan dapat disimpan ulang tanpa kehilangan teks.
- **FR-006**: Kartu lowongan di halaman Karir MUST menampilkan ringkasan teks polos (tanpa tanda format) maksimal dua baris.
- **FR-007**: Deskripsi meta halaman detail MUST berupa teks polos ringkas dari deskripsi (tanpa tanda format).
- **FR-008**: Semua konten deskripsi yang ditampilkan di halaman publik MUST dibersihkan dari skrip, atribut peristiwa, dan alamat tautan berbahaya, serta mempertahankan format yang wajar.
- **FR-009**: Perubahan deskripsi MUST langsung terlihat di halaman Karir dan detail (tidak tertahan cache).
- **FR-010**: Fitur ini MUST tidak mengubah bidang lain lowongan (judul, lokasi, tipe pekerjaan, status aktif) maupun perilaku tampil/sembunyi lowongan.
- **FR-011**: Pembaruan MUST aman dijalankan di instalasi yang sudah berisi lowongan: tidak ada data hilang dan tidak ada langkah manual bagi operator selain pembaruan standar.

### Key Entities *(include if feature involves data)*

- **Lowongan Karir**: posisi pekerjaan dengan judul, lokasi, tipe pekerjaan, status aktif, dan **deskripsi** (sebelumnya teks polos, kini teks berformat). Deskripsi lama tetap valid sebagai konten.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin dapat menyusun deskripsi lowongan dengan sedikitnya tiga jenis format (judul bagian, daftar, penekanan) dan melihatnya tampil benar di situs dalam satu kali simpan.
- **SC-002**: 100% lowongan yang sudah ada tampil di halaman Karir dan detail tanpa perbedaan paragraf atau jeda baris yang berarti setelah pembaruan.
- **SC-003**: 0 skrip, atribut peristiwa, atau tautan berbahaya dari deskripsi lolos ke halaman publik pada seluruh skenario uji.
- **SC-004**: Seluruh kartu lowongan di halaman Karir tetap seragam tingginya (dua baris ringkasan) untuk deskripsi pendek, panjang, maupun berformat.
- **SC-005**: Tidak ada lowongan yang kehilangan teks setelah dibuka dan disimpan ulang di editor.

## Assumptions

- Hanya kolom **Deskripsi** yang ada pada lowongan; kolom terpisah untuk kualifikasi atau tanggung jawab tidak ditambahkan. Admin menuliskannya sebagai judul bagian dan daftar di dalam deskripsi.
- Gaya tampil mengikuti teks isi di halaman lain (ukuran, warna, jarak); tidak ada perubahan desain pada hero, tombol, atau tata letak halaman detail.
- Tombol "Lamar Sekarang" dan alur lamaran tidak berubah.
- Pembersihan konten memakai daftar format yang sama dengan modul lain yang sudah memakai teks berformat.
- Batas panjang deskripsi mengikuti kapasitas kolom teks panjang yang ada; tidak ada batas karakter baru.
