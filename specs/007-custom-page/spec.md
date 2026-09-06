# Feature Specification: Custom Page (Halaman Statis Bebas)

**Feature Branch**: `007-custom-page`

**Created**: 2026-09-07

**Status**: Draft

**Input**: User description: "AMC-217: Custom Page — halaman statis (About Us, TnC, Privacy Policy) dengan rich text editor + slug. Admin bisa membuat, mengedit, menghapus halaman statis bebas (judul, slug, isi lewat rich text editor) lewat panel admin, tanpa developer perlu membuat Blade view baru untuk tiap halaman legal/informasi sederhana. Prioritas awal: mengisi halaman Kebijakan Privasi dan Syarat & Ketentuan yang saat ini belum ada sungguhan (link footer masih placeholder ke /tentang-kami)."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Admin membuat & mengedit halaman statis (Priority: P1) 🎯 MVP

Admin membuka menu Halaman di panel admin, menulis halaman baru (judul, isi lewat rich text editor), menyimpannya, lalu halaman tsb langsung bisa diakses pengunjung lewat URL yang dihasilkan dari judulnya — tanpa developer perlu membuat file Blade baru. Admin juga bisa mengedit atau menghapus halaman yang sudah ada.

**Why this priority**: Nilai bisnis inti tiket — tanpa kemampuan tulis/edit dasar, halaman legal/informasi sederhana (Kebijakan Privasi, Syarat & Ketentuan) tetap tidak bisa dibuat sungguhan oleh siapa pun selain developer.

**Independent Test**: Login sebagai admin, tulis halaman baru berjudul "Kebijakan Privasi" dengan isi lewat rich text editor, simpan, buka URL halaman tsb sebagai pengunjung, verifikasi isi tampil sesuai yang ditulis (termasuk format seperti bold/heading/link). Edit halaman tsb, verifikasi perubahan tampil. Hapus halaman, verifikasi URL-nya mengembalikan 404.

**Acceptance Scenarios**:

1. **Given** admin berada di form tambah halaman, **When** admin mengisi judul dan isi lewat rich text editor lalu menyimpan, **Then** halaman tersimpan dan langsung bisa diakses pengunjung di URL-nya tanpa deploy ulang.
2. **Given** admin mengisi judul tanpa mengisi slug, **When** admin menyimpan, **Then** sistem menghasilkan slug otomatis dari judul (dan admin tetap bisa meng-override slug secara manual).
3. **Given** admin mencoba menyimpan halaman dengan slug yang sudah dipakai halaman lain, **When** admin menyimpan, **Then** sistem menolak dengan pesan error jelas, bukan menyimpan duplikat.
4. **Given** admin mengedit halaman yang sudah ada, **When** admin mengubah judul/isi dan menyimpan, **Then** perubahan langsung tampil di URL publik pada request berikutnya.
5. **Given** admin mencoba menyimpan halaman tanpa mengisi judul atau isi, **When** admin menyimpan, **Then** sistem menolak dengan pesan validasi per field.
6. **Given** admin menghapus sebuah halaman, **When** admin mengonfirmasi penghapusan, **Then** URL halaman tsb mengembalikan 404 pada request berikutnya.

---

### User Story 2 - Pengunjung mengakses Kebijakan Privasi & Syarat Ketentuan yang sungguhan (Priority: P1)

Pengunjung yang mengklik link "Kebijakan Privasi" atau "Syarat & Ketentuan" di footer situs sekarang diarahkan ke halaman sungguhan berisi konten yang relevan — bukan lagi ke halaman "Tentang Kami" seperti placeholder saat ini.

**Why this priority**: Ini adalah kebutuhan konkret yang memicu tiket ini — kedua link tsb saat ini menyesatkan pengunjung (mengarah ke halaman yang tidak relevan), yang berisiko dari sisi kepatuhan/kepercayaan pengunjung.

**Independent Test**: Sebagai admin, buat dua halaman "Kebijakan Privasi" dan "Syarat & Ketentuan" via User Story 1, lalu perbarui link footer supaya mengarah ke keduanya. Buka situs sebagai pengunjung, klik masing-masing link di footer, verifikasi mendarat di halaman yang isinya sesuai (bukan lagi Tentang Kami).

