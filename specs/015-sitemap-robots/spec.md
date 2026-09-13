# Feature Specification: Sitemap & Robots Otomatis

**Feature Branch**: `015-sitemap-robots`

**Created**: 2026-09-13

**Status**: Draft

**Input**: User description: "AMC-224: Sitemap.xml & robots.txt otomatis. Berdasarkan Linear (team Amaya ECOM, project Web Solarpanel Kit, parent Epic 5 - SEO & Performance AMC-194). Sibling langsung dari AMC-223 (SEO Management, sudah selesai) yang baru menambahkan meta tags/OG/Twitter/JSON-LD per halaman — sekarang search engine perlu tahu URL mana saja yang harus di-crawl. Scope: (1) sitemap.xml dinamis mencakup seluruh halaman publik yang bisa diakses — halaman statis (Beranda, Tentang Kami, Kontak, Karir jika modul aktif, FAQ, Artikel index, Produk index, Portfolio index), dan seluruh konten published/aktif (Product, Article yang published, CustomPage, PortfolioProject yang aktif); (2) robots.txt yang saat ini statis (Disallow kosong, mengizinkan semua) diarahkan agar menyertakan referensi ke sitemap.xml."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Search engine menemukan seluruh halaman yang bisa diakses pengunjung (Priority: P1)

Search engine (Google, Bing, dll.) membuka `sitemap.xml` situs dan menemukan daftar lengkap URL yang benar-benar bisa diakses pengunjung saat ini — halaman utama, katalog produk beserta setiap produknya, artikel yang sudah dipublikasikan, proyek portfolio yang aktif, halaman statis, dan (bila modul Karir aktif) halaman karir. Halaman yang saat ini TIDAK bisa diakses pengunjung (artikel draft/terjadwal, proyek portfolio nonaktif, halaman karir saat modul dimatikan) TIDAK ikut terdaftar.

**Why this priority**: Ini inti dari fitur — tanpa daftar URL yang akurat, seluruh investasi SEO per-halaman (meta tags, structured data dari AMC-223) tidak maksimal karena search engine bisa saja tidak pernah menemukan sebagian konten, atau sebaliknya mengindeks URL yang sudah tidak valid.

**Independent Test**: Buka `/sitemap.xml` — setiap URL yang tercantum, ketika dibuka satu per satu, MUST mengembalikan halaman yang sukses tampil (bukan halaman kosong/error); publikasikan satu artikel baru atau ubah satu proyek portfolio jadi aktif → muncul di `sitemap.xml` pada permintaan berikutnya tanpa perlu proses manual tambahan; nonaktifkan satu proyek portfolio atau matikan modul Karir → URL terkait hilang dari `sitemap.xml` pada permintaan berikutnya.

**Acceptance Scenarios**:

1. **Given** situs memiliki produk, artikel published, dan proyek portfolio aktif, **When** `sitemap.xml` dibuka, **Then** setiap URL detail dari ketiganya tercantum, masing-masing hanya satu kali.
2. **Given** ada artikel berstatus draft atau terjadwal (belum waktunya tayang), **When** `sitemap.xml` dibuka, **Then** URL artikel tsb TIDAK tercantum.
3. **Given** ada proyek portfolio yang dinonaktifkan, **When** `sitemap.xml` dibuka, **Then** URL proyek tsb TIDAK tercantum.
4. **Given** modul Karir sedang dimatikan (lihat AMC-212), **When** `sitemap.xml` dibuka, **Then** URL halaman Karir TIDAK tercantum; **Given** modul Karir diaktifkan kembali, **Then** URL tsb muncul kembali pada permintaan berikutnya.
5. **Given** admin menambah/menghapus/menerbitkan konten apa pun, **When** `sitemap.xml` dibuka ulang setelahnya, **Then** daftar URL langsung mencerminkan perubahan tanpa perlu proses build/deploy tambahan.

---

### User Story 2 - Search engine diarahkan ke sitemap lewat robots.txt (Priority: P2)

Search engine yang membuka `robots.txt` situs menemukan referensi eksplisit ke lokasi `sitemap.xml`, sehingga proses penemuan (discovery) URL tidak bergantung pada admin mendaftarkan sitemap secara manual di setiap search engine console.

**Why this priority**: Pelengkap alami dari User Story 1 — banyak crawler membaca `robots.txt` lebih dulu sebelum mencari sitemap; tanpa referensi ini, manfaat sitemap baru terasa setelah admin repot mendaftarkannya manual.

**Independent Test**: Buka `/robots.txt` — baris `Sitemap:` MUST mengarah ke URL absolut `sitemap.xml` yang valid dan bisa diakses (dites lewat User Story 1); aturan `Disallow` yang sudah ada (mengizinkan seluruh halaman publik di-crawl) TIDAK berubah.

**Acceptance Scenarios**:

1. **Given** pengunjung/crawler membuka `robots.txt`, **When** melihat isinya, **Then** terdapat baris yang menunjuk ke URL absolut `sitemap.xml` situs ini.
2. **Given** situs diakses dari domain/environment yang berbeda (mis. staging vs production), **When** `robots.txt` dibuka, **Then** URL sitemap yang tercantum MUST selalu mengarah ke domain yang sedang diakses (bukan domain hardcoded lain).

---

### Edge Cases

