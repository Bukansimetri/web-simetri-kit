# Feature Specification: Modul Client Logos

**Feature Branch**: `009-client-logos-module`

**Created**: 2026-09-07

**Status**: Draft

**Input**: User description: "AMC-211: Modul Client Logos — CRUD admin (Filament Resource) untuk logo strip perusahaan partner/klien: nama perusahaan, logo (gambar), URL tautan opsional, urutan tampil, toggle aktif. Ditampilkan sebagai logo strip di halaman publik. Pola identik modul konten lain yang sudah ada (Testimonials, Career, Custom Page): satu Filament Resource + rendering di Blade section, tanpa dependency baru. Section otomatis tidak dirender bila tidak ada logo aktif. Prioritas: social proof 'dipercaya oleh' di halaman publik."

## Clarifications

### Session 2026-09-07

- Q: Di halaman mana logo strip ditampilkan? → A: Halaman "Tentang Kami" (`/tentang-kami`), setelah section testimoni dan sebelum CTA band penutup. Konsisten dengan modul Testimonials (008); beranda tidak disentuh.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Admin mengelola logo klien/partner (Priority: P1) 🎯 MVP

Admin membuka menu Logo Klien di panel admin, menambah entri baru (nama perusahaan, unggah file logo, URL tautan opsional), mengatur urutan tampil dan status aktif, lalu menyimpannya. Admin juga bisa mengedit dan menghapus entri yang sudah ada.

**Why this priority**: Tanpa CRUD dasar, tidak ada cara bagi non-developer untuk mengelola daftar logo "dipercaya oleh" — inti tiket.

**Independent Test**: Login sebagai admin, tambah 4 logo (satu tanpa URL tautan), atur urutan dan aktif/nonaktif, verifikasi daftar panel menampilkan semuanya sesuai urutan; edit satu entri; hapus satu entri dengan konfirmasi, verifikasi hilang dari daftar.

**Acceptance Scenarios**:

1. **Given** admin di form tambah logo, **When** admin mengisi nama perusahaan, mengunggah file logo, dan menyimpan tanpa URL tautan, **Then** entri tersimpan dan tampil di daftar panel.
2. **Given** admin di form tambah logo, **When** admin mengisi URL tautan lalu menyimpan, **Then** URL tersimpan dan terasosiasi dengan logo tersebut.
3. **Given** admin mencoba menyimpan entri tanpa nama perusahaan atau tanpa file logo, **When** admin menyimpan, **Then** sistem menolak dengan pesan validasi per field.
4. **Given** admin mengisi URL tautan dengan format yang tidak valid, **When** admin menyimpan, **Then** sistem menolak dengan pesan error.
5. **Given** admin mengedit entri yang sudah ada, **When** admin mengubah nama/logo/URL/urutan/status dan menyimpan, **Then** perubahan tersimpan.
6. **Given** admin menghapus entri, **When** admin mengonfirmasi penghapusan, **Then** entri terhapus dan tidak lagi muncul di daftar maupun di halaman publik.

---

### User Story 2 - Pengunjung melihat logo strip "dipercaya oleh" (Priority: P1)

Pengunjung yang membuka halaman "Tentang Kami" melihat satu baris/strip logo perusahaan partner/klien yang dikelola admin — memberi bukti sosial bahwa produk/jasa dipercaya oleh perusahaan nyata.

**Why this priority**: Tujuan bisnis tiket — menampilkan social proof yang kredibel dan bisa diperbarui tanpa developer.

**Independent Test**: Dengan beberapa logo aktif dan nonaktif dibuat via User Story 1, buka halaman Tentang Kami, verifikasi hanya logo aktif yang tampil dalam urutan yang ditetapkan admin; logo dengan URL tautan bisa diklik menuju URL tsb; nonaktifkan semua logo, verifikasi strip tidak muncul sama sekali (tidak ada area/heading kosong).

**Acceptance Scenarios**:

1. **Given** ada 4 logo aktif dengan urutan 1–4, **When** pengunjung membuka halaman Tentang Kami, **Then** keempatnya tampil di logo strip sesuai urutan tersebut.
2. **Given** sebuah logo berstatus nonaktif, **When** pengunjung membuka halaman tsb, **Then** logo tersebut tidak tampil.
3. **Given** sebuah logo memiliki URL tautan, **When** pengunjung mengklik logo tsb, **Then** pengunjung diarahkan ke URL tautan di tab baru.
4. **Given** sebuah logo tanpa URL tautan, **When** pengunjung melihat logo tsb, **Then** logo tampil sebagai gambar biasa tanpa tautan (tidak bisa diklik).
5. **Given** tidak ada logo aktif sama sekali, **When** pengunjung membuka halaman tsb, **Then** logo strip tidak dirender (halaman tetap rapi tanpa area kosong) dan section lain tetap utuh.

---

### Edge Cases

