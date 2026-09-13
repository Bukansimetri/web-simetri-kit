# Feature Specification: Optimasi Performa Halaman Publik

**Feature Branch**: `016-performance-optimization`

**Created**: 2026-09-13

**Status**: Draft

**Input**: User description: "AMC-225: Optimasi performa (caching, lazy load gambar, asset bundling). Berdasarkan Linear (team Amaya ECOM, project Web Solarpanel Kit, parent Epic 5 - SEO & Performance AMC-194). Audit kondisi saat ini: tidak ada satu pun <img> di 15+ view publik yang pakai lazy loading; tidak ada caching sama sekali untuk query publik read-heavy (Home, Produk, Artikel, Portfolio semua query fresh ke DB tiap request meski kontennya jarang berubah); stylesheet Material Symbols (font ikon) dimuat render-blocking di <head> tiap halaman publik. Scope: (1) lazy-load gambar below-the-fold di seluruh halaman publik, gambar hero/sampul pertama tetap dimuat segera; (2) caching sementara (short TTL) untuk query halaman publik read-only (Beranda, index & detail Produk/Artikel/Portfolio, FAQ, Tentang Kami) tanpa mempengaruhi kesegaran data di admin panel; (3) muat stylesheet font ikon secara non-blocking supaya tidak menghalangi tampilan awal halaman."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Halaman bergambar banyak terasa lebih cepat dan hemat kuota (Priority: P1)

Pengunjung membuka halaman yang penuh gambar (katalog produk, galeri portfolio, daftar artikel) — gambar yang langsung terlihat di layar tampil secepat sebelumnya, sementara gambar-gambar lain yang masih di bawah (belum terlihat) baru diunduh saat pengunjung mulai scroll mendekatinya. Halaman terasa lebih responsif sejak awal dan tidak menghabiskan kuota untuk gambar yang mungkin tidak pernah dilihat pengunjung.

**Why this priority**: Perbaikan paling langsung terasa oleh pengunjung dan paling rendah risiko — tidak mengubah data atau perilaku sistem, murni menunda pengunduhan sesuatu yang belum terlihat. Situs ini sangat bergantung pada gambar (galeri produk, portfolio, foto artikel), jadi manfaatnya besar.

**Independent Test**: Buka halaman dengan banyak gambar (mis. katalog Produk atau daftar Portfolio) dalam kondisi jaringan lambat/disimulasikan — gambar pertama di layar tampil tanpa jeda tambahan, gambar-gambar di bagian bawah baru terlihat termuat begitu di-scroll ke area itu; total data gambar yang terunduh saat halaman baru dibuka (sebelum scroll) jauh lebih sedikit dibanding sebelum optimasi.

**Acceptance Scenarios**:

1. **Given** pengunjung membuka halaman apa pun yang punya gambar hero/banner/sampul di posisi teratas, **When** halaman baru dimuat, **Then** gambar teratas itu MUST tetap tampil segera tanpa penundaan tambahan.
2. **Given** halaman punya beberapa gambar di bawah area pandang awal (mis. daftar produk, galeri portfolio, kartu artikel lanjutan), **When** halaman baru dimuat dan pengunjung belum scroll, **Then** gambar-gambar tsb MUST belum diunduh.
3. **Given** kondisi di atas, **When** pengunjung scroll mendekati posisi gambar tsb, **Then** gambar MUST mulai dimuat dan tampil sebelum benar-benar terlihat penuh di layar (tidak ada jeda kotak kosong yang mengganggu).
4. **Given** browser pengunjung tidak menjalankan JavaScript, **When** halaman dibuka dan discroll, **Then** seluruh gambar MUST tetap bisa tampil (penundaan pemuatan tidak boleh bergantung sepenuhnya pada JavaScript untuk menampilkan gambar).

---

### User Story 2 - Halaman publik yang sering dibuka tetap cepat tanpa mengulang seluruh proses dari awal (Priority: P2)

Saat banyak pengunjung membuka halaman publik yang sama (atau pengunjung yang sama membuka ulang), sistem tidak perlu mengulang seluruh proses pengambilan data dari awal setiap kali — sehingga halaman tetap terasa cepat meski trafik meningkat. Di saat bersamaan, admin yang baru saja mengubah konten (menerbitkan artikel, mengubah harga produk, dll.) tetap bisa memverifikasi perubahannya di panel admin secara instan, dan perubahan itu terlihat oleh pengunjung publik dalam waktu singkat yang wajar.

**Why this priority**: Nilai tambah nyata untuk skalabilitas dan konsistensi kecepatan, tapi manfaatnya baru terasa signifikan saat trafik meningkat atau konten kompleks (banyak relasi data) — berbeda dari US1 yang manfaatnya langsung terasa di kunjungan pertama. Ada trade-off kesegaran data yang perlu dijaga hati-hati, jadi wajar diletakkan setelah perbaikan yang lebih aman.

