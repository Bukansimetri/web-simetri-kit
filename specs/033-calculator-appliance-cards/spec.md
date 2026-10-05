# Feature Specification: Perapian Kartu Peralatan di Kalkulator

**Feature Branch**: `033-calculator-appliance-cards`

**Created**: 2026-10-05

**Status**: Draft

**Input**: User description: "Perapian tampilan kartu peralatan listrik pada kalkulator estimasi (metode 'Berdasarkan Peralatan') di beranda: pengatur jumlah diperkecil dan seragam di semua kartu, teks nama alat dan keterangan watt diperkecil dengan bobot huruf dikurangi, nama panjang tidak mengganggu pengatur jumlah."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Pengatur Jumlah Seragam dan Ringkas (Priority: P1)

Pengunjung yang memilih metode "Berdasarkan Peralatan" di kalkulator beranda melihat daftar kartu peralatan. Di setiap kartu, pengatur jumlah (tombol kurang, angka, tombol tambah) berukuran sama persis dan berada di posisi yang sama, tidak peduli panjang nama alat. Ukurannya lebih kecil dari sekarang sehingga kartu terlihat lebih rapi.

**Why this priority**: Ini keluhan utama: pengatur jumlah saat ini berbeda lebar antar kartu dan tampak berantakan, terutama ketika nama alat panjang.

**Independent Test**: Buka kalkulator dengan metode peralatan dan bandingkan lebar, tinggi, dan posisi pengatur jumlah di semua kartu, termasuk kartu bernama panjang ("Mesin Cuci 2 Tabung", "Kompor Listrik"). Semuanya identik.

**Acceptance Scenarios**:

1. **Given** daftar peralatan tampil di desktop (dua kolom), **When** pengunjung membandingkan semua kartu, **Then** pengatur jumlah memiliki lebar dan tinggi yang sama di setiap kartu dan menempel di sisi kanan kartu dengan jarak yang sama.
2. **Given** nama alat pendek (mis. "TV") dan nama panjang (mis. "Mesin Cuci 2 Tabung"), **Then** pengatur jumlah keduanya tidak berubah ukuran maupun posisi.
3. **Given** pengatur jumlah dibandingkan dengan tampilan sebelumnya, **Then** ukurannya lebih kecil (lebar total tidak lebih dari 80 px dan tinggi tidak lebih dari 32 px di layar desktop).
4. **Given** pengunjung menekan tombol tambah atau kurang, atau mengetik angka langsung, **Then** jumlah berubah seperti sebelumnya, tidak bisa kurang dari nol, dan hasil perhitungan direset seperti perilaku sekarang.

---

### User Story 2 - Teks Lebih Kecil dan Ringan (Priority: P1)

Nama alat dan keterangan daya (watt) pada setiap kartu tampil dengan ukuran lebih kecil dan bobot huruf lebih ringan dari sekarang (tidak lagi tebal), sehingga terlihat tenang dan seimbang dengan pengatur jumlah yang lebih kecil.

**Why this priority**: Klien meminta secara eksplisit teks diperkecil dan bobotnya dikurangi.

**Independent Test**: Bandingkan teks kartu sebelum dan sesudah: nama alat tidak lagi bold, ukuran lebih kecil, watt tetap terbaca.

**Acceptance Scenarios**:

1. **Given** kartu peralatan, **Then** nama alat tampil dengan ukuran lebih kecil dan bobot huruf lebih ringan dari sebelumnya (tidak bold/tebal), tetap lebih menonjol daripada keterangan watt.
2. **Given** keterangan watt, **Then** tetap terbaca (tidak lebih kecil dari 11 px) dan tidak lebih tebal dari nama alat.
3. **Given** teks pada kartu, **Then** kontras warna tetap memadai untuk dibaca.

---

### User Story 3 - Nama Panjang Tidak Mengganggu (Priority: P2)

Nama alat yang panjang tetap terbaca dan tidak mendorong, mengecilkan, atau menutupi pengatur jumlah. Nama boleh membungkus ke dua baris atau dipotong dengan rapi.

**Why this priority**: Pada tampilan sekarang nama panjang berdesakan dengan pengatur jumlah.

**Independent Test**: Tambahkan peralatan dengan nama sangat panjang (mis. 40 karakter) dan periksa kartunya.

**Acceptance Scenarios**:

1. **Given** nama alat yang membutuhkan dua baris, **Then** nama membungkus ke dua baris dalam ruang yang tersedia dan pengatur jumlah tidak berubah.
2. **Given** nama alat lebih panjang dari dua baris, **Then** nama dipotong dengan tanda elipsis dan nama lengkap tersedia sebagai keterangan saat disentuh atau diarahkan kursor.
3. **Given** kartu dengan ikon gambar kustom maupun ikon bawaan, **Then** ukuran ikon dan posisinya sama.

---

### User Story 4 - Rapi di Ponsel dan Desktop (Priority: P2)

Semua kartu bertinggi sama dan rapi pada layar ponsel (satu kolom) dan desktop (dua kolom). Tombol kurang dan tambah tetap nyaman disentuh di ponsel.

**Why this priority**: Pengatur jumlah yang lebih kecil tidak boleh menyulitkan penggunaan di layar sentuh.

