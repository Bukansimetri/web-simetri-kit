# Feature Specification: Theme & Branding System

**Feature Branch**: `002-theme-branding-system`

**Created**: 2026-08-17

**Status**: Draft

**Input**: User description: "Epic 4 - Theme & Branding System. Memperluas Brand Settings dari Epic 2 (app_name, primary_color) menjadi skema tema penuh (secondary color, font, OG image) yang dipakai lewat CSS variable, dengan beberapa varian komponen Blade per section (hero, about, dst.) yang bisa dipilih admin per instalasi klien tanpa redeploy, sesuai prinsip constitution 'White-Label by Default' dan 'Settings-Driven Theming, No Page Builder'." Diperbarui 2026-09-01 setelah mockup desain lengkap ("Luminous Azure" untuk brand SUOER, 8 halaman) tersedia di `public/mockup-html/` — lihat Clarifications untuk pivot scope.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Pengunjung melihat Home & Produk dengan desain final SUOER (Priority: P1) 🎯 MVP

Pengunjung situs membuka halaman Home dan langsung melihat desain company profile SUOER yang sudah jadi (bukan placeholder starter kit generik) — termasuk kalkulator estimasi hemat listrik. Pengunjung juga bisa menjelajah daftar Produk dan halaman Detail Produk dengan tampilan yang sama profesionalnya, memakai data contoh sementara sambil modul CRUD Produk (Epic 3) belum selesai.

**Why this priority**: Home dan Produk adalah halaman yang paling menentukan kesan pertama dan nilai bisnis inti (jualan produk solar panel). Tanpa ini, situs masih terlihat seperti starter kit kosong.

**Independent Test**: Buka `/` dan halaman daftar/detail Produk tanpa login, verifikasi struktur & elemen visual sesuai mockup (`home_suoer_html_calculator_results`, `produk_suoer_luminous_azure`, `produk_detail_suoer_header_aligned`), verifikasi kalkulator di Home bisa dipakai dan menampilkan hasil estimasi tanpa reload halaman.

**Acceptance Scenarios**:

1. **Given** pengunjung membuka halaman Home, **When** halaman selesai dimuat, **Then** seluruh section (hero, kalkulator, dst.) tampil sesuai mockup dengan warna/font dari Theme Settings (default Luminous Azure).
2. **Given** pengunjung mengisi kalkulator estimasi (mode "per alat" atau "per tagihan"), **When** pengunjung submit input, **Then** hasil estimasi tampil di halaman yang sama tanpa reload penuh.
3. **Given** pengunjung membuka daftar Produk, **When** pengunjung memilih salah satu produk, **Then** halaman Detail Produk terkait tampil dengan data & layout sesuai mockup.

---

### User Story 2 - Pengunjung mengakses halaman pendukung company profile (Priority: P2)

Pengunjung dapat membuka halaman Tentang Kami, Kontak, Karir, Artikel, dan FAQ, masing-masing dengan tampilan sesuai mockup dan data contoh sementara. Di halaman Kontak, pengunjung bisa mengisi dan memvalidasi form, meski pengiriman sungguhan (simpan + notifikasi) belum aktif di fitur ini.

**Why this priority**: Melengkapi kesan profesional company profile di luar halaman inti bisnis (Home/Produk), tapi tidak seblocking US1 untuk MVP awal.

**Independent Test**: Buka tiap halaman (Tentang Kami, Kontak, Karir, Artikel, FAQ) tanpa login, verifikasi struktur sesuai mockup masing-masing. Di Kontak, isi form dengan data tidak valid → verifikasi pesan validasi muncul; isi dengan data valid dan submit → verifikasi tidak ada error teknis yang tampil ke pengguna meski data belum benar-benar tersimpan/terkirim.

**Acceptance Scenarios**:

