# Feature Specification: Modul Banner Management

**Feature Branch**: `012-banner-management-module`

**Created**: 2026-09-10

**Status**: Draft

**Input**: User description: "AMC-214: Modul Banner Management — CRUD admin (Filament Resource) untuk banner promosi: judul (internal), gambar banner, URL tautan (opsional), teks alt, urutan tampil, periode tayang (tanggal mulai & selesai, opsional), toggle aktif. Banner hanya tampil di halaman publik bila aktif DAN berada dalam periode tayang (atau periode kosong = selalu). Gambar di-resize + konversi WebP (pola ImageUploads::storeAsWebp maxWidth seperti modul Portfolio 010 & Team 011). Pola CRUD identik modul konten lain (Testimonials 008, Client Logos 009). Section otomatis tidak dirender bila tidak ada banner yang memenuhi syarat tayang. Menutup sebagian Epic 3 (AMC-192)."

> **Catatan (2026-09-14)**: Modul ini diperluas oleh fitur
> [022-banner-hero-slider](../022-banner-hero-slider/spec.md). Banner kini
> membawa konten slide hero penuh (badge, judul, subjudul, dua CTA, trust
> bar, preset tampilan), auto-rotate carousel yang dijelaskan di klarifikasi
> di bawah **tidak lagi berlaku** — slider kini manual tanpa perpindahan
> otomatis (FR-011 fitur 022). Dokumen ini dipertahankan sebagai catatan
> sejarah keputusan awal; rujuk spec 022 untuk perilaku yang berlaku saat ini.

## Clarifications

### Session 2026-09-10

- Q: Di mana banner ditampilkan di sisi publik? → A: Area banner **menggantikan section Hero di beranda**. Bila ada ≥1 banner yang memenuhi syarat tayang, carousel banner tampil sebagai hero beranda. Bila tidak ada banner yang tayang, beranda memakai **fallback ke Hero statis** yang sudah ada (beranda selalu punya hero). Halaman lain tidak menampilkan banner.
- Q: Bila ada beberapa banner yang memenuhi syarat tayang pada saat bersamaan, bagaimana ditampilkan? → A: **Carousel/slider** — banner bergiliran otomatis (auto-rotate), satu banner terlihat pada satu waktu, dengan kontrol navigasi. Urutan giliran mengikuti nilai urutan admin.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Admin mengelola banner promosi (Priority: P1) 🎯 MVP

Admin membuka menu Banner di panel admin, menambah banner baru (judul internal untuk identifikasi, unggah gambar, teks alt, opsional: URL tautan, tanggal mulai & selesai tayang), mengatur urutan tampil dan status aktif, lalu menyimpannya. Admin juga bisa mengedit dan menghapus banner.

**Why this priority**: Nilai bisnis inti tiket — tanpa CRUD dasar, tidak ada cara bagi non-developer memasang/mengganti banner promosi (mis. promo musiman, pengumuman) di situs.

**Independent Test**: Login sebagai admin, buat 3 banner (satu tanpa URL & tanpa periode, satu dengan periode masih berlaku, satu dengan periode sudah lewat), atur urutan dan aktif/nonaktif, verifikasi daftar panel menampilkan semuanya beserta indikator status tayang; edit satu banner (ganti gambar + geser periode); hapus satu banner dengan konfirmasi, verifikasi hilang dari daftar.

**Acceptance Scenarios**:

