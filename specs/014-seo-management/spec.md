# Feature Specification: SEO Management

**Feature Branch**: `014-seo-management`

**Created**: 2026-09-12

**Status**: Draft

**Input**: User description: "AMC-223: Setting SEO global + per halaman/konten (meta title, description, OG image). Berdasarkan Linear (team Amaya ECOM, project Web Solarpanel Kit, parent Epic 5 - SEO & Performance AMC-194). Scope: (1) SEO meta component di layout publik — OG tags, Twitter card, canonical URL, fallback ke BrandSettings (app_name, og_image_path yang sudah ada tapi belum dipakai); (2) field meta_title/meta_description/og_image per konten di Product, Article, CustomPage, PortfolioProject dengan tab \"SEO\" di Filament resource masing-masing; (3) JSON-LD structured data — Organization global, FAQPage di halaman FAQ, Article di halaman artikel, Product di halaman produk."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Setiap halaman publik tampil rapi saat dibagikan (Priority: P1)

Saat pengunjung membagikan link halaman mana pun di situs (beranda, kontak, karir, FAQ, atau halaman konten) ke WhatsApp, Facebook, atau X/Twitter, pratinjau yang muncul menampilkan judul, deskripsi, dan gambar yang relevan dan benar — bukan blank, generik, atau salah gambar. Search engine juga melihat satu alamat kanonis yang konsisten untuk tiap halaman, tidak ada duplikat URL yang membingungkan pengindeksan.

**Why this priority**: Ini fondasi yang berlaku ke *seluruh* situs sekaligus tanpa butuh admin mengisi apa pun — nilai langsung terasa begitu dirilis, dan seluruh cerita lain (per-konten, structured data) dibangun di atasnya.

**Independent Test**: Bagikan URL halaman apa pun (termasuk yang belum pernah disentuh admin) ke social media debugger (mis. Facebook Sharing Debugger) — pratinjau tampil dengan judul, deskripsi, dan gambar yang masuk akal, dan `rel=canonical` mengarah ke URL bersih halaman itu sendiri.

**Acceptance Scenarios**:

1. **Given** admin belum pernah mengatur SEO apa pun, **When** halaman beranda dibagikan ke social media, **Then** pratinjau menampilkan nama brand, deskripsi singkat perusahaan, dan gambar default — tidak ada field yang kosong.
2. **Given** pengunjung membuka halaman apa pun di situs, **When** melihat kode halaman, **Then** terdapat satu `canonical URL` yang mengarah ke URL bersih halaman tersebut (tanpa parameter tracking/query yang tidak relevan).
3. **Given** admin mengganti nama brand atau gambar OG default di pengaturan brand, **When** halaman yang belum punya override khusus dibagikan ulang, **Then** pratinjau ikut memakai nama/gambar brand yang baru.

---

### User Story 2 - Admin mengoptimalkan tampilan pencarian per konten (Priority: P2)

Admin ingin produk, artikel, halaman statis (About/TnC/Privacy), dan proyek portfolio tertentu tampil dengan judul dan deskripsi pencarian yang dioptimalkan (bukan sekadar nama produk apa adanya), serta punya gambar pratinjau sosial sendiri yang beda dari gambar konten utamanya — tanpa perlu bantuan developer.

**Why this priority**: Nilai tambah signifikan untuk konten yang penting secara bisnis (produk unggulan, artikel promosi), tapi situs tetap berfungsi baik tanpa ini karena User Story 1 sudah menyediakan fallback yang layak.

**Independent Test**: Buka salah satu produk di panel admin, isi tab "SEO" dengan judul/deskripsi/gambar kustom, simpan, lalu buka halaman produk tersebut di publik dan bagikan — pratinjau memakai nilai kustom itu, bukan fallback otomatis.

**Acceptance Scenarios**:

1. **Given** admin membuka form edit Produk/Artikel/Halaman Statis/Proyek Portfolio, **When** admin membuka tab "SEO", **Then** tersedia field judul pencarian, deskripsi pencarian, dan unggah gambar pratinjau sosial — semuanya opsional.
2. **Given** admin mengisi ketiga field SEO pada satu produk lalu menyimpan, **When** halaman produk tersebut diakses di publik, **Then** judul tab browser, meta deskripsi, dan gambar Open Graph memakai nilai yang diisi admin.
3. **Given** admin membiarkan field SEO kosong pada suatu artikel, **When** halaman artikel diakses, **Then** sistem otomatis memakai judul artikel, ringkasan (excerpt) artikel, dan gambar utama artikel sebagai pengganti — tetap tidak ada yang kosong.
4. **Given** admin mengetik deskripsi pencarian yang sangat panjang, **When** admin menyimpan, **Then** sistem menampilkan indikator panjang karakter yang disarankan sehingga admin tahu bagian mana yang berisiko terpotong di hasil pencarian.