**Acceptance Scenarios**:

1. **Given** admin sudah membuat halaman "Kebijakan Privasi" dan "Syarat & Ketentuan", **When** pengunjung mengklik link terkait di footer, **Then** pengunjung mendarat di halaman dengan judul dan isi yang sesuai, bukan halaman Tentang Kami.
2. **Given** salah satu dari kedua halaman tsb belum dibuat admin, **When** pengunjung mengklik link terkait di footer, **Then** sistem menampilkan halaman "tidak ditemukan" (404) — bukan silently redirect ke halaman lain yang tidak relevan.

### Edge Cases

- Apa yang terjadi jika admin membuat halaman dengan slug yang sama persis dengan nama URL statis lain (mis. "produk", "karir")? Tidak ada konflik — Custom Page selalu diakses lewat `/halaman/{slug}` (FR-014), path yang berbeda dari `/produk` atau `/karir`, jadi slug seperti "produk" tetap valid dipakai sebagai slug Custom Page tanpa bentrok.
- Apa yang terjadi jika seluruh halaman custom dihapus? Tidak ada halaman "index" gabungan yang perlu ditampilkan — setiap halaman diakses langsung lewat URL/slug-nya masing-masing (tidak seperti Artikel/Produk yang punya halaman listing).
- Apa yang terjadi jika isi rich text menyertakan gambar inline? Gambar MUST tersimpan dan tampil sebagaimana mestinya (dukungan bawaan rich text editor), tapi tidak ada validasi ukuran/kompresi khusus untuk gambar inline di fitur ini (di luar scope, berbeda dari featured image Artikel).

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Admin panel MUST menyediakan halaman daftar Custom Page (CRUD) — tambah, edit, hapus.
- **FR-002**: Admin MUST bisa menulis halaman baru dengan mengisi: judul dan isi (lewat rich text editor).
- **FR-003**: Sistem MUST menghasilkan slug otomatis dari judul halaman saat dibuat, dan admin MUST bisa meng-override slug secara manual.
- **FR-004**: Sistem MUST memvalidasi slug Custom Page unik antar halaman — submit dengan slug yang sudah dipakai MUST ditolak dengan pesan error jelas.
- **FR-005**: Slug Custom Page MUST hanya perlu unik terhadap sesama Custom Page — TIDAK perlu divalidasi terhadap daftar route statis yang sudah ada, karena Custom Page diakses lewat prefix path terpisah (`/halaman/{slug}`, FR-014) yang secara struktural tidak pernah tumpang tindih dengan route statis manapun (mis. `/produk`, `/karir`).
- **FR-006**: Admin MUST bisa mengedit judul dan isi halaman yang sudah ada, dan perubahan MUST langsung tercermin di URL publiknya tanpa deploy ulang.
- **FR-007**: Admin MUST bisa menghapus halaman, dengan konfirmasi terlebih dahulu sebelum penghapusan diproses; setelah dihapus, URL halaman tsb MUST mengembalikan 404.
- **FR-008**: Sistem MUST memvalidasi field wajib (judul, isi) — submit tanpa field wajib MUST ditolak dengan pesan error per field.
- **FR-009**: Isi halaman MUST ditulis lewat rich text editor (WYSIWYG) yang mendukung format dasar (bold/italic, heading, link, gambar inline) — bukan textarea teks polos — dan disimpan sebagai HTML.
- **FR-010**: CRUD Custom Page MUST terbuka untuk semua role yang memiliki akses panel admin, konsisten dengan resource admin lain yang sudah ada.
- **FR-011**: Mengakses URL Custom Page yang slug-nya tidak/belum ada MUST mengembalikan 404.
- **FR-012**: Link "Kebijakan Privasi" dan "Syarat & Ketentuan" di navigasi footer publik MUST diarahkan ke URL Custom Page terkait (bukan lagi ke `/tentang-kami`).
- **FR-013**: Halaman "Tentang Kami" yang sudah ada (desain khusus dengan hero section, grid visi-misi, dll.) MUST TETAP menjadi halaman Blade terpisah yang tidak diubah/dimigrasikan oleh fitur ini — Custom Page adalah modul BARU untuk halaman-halaman yang belum ada (mis. Kebijakan Privasi, Syarat & Ketentuan), bukan pengganti halaman dengan desain khusus yang sudah ada (Clarifications Q1).
- **FR-014**: URL Custom Page MUST diakses lewat prefix path `/halaman/{slug}` — bukan di root — sehingga TIDAK PERNAH bentrok dengan route statis mana pun yang sudah ada atau akan ditambahkan di masa depan (Clarifications Q2).

