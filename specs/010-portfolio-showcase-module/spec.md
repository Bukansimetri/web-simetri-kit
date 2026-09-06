# Feature Specification: Modul Portfolio / Project Showcase

**Feature Branch**: `010-portfolio-showcase-module`

**Created**: 2026-09-07

**Status**: Draft

**Input**: User description: "AMC-208: Modul Portfolio / Project Showcase — CRUD admin (Filament Resource) untuk proyek/portofolio: judul, slug, kategori, deskripsi (rich text), galeri gambar (banyak), URL tautan proyek opsional, nama klien opsional, tanggal selesai opsional, urutan, toggle aktif. Plus taxonomy kategori portfolio (CRUD terpisah, pola identik Kategori Artikel). Ditampilkan di halaman publik: listing /portfolio dengan filter kategori + halaman detail /portfolio/{slug}. Pola identik modul Blog/Artikel (005) dan Produk (003). Rendering ini menutup bagian 'portfolio' di AMC-218. Tanpa dependency baru; galeri gambar pakai pola images JSON array seperti Produk."

## Clarifications

### Session 2026-09-07

- Q: Visibilitas proyek — pakai draft/publish terjadwal (seperti Artikel) atau toggle aktif sederhana (seperti Career/Testimonials)? → A: Toggle aktif sederhana (`is_active` boolean), tanpa alur draft/publish atau penjadwalan. Konsisten dengan modul 006–009.
- Q: Perilaku listing `/portfolio` saat belum ada proyek aktif sama sekali? → A: Tampilkan halaman listing dengan empty-state ("Belum ada proyek"), konsisten dengan `/artikel` (bukan 404).
- Q: Target ukuran maksimum gambar galeri portfolio (resize + WebP)? → A: Downscale ke lebar maks 1200px (tinggi proporsional, rasio dipertahankan), disimpan sebagai WebP; form menampilkan rekomendasi "1200×900px". Gambar lebih kecil dari 1200px lebar tidak di-upscale.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Admin mengelola kategori portfolio (Priority: P2)

Admin membuka menu Kategori Portfolio di panel admin, menambah/mengubah/menghapus kategori (nama, urutan tampil). Kategori dipakai untuk mengelompokkan dan memfilter proyek.

**Why this priority**: Kategori adalah prasyarat data untuk mengelompokkan proyek dan filter di halaman publik, tapi bukan nilai bisnis utama tiket (showcase proyek). Diprioritaskan setelah CRUD proyek agar proyek pertama bisa dibuat lebih dulu (dengan kategori minimal).

**Independent Test**: Login sebagai admin, buat 3 kategori dengan urutan berbeda, ubah nama satu kategori, hapus satu kategori yang belum dipakai proyek; verifikasi daftar kategori di panel mencerminkan perubahan sesuai urutan.

**Acceptance Scenarios**:

1. **Given** admin di form tambah kategori, **When** admin mengisi nama dan urutan lalu menyimpan, **Then** kategori tersimpan dan tampil di daftar.
2. **Given** admin mencoba menyimpan kategori dengan nama yang sudah dipakai kategori lain, **When** admin menyimpan, **Then** sistem menolak dengan pesan error.
3. **Given** sebuah kategori masih dipakai oleh minimal satu proyek, **When** admin mencoba menghapusnya, **Then** sistem mencegah/memperingatkan penghapusan agar tidak ada proyek tanpa kategori.

---

### User Story 2 - Admin mengelola proyek portfolio (Priority: P1) 🎯 MVP

Admin membuka menu Portfolio di panel admin, menambah proyek baru (judul, kategori, deskripsi lewat rich text editor, galeri gambar, opsional: URL tautan proyek, nama klien, tanggal selesai), mengatur urutan tampil dan status aktif, lalu menyimpannya. Slug dihasilkan otomatis dari judul dan bisa di-override. Admin juga bisa mengedit dan menghapus proyek.

**Why this priority**: Nilai bisnis inti tiket — tanpa CRUD proyek, tidak ada showcase yang bisa ditampilkan ke calon klien. Ini juga yang menutup bagian "portfolio" di AMC-218.

**Independent Test**: Login sebagai admin, buat 2 proyek pada kategori berbeda dengan galeri 2–3 gambar masing-masing (satu proyek tanpa URL/klien/tanggal), verifikasi slug ter-generate, edit satu proyek (ganti kategori + tambah gambar), hapus satu proyek dengan konfirmasi; verifikasi daftar panel mencerminkan perubahan.

**Acceptance Scenarios**:

1. **Given** admin di form tambah proyek, **When** admin mengisi judul, memilih kategori, menulis deskripsi, mengunggah beberapa gambar, dan menyimpan tanpa URL/klien/tanggal, **Then** proyek tersimpan dan tampil di daftar panel.
2. **Given** admin mengisi judul tanpa mengisi slug, **When** admin menyimpan, **Then** sistem menghasilkan slug otomatis dari judul (dan admin tetap bisa meng-override slug).
3. **Given** admin mencoba menyimpan proyek dengan slug yang sudah dipakai proyek lain, **When** admin menyimpan, **Then** sistem menolak dengan pesan error.
4. **Given** admin mencoba menyimpan proyek tanpa judul / tanpa kategori / tanpa deskripsi / tanpa satu pun gambar, **When** admin menyimpan, **Then** sistem menolak dengan pesan validasi per field.
5. **Given** admin mengisi URL tautan proyek dengan format tidak valid, **When** admin menyimpan, **Then** sistem menolak dengan pesan error.
6. **Given** admin mengedit proyek dan mengubah urutan gambar galeri, **When** admin menyimpan, **Then** urutan gambar tersimpan sesuai yang diatur.
7. **Given** admin menghapus proyek, **When** admin mengonfirmasi penghapusan, **Then** proyek terhapus dan URL detailnya mengembalikan 404.

---

### User Story 3 - Pengunjung menjelajah portfolio di halaman publik (Priority: P1)

Pengunjung membuka halaman `/portfolio` dan melihat daftar proyek aktif (gambar sampul, judul, kategori). Pengunjung bisa memfilter daftar berdasarkan kategori. Mengklik sebuah proyek membuka halaman detail `/portfolio/{slug}` berisi galeri gambar, deskripsi, dan (bila ada) nama klien, tanggal selesai, serta tautan ke proyek/situs terkait.

**Why this priority**: Ini realisasi nilai bisnis — calon klien menilai kapabilitas lewat proyek nyata. Setara prioritas dengan CRUD proyek (US2) karena keduanya dibutuhkan untuk showcase yang berfungsi.

**Independent Test**: Dengan beberapa proyek aktif & nonaktif pada 2+ kategori dibuat via US1/US2, buka `/portfolio`: verifikasi hanya proyek aktif tampil, terurut sesuai urutan admin; pilih filter kategori, verifikasi hanya proyek kategori itu tampil; buka satu proyek, verifikasi halaman detail menampilkan galeri + deskripsi + metadata opsional yang terisi; buka slug proyek nonaktif/tidak ada, verifikasi 404.

**Acceptance Scenarios**:

1. **Given** ada 4 proyek aktif dan 1 nonaktif, **When** pengunjung membuka `/portfolio`, **Then** hanya 4 proyek aktif yang tampil, terurut sesuai nilai urutan admin.
2. **Given** pengunjung berada di `/portfolio`, **When** pengunjung memilih filter kategori tertentu, **Then** hanya proyek aktif pada kategori tsb yang tampil, dan pilihan filter tercermin di URL/kondisi halaman sehingga bisa dibagikan.
3. **Given** sebuah proyek aktif punya galeri 3 gambar + nama klien + tanggal selesai + URL tautan, **When** pengunjung membuka halaman detailnya, **Then** semua elemen tsb tampil, dan tautan proyek membuka di tab baru.
4. **Given** sebuah proyek aktif tanpa URL/klien/tanggal, **When** pengunjung membuka detailnya, **Then** halaman tetap tampil rapi tanpa label kosong untuk field yang tidak diisi.
5. **Given** slug proyek yang nonaktif atau tidak ada, **When** pengunjung membuka `/portfolio/{slug}` tsb, **Then** sistem mengembalikan 404.
6. **Given** belum ada proyek aktif sama sekali, **When** pengunjung membuka `/portfolio`, **Then** halaman listing tetap tampil dengan pesan "Belum ada proyek" (bukan 404).

---

### Edge Cases

- Dua proyek memiliki nilai urutan yang sama → ditampilkan dengan urutan sekunder deterministik (mis. berdasarkan waktu dibuat), tanpa error.
- Kategori dihapus saat masih dipakai proyek → dicegah (lihat US1 Scenario 3); proyek tidak boleh berakhir tanpa kategori.
- Filter kategori mengarah ke kategori yang tidak punya proyek aktif → listing menampilkan empty-state untuk kategori tsb, bukan error.
- Filter kategori dengan nilai yang tidak dikenal (mis. slug kategori dimanipulasi di URL) → diperlakukan sebagai "semua" atau 404 yang wajar, tidak menampilkan error mentah.
- Gambar galeri dengan rasio berbeda-beda → ditampilkan dalam bingkai konsisten di grid & detail, tidak merusak layout.
- File gambar dihapus manual dari storage → slot gambar tsb tidak menampilkan gambar rusak.
- Proyek aktif tanpa gambar (jika lolos validasi karena data lama) → kartu listing memakai placeholder netral, tidak merusak grid.
- URL tautan proyek tanpa skema (mis. "contoh.com") → ditolak saat validasi.