1. **Given** admin di form tambah banner, **When** admin mengisi judul, mengunggah gambar, mengisi teks alt, dan menyimpan tanpa URL & tanpa periode, **Then** banner tersimpan dan tampil di daftar panel.
2. **Given** admin mengunggah gambar berukuran besar, **When** admin menyimpan, **Then** gambar disimpan dalam format WebP dengan lebar maksimum yang ditetapkan (di-downscale bila lebih besar, tidak di-upscale).
3. **Given** admin mencoba menyimpan banner tanpa judul / tanpa gambar / tanpa teks alt, **When** admin menyimpan, **Then** sistem menolak dengan pesan validasi per field.
4. **Given** admin mengisi URL tautan dengan format tidak valid, **When** admin menyimpan, **Then** sistem menolak dengan pesan error.
5. **Given** admin mengisi tanggal selesai lebih awal dari tanggal mulai, **When** admin menyimpan, **Then** sistem menolak dengan pesan error.
6. **Given** admin mengedit banner dan menggeser periode tayang, **When** admin menyimpan, **Then** perubahan tersimpan dan status tayang di daftar panel diperbarui.
7. **Given** admin menghapus banner, **When** admin mengonfirmasi penghapusan, **Then** banner terhapus dan tidak lagi muncul di daftar maupun di halaman publik.

---

### User Story 2 - Pengunjung melihat banner yang sedang tayang sebagai hero beranda (Priority: P1)

Pengunjung yang membuka beranda melihat banner promosi yang dikelola admin di posisi hero — hanya banner yang berstatus aktif dan berada dalam periode tayangnya (atau tanpa periode = selalu tayang). Bila ada lebih dari satu banner tayang, mereka bergiliran dalam carousel. Banner dengan URL tautan bisa diklik menuju halaman terkait. Bila tidak ada banner tayang, beranda menampilkan hero statis biasa.

**Why this priority**: Tujuan bisnis tiket — menampilkan promosi/pengumuman tepat waktu tanpa developer, dan otomatis berhenti tayang setelah periode berakhir.

**Independent Test**: Dengan beberapa banner (aktif dalam periode, aktif tapi periode lewat, aktif tapi periode belum mulai, nonaktif) dibuat via User Story 1, buka beranda, verifikasi hanya banner yang aktif + dalam periode yang tampil di posisi hero, terurut sesuai urutan admin, sebagai carousel bila >1; banner dengan URL bisa diklik; hapus/nonaktifkan/lewatkan periode semua banner, verifikasi beranda kembali menampilkan hero statis (tanpa ruang kosong) dan konten halaman lain tetap utuh.

**Acceptance Scenarios**:

1. **Given** ada 2 banner aktif yang keduanya dalam periode tayang, **When** pengunjung membuka beranda, **Then** kedua banner tampil di posisi hero sebagai carousel, urutan giliran sesuai urutan admin.
2. **Given** sebuah banner aktif tetapi tanggal selesai tayangnya sudah lewat, **When** pengunjung membuka beranda, **Then** banner tersebut tidak tampil.
3. **Given** sebuah banner aktif tetapi tanggal mulai tayangnya masih di masa depan, **When** pengunjung membuka beranda, **Then** banner tersebut belum tampil.
4. **Given** sebuah banner berstatus nonaktif meski periodenya berlaku, **When** pengunjung membuka beranda, **Then** banner tersebut tidak tampil.
5. **Given** hanya 1 banner yang tayang, **When** pengunjung membuka beranda, **Then** banner tsb tampil tunggal di posisi hero tanpa kontrol carousel.
6. **Given** sebuah banner tayang punya URL tautan, **When** pengunjung mengklik banner tsb, **Then** pengunjung diarahkan ke URL tautan.
7. **Given** sebuah banner tayang tanpa URL tautan, **When** pengunjung melihat banner tsb, **Then** banner tampil sebagai gambar biasa tanpa tautan.
8. **Given** tidak ada banner yang memenuhi syarat tayang, **When** pengunjung membuka beranda, **Then** posisi hero menampilkan Hero statis yang sudah ada (tanpa ruang kosong) dan konten lain tetap utuh.

---

### Edge Cases