### Key Entities

- **Custom Page** (baru): judul, slug (unik), isi (HTML dari rich text editor). Tidak berelasi ke entity lain, tidak punya kategori/tag/status draft-publish (setiap halaman yang dibuat langsung dapat diakses — lihat Assumptions).

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin dapat menulis halaman baru dan melihatnya bisa diakses pengunjung dalam satu kali proses simpan, tanpa bantuan developer atau deploy ulang.
- **SC-002**: Admin dapat menyelesaikan pembuatan halaman "Kebijakan Privasi" atau "Syarat & Ketentuan" (isi lengkap + simpan) dalam waktu kurang dari 5 menit.
- **SC-003**: 100% percobaan submit dengan data tidak valid (slug duplikat antar Custom Page, field wajib kosong) ditolak dengan pesan error yang jelas.
- **SC-004**: Setelah halaman dihapus, 100% akses ke URL lamanya mengembalikan 404.
- **SC-005**: 100% pengunjung yang mengklik link "Kebijakan Privasi"/"Syarat & Ketentuan" di footer (setelah admin membuat kedua halaman tsb) mendarat di halaman dengan konten yang sesuai, bukan halaman Tentang Kami.

## Clarifications

### Session 2026-09-07

- Q: Halaman "Tentang Kami" yang sudah ada (desain custom: hero section, grid visi-misi, dll.) — apakah perlu dimigrasikan jadi Custom Page (rich text polos, kehilangan desain khusus), atau Custom Page murni modul baru untuk halaman yang belum ada, dan Tentang Kami dibiarkan seperti sekarang? → A: Tentang Kami TETAP Blade khusus, tidak disentuh — Custom Page murni untuk halaman baru (FR-013).
- Q: URL Custom Page sebaiknya pakai prefix path tertentu, atau langsung di root dengan validasi anti-bentrok? → A: Prefix `/halaman/{slug}` — tidak pernah bentrok dengan route statis mana pun, tanpa perlu daftar reserved slug (FR-014, FR-005).

## Assumptions

- Custom Page TIDAK punya konsep draft/publish terpisah (berbeda dari Artikel) — begitu halaman dibuat, langsung bisa diakses pengunjung di URL-nya; menghapusnya adalah satu-satunya cara "menyembunyikan" halaman. Ini reasonable karena use-case utamanya (halaman legal/informasi statis) umumnya tidak butuh alur draft yang rumit.
- Tidak ada halaman listing/index gabungan untuk Custom Page di sisi publik — setiap halaman murni diakses lewat URL-nya sendiri, konsisten dengan sifatnya sebagai halaman statis mandiri (bukan konten yang di-browse seperti Artikel/Produk).
- Custom Page TIDAK mendukung nested/hierarki (halaman induk-anak) — setiap halaman berdiri sendiri dengan satu slug flat, sesuai kebutuhan tiket (halaman legal sederhana, bukan struktur dokumentasi bertingkat).
- Gambar inline dalam isi rich text (jika ada) memakai perilaku upload bawaan `RichEditor` Filament, TANPA validasi/kompresi tambahan (berbeda dari featured image Artikel yang wajib WebP) — di luar scope fitur ini.
- Tidak ada SEO meta fields tambahan (meta title/description custom per halaman) di v1 — memakai judul halaman sebagai `<title>` dan potongan awal isi sebagai fallback deskripsi, konsisten pola yang sudah ada di modul lain.