## Requirements *(mandatory)*

### Functional Requirements

#### Kategori Portfolio

- **FR-001**: Admin panel MUST menyediakan menu CRUD Kategori Portfolio — tambah, daftar, edit, hapus — terpisah dari CRUD proyek, pola konsisten dengan Kategori Artikel yang sudah ada.
- **FR-002**: Setiap kategori MUST memiliki: nama (wajib, unik antar kategori portfolio) dan nilai urutan tampil.
- **FR-003**: Sistem MUST mencegah penghapusan kategori yang masih direferensikan oleh minimal satu proyek, atau memberi peringatan jelas, sehingga tidak ada proyek tanpa kategori.

#### Proyek Portfolio

- **FR-004**: Admin panel MUST menyediakan menu CRUD Portfolio — tambah, daftar, edit, hapus.
- **FR-005**: Setiap proyek MUST memiliki: judul (wajib), slug unik antar proyek (auto dari judul, bisa di-override), kategori (wajib, satu kategori per proyek), deskripsi via rich text editor (wajib), galeri gambar dengan minimal satu gambar (wajib), nilai urutan tampil, status aktif/nonaktif. Field opsional: URL tautan proyek, nama klien, tanggal selesai.
- **FR-006**: Sistem MUST menghasilkan slug otomatis dari judul saat dibuat, dan admin MUST bisa meng-override slug secara manual.
- **FR-007**: Sistem MUST memvalidasi slug proyek unik antar proyek — submit dengan slug yang sudah dipakai MUST ditolak dengan pesan error jelas.
- **FR-008**: Sistem MUST memvalidasi field wajib (judul, kategori, deskripsi, minimal satu gambar galeri) — submit tanpa salah satunya MUST ditolak dengan pesan error per field.
- **FR-009**: Jika URL tautan proyek diisi, sistem MUST memvalidasinya sebagai URL absolut valid (berskema http/https) — nilai tidak valid MUST ditolak.
- **FR-010**: Admin MUST bisa mengunggah beberapa gambar per proyek dan mengatur urutannya; gambar pertama dalam urutan berfungsi sebagai gambar sampul.
- **FR-010a**: Form unggah galeri MUST menampilkan informasi ukuran gambar yang direkomendasikan (rekomendasi: 1200×900px, rasio bebas) sebagai teks bantuan.
- **FR-010b**: Setiap gambar yang diunggah MUST diproses saat simpan: bila lebarnya melebihi 1200px, gambar di-downscale ke lebar 1200px dengan mempertahankan rasio (gambar lebih kecil tidak di-upscale); hasil akhir MUST disimpan dalam format WebP. Proses ini MUST tidak menolak gambar karena dimensi — hanya menyesuaikan.
- **FR-011**: Admin MUST bisa menetapkan nilai urutan tampil per proyek; halaman listing publik MUST menampilkan proyek aktif diurutkan berdasarkan nilai urutan tsb (menaik), dengan urutan sekunder deterministik saat nilai sama.
- **FR-012**: Admin MUST bisa mengaktifkan/menonaktifkan tiap proyek; hanya proyek berstatus aktif yang tampil di halaman publik (listing & detail).
- **FR-013**: Admin MUST bisa menghapus proyek dengan konfirmasi terlebih dahulu; setelah dihapus, URL detail proyek tsb MUST mengembalikan 404.
- **FR-014**: Perubahan proyek/kategori (tambah/edit/hapus/aktif/urutan/filter) MUST tercermin di halaman publik pada request berikutnya tanpa deploy ulang.
- **FR-015**: CRUD Portfolio dan Kategori Portfolio MUST terbuka untuk semua role yang memiliki akses panel admin, konsisten dengan resource admin lain yang sudah ada.

#### Halaman Publik

- **FR-016**: Sistem MUST menyediakan halaman listing publik di `/portfolio` yang menampilkan proyek aktif dengan: gambar sampul, judul, dan kategori per proyek.
- **FR-017**: Halaman listing `/portfolio` MUST menyediakan filter berdasarkan kategori portfolio; memilih kategori MUST membatasi daftar ke proyek aktif kategori tsb, dan kondisi filter MUST tercermin dalam URL agar bisa dibagikan/di-bookmark.
- **FR-018**: Jika tidak ada proyek aktif (atau tidak ada pada kategori terfilter), halaman listing MUST tetap tampil dengan pesan empty-state yang jelas — bukan 404.
- **FR-019**: Sistem MUST menyediakan halaman detail publik di `/portfolio/{slug}` untuk proyek aktif, menampilkan: judul, kategori, galeri gambar (semua gambar, sesuai urutan), deskripsi lengkap, dan — bila diisi — nama klien, tanggal selesai, serta tautan proyek (membuka di tab baru).
- **FR-020**: Mengakses `/portfolio/{slug}` untuk proyek nonaktif atau slug yang tidak ada MUST mengembalikan 404.
- **FR-021**: Halaman detail MUST tidak menampilkan label/section kosong untuk field opsional yang tidak diisi (nama klien, tanggal selesai, tautan proyek).

