# Feature Specification: Demo Content Seeder

**Feature Branch**: `019-demo-content-seeder`

**Created**: 2026-09-13

**Status**: Draft

**Input**: User description: "Seeder dummy content demo (services, team, testimonials, portfolio) untuk showcase ke calon klien (AMC-229)."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Mengisi instalasi demo dengan konten contoh yang realistis (Priority: P1)

Seorang sales/ops ingin menunjukkan Web Solarpanel Kit ke calon klien. Alih-alih mendemokan situs kosong tanpa isi (yang terlihat tidak meyakinkan), ia menjalankan satu perintah untuk mengisi instalasi demo dengan konten contoh yang realistis di seluruh modul utama yang dilihat calon klien pertama kali: Layanan/Produk, Tim, Testimoni, dan Portfolio/Showcase Proyek — sehingga situs demo langsung terlihat lengkap dan meyakinkan tanpa perlu mengetik konten satu per satu.

**Why this priority**: Ini inti dari kebutuhan AMC-229 — tanpa konten contoh yang realistis di keempat modul ini, demo ke calon klien tidak dapat dilakukan secara efektif, yang berdampak langsung pada proses penjualan/onboarding.

**Independent Test**: Pada instalasi baru dengan database kosong, jalankan perintah pengisian konten demo; verifikasi setiap modul (Layanan, Tim, Testimoni, Portfolio) menampilkan beberapa entri contoh yang realistis saat halaman publik terkait dibuka.

**Acceptance Scenarios**:

1. **Given** instalasi baru dengan database kosong, **When** konten demo diisi, **Then** halaman publik Layanan/Produk menampilkan beberapa contoh layanan dengan nama, deskripsi, dan gambar yang realistis.
2. **Given** konten demo telah diisi, **When** halaman Tentang Kami/Tim dibuka, **Then** beberapa anggota tim contoh (nama, jabatan, bio singkat) tampil.
3. **Given** konten demo telah diisi, **When** halaman yang menampilkan testimoni dibuka, **Then** beberapa testimoni contoh (nama, rating, isi ulasan) tampil.
4. **Given** konten demo telah diisi, **When** halaman Portfolio/Showcase Proyek dibuka, **Then** beberapa contoh proyek dengan kategori dan gambar tampil.

---

### User Story 2 - Konten demo tidak pernah tercampur ke instalasi produksi klien (Priority: P1)

Seorang developer/ops yang sedang men-setup instalasi **produksi** untuk klien sesungguhnya (bukan demo penjualan) tidak ingin konten contoh/dummy ini muncul di situs klien. Konten demo harus merupakan langkah terpisah dan eksplisit, bukan sesuatu yang otomatis ikut terisi saat menjalankan setup instalasi standar.

**Why this priority**: Konten dummy yang bocor ke situs klien yang sudah live (mis. testimoni palsu atau nama tim fiktif) merusak kredibilitas produk yang dijual ke klien dan berpotensi menimbulkan masalah kontrak/kepercayaan — risiko ini harus dicegah sejak desain, sama pentingnya dengan tersedianya konten demo itu sendiri.

**Independent Test**: Jalankan proses setup instalasi standar (tanpa secara eksplisit meminta konten demo); verifikasi tidak ada satu pun konten contoh/dummy yang muncul di keempat modul tersebut.

**Acceptance Scenarios**:

1. **Given** instalasi baru disiapkan lewat proses setup standar tanpa instruksi eksplisit untuk mengisi konten demo, **When** halaman publik modul-modul tersebut dibuka, **Then** modul tampil kosong (atau kondisi default tanpa data), bukan berisi konten contoh.
2. **Given** operator ingin mengisi konten demo, **When** ia menjalankan langkah pengisian konten demo secara eksplisit dan terpisah, **Then** konten demo baru muncul setelah langkah tersebut dijalankan.

---

### User Story 3 - Membersihkan konten demo sebelum go-live (Priority: P2)

Setelah demo ke calon klien selesai dan klien memutuskan lanjut menjadi pelanggan, developer/ops perlu membersihkan seluruh konten contoh dari instalasi tersebut sebelum diserahkan sebagai situs produksi, tanpa harus menghapus satu per satu secara manual lewat admin panel.

**Why this priority**: Ini melengkapi siklus hidup konten demo (isi → tunjukkan → bersihkan) sehingga instalasi yang tadinya dipakai demo bisa langsung dipakai produksi tanpa kerja manual berulang, meski ini kasus sekunder dibanding kemampuan mengisi (P1) dan mencegah kebocoran otomatis (P1).

**Independent Test**: Pada instalasi yang sudah berisi konten demo, jalankan langkah pembersihan konten demo; verifikasi keempat modul kembali kosong tanpa memengaruhi data lain yang mungkin sudah ditambahkan admin secara manual.

**Acceptance Scenarios**:

1. **Given** instalasi berisi konten demo di keempat modul, **When** operator menjalankan langkah pembersihan konten demo, **Then** seluruh entri contoh di keempat modul tersebut terhapus.
2. **Given** admin telah menambahkan satu entri asli miliknya sendiri (bukan bagian dari data demo) di salah satu modul, **When** pembersihan konten demo dijalankan, **Then** entri milik admin tersebut tidak ikut terhapus.

---

### Edge Cases