1. **Given** pengunjung membuka salah satu halaman pendukung (Tentang Kami/Karir/Artikel/FAQ), **When** halaman dimuat, **Then** konten & layout tampil sesuai mockup terkait dengan data contoh.
2. **Given** pengunjung mengisi form Kontak dengan field wajib kosong/format salah, **When** submit ditekan, **Then** sistem menampilkan pesan validasi yang jelas tanpa mengirim data.
3. **Given** pengunjung mengisi form Kontak dengan data valid, **When** submit ditekan, **Then** sistem menampilkan konfirmasi yang wajar ke pengguna (tanpa error), meski penyimpanan/notifikasi sungguhan belum diimplementasikan di fitur ini.

---

### User Story 3 - Admin menyesuaikan warna sekunder, font, dan OG image brand (Priority: P3)

Admin membuka Theme Settings di panel admin dan melihat nilai default sudah terisi mengikuti desain Luminous Azure (warna sekunder, font heading & body, OG image). Admin dapat mengubah nilai-nilai ini agar instalasi klien lain (bukan SUOER) bisa memakai identitas visual sendiri tanpa developer mengubah kode.

**Why this priority**: Memastikan Epic 4 tetap memenuhi Principle I & II constitution (multi-client reusability, white-label) — desain Luminous Azure adalah default yang bagus untuk SUOER, tapi harus tetap bisa diganti untuk klien berikutnya. Prioritas P3 karena SUOER sendiri sudah terlayani baik oleh nilai default tanpa perlu admin mengubah apa pun dulu.

**Independent Test**: Login sebagai admin, buka Theme Settings, verifikasi nilai default (warna sekunder, font heading/body, OG image) sudah terisi sesuai Luminous Azure. Ubah warna sekunder & font, simpan, reload halaman publik manapun, verifikasi perubahan tampil. Upload OG image baru, verifikasi meta tag `og:image` berubah.

**Acceptance Scenarios**:

1. **Given** instalasi baru belum pernah diubah adminnya, **When** admin membuka Theme Settings, **Then** field warna sekunder, font heading, font body, dan OG image sudah terisi nilai default Luminous Azure.
2. **Given** admin mengubah warna sekunder dan/atau font, **When** admin menyimpan, **Then** seluruh halaman publik (8 halaman) ikut berubah tanpa redeploy.
3. **Given** admin mengupload OG image baru, **When** admin menyimpan, **Then** meta tag `og:image` halaman publik yang belum punya OG image spesifik menunjuk ke gambar baru tsb.

### Edge Cases