- Dua banner memiliki nilai urutan yang sama → ditampilkan dengan urutan sekunder deterministik (mis. berdasarkan waktu dibuat), tanpa error.
- Hanya tanggal mulai diisi (tanpa tanggal selesai) → banner tayang sejak tanggal mulai tanpa batas akhir.
- Hanya tanggal selesai diisi (tanpa tanggal mulai) → banner tayang hingga tanggal selesai tanpa batas awal.
- Banner tepat pada hari tanggal mulai atau tanggal selesai → dianggap sedang dalam periode (inklusif).
- Gambar banner dengan rasio berbeda-beda → ditampilkan dalam bingkai/perilaku yang konsisten, tidak merusak layout halaman.
- File gambar dihapus manual dari storage → slot banner tsb tidak menampilkan gambar rusak (disembunyikan).
- URL tautan tanpa skema (mis. "promo.com") → ditolak saat validasi.
- Banyak banner tayang bersamaan → dirender sebagai carousel di posisi hero (FR-016); layout beranda tetap rapi.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Admin panel MUST menyediakan menu CRUD Banner — tambah, daftar, edit, hapus.
- **FR-002**: Setiap banner MUST memiliki: judul internal (wajib — untuk identifikasi admin, tidak wajib tampil ke publik), gambar (wajib), teks alt (wajib — untuk aksesibilitas), URL tautan (opsional), tanggal mulai tayang (opsional), tanggal selesai tayang (opsional), nilai urutan tampil, dan status aktif/nonaktif.
- **FR-003**: Sistem MUST memvalidasi field wajib (judul, gambar, teks alt) — submit tanpa salah satunya MUST ditolak dengan pesan error per field.
- **FR-004**: Jika URL tautan diisi, sistem MUST memvalidasinya sebagai URL absolut valid (berskema http/https) — nilai tidak valid MUST ditolak.
- **FR-005**: Jika kedua tanggal periode diisi, sistem MUST menolak submit ketika tanggal selesai lebih awal dari tanggal mulai.
- **FR-006**: Gambar banner MUST diproses saat simpan: bila lebarnya melebihi batas yang ditetapkan, gambar di-downscale ke lebar batas tsb dengan mempertahankan rasio (tanpa upscale); hasil akhir MUST disimpan dalam format WebP. Form MUST menampilkan informasi ukuran gambar yang direkomendasikan.
- **FR-007**: Admin MUST bisa menetapkan nilai urutan tampil per banner; halaman publik MUST menampilkan banner yang memenuhi syarat tayang diurutkan berdasarkan nilai urutan tsb (menaik), dengan urutan sekunder deterministik saat nilai sama.
- **FR-008**: Sebuah banner MUST tampil di halaman publik HANYA jika: berstatus aktif, DAN (tanggal mulai kosong ATAU tanggal mulai ≤ hari ini), DAN (tanggal selesai kosong ATAU tanggal selesai ≥ hari ini). Periode bersifat inklusif pada kedua ujung.
- **FR-009**: Admin MUST bisa menghapus banner dengan konfirmasi terlebih dahulu; setelah dihapus, banner MUST hilang dari daftar admin dan halaman publik.
- **FR-010**: Perubahan banner (tambah/edit/hapus/aktif/urutan/periode) MUST tercermin di halaman publik pada request berikutnya tanpa deploy ulang.
- **FR-011**: Area banner di halaman publik MUST menampilkan, per banner yang tayang: gambar dengan teks alt-nya; jika banner punya URL tautan, gambar MUST menjadi tautan yang membuka URL tsb; jika tidak, gambar MUST tampil sebagai gambar biasa.
- **FR-012**: Jika tidak ada banner yang memenuhi syarat tayang, beranda MUST menampilkan Hero statis yang sudah ada sebagai fallback (beranda selalu punya hero) — tidak ada ruang kosong; konten halaman lain MUST tidak terpengaruh.
- **FR-013**: Daftar banner di admin panel MUST menunjukkan status tayang tiap banner (mis. "Tayang", "Terjadwal", "Kedaluwarsa", "Nonaktif") sehingga admin tahu banner mana yang sedang tampil publik tanpa membuka situs.
- **FR-014**: CRUD Banner MUST terbuka untuk semua role yang memiliki akses panel admin, konsisten dengan resource admin lain yang sudah ada.
- **FR-015**: Area banner MUST menempati posisi section Hero di beranda (`/`) — menggantikan Hero statis ketika ada ≥1 banner tayang. Halaman publik lain TIDAK menampilkan banner di v1.
- **FR-016**: Bila terdapat >1 banner tayang, area banner MUST dirender sebagai carousel/slider: satu banner terlihat pada satu waktu, bergiliran otomatis, dengan kontrol navigasi (mis. titik indikator dan/atau tombol maju/mundur). Urutan giliran mengikuti nilai urutan admin (FR-007). Bila hanya 1 banner tayang, banner tsb dirender tunggal tanpa kontrol carousel.