**Independent Test**: Buka halaman publik read-only (mis. Beranda atau katalog Produk) dua kali berturut-turut dalam waktu singkat — kunjungan kedua terasa sama cepat atau lebih cepat, tanpa mengubah tampilan/isi apa pun. Ubah satu konten di admin (mis. ubah judul produk) → buka halaman admin: perubahan terlihat langsung; buka halaman publik: perubahan terlihat dalam rentang waktu singkat yang disepakati (bukan instan sempurna, tapi juga bukan basi berjam-jam).

**Acceptance Scenarios**:

1. **Given** halaman publik read-only (Beranda, index/detail Produk, index/detail Artikel, index/detail Portfolio, FAQ, Tentang Kami) dibuka berulang kali dalam waktu singkat, **When** dibandingkan kunjungan pertama vs berikutnya, **Then** kunjungan berikutnya MUST tidak lebih lambat.
2. **Given** admin mengubah/menambah/menghapus konten pada modul yang halamannya di-cache, **When** admin membuka kembali halaman pengelolaan konten tsb di panel admin, **Then** perubahan MUST langsung terlihat tanpa penundaan apa pun (panel admin tidak boleh terpengaruh caching).
3. **Given** kondisi di atas, **When** pengunjung publik membuka halaman terkait dalam rentang waktu singkat yang disepakati, **Then** perubahan tsb MUST sudah terlihat oleh pengunjung publik.
4. **Given** halaman dengan interaksi/state per-pengunjung (mis. Kontak dengan pengiriman form, Kalkulator estimasi), **When** halaman tsb diakses, **Then** MUST tidak terpengaruh oleh penyimpanan sementara (selalu diproses segar per permintaan).

---

### User Story 3 - Konten utama halaman tampil tanpa terhalang sumber daya pendukung (Priority: P3)

Saat halaman publik mulai dimuat, teks dan tata letak utama tampil tanpa harus menunggu sumber daya pendukung pihak ketiga yang tidak esensial (seperti kumpulan ikon) selesai diunduh terlebih dahulu — ikon-ikon tetap muncul dan berfungsi normal begitu siap, tanpa menunda kemunculan konten utama.

**Why this priority**: Manfaat lebih halus/terukur dibanding dua cerita sebelumnya (mempercepat beberapa saat di awal render, bukan mengubah pengalaman scroll atau kecepatan berulang), jadi wajar jadi prioritas terakhir sebagai penyempurnaan.

**Independent Test**: Buka halaman publik apa pun dan amati urutan tampilnya elemen — teks/tata letak utama halaman MUST terlihat tanpa menunggu sumber daya ikon pihak ketiga selesai dimuat; begitu sumber daya itu siap, seluruh ikon di halaman MUST tetap tampil benar (tidak ada ikon hilang/rusak).

**Acceptance Scenarios**:

1. **Given** halaman publik dimuat dalam kondisi jaringan lambat/disimulasikan untuk sumber daya pihak ketiga, **When** halaman mulai dirender, **Then** teks dan tata letak utama MUST tetap tampil tanpa menunggu sumber daya ikon tsb.
2. **Given** kondisi di atas, **When** sumber daya ikon akhirnya selesai dimuat, **Then** seluruh ikon di halaman (navigasi, tombol, kartu, dll.) MUST tampil dan berfungsi normal seperti sebelumnya — tidak ada regresi visual.

---

### Edge Cases