- Situs baru tanpa konten sama sekali (belum ada produk/artikel/portfolio) → `sitemap.xml` tetap valid dan bisa dibuka, hanya berisi halaman-halaman statis yang selalu ada.
- Custom Page dengan slug yang mengandung karakter yang perlu di-escape untuk XML (mis. `&`) → URL tetap valid secara format XML, tidak merusak keseluruhan dokumen.
- Jumlah konten sangat banyak (ratusan/ribuan produk-artikel-proyek) → `sitemap.xml` tetap menghasilkan satu dokumen valid yang bisa dibuka tanpa timeout pada penggunaan wajar starter kit ini (lihat Success Criteria).
- Permintaan `sitemap.xml` atau `robots.txt` MUST selalu berhasil (tidak pernah mengembalikan halaman error) selama situs itu sendiri bisa diakses.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Sistem MUST menyediakan `sitemap.xml` yang dapat diakses publik di URL root situs (`/sitemap.xml`).
- **FR-002**: `sitemap.xml` MUST mencantumkan seluruh halaman statis yang selalu bisa diakses pengunjung: Beranda, Tentang Kami, Kontak, FAQ, index Artikel, index Produk, index Portfolio.
- **FR-003**: `sitemap.xml` MUST mencantumkan halaman Karir hanya ketika modul Karir sedang aktif, dan MUST tidak mencantumkannya ketika modul tsb dimatikan (FR ini konsisten dengan aturan akses modul Karir yang sudah ada).
- **FR-004**: `sitemap.xml` MUST mencantumkan URL detail setiap Produk yang ada.
- **FR-005**: `sitemap.xml` MUST mencantumkan URL detail setiap Artikel yang berstatus published (sudah lewat tanggal terbitnya), dan MUST tidak mencantumkan artikel draft atau yang dijadwalkan untuk masa depan.
- **FR-006**: `sitemap.xml` MUST mencantumkan URL setiap Halaman Statis (Custom Page) yang ada.
- **FR-007**: `sitemap.xml` MUST mencantumkan URL detail setiap Proyek Portfolio yang berstatus aktif, dan MUST tidak mencantumkan proyek yang dinonaktifkan.
- **FR-008**: Setiap URL di `sitemap.xml` MUST berupa URL absolut (termasuk skema dan domain) yang valid dan dapat langsung diakses.
- **FR-009**: `sitemap.xml` MUST dihasilkan berdasarkan data terkini setiap kali diakses (bukan berkas statis yang dibuat sekali saat build) — perubahan konten (tambah/hapus/ubah status) MUST langsung tercermin pada permintaan berikutnya.
- **FR-010**: `robots.txt` yang sudah ada MUST diperbarui untuk menyertakan referensi ke lokasi `sitemap.xml`, memakai URL absolut yang sesuai domain yang sedang diakses.
- **FR-011**: Aturan akses (`Disallow`) yang sudah ada di `robots.txt` — mengizinkan seluruh halaman publik untuk di-crawl — MUST tidak berubah oleh fitur ini.
- **FR-012**: Setiap URL yang tercantum di `sitemap.xml` MUST, ketika diakses langsung, berhasil menampilkan halaman (tidak mengarah ke halaman yang gagal dimuat).

### Key Entities

- Tidak ada entitas data baru — fitur ini murni membaca data konten yang sudah ada (Produk, Artikel, Halaman Statis, Proyek Portfolio, status modul Karir) untuk menyusun satu daftar URL, tanpa menyimpan state baru.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% URL yang tercantum di `sitemap.xml`, saat diakses satu per satu, berhasil menampilkan halaman (nol URL rusak/404).
- **SC-002**: Konten baru yang ditambahkan admin (mis. artikel dipublikasikan, proyek portfolio diaktifkan) muncul di `sitemap.xml` pada permintaan berikutnya — tanpa langkah manual atau jeda proses build.
- **SC-003**: Konten yang tidak lagi bisa diakses publik (nonaktif/dihapus/draft) hilang dari `sitemap.xml` pada permintaan berikutnya — nol URL basi yang mengarah ke konten tak-tersedia.
- **SC-004**: `robots.txt` yang diuji lewat validator sitemap/robots standar menunjukkan referensi sitemap yang valid dan dapat ditemukan.
- **SC-005**: `sitemap.xml` tetap dapat diakses dan valid pada volume konten skala wajar starter kit ini (ratusan item gabungan produk/artikel/portfolio/halaman) tanpa gagal dimuat.

## Assumptions

- Cakupan "halaman publik yang bisa diakses" mengikuti daftar route publik yang sudah ada saat ini (lihat modul-modul yang sudah selesai: Produk, Artikel, Portfolio, Custom Page, Karir, FAQ, Kontak, Tentang Kami, Beranda) — bila ada modul publik baru di masa depan, penambahannya ke sitemap adalah pekerjaan lanjutan, bukan bagian dari fitur ini.
- FAQ tidak punya halaman detail per item (satu halaman `/faq` berisi semua pertanyaan) — yang dicantumkan di sitemap hanya URL `/faq` itu sendiri (sudah tercakup di FR-002), bukan per-pertanyaan.
- Tidak ada dukungan multi-sitemap/sitemap index terpisah — satu `sitemap.xml` tunggal dianggap cukup untuk skala volume konten starter kit ini (lihat SC-005); pemecahan jadi beberapa file sitemap adalah peningkatan performa yang bisa ditangani terpisah bila volume ternyata jauh melampaui ini.
- Prioritas (`priority`) dan frekuensi perubahan (`changefreq`) per URL — bila disertakan dalam format sitemap — memakai nilai wajar/konsisten yang sama untuk kategori URL yang sejenis, bukan dikustomisasi manual per item oleh admin (tidak ada kebutuhan admin mengatur ini secara eksplisit).
- Fitur ini tidak menambah kontrol admin baru (mis. toggle "sembunyikan dari sitemap" per konten) — cakupan sitemap murni mengikuti status publik/aktif konten yang sudah ada saat ini.