### Key Entities *(include if feature involves data)*

- **Banner**: satu banner promosi. Atribut: judul internal (wajib), gambar (wajib, disimpan WebP dengan batas lebar), teks alt (wajib), URL tautan (opsional, absolut http/https), tanggal mulai tayang (opsional), tanggal selesai tayang (opsional), urutan tampil (angka), status aktif (boolean). Tidak berelasi ke entity lain.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin dapat membuat satu banner lengkap (gambar + alt + periode) dan melihatnya tampil di halaman publik dalam satu kali proses simpan, tanpa bantuan developer atau deploy ulang.
- **SC-002**: Sebuah banner dengan tanggal selesai kemarin tidak lagi tampil di halaman publik hari ini, tanpa admin perlu menonaktifkannya manual.
- **SC-003**: 100% percobaan submit dengan data tidak valid (field wajib kosong, URL tidak valid, tanggal selesai sebelum mulai) ditolak dengan pesan error yang jelas.
- **SC-004**: 100% banner yang nonaktif, di luar periode, atau terhapus tidak muncul di halaman publik.
- **SC-005**: Saat tidak ada banner yang tayang, beranda tetap tampil rapi dengan Hero statis di posisi hero (tanpa area kosong), dan konten lain tetap utuh.
- **SC-006**: 100% gambar banner yang diunggah tersimpan dalam format WebP dengan lebar tidak melebihi batas, tanpa admin perlu memproses gambar secara manual.
- **SC-007**: Admin dapat menentukan banner mana yang sedang tayang publik hanya dari daftar di panel admin (indikator status), tanpa membuka situs.

## Assumptions

- "Periode tayang" berbasis **tanggal** (bukan tanggal+jam) — banner mulai/berhenti tayang pada awal/akhir hari yang ditentukan. Evaluasi periode memakai tanggal saat request (zona waktu aplikasi).
- Gambar banner mengikuti pola resize + konversi WebP yang sudah dipakai modul Portfolio (010) & Team (011) lewat helper gambar project ini. Batas lebar rekomendasi disesuaikan saat plan (banner biasanya lebar penuh — mis. 1600px). Tidak ada validasi dimensi/rasio keras.
- Tidak ada toggle modul tingkat-atas — mekanisme "sembunyikan" cukup lewat menonaktifkan/menghapus/mengatur periode tiap banner; bila tidak ada yang tayang, area otomatis tidak dirender (FR-012).
- Tidak ada pengelompokan/penempatan banner per-slot (mis. "banner beranda" vs "banner halaman produk") di v1 — satu daftar banner untuk satu lokasi tampil (posisi hero beranda).
- Judul internal tidak dirender ke pengunjung secara default (hanya untuk identifikasi di admin); teks yang tampil ke pengunjung ada di dalam gambar banner itu sendiri.
- Tidak ada pelacakan klik/impresi banner (analytics) di v1.
- URL tautan banner membuka di tab yang sama secara default (perilaku banner promosi umum); dapat disesuaikan saat plan bila diinginkan tab baru.
- Section Hero statis yang ada sekarang (`resources/views/components/sections/hero.blade.php`) TIDAK dihapus — tetap dipakai sebagai fallback ketika tidak ada banner tayang (FR-012). Hanya `pages/home.blade.php` yang diubah untuk memilih antara carousel banner atau hero statis.
- Carousel banner: auto-rotate dengan interval wajar (mis. 5 detik) dan berhenti/berganti saat interaksi pengguna; implementasi memakai pustaka front-end yang sudah ada di project (mis. Alpine.js) tanpa dependency baru — dikonfirmasi saat plan.

