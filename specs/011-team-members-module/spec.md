# Feature Specification: Modul Team Members

**Feature Branch**: `011-team-members-module`

**Created**: 2026-09-08

**Status**: Draft

**Input**: User description: "AMC-209: Modul Team Members — CRUD admin (Filament Resource) untuk anggota tim: nama, jabatan, foto, bio singkat, social link (opsional, mis. LinkedIn/Instagram), urutan tampil, toggle aktif. Ditampilkan sebagai section 'Tim Kami' di halaman Tentang Kami. Pola identik modul Testimonials (008) dan Client Logos (009): satu Filament Resource + rendering di Blade section, tanpa dependency baru. Foto di-resize + konversi WebP (pakai ImageUploads::storeAsWebp dengan maxWidth seperti modul Portfolio 010). Section otomatis tidak dirender bila tidak ada anggota aktif. Menutup sebagian Epic 3 (AMC-192)."

## Clarifications

### Session 2026-09-08

- Q: Struktur social link anggota tim? → A: Satu field `linkedin_url` saja, opsional (nullable). Boleh dikosongkan; bila diisi harus URL absolut http/https. Platform sosial lain di luar scope v1.
- Q: Penempatan section "Tim Kami" di halaman Tentang Kami? → A: Setelah section "Nilai-Nilai Kami" dan sebelum section testimoni (modul 008). Section eksisting lain tidak diubah.
- Q: Apakah foto anggota wajib? → A: Wajib — kartu tim tanpa foto tidak informatif; berbeda dari Testimonials yang fotonya opsional.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Admin mengelola anggota tim (Priority: P1) 🎯 MVP

Admin membuka menu Tim di panel admin, menambah anggota baru (nama, jabatan, unggah foto, bio singkat, social link opsional), mengatur urutan tampil dan status aktif, lalu menyimpannya. Admin juga bisa mengedit dan menghapus anggota.

**Why this priority**: Nilai bisnis inti tiket — tanpa CRUD dasar, tidak ada cara bagi non-developer mengisi/memperbarui profil tim di halaman Tentang Kami.

**Independent Test**: Login sebagai admin, buat 4 anggota tim dengan jabatan berbeda dan foto masing-masing (dua dengan social link, dua tanpa), atur urutan dan aktif/nonaktif, verifikasi daftar panel menampilkan semuanya sesuai urutan; edit satu anggota (ganti jabatan + foto); hapus satu anggota dengan konfirmasi, verifikasi hilang dari daftar.

**Acceptance Scenarios**:

1. **Given** admin di form tambah anggota, **When** admin mengisi nama, jabatan, mengunggah foto, mengisi bio, dan menyimpan tanpa social link, **Then** anggota tersimpan dan tampil di daftar panel.
2. **Given** admin mengunggah foto berukuran besar, **When** admin menyimpan, **Then** foto disimpan dalam format WebP dengan lebar maksimum yang ditetapkan (di-downscale bila lebih besar, tidak di-upscale).
3. **Given** admin mencoba menyimpan anggota tanpa nama / tanpa jabatan / tanpa foto / tanpa bio, **When** admin menyimpan, **Then** sistem menolak dengan pesan validasi per field.
4. **Given** admin mengisi social link dengan format URL yang tidak valid, **When** admin menyimpan, **Then** sistem menolak dengan pesan error.
5. **Given** admin mengedit anggota yang sudah ada, **When** admin mengubah jabatan/bio/urutan/status/foto dan menyimpan, **Then** perubahan tersimpan.
6. **Given** admin menghapus anggota, **When** admin mengonfirmasi penghapusan, **Then** anggota terhapus dan tidak lagi muncul di daftar maupun di halaman publik.

---

### User Story 2 - Pengunjung melihat profil tim di halaman Tentang Kami (Priority: P1)

Pengunjung yang membuka halaman "Tentang Kami" melihat section "Tim Kami" berisi kartu anggota tim aktif (foto, nama, jabatan, bio singkat, dan — bila ada — tautan ke profil LinkedIn) yang dikelola admin.

**Why this priority**: Tujuan bisnis tiket — menampilkan wajah di balik perusahaan untuk membangun kepercayaan calon klien.