### Key Entities *(include if feature involves data)*

- **Portfolio Category**: kategori pengelompokan proyek. Atribut: nama (wajib, unik), urutan tampil (angka). Punya banyak Portfolio Project.
- **Portfolio Project**: satu proyek/portofolio. Atribut: judul (wajib), slug (unik), kategori (wajib — satu, referensi ke Portfolio Category), deskripsi HTML dari rich text (wajib), galeri gambar (daftar terurut, minimal satu; tiap gambar disimpan sebagai WebP lebar maks 1200px — FR-010b), urutan tampil (angka), status aktif (boolean). Opsional: URL tautan proyek (absolut http/https), nama klien, tanggal selesai.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin dapat membuat satu proyek lengkap (judul, kategori, deskripsi, galeri) dan melihatnya tampil di `/portfolio` serta halaman detailnya dalam satu kali proses simpan, tanpa bantuan developer atau deploy ulang.
- **SC-002**: Admin dapat menyusun ulang urutan proyek dan urutan gambar galeri; perubahan terlihat di halaman publik pada refresh berikutnya.
- **SC-003**: 100% percobaan submit dengan data tidak valid (field wajib kosong, slug duplikat, galeri kosong, URL tautan tidak valid) ditolak dengan pesan error yang jelas.
- **SC-004**: 100% proyek berstatus nonaktif atau terhapus tidak muncul di listing maupun dapat diakses via URL detail (404).
- **SC-005**: Pengunjung dapat memfilter listing berdasarkan kategori dan mendapatkan hanya proyek kategori tsb dalam 100% kasus; URL hasil filter dapat dibuka ulang dan menampilkan hasil yang sama.
- **SC-006**: Halaman detail proyek menampilkan seluruh gambar galeri sesuai urutan yang diatur admin dalam 100% kasus.
- **SC-006a**: 100% gambar galeri yang diunggah tersimpan dalam format WebP dengan lebar maksimum 1200px, tanpa admin perlu memproses gambar secara manual terlebih dahulu.
- **SC-007**: Bagian "portfolio" pada AMC-218 dianggap selesai — `/portfolio` dan `/portfolio/{slug}` menampilkan konten yang dikelola admin, bukan placeholder.

## Assumptions

- Satu proyek termasuk dalam **tepat satu** kategori (bukan multi-kategori/tag). Sistem tag terpisah di luar scope v1.
- Galeri gambar memakai pola penyimpanan daftar gambar terurut seperti modul Produk yang sudah ada. Setiap gambar di-downscale ke lebar maks 1200px (rasio dipertahankan, tanpa upscale) dan disimpan sebagai WebP saat simpan (FR-010b); form menampilkan rekomendasi ukuran (FR-010a). Tidak ada penolakan berdasarkan dimensi/rasio. Minimal satu gambar wajib.
- Visibilitas proyek memakai **toggle aktif sederhana** (`aktif`/`nonaktif`), bukan alur draft/publish terjadwal seperti Artikel (dikonfirmasi klarifikasi 2026-09-07). Portofolio proyek umumnya tidak butuh penjadwalan publish.
- Tidak ada halaman "proyek terkait"/rekomendasi di detail v1.
- Tidak ada paginasi wajib pada listing v1 — menampilkan semua proyek aktif (atau hasil filter) dalam satu grid memadai untuk volume yang diharapkan (puluhan). Paginasi adalah peningkatan opsional, bukan requirement.
- Filter kategori bersifat pilih-satu (bukan multi-select) di v1.
- Tidak ada SEO meta fields khusus per proyek di v1 — memakai judul sebagai `<title>` dan potongan awal deskripsi sebagai fallback, konsisten modul lain.
- Halaman/section lain yang sudah ada tidak diubah; modul ini menambah route `/portfolio` + `/portfolio/{slug}` baru dan resource admin baru. (Menautkan portfolio dari navigasi/footer adalah peningkatan terpisah, bukan bagian scope inti tiket ini.)
- Tanggal selesai adalah tanggal (tanpa jam); ditampilkan dalam format lokal yang mudah dibaca.