- Apa yang terjadi jika data seed untuk sebuah modul (Produk/Artikel/Karir/FAQ) kosong? Halaman terkait MUST menampilkan empty state yang wajar (bukan error/500 atau layout rusak).
- Apa yang terjadi jika admin mengupload file OG image dengan format/ukuran tidak valid? Sistem MUST menolak dengan pesan error jelas, konsisten dengan validasi upload logo/favicon di Epic 2.
- Apa yang terjadi saat admin mengosongkan kembali warna sekunder/font setelah sebelumnya diisi? Sistem MUST kembali ke default Luminous Azure, bukan menyisakan nilai kosong yang merusak tampilan.
- Bagaimana kalkulator Home menangani input tidak wajar (misal 0 atau negatif)? Sistem MUST menampilkan pesan validasi, bukan hasil estimasi yang salah/NaN.
- Bagaimana kontras warna sekunder terhadap teks (aksesibilitas)? Di luar scope validasi otomatis untuk v1 — dicatat sebagai batasan, bukan requirement.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Sistem MUST merender 8 halaman publik — Home, Produk (list), Produk Detail, Tentang Kami, Kontak, Karir, Artikel, dan FAQ — sesuai struktur & konten pada mockup di `public/mockup-html/`, menggunakan data contoh/seed sementara untuk konten yang biasanya berasal dari modul Epic 3.
- **FR-002**: Styling seluruh halaman (warna, font, radius, spacing) MUST diambil dari Theme Settings via CSS variable, bukan di-hardcode di Blade/CSS per halaman.
- **FR-003**: Admin panel MUST menyediakan halaman Theme Settings untuk mengatur warna sekunder, font heading, font body, dan OG image default, melengkapi Brand Settings (nama, logo, favicon, warna primer) dari Epic 2.
- **FR-004**: Pemilihan font (heading maupun body) MUST dibatasi ke dropdown daftar kurasi (Google Fonts umum, termasuk Manrope & Be Vietnam Pro) — admin tidak bisa input nama/URL font bebas.
- **FR-005**: Ketika Theme Settings belum diisi admin, sistem MUST menggunakan nilai default Luminous Azure (warna sekunder, font heading/body, OG image) sehingga situs tetap tampil utuh sejak instalasi pertama kali.
- **FR-006**: Kalkulator estimasi hemat listrik di Home MUST berjalan sepenuhnya di sisi client (tanpa memerlukan penyimpanan data ke server) dan MUST menampilkan hasil tanpa reload halaman penuh.
- **FR-007**: Form Kontak MUST menampilkan seluruh field sesuai mockup dan MUST melakukan validasi input dasar di sisi client; pengiriman sungguhan (penyimpanan ke database dan notifikasi email/WA) MUST NOT diimplementasikan dalam fitur ini (ditunda ke tiket Epic 3 terpisah, AMC-216).
- **FR-008**: Struktur data seed untuk Produk, Artikel, Karir, dan FAQ MUST dirancang agar modul CRUD Epic 3 di masa depan (AMC-207, AMC-213, AMC-212, dst.) dapat menggantikan sumber data tanpa mengubah Blade/tampilan.
- **FR-009**: Admin MUST bisa mengupload/mengganti/menghapus OG image default lewat Theme Settings, dengan validasi tipe file gambar yang sama seperti upload logo/favicon.
- **FR-010**: Halaman publik yang tidak memiliki OG image spesifik MUST menggunakan OG image default dari Theme Settings; jika belum diatur admin, MUST menggunakan gambar default Luminous Azure.
- **FR-011**: Sistem tidak lagi menyediakan mekanisme "pilih varian tampilan per section" (hero-v1/v2, dsb.) di fitur ini — satu desain Luminous Azure adalah tampilan default final untuk semua section pada v1.

### Key Entities

- **Theme Settings**: Perluasan dari Brand Settings (Epic 2) — menambahkan warna sekunder, font heading, font body, dan OG image default per instalasi; nilai default = token desain Luminous Azure.
- **Product (seed)**: Representasi produk solar panel untuk halaman Produk/Produk Detail (nama, deskripsi, spesifikasi, harga, gambar) — struktur minimal, siap digantikan modul CRUD Epic 3.
- **Article (seed)**: Representasi artikel/blog untuk halaman Artikel (judul, ringkasan, konten, gambar, tanggal terbit).
- **Job Opening (seed)**: Representasi lowongan kerja untuk halaman Karir (judul posisi, lokasi, tipe kerja, deskripsi).
- **FAQ Item (seed)**: Representasi pertanyaan & jawaban untuk halaman FAQ (pertanyaan, jawaban, kategori opsional).

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Kedelapan halaman publik dapat diakses dan menampilkan struktur/konten sesuai mockup referensi dalam satu kali kunjungan, tanpa elemen rusak atau placeholder starter kit generik.
- **SC-002**: Admin dapat mengubah warna sekunder, font, atau OG image dan melihat perubahan tampil di seluruh halaman publik dalam satu kali reload, tanpa bantuan developer atau deploy ulang.
- **SC-003**: 100% instalasi baru yang belum mengatur Theme Settings tetap menampilkan tampilan Luminous Azure yang utuh (tidak ada elemen rusak/kosong akibat pengaturan tema kosong).
- **SC-004**: Pengunjung dapat mengisi & memvalidasi form Kontak tanpa menemui error teknis, meski penyimpanan/notifikasi sungguhan belum aktif.
- **SC-005**: Pengunjung dapat memperoleh hasil estimasi dari kalkulator Home dalam satu kali interaksi, tanpa reload halaman.