---

### User Story 3 - Search engine menampilkan hasil pencarian yang lebih kaya (Priority: P3)

Search engine dapat memahami struktur data situs (identitas perusahaan, daftar tanya-jawab, artikel, produk) sehingga berpotensi menampilkan hasil pencarian yang lebih kaya (rich result) — misalnya daftar pertanyaan FAQ langsung di hasil pencarian Google, atau info perusahaan di panel pengetahuan.

**Why this priority**: Manfaat jangka menengah/panjang untuk visibilitas pencarian organik, tapi tidak terlihat langsung oleh pengunjung situs seperti dua cerita sebelumnya, dan bergantung pada crawling/reindexing search engine yang di luar kendali sistem.

**Independent Test**: Tempel URL halaman FAQ, halaman artikel, atau halaman produk ke Google Rich Results Test — markup `Organization`, `FAQPage`, `Article`, atau `Product` yang sesuai terdeteksi valid tanpa error.

**Acceptance Scenarios**:

1. **Given** halaman publik apa pun dimuat, **When** search engine membaca kode halaman, **Then** terdapat markup data terstruktur `Organization` berisi nama brand, URL situs, dan logo.
2. **Given** halaman FAQ menampilkan daftar pertanyaan, **When** search engine membaca kode halaman, **Then** terdapat markup `FAQPage` yang mencerminkan pertanyaan dan jawaban yang sedang ditampilkan.
3. **Given** halaman detail artikel yang sudah dipublikasikan, **When** search engine membaca kode halaman, **Then** terdapat markup `Article` berisi judul, gambar, tanggal terbit, dan penulis/redaksi.
4. **Given** halaman detail produk, **When** search engine membaca kode halaman, **Then** terdapat markup `Product` berisi nama, deskripsi, dan gambar produk.

---

### Edge Cases

- Konten (produk/artikel/proyek/halaman) tanpa gambar sama sekali, dan admin juga tidak mengunggah gambar SEO kustom → sistem memakai gambar OG default situs, bukan gambar rusak/kosong.
- Artikel belum dipublikasikan (draft) tetap tidak bisa diakses publik (perilaku existing tidak berubah), sehingga tidak perlu markup SEO/structured data untuknya.
- FAQ tanpa item sama sekali → halaman FAQ tidak menyisipkan markup `FAQPage` kosong (menghindari hasil pencarian kosong/menyesatkan).
- Produk tanpa harga (price kosong) → markup `Product` tetap valid tanpa klaim harga/ketersediaan yang tidak akurat.
- Admin mengisi judul SEO tapi mengosongkan deskripsi SEO (atau sebaliknya) → tiap field jatuh ke fallback masing-masing secara independen, bukan ikut kosong semua.
- Nama brand atau gambar OG default diubah di pengaturan brand → seluruh halaman yang belum punya override kustom otomatis mengikuti nilai terbaru pada permintaan berikutnya (tidak perlu edit ulang satu-satu).

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Setiap halaman publik MUST menyertakan meta tag Open Graph (judul, deskripsi, gambar, tipe, nama situs) dan Twitter Card yang terisi — tidak boleh ada nilai kosong/placeholder.
- **FR-002**: Setiap halaman publik MUST menyertakan satu `canonical URL` yang mengarah ke alamat bersih halaman itu sendiri.
- **FR-003**: Sistem MUST menyediakan nilai SEO default tingkat situs (judul situs, deskripsi situs, gambar OG default) yang dipakai halaman mana pun yang belum punya override khusus — nilai default ini bersumber dari pengaturan brand yang sudah ada (nama brand, gambar OG).
- **FR-004**: Admin MUST dapat mengatur judul pencarian (meta title), deskripsi pencarian (meta description), dan gambar pratinjau sosial (OG image) secara individual untuk tiap Produk, Artikel, Halaman Statis (Custom Page), dan Proyek Portfolio, melalui satu tab "SEO" khusus di form masing-masing.
- **FR-005**: Ketiga field SEO per konten (judul, deskripsi, gambar) MUST bersifat opsional — admin boleh mengisi sebagian atau tidak mengisi sama sekali.
- **FR-006**: Saat field SEO per konten dikosongkan, sistem MUST menghasilkan fallback yang relevan secara independen per field: judul → nama/judul konten, deskripsi → ringkasan/deskripsi singkat konten yang sudah ada, gambar → gambar utama/pertama konten tersebut, lalu ke gambar OG default situs bila konten juga tidak punya gambar.
- **FR-007**: Sistem MUST menampilkan indikator panjang karakter yang disarankan pada field judul dan deskripsi SEO di form admin, sebagai panduan (bukan pembatas keras yang menolak penyimpanan).
- **FR-008**: Sistem MUST menyisipkan markup data terstruktur (JSON-LD) `Organization` — berisi nama brand, URL situs, dan logo — di setiap halaman publik.
- **FR-009**: Halaman FAQ MUST menyisipkan markup data terstruktur `FAQPage` yang mencerminkan daftar pertanyaan-jawaban yang sedang ditampilkan, dan MUST dilewati (tidak disisipkan) apabila tidak ada item FAQ.
- **FR-010**: Halaman detail Artikel yang published MUST menyisipkan markup data terstruktur `Article` berisi judul, gambar, tanggal terbit, dan redaksi/penulis.
- **FR-011**: Halaman detail Produk MUST menyisipkan markup data terstruktur `Product` berisi nama, deskripsi, dan gambar produk; harga MUST hanya disertakan dalam markup apabila produk tersebut memiliki harga terisi.
- **FR-012**: Perubahan pada pengaturan SEO default situs (di pengaturan brand) MUST langsung berlaku ke semua halaman yang belum punya override kustom, tanpa perlu mengedit ulang tiap konten.
- **FR-013**: Sistem MUST tetap menampilkan halaman secara normal bagi pengunjung meskipun seluruh pengaturan SEO (default maupun per-konten) belum pernah diisi admin sama sekali sejak instalasi awal.