**Independent Test**: Dengan beberapa anggota aktif dan nonaktif dibuat via User Story 1, buka halaman Tentang Kami sebagai pengunjung, verifikasi hanya anggota aktif yang tampil dalam urutan yang ditetapkan admin; anggota dengan LinkedIn punya ikon/tautan yang bisa diklik (membuka di tab baru); nonaktifkan semua anggota, verifikasi section "Tim Kami" tidak muncul sama sekali (tidak ada area/heading kosong) dan section lain tetap utuh.

**Acceptance Scenarios**:

1. **Given** ada 4 anggota aktif dengan urutan 1–4, **When** pengunjung membuka halaman Tentang Kami, **Then** keempatnya tampil di section "Tim Kami" sesuai urutan tersebut, setelah section "Nilai-Nilai Kami".
2. **Given** seorang anggota berstatus nonaktif, **When** pengunjung membuka halaman Tentang Kami, **Then** anggota tersebut tidak tampil.
3. **Given** seorang anggota aktif punya social link, **When** pengunjung mengklik tautan LinkedIn anggota tsb, **Then** pengunjung diarahkan ke URL LinkedIn di tab baru.
4. **Given** seorang anggota aktif tanpa social link, **When** pengunjung melihat kartunya, **Then** kartu tampil rapi tanpa ikon/area LinkedIn kosong.
5. **Given** tidak ada anggota aktif sama sekali, **When** pengunjung membuka halaman Tentang Kami, **Then** section "Tim Kami" tidak dirender dan section lain (hero, visi, misi, nilai, testimoni, logo klien, CTA) tetap tampil.

---

### Edge Cases

- Dua anggota memiliki nilai urutan yang sama → ditampilkan berdampingan dengan urutan sekunder deterministik (mis. berdasarkan waktu dibuat), tanpa error.
- Foto anggota dengan rasio berbeda-beda → ditampilkan dalam bingkai konsisten (di-fit/crop) di grid, tidak merusak layout.
- File foto dihapus manual dari storage → slot foto tidak menampilkan gambar rusak (disembunyikan atau placeholder inisial).
- Bio sangat panjang → ditampilkan apa adanya; tidak ada pemotongan otomatis di v1 (admin menjaga panjang wajar).
- Social link tanpa skema (mis. "linkedin.com/in/nama") → ditolak saat validasi.
- Jumlah anggota aktif banyak (mis. > 12) → grid membungkus ke baris berikutnya, tanpa merusak layout halaman.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Admin panel MUST menyediakan menu CRUD Tim — tambah, daftar, edit, hapus.
- **FR-002**: Setiap anggota tim MUST memiliki: nama (wajib), jabatan (wajib), foto (wajib), bio singkat (wajib), `linkedin_url` (opsional, boleh dikosongkan), nilai urutan tampil, dan status aktif/nonaktif.
- **FR-003**: Sistem MUST memvalidasi field wajib (nama, jabatan, foto, bio) — submit tanpa salah satunya MUST ditolak dengan pesan error per field. `linkedin_url` yang kosong MUST diterima tanpa error.
- **FR-004**: Jika `linkedin_url` diisi, sistem MUST memvalidasinya sebagai URL absolut valid (berskema http/https) — nilai tidak valid MUST ditolak; jika kosong, anggota MUST tetap tersimpan.
- **FR-005**: Foto anggota MUST diproses saat simpan: bila lebarnya melebihi batas yang ditetapkan, foto di-downscale ke lebar batas tsb dengan mempertahankan rasio (tanpa upscale); hasil akhir MUST disimpan dalam format WebP. Form MUST menampilkan informasi ukuran foto yang direkomendasikan.
- **FR-006**: Admin MUST bisa menetapkan nilai urutan tampil per anggota; halaman publik MUST menampilkan anggota aktif diurutkan berdasarkan nilai urutan tsb (menaik), dengan urutan sekunder deterministik saat nilai sama.
- **FR-007**: Admin MUST bisa mengaktifkan/menonaktifkan tiap anggota; hanya anggota berstatus aktif yang tampil di halaman publik.
- **FR-008**: Admin MUST bisa menghapus anggota dengan konfirmasi terlebih dahulu; setelah dihapus, anggota MUST hilang dari daftar admin dan halaman publik.
- **FR-009**: Perubahan anggota (tambah/edit/hapus/aktif/urutan) MUST tercermin di halaman Tentang Kami pada request berikutnya tanpa deploy ulang.
- **FR-010**: Section "Tim Kami" MUST menampilkan, per anggota aktif: foto, nama, jabatan, bio singkat, dan — bila diisi — tautan LinkedIn (membuka di tab baru).
- **FR-011**: Jika tidak ada anggota aktif, halaman Tentang Kami MUST tidak merender section "Tim Kami" sama sekali (tanpa area/heading kosong), konsisten dengan pola modul opsional lain; section Tentang Kami lain MUST tidak terpengaruh.
- **FR-012**: CRUD Tim MUST terbuka untuk semua role yang memiliki akses panel admin, konsisten dengan resource admin lain yang sudah ada.
- **FR-013**: Section "Tim Kami" MUST ditempatkan di halaman "Tentang Kami" (`/tentang-kami`), setelah section "Nilai-Nilai Kami" dan sebelum section testimoni (modul 008). Beranda dan halaman lain TIDAK menampilkan section tim di v1.