## Clarifications

### Session 2026-08-17

- Q: Section mana saja yang masuk scope v1, dan berapa varian per section? → A: Semua section utama company profile (Hero, About, Services, Portfolio, Team, Testimonials), minimal 2 varian masing-masing. *(Digantikan oleh keputusan sesi 2026-09-01 — lihat FR-011.)*
- Q: Bagaimana admin memilih font? → A: Dropdown dari daftar kurasi (6–10 Google Fonts umum), tidak ada input bebas.
- Q: Apakah live preview (lihat perubahan sebelum disimpan) termasuk scope fitur ini? → A: Tidak — deferred, keluar dari scope spec ini sepenuhnya (AMC-222 tetap "opsional, nice-to-have", jadi feature/issue terpisah bila dibutuhkan nanti).

### Session 2026-09-01

Konteks: mockup desain lengkap ("Luminous Azero"/Luminous Azure untuk brand SUOER, 8 halaman HTML + `DESIGN.md`) tersedia di `public/mockup-html/`, mengubah beberapa asumsi sesi sebelumnya.

- Q: Bagaimana hubungan mockup Luminous Azure dengan konsep "varian per section" dari sesi sebelumnya? → A: Mockup menggantikan seluruh konsep varian — v1 hanya mengimplementasikan 1 desain (Luminous Azure) apa adanya, tanpa sistem pilih-varian (lihat FR-011).
- Q: Token desain di `DESIGN.md` (warna, font Manrope/Be Vietnam Pro, dst.) statusnya apa untuk starter kit? → A: Jadi default value Theme Settings (FR-005), tetap bisa diganti admin per instalasi klien lain — bukan hardcode permanen.
- Q: Apakah Epic 4 mengimplementasikan seluruh 8 halaman mockup (termasuk logic modul Produk/Artikel/Karir/dst.), atau hanya fondasi visual? → A: Seluruh 8 halaman diimplementasikan di fitur ini (FR-001); Epic 3 dianggap ter-absorb untuk sisi tampilan publiknya.
- Q: Apakah implementasi 8 halaman termasuk CRUD admin penuh (Filament Resource) per modul, atau render halaman dulu? → A: Render halaman publik dulu pakai data seed/statis (FR-001, FR-008); CRUD admin per modul (AMC-207/213/212/dst.) jadi increment terpisah setelah ini.
- Q: Apakah form Kontak harus benar-benar mengirim data (simpan + notifikasi)? → A: Tidak untuk fitur ini — form tampil & tervalidasi (FR-007), submit sungguhan ditunda ke AMC-216.

## Assumptions

- Fitur ini memperluas `BrandSettings` (atau setara) yang sudah ada dari Epic 2, bukan membuat sistem settings terpisah — konsisten dengan Principle III constitution (Settings-Driven Theming).
- Font Theme Settings dipecah menjadi dua peran (heading & body) mengikuti pasangan Manrope/Be Vietnam Pro pada mockup, tetap dalam bentuk dropdown kurasi (bukan input bebas) — perluasan wajar dari keputusan "dropdown kurasi" di sesi klarifikasi sebelumnya, bukan kontradiksi.
- Data seed untuk Produk, Artikel, Karir, dan FAQ bersifat sementara/contoh; CRUD admin penuh untuk modul-modul ini adalah pekerjaan lanjutan terpisah (referensi tiket Linear AMC-207, AMC-213, AMC-212, AMC-217, AMC-234) dan tidak diblokir oleh fitur ini.
- Form Kontak dan modul lain yang butuh integrasi eksternal (notifikasi WA/email) di luar scope; keputusan integrasi tsb menyusul di AMC-216.
- Live preview (AMC-222) tetap di luar scope spec ini; dapat diajukan sebagai feature terpisah setelah rilis fitur ini.
- Validasi kontras warna untuk aksesibilitas tidak termasuk scope v1 (dicatat sebagai edge case, bukan requirement).