- Dua logo memiliki nilai urutan yang sama → ditampilkan berdampingan dengan urutan sekunder yang stabil (mis. berdasarkan waktu dibuat), tanpa error.
- File logo dengan rasio/ukuran tidak seragam → ditampilkan dalam tinggi yang konsisten (di-fit), tidak merusak tata letak strip.
- File logo yang dihapus dari storage secara manual → slot logo tsb tidak menampilkan gambar rusak (disembunyikan atau placeholder netral).
- Jumlah logo aktif banyak (mis. > 12) → strip tetap rapi (membungkus ke baris berikutnya atau menggeser), tanpa merusak layout halaman.
- URL tautan tanpa skema (mis. "contoh.com" tanpa "https://") → ditolak saat validasi atau dinormalisasi; tidak menghasilkan tautan relatif yang salah arah.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Admin panel MUST menyediakan menu CRUD Logo Klien — tambah, daftar, edit, hapus.
- **FR-002**: Setiap entri logo MUST memiliki: nama perusahaan (wajib), file logo (wajib), URL tautan (opsional), nilai urutan tampil, dan status aktif/nonaktif.
- **FR-003**: Sistem MUST memvalidasi field wajib (nama perusahaan, file logo) — submit tanpa salah satunya MUST ditolak dengan pesan error per field.
- **FR-004**: Jika URL tautan diisi, sistem MUST memvalidasinya sebagai URL absolut yang valid (berskema http/https) — nilai tidak valid MUST ditolak.
- **FR-005**: Admin MUST bisa menetapkan nilai urutan tampil per entri; halaman publik MUST menampilkan logo aktif diurutkan berdasarkan nilai urutan tsb (menaik), dengan urutan sekunder yang deterministik saat nilai urutan sama.
- **FR-006**: Admin MUST bisa mengaktifkan/menonaktifkan tiap entri; hanya entri berstatus aktif yang tampil di halaman publik.
- **FR-007**: Admin MUST bisa menghapus entri dengan konfirmasi terlebih dahulu; setelah dihapus, entri MUST hilang dari daftar admin dan halaman publik.
- **FR-008**: Perubahan entri (tambah/edit/hapus/aktif/urutan) MUST tercermin di halaman publik pada request berikutnya tanpa deploy ulang.
- **FR-009**: Logo strip di halaman publik MUST menampilkan, per entri aktif: gambar logo dengan teks alternatif memakai nama perusahaan; jika entri punya URL tautan, logo MUST menjadi tautan yang membuka URL tsb di tab baru; jika tidak, logo MUST tampil sebagai gambar biasa.
- **FR-010**: Jika tidak ada logo aktif, halaman publik MUST tidak merender logo strip sama sekali (tanpa area/heading kosong), konsisten dengan pola modul opsional lain; section lain di halaman tsb MUST tidak terpengaruh.
- **FR-011**: CRUD Logo Klien MUST terbuka untuk semua role yang memiliki akses panel admin, konsisten dengan resource admin lain yang sudah ada.
- **FR-012**: Logo strip MUST ditempatkan di halaman "Tentang Kami" (`/tentang-kami`), setelah section testimoni (modul 008) dan sebelum CTA band penutup. Beranda dan halaman lain TIDAK menampilkan logo strip di v1.

### Key Entities *(include if feature involves data)*

- **Client Logo**: satu logo perusahaan partner/klien. Atribut: nama perusahaan (wajib), file logo/gambar (wajib), URL tautan (opsional, absolut http/https), urutan tampil (angka), status aktif (boolean). Tidak berelasi ke entity lain.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin dapat menambahkan satu logo (nama + file) dan melihatnya tampil di halaman publik dalam satu kali proses simpan, tanpa bantuan developer atau deploy ulang.
- **SC-002**: Admin dapat menyusun ulang urutan logo dan perubahannya terlihat di halaman publik pada refresh berikutnya.
- **SC-003**: 100% percobaan submit dengan data tidak valid (field wajib kosong, URL tautan tidak valid) ditolak dengan pesan error yang jelas.
- **SC-004**: 100% logo berstatus nonaktif atau terhapus tidak muncul di halaman publik.
- **SC-005**: Saat tidak ada logo aktif, halaman Tentang Kami tetap tampil rapi tanpa area logo strip kosong, dan section lain tetap utuh.
- **SC-006**: 100% logo dengan URL tautan mengarahkan pengunjung ke URL yang benar di tab baru saat diklik.

## Assumptions

- Logo strip adalah SATU section tampilan tetap (baris logo yang di-grayscale/fit dengan tinggi seragam) — bukan carousel/slider wajib; perilaku menggeser/membungkus baris adalah pilihan implementasi tampilan, bukan requirement.
- File logo mengikuti perilaku upload gambar yang sudah dipakai modul lain di project ini (penyimpanan gambar seperti pada modul Testimonials/Artikel); tidak ada persyaratan dimensi/rasio khusus yang divalidasi keras. Format transparan (PNG/SVG) direkomendasikan tetapi tidak dipaksakan.
- Tidak ada toggle modul tingkat-atas (aktif/nonaktif seluruh modul) — mekanisme "sembunyikan" cukup lewat menonaktifkan/menghapus tiap entri; bila semua nonaktif, strip otomatis tidak dirender (FR-010).
- Tidak ada kategori/pengelompokan logo (mis. "partner" vs "klien") di v1 — satu daftar flat.
- Tidak ada teks/heading yang bisa dikonfigurasi admin untuk strip (mis. judul "Dipercaya oleh") di v1 — heading section (bila ada) bersifat statis di template.
- Logo tanpa URL tautan tidak bisa diklik; tidak ada perilaku modal/lightbox.
- Halaman "Tentang Kami" hanya ditambah satu section logo strip baru (setelah section testimoni modul 008, sebelum CTA band); seluruh section eksisting tidak diubah. Beranda tidak disentuh.
- Modul Testimonials (spec 008) sudah lebih dulu menyisipkan section testimoni di halaman Tentang Kami; modul ini menempatkan logo strip tepat setelahnya.