### Key Entities *(include if feature involves data)*

- **Team Member**: satu anggota tim. Atribut: nama (wajib), jabatan (wajib), foto (wajib, disimpan WebP dengan batas lebar), bio singkat (wajib, teks biasa), `linkedin_url` (opsional, nullable, URL absolut http/https), urutan tampil (angka), status aktif (boolean). Tidak berelasi ke entity lain.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin dapat menambahkan satu anggota tim lengkap (termasuk foto) dan melihatnya tampil di halaman Tentang Kami dalam satu kali proses simpan, tanpa bantuan developer atau deploy ulang.
- **SC-002**: Admin dapat menyusun ulang urutan anggota dan perubahannya terlihat di halaman Tentang Kami pada refresh berikutnya.
- **SC-003**: 100% percobaan submit dengan data tidak valid (field wajib kosong, social link tidak valid) ditolak dengan pesan error yang jelas.
- **SC-004**: 100% anggota berstatus nonaktif atau terhapus tidak muncul di halaman publik.
- **SC-005**: Saat tidak ada anggota aktif, halaman Tentang Kami tetap tampil rapi tanpa section "Tim Kami" kosong, dan section lain tetap utuh.
- **SC-006**: 100% foto anggota yang diunggah tersimpan dalam format WebP dengan lebar tidak melebihi batas, tanpa admin perlu memproses gambar secara manual.
- **SC-007**: 100% anggota dengan LinkedIn mengarahkan pengunjung ke URL yang benar di tab baru saat diklik.

## Assumptions

- Foto anggota mengikuti pola resize + konversi WebP yang sudah dipakai modul Portfolio (010) lewat helper gambar project ini. Batas lebar rekomendasi: 800px (foto potret kepala/bahu, tidak butuh resolusi besar) — dapat disesuaikan saat plan. Foto wajib (hasil klarifikasi).
- Bio adalah teks biasa (textarea), bukan rich text — cukup untuk deskripsi singkat.
- Tidak ada toggle modul tingkat-atas — mekanisme "sembunyikan" cukup lewat menonaktifkan/menghapus tiap anggota; bila semua nonaktif, section otomatis tidak dirender (FR-011).
- Tidak ada pengelompokan anggota (mis. departemen/divisi) di v1 — satu daftar flat.
- Tidak ada halaman detail per anggota — hanya kartu di section Tentang Kami.
- Section "Tim Kami" hanya ditambahkan ke halaman Tentang Kami; halaman itu sejak modul 008 & 009 sudah mengirim data section (`$testimonials`, `$clientLogos`) — modul ini menambah satu variabel lagi. Section eksisting tidak diubah selain penyisipan.
- Tidak ada foto/CV/kontak email pribadi anggota di v1 (privasi) — hanya nama publik, jabatan, bio, dan tautan profil sosial profesional.