### Key Entities

- **Metadata SEO Konten**: Kumpulan tiga atribut opsional (judul pencarian, deskripsi pencarian, gambar pratinjau sosial) yang melekat pada satu Produk, Artikel, Halaman Statis, atau Proyek Portfolio. Independen dari konten utama (nama/judul, deskripsi, gambar konten) yang sudah ada — hanya dipakai untuk tampilan di luar situs (hasil pencarian, pratinjau share).
- **Pengaturan SEO Default Situs**: Perluasan dari pengaturan brand yang sudah ada (nama brand, gambar OG) — menjadi sumber fallback tunggal untuk semua halaman yang tidak punya Metadata SEO Konten sendiri.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% halaman publik (beranda, kontak, karir, FAQ, tentang kami, serta seluruh Produk/Artikel/Halaman Statis/Proyek Portfolio yang published) menghasilkan pratinjau share yang lengkap (judul, deskripsi, gambar terisi) saat diuji lewat social media debugger — tidak ada satu pun yang blank atau generik.
- **SC-002**: Admin dapat mengubah judul, deskripsi, dan gambar pencarian untuk satu konten spesifik dan melihat perubahannya tercermin di halaman publik dalam satu kali simpan, tanpa bantuan developer.
- **SC-003**: Google Rich Results Test / Schema Markup Validator menunjukkan nol error pada markup `Organization`, `FAQPage`, `Article`, dan `Product` di halaman-halaman yang relevan.
- **SC-004**: Tidak ada halaman publik yang terdeteksi memiliki lebih dari satu `canonical URL` berbeda untuk konten yang sama (nol temuan duplicate-canonical saat audit).
- **SC-005**: Mengganti gambar OG default atau nama brand di satu tempat (pengaturan brand) langsung terlihat di seluruh halaman yang belum di-override, tanpa mengedit konten satu per satu.

## Assumptions

- Cakupan konten yang mendapat override SEO per-item dibatasi sesuai yang diminta: Produk, Artikel, Halaman Statis (Custom Page), dan Proyek Portfolio. Halaman statis bawaan (Beranda, Tentang Kami, Kontak, Karir, FAQ) memakai judul/deskripsi halaman yang sudah ada di kode (`@section('title')`/`@section('meta_description')`) ditambah fallback OG/Twitter/canonical baru dari fitur ini — tidak mendapat form admin sendiri di iterasi ini.
- Markup `Product` JSON-LD tidak menyertakan atribut ketersediaan stok (`availability`) karena situs ini adalah katalog/company-profile, bukan checkout online — hanya `price` yang disertakan, dan hanya jika field harga produk terisi.
- Markup `Organization` tidak menyertakan `sameAs` (tautan sosial media) karena URL sosial media resmi belum tersimpan sebagai data terkelola di sistem manapun saat ini — bisa ditambahkan di iterasi berikutnya begitu data itu tersedia.
- Kontrol "noindex" per konten (mengecualikan halaman tertentu dari pengindeksan search engine) berada di luar cakupan fitur ini.
- Sitemap XML dan `robots.txt` dinamis berada di luar cakupan fitur ini (item backlog terpisah, AMC-224).
- Panjang karakter yang disarankan mengikuti konvensi umum hasil pencarian (judul ±60 karakter, deskripsi ±160 karakter) sebagai panduan visual, bukan validasi yang menolak penyimpanan.
- Gambar pratinjau sosial (OG image) yang diunggah admin tidak divalidasi rasio/dimensi secara ketat oleh sistem; hanya diberi panduan ukuran yang disarankan.