**Independent Test**: Buka kalkulator di lebar 360 px dan 1440 px.

**Acceptance Scenarios**:

1. **Given** layar 360 px, **Then** kartu tersusun satu kolom, semua bertinggi sama, tanpa gulir horizontal.
2. **Given** layar sentuh, **Then** area yang dapat disentuh untuk setiap tombol kurang dan tambah tidak kurang dari 32 × 32 px, walau tampilannya lebih kecil.
3. **Given** layar desktop 1440 px, **Then** kartu tersusun dua kolom dengan tinggi sama.

---

### Edge Cases

- Jumlah peralatan ganjil: baris terakhir tetap rapi.
- Angka jumlah besar (mis. 99 atau 100) tetap terbaca di dalam pengatur jumlah tanpa melebarkannya.
- Nilai kosong atau bukan angka pada input jumlah: diperlakukan seperti sekarang (tidak merusak perhitungan).
- Peralatan dengan ikon gambar unggahan yang rasio gambarnya tidak persegi: tetap pas dalam kotak ikon yang sama.
- Daftar peralatan kosong: perilaku saat ini tidak berubah.
- Mode gelap/terang dan zoom peramban hingga 150%: kartu tetap terbaca dan tidak tumpang tindih.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Pengatur jumlah pada setiap kartu peralatan MUST memiliki lebar, tinggi, dan posisi yang sama persis di semua kartu, tidak bergantung pada panjang nama alat, jumlah baris nama, atau nilai jumlah.
- **FR-002**: Pengatur jumlah MUST lebih kecil dari tampilan sebelumnya: lebar total maksimal 80 px dan tinggi maksimal 32 px pada layar desktop.
- **FR-003**: Nama alat MUST tampil dengan ukuran huruf lebih kecil dan bobot huruf lebih ringan dari sebelumnya (tidak lagi bold).
- **FR-004**: Keterangan watt MUST lebih kecil atau sama dari nama alat, lebih ringan atau sama bobotnya, dan tidak lebih kecil dari 11 px.
- **FR-005**: Nama alat yang panjang MUST tidak mengubah ukuran atau posisi pengatur jumlah; nama boleh membungkus hingga dua baris dan selebihnya dipotong dengan elipsis, dengan nama lengkap tersedia sebagai keterangan.
- **FR-006**: Semua kartu peralatan MUST bertinggi sama dalam satu baris dan rapi pada layar ponsel (satu kolom, mulai 320 px) maupun desktop (dua kolom), tanpa gulir horizontal.
- **FR-007**: Area sentuh tombol kurang dan tambah MUST minimal 32 × 32 px pada layar sentuh.
- **FR-008**: Pengatur jumlah MUST tetap berfungsi seperti sebelumnya: tombol tambah/kurang mengubah jumlah, jumlah tidak bisa kurang dari nol, angka dapat diketik langsung, dan setiap perubahan mereset hasil perhitungan.
- **FR-009**: Ikon peralatan (bawaan maupun gambar kustom) MUST tampil dalam kotak ikon berukuran sama pada semua kartu.
- **FR-010**: Perubahan MUST tidak mengubah perhitungan kalkulator, daftar atau data peralatan, halaman admin, maupun bagian kalkulator lain (metode tagihan, formulir kontak, hasil).
- **FR-011**: Perubahan hanya berlaku pada kalkulator estimasi di beranda; Kalkulator Detail Sistem PLTS (yang saat ini disembunyikan di kode) tidak termasuk.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Pada semua kartu di kalkulator, selisih lebar dan tinggi pengatur jumlah antar kartu adalah 0 px, diuji pada nama alat terpendek dan terpanjang yang ada.
- **SC-002**: Ukuran pengatur jumlah turun minimal 20% dibanding sebelumnya (lebar × tinggi), dan tetap memenuhi batas FR-002.
- **SC-003**: Tidak ada nama alat yang menyebabkan pengatur jumlah berubah ukuran atau tertutup, diuji dengan nama hingga 40 karakter.
- **SC-004**: Pada lebar 320 px hingga 1440 px tidak ada gulir horizontal dan semua kartu pada satu baris bertinggi sama.
- **SC-005**: Tombol kurang dan tambah dapat disentuh dengan area minimal 32 × 32 px; pengguna dapat menambah dan mengurangi jumlah tanpa salah sentuh pada pengujian ponsel.
- **SC-006**: Hasil perhitungan kalkulator untuk set masukan yang sama identik sebelum dan sesudah perubahan.
- **SC-007**: Seluruh pengujian otomatis yang ada tetap lulus.

## Assumptions

- Lebar dan tinggi pengatur jumlah dipilih seragam dan tidak berubah antar breakpoint; batas angka pada FR-002 adalah batas maksimum, dan ukuran pastinya ditetapkan saat desain tampilan dengan mengacu ke screenshot klien.
- "Bobot lebih ringan" berarti dari bold ke medium/normal; nama alat tetap sedikit lebih menonjol daripada watt.
- Tampilan kartu secara umum (warna latar, sudut, jarak antar kartu, tinggi kartu) dipertahankan; hanya isi kartu yang dirapikan.
- Nama alat penuh tersedia sebagai teks keterangan (tooltip) ketika dipotong.
- Tidak ada perubahan pada data peralatan atau pengaturan di admin.