- Pengunjung scroll sangat cepat melewati banyak gambar sekaligus → gambar-gambar tsb tetap harus mulai dimuat lebih awal (sesaat sebelum benar-benar terlihat), bukan menunggu tepat pas terlihat, supaya tidak terasa "telat muncul".
- Data yang sedang disimpan sementara (cache) dihapus/diubah adminnya sesaat sebelum masa berlaku cache habis → pengunjung publik MUST tidak melihat versi yang sudah dihapus lebih lama dari rentang waktu singkat yang disepakati (Acceptance Scenario US2 #3).
- Situs dengan trafik sangat rendah (baru instalasi/demo) → penyimpanan sementara tetap boleh aktif dan tidak menimbulkan efek samping, tidak bergantung pada volume trafik untuk bermanfaat/aman.
- Sumber daya ikon pihak ketiga gagal dimuat sama sekali (mis. terblokir jaringan pengunjung) → halaman MUST tetap bisa dipakai sepenuhnya (navigasi/tombol tetap berfungsi meski ikon visual tidak tampil).

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Sistem MUST menunda pengunduhan gambar yang berada di luar area pandang awal (below-the-fold) di seluruh halaman publik yang menampilkan gambar, sampai pengunjung mendekati posisinya saat scroll.
- **FR-002**: Sistem MUST TETAP memuat segera (tanpa penundaan) gambar hero/banner/sampul pertama yang tampil di area pandang awal tiap halaman — penundaan pemuatan HANYA berlaku untuk gambar di luar area pandang awal.
- **FR-003**: Penerapan FR-001/FR-002 MUST konsisten di seluruh halaman publik yang menampilkan gambar (beranda, produk, artikel, portfolio, testimoni, dll.), bukan sebagian saja.
- **FR-004**: Penundaan pemuatan gambar MUST tidak bergantung sepenuhnya pada JavaScript — gambar MUST tetap bisa tampil bila JavaScript pengunjung nonaktif.
- **FR-005**: Sistem MUST menyimpan sementara hasil pengambilan data untuk halaman publik read-only yang datanya jarang berubah (Beranda, index & detail Produk, index & detail Artikel, index & detail Portfolio, FAQ, Tentang Kami).
- **FR-006**: Halaman dengan interaksi/state per-pengunjung (Kontak dengan pengiriman form, Kalkulator estimasi) MUST TIDAK ikut disimpan sementara — selalu diproses segar per permintaan.
- **FR-007**: Penyimpanan sementara MUST punya masa berlaku singkat yang wajar, sehingga perubahan konten oleh admin terlihat oleh pengunjung publik dalam rentang waktu singkat tsb, bukan basi berkepanjangan.
- **FR-008**: Panel pengelolaan konten (admin) MUST TIDAK terpengaruh oleh penyimpanan sementara FR-005 — admin MUST selalu melihat data terkini persis setelah menyimpan perubahan.
- **FR-009**: Sistem MUST memuat sumber daya pendukung pihak ketiga yang tidak esensial untuk tampilan awal (kumpulan ikon) dengan cara yang tidak menghalangi teks/tata letak utama halaman tampil lebih dulu.
- **FR-010**: Penerapan FR-009 MUST tidak mengubah tampilan akhir halaman — seluruh ikon MUST tetap muncul dan berfungsi normal setelah sumber dayanya siap.

### Key Entities

- Tidak ada entitas data baru — fitur ini murni mengubah cara gambar dimuat di sisi tampilan dan menambah lapisan penyimpanan-sementara (cache) di atas data yang sudah ada, tanpa menyimpan state baru yang bermakna bisnis.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Jumlah data gambar yang terunduh saat pertama kali membuka halaman bergambar banyak (sebelum scroll) berkurang signifikan dibanding sebelum optimasi.
- **SC-002**: Waktu sampai konten utama halaman terlihat pengunjung (halaman dengan banyak gambar maupun tidak) membaik terukur dibanding sebelum optimasi, diverifikasi lewat pengukuran kecepatan halaman standar pihak ketiga yang umum dipakai.
- **SC-003**: Halaman publik read-only yang dibuka berulang kali dalam waktu singkat menunjukkan waktu respons yang konsisten cepat, tidak melambat seiring jumlah kunjungan.
- **SC-004**: Perubahan konten yang disimpan admin terlihat oleh pengunjung publik dalam rentang waktu singkat yang wajar (dalam hitungan menit) — bukan instan sempurna, tapi juga tidak pernah lebih dari itu tanpa alasan.
- **SC-005**: Admin yang menyimpan perubahan konten melihat hasilnya di panel admin seketika, 100% dari waktu, tanpa pengecualian.
- **SC-006**: Skor pengukuran kecepatan halaman standar pihak ketiga untuk halaman-halaman utama (Beranda, Produk, Artikel, Portfolio) membaik dibanding kondisi sebelum optimasi, tanpa ada halaman yang justru memburuk.

## Assumptions

- Penundaan pemuatan gambar (FR-001/FR-004) memakai dukungan bawaan browser modern untuk menunda gambar di luar layar — bukan pustaka pihak ketiga tambahan; browser lama yang tidak mendukung tetap memuat gambar seperti biasa (bukan gagal/rusak).
- Penyimpanan sementara (FR-005/FR-007) memakai masa berlaku pendek yang seragam (hitungan menit) untuk semua halaman yang tercakup — bukan diatur berbeda-beda per jenis konten oleh admin; ini trade-off sederhana antara kecepatan dan kesegaran data, sesuai skala starter kit ini.
- Tidak ada mekanisme "hapus paksa cache seketika saat admin menyimpan" di iterasi ini (mis. saat admin publish artikel, publik tidak otomatis instan melihatnya) — keterlambatan singkat (FR-007) dianggap dapat diterima demi menjaga kompleksitas tetap rendah; penghapusan cache instan berbasis event adalah peningkatan yang bisa ditangani terpisah bila kebutuhan berkembang.
- Cakupan halaman yang disimpan sementara terbatas pada halaman publik murni-baca yang disebutkan eksplisit (Beranda, Produk, Artikel, Portfolio, FAQ, Tentang Kami) — halaman lain (Kontak, Kalkulator, Karir bila kontennya dianggap sering berubah oleh admin klien) TIDAK termasuk kecuali diminta terpisah.
- Tidak ada kontrol admin baru terkait pengaturan performa ini (mis. toggle nyala/mati caching atau lazy-load per klien) — perilaku berlaku seragam di semua instalasi starter kit ini, konsisten dengan sifatnya sebagai perbaikan teknis latar belakang, bukan fitur yang dikonfigurasi pengguna.
- Optimasi pemuatan sumber daya pihak ketiga (FR-009/FR-010) terbatas pada cara pemuatannya (non-blocking) — proses build/bundling aset CSS/JS milik situs sendiri (lewat build tool yang sudah dipakai) sudah dianggap memadai dan di luar cakupan perubahan pada iterasi ini.