- Apa yang terjadi bila pengisian konten demo dijalankan dua kali berturut-turut? Sistem tidak boleh menghasilkan duplikasi entri contoh yang sama persis berulang kali.
- Bagaimana jika modul tertentu (mis. Portfolio) sudah dinonaktifkan/tidak dipakai untuk instalasi tersebut? Pengisian konten demo untuk modul yang nonaktif tidak boleh menyebabkan error, cukup dilewati atau tetap diisi datanya secara tersimpan (tanpa terlihat) sesuai perilaku modul saat nonaktif.
- Bagaimana jika pembersihan konten demo dijalankan pada instalasi yang tidak pernah diisi konten demo? Proses harus selesai tanpa error, tidak melakukan apa pun.
- Bagaimana jika ada relasi antar modul dalam data demo (mis. kategori portfolio dipakai proyek portfolio)? Pengisian maupun pembersihan harus menjaga konsistensi relasi tsb (tidak meninggalkan referensi rusak/kategori yatim).

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Sistem MUST menyediakan cara untuk mengisi konten contoh (demo) yang realistis ke modul Layanan/Produk, Tim, Testimoni, dan Portfolio/Showcase Proyek sekaligus dalam satu langkah eksplisit.
- **FR-002**: Konten demo pada setiap modul MUST cukup lengkap untuk ditampilkan secara wajar di halaman publik terkait (mis. Layanan MUST memiliki nama, deskripsi, dan gambar; Tim MUST memiliki nama, jabatan, dan bio singkat; Testimoni MUST memiliki nama pemberi testimoni, rating, dan isi ulasan; Portfolio MUST memiliki nama proyek, kategori, dan gambar).
- **FR-003**: Pengisian konten demo MUST TIDAK berjalan otomatis sebagai bagian dari proses setup instalasi standar (lihat fitur "Setup Client Command", 018-setup-client-command) — harus merupakan langkah terpisah dan eksplisit yang harus sengaja dijalankan operator.
- **FR-004**: Sistem MUST menyediakan cara terpisah untuk membersihkan/menghapus seluruh konten demo dari keempat modul tersebut tanpa memengaruhi entri lain yang bukan bagian dari data demo.
- **FR-005**: Menjalankan pengisian konten demo lebih dari sekali MUST TIDAK menghasilkan duplikasi entri contoh yang identik.
- **FR-006**: Sistem MUST menjaga konsistensi relasi antar data demo (mis. kategori yang dipakai entri portfolio contoh) baik saat pengisian maupun pembersihan.
- **FR-007**: Konten demo MUST menggunakan bahasa dan konteks bisnis yang relevan dengan domain kit ini (produk/energi surya) sehingga terlihat kredibel saat didemokan, bukan teks placeholder generik.

### Key Entities

- **Layanan/Produk contoh**: Representasi item yang ditawarkan (nama, deskripsi singkat & lengkap, gambar, kategori) — memakai struktur modul Layanan/Produk yang sudah ada.
- **Anggota Tim contoh**: Representasi orang dalam organisasi (nama, jabatan, bio singkat, foto) — memakai struktur modul Tim yang sudah ada.
- **Testimoni contoh**: Representasi ulasan pelanggan (nama pemberi, atribusi/afiliasi, rating, isi ulasan) — memakai struktur modul Testimoni yang sudah ada.
- **Proyek Portfolio contoh** (beserta **Kategori Portfolio contoh** yang menaunginya): Representasi hasil kerja yang ditampilkan sebagai showcase (nama proyek, kategori, gambar, deskripsi) — memakai struktur modul Portfolio yang sudah ada.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Sales/ops dapat menyiapkan instalasi demo yang terlihat lengkap di keempat modul (Layanan, Tim, Testimoni, Portfolio) dalam satu langkah, dalam waktu kurang dari 1 menit.
- **SC-002**: 100% instalasi yang disiapkan lewat proses setup standar (tanpa instruksi eksplisit mengisi demo) tidak mengandung satu pun konten contoh/dummy di keempat modul tersebut.
- **SC-003**: Instalasi yang sebelumnya dipakai demo dapat dibersihkan dari seluruh konten contoh dalam satu langkah, tanpa kehilangan data asli yang sudah ditambahkan admin secara terpisah — diverifikasi 100% pada pengujian.
- **SC-004**: Mengisi ulang konten demo berkali-kali pada instalasi yang sama tidak pernah menghasilkan entri duplikat yang identik.

## Assumptions

- "Layanan" pada judul fitur merujuk pada modul Layanan/Produk yang sudah ada di kit ini (sebelumnya diimplementasikan sebagai modul "Produk" pada AMC-207), bukan modul terpisah baru.
- Konten demo yang sudah ada untuk modul Layanan/Produk dan Testimoni saat ini tergabung langsung ke dalam proses seeding standar proyek (`database/seeders/DatabaseSeeder.php`); pekerjaan ini MENCAKUP memisahkannya ke jalur pengisian konten demo yang eksplisit sesuai FR-003, agar konsisten dengan seluruh modul lain yang disebut fitur ini (Tim, Portfolio) dan dengan standar deployment yang berlaku untuk kit ini.
- Operator yang menjalankan pengisian/pembersihan konten demo adalah developer/ops lewat command-line saat provisioning/dekomisioning instalasi demo, bukan pengguna akhir lewat antarmuka web.
- Modul Portfolio dan Tim yang dipakai fitur ini adalah struktur data yang sudah ada di kit ini (masing-masing sudah punya CRUD admin sendiri); fitur ini hanya menambahkan data contoh, tidak mengubah struktur/model modul tersebut.
- Jumlah entri contoh per modul tidak dispesifikasikan secara ketat oleh bisnis; cukup untuk membuat setiap halaman publik terkait terlihat wajar dan tidak kosong (indikatif: beberapa hingga sekitar selusin entri per modul, mengikuti pola entri contoh yang sudah ada di kit ini).
