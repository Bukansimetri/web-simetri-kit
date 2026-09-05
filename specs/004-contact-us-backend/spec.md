# Feature Specification: Contact Us Backend

**Feature Branch**: `004-contact-us-backend`

**Created**: 2026-09-05

**Status**: Draft

**Input**: User description: "AMC-216: Contact Us form + notifikasi email/WA + Contact Us Resource di panel. Halaman /kontak (dari 002-theme-branding-system) sudah punya form lengkap (nama, no. HP/WhatsApp, topik kebutuhan, pesan) dengan validasi client-side, tapi TIDAK ada submit sungguhan — form cuma menampilkan pesan sukses palsu tanpa menyimpan atau mengirim apa pun. Tujuan: form Kontak benar-benar menyimpan submission ke database dan mengirim notifikasi ke admin/tim sales, plus admin bisa melihat & mengelola daftar submission masuk lewat panel admin."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Pengunjung mengirim pesan lewat form Kontak (Priority: P1) 🎯 MVP

Pengunjung situs mengisi form Kontak (nama, nomor HP/WhatsApp, topik kebutuhan, pesan) dan menekan kirim. Pesan tsb benar-benar tersimpan di sistem — bukan cuma pesan sukses palsu seperti sebelumnya — dan pengunjung melihat konfirmasi bahwa pesannya sudah diterima.

**Why this priority**: Ini inti dari tiket — tanpa penyimpanan sungguhan, setiap lead yang mengisi form hilang begitu saja, padahal itu satu-satunya tujuan halaman Kontak ada.

**Independent Test**: Buka `/kontak` tanpa login, isi form dengan data valid, submit, verifikasi muncul konfirmasi sukses dan submission tsb ada di database (bisa dicek lewat panel admin di US2).

**Acceptance Scenarios**:

1. **Given** pengunjung mengisi seluruh field wajib dengan data valid, **When** pengunjung submit, **Then** submission tersimpan di sistem dan pengunjung melihat konfirmasi sukses tanpa reload halaman penuh.
2. **Given** pengunjung mengisi form dengan field wajib kosong atau format tidak valid, **When** pengunjung submit, **Then** sistem menampilkan pesan validasi yang jelas dan submission TIDAK tersimpan.
3. **Given** submission berhasil tersimpan, **When** proses penyimpanan selesai, **Then** sistem MUST juga memicu notifikasi ke admin (lihat US3) tanpa membuat pengunjung menunggu lama untuk melihat konfirmasi.
4. **Given** submission berhasil tersimpan dan nomor WhatsApp bisnis instalasi sudah dikonfigurasi, **When** konfirmasi sukses tampil, **Then** sistem MUST membuka WhatsApp (link `wa.me`) ke nomor bisnis tsb di tab/jendela baru, dengan isi pesan sudah terisi otomatis dari data submission — supaya pengunjung tinggal menekan kirim di WhatsApp mereka sendiri.
5. **Given** nomor WhatsApp bisnis instalasi BELUM dikonfigurasi, **When** submission berhasil tersimpan, **Then** sistem MUST tetap menampilkan konfirmasi sukses seperti biasa tanpa mencoba membuka WhatsApp (bukan error/link rusak).

---

### User Story 2 - Admin melihat & mengelola daftar pesan masuk (Priority: P1)

Admin membuka panel admin dan melihat daftar seluruh pesan yang masuk dari form Kontak, termasuk detail pengirim dan isi pesan, serta bisa menandai status tiap pesan (mis. belum ditindaklanjuti / sudah dihubungi).

**Why this priority**: Melengkapi US1 — pesan yang tersimpan tidak berguna kalau admin tidak punya cara melihatnya. Prioritas sama tingginya karena US1 tanpa ini cuma "menyimpan ke lubang hitam".

**Independent Test**: Login sebagai admin, buka menu Pesan Masuk/Contact Us, verifikasi submission dari US1 muncul dengan seluruh detailnya. Ubah status salah satu submission, verifikasi tersimpan.

**Acceptance Scenarios**:

1. **Given** ada submission tersimpan dari pengunjung, **When** admin membuka daftar Contact Us di panel admin, **Then** seluruh submission tampil dengan nama, kontak, topik, pesan, dan waktu masuk.
2. **Given** admin membuka salah satu submission, **When** admin mengubah statusnya (mis. jadi "Sudah Dihubungi"), **Then** perubahan status tersimpan dan terlihat di daftar.
3. **Given** admin ingin fokus ke pesan yang belum ditindaklanjuti, **When** admin memfilter berdasarkan status, **Then** hanya submission dengan status tsb yang tampil.

---

### User Story 3 - Admin mendapat notifikasi otomatis saat ada pesan baru (Priority: P2)

Setiap kali ada pengunjung mengirim form Kontak, admin/tim sales otomatis mendapat notifikasi (email) berisi ringkasan pesan — tanpa harus terus-menerus mengecek panel admin secara manual.

**Why this priority**: Meningkatkan kecepatan respons ke lead, tapi situs tetap berfungsi (pesan tetap tersimpan & terlihat di panel admin) walau notifikasi belum aktif — jadi tidak seblocking US1/US2 untuk nilai bisnis inti.

**Independent Test**: Konfigurasikan alamat email tujuan notifikasi, isi & submit form Kontak dari sisi pengunjung, verifikasi email notifikasi diterima berisi ringkasan pesan (nama, kontak, topik, isi pesan) dalam waktu wajar setelah submit.

**Acceptance Scenarios**:

1. **Given** alamat email tujuan notifikasi sudah dikonfigurasi, **When** pengunjung berhasil submit form Kontak, **Then** email notifikasi terkirim ke alamat tsb berisi ringkasan submission.
2. **Given** pengiriman email notifikasi gagal (mis. konfigurasi SMTP salah), **When** pengunjung submit form, **Then** submission tetap tersimpan dan pengunjung tetap melihat konfirmasi sukses — kegagalan notifikasi tidak boleh membuat submission hilang atau pengunjung melihat error.

### Edge Cases

- Apa yang terjadi jika pengunjung submit form berkali-kali dalam waktu singkat (spam/double-click)? Sistem MUST punya pembatasan wajar (rate limit) untuk mencegah spam submission, tanpa memblokir pengunjung yang sah mengirim pesan sekali.
- Apa yang terjadi jika pengiriman notifikasi (email) gagal? Submission MUST tetap tersimpan (lihat US3, Acceptance Scenario 2) — kegagalan notifikasi dicatat untuk keperluan debug, tidak menggagalkan submission.
- Apa yang terjadi jika field nomor HP/WhatsApp diisi format tidak valid? Sistem MUST menolak dengan pesan validasi yang jelas (konsisten dengan validasi client-side yang sudah ada).
- Apa yang terjadi jika admin menghapus sebuah submission? Submission MUST hilang dari daftar admin secara permanen; tidak ada dampak ke halaman publik (form Kontak tetap berfungsi normal untuk submission baru).
- Apa yang terjadi jika browser pengunjung memblokir pop-up/tab baru saat sistem mencoba membuka WhatsApp? Konfirmasi sukses tetap MUST tampil di halaman (FR-003); pengunjung tetap bisa membuka WhatsApp manual lewat tombol/link yang sama yang ditampilkan di layar (bukan hanya auto-open yang bisa gagal diam-diam).

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Sistem MUST menyimpan setiap submission form Kontak (nama, nomor HP/WhatsApp, topik kebutuhan, pesan, waktu submit) ke penyimpanan permanen.
- **FR-002**: Sistem MUST memvalidasi field wajib (nama, nomor HP/WhatsApp, pesan) sebelum menyimpan — submission dengan field wajib kosong/tidak valid MUST ditolak dengan pesan error yang jelas, tidak tersimpan.
- **FR-003**: Pengunjung MUST melihat konfirmasi sukses setelah submission berhasil tersimpan, tanpa reload halaman penuh (mempertahankan UX form yang sudah ada).
- **FR-004**: Sistem MUST membatasi laju submission (rate limiting) untuk mencegah spam, tanpa mengganggu pengunjung yang mengirim pesan secara wajar.
- **FR-005**: Admin panel MUST menyediakan halaman daftar submission Contact Us yang menampilkan seluruh detail pesan masuk (nama, kontak, topik, isi pesan, waktu masuk), dengan kemampuan cari/filter dasar.
- **FR-006**: Admin MUST bisa mengubah status tiap submission (mis. Baru / Sudah Dihubungi / Selesai) dan memfilter daftar berdasarkan status.
- **FR-007**: Admin MUST bisa menghapus submission yang tidak diperlukan lagi.
- **FR-008**: Sistem MUST mengirim notifikasi email ke alamat yang dikonfigurasi per instalasi setiap kali ada submission baru, berisi ringkasan data submission.
- **FR-009**: Kegagalan pengiriman notifikasi MUST NOT menggagalkan penyimpanan submission maupun konfirmasi sukses ke pengunjung (FR-001/FR-003 tetap terpenuhi terlepas dari status notifikasi).
- **FR-010**: Alamat email tujuan notifikasi MUST dikonfigurasi per instalasi (bukan hardcode), konsisten dengan Principle I constitution.
- **FR-011**: CRUD/akses halaman Contact Us Resource MUST terbuka untuk semua role yang memiliki akses panel admin, konsisten dengan resource admin lain yang sudah ada (Produk, Kategori).
- **FR-012**: Setelah submission tersimpan, sistem MUST mengarahkan pengunjung membuka WhatsApp (link `wa.me`) ke nomor WhatsApp bisnis yang dikonfigurasi per instalasi, dengan isi pesan pre-filled dari data submission (nama, topik, pesan) — supaya pengunjung bisa langsung mengirim pesan tsb via WhatsApp mereka sendiri, tanpa integrasi API WhatsApp berbayar.
- **FR-013**: Nomor WhatsApp bisnis tujuan MUST dikonfigurasi per instalasi (bukan hardcode), konsisten dengan Principle I constitution; jika belum dikonfigurasi, sistem MUST melewati langkah buka WhatsApp tanpa error (FR-001–FR-003 tetap berjalan normal).
- **FR-014**: Sistem MUST NOT mengirim email auto-reply/konfirmasi ke pengunjung — notifikasi submission baru hanya ditujukan ke admin (FR-008).

### Key Entities

- **Contact Submission**: Entity baru — nama, nomor HP/WhatsApp, topik kebutuhan (opsional), pesan, status (Baru/Sudah Dihubungi/Selesai), waktu masuk. Tidak berelasi ke entity lain — setiap submission berdiri sendiri.
- **WhatsApp Business Number** (perluasan pengaturan brand yang sudah ada): nomor WhatsApp tujuan redirect (FR-012/FR-013) — satu nilai per instalasi, dikelola admin lewat panel yang sama dengan Brand/Theme Settings.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: 100% submission form Kontak dengan data valid berhasil tersimpan dan bisa dilihat admin di panel admin, tanpa ada yang hilang.
- **SC-002**: Admin menerima notifikasi untuk submission baru dalam waktu kurang dari 5 menit setelah pengunjung submit (selama konfigurasi notifikasi aktif).
- **SC-003**: Admin dapat menemukan dan menindaklanjuti (mengubah status) sebuah submission dalam waktu kurang dari 1 menit dari saat membuka panel admin.
- **SC-004**: 100% percobaan submit dengan data tidak valid ditolak dengan pesan error yang jelas, tanpa ada data rusak/parsial yang tersimpan.
- **SC-005**: Kegagalan pengiriman notifikasi tidak pernah menyebabkan submission hilang — 100% submission valid tetap tersimpan terlepas dari status pengiriman notifikasi.
- **SC-006**: Ketika nomor WhatsApp bisnis sudah dikonfigurasi, pengunjung dapat mengirim pesannya via WhatsApp dalam satu langkah tambahan (klik/redirect) setelah submit form, tanpa perlu mengetik ulang pesannya.

## Clarifications

### Session 2026-09-05

- Q: Apakah notifikasi WhatsApp (bukan cuma email) termasuk scope v1, dan lewat mekanisme apa? → A: Bukan notifikasi ke admin via API — submission disimpan ke database dulu, lalu pengunjung diarahkan (redirect) ke `wa.me` dengan pesan pre-filled, sehingga pengunjung sendiri yang mengirim via WhatsApp mereka (FR-012, FR-013). Tanpa integrasi API WhatsApp berbayar.
- Q: Apakah pengunjung juga menerima email konfirmasi/auto-reply setelah submit? → A: Tidak — notifikasi hanya ke admin lewat email (FR-014).

## Assumptions

- Fitur ini menambah entity baru `Contact Submission` — form Kontak yang sudah ada dari 002-theme-branding-system (UI, field, validasi client-side) tidak berubah strukturnya, hanya ditambah kemampuan submit sungguhan ke backend.
- Notifikasi ke admin dikirim lewat email (Laravel Mail, konfigurasi SMTP via `.env` per instalasi) — mekanisme standar yang sudah didukung penuh oleh Laravel tanpa dependency tambahan (Principle V).
- "Notifikasi WhatsApp" diwujudkan sebagai redirect pengunjung ke `wa.me` dengan pesan pre-filled (bukan pesan otomatis dari sistem ke admin via API) — nol biaya provider, konsisten Principle V.
- Rate limiting memakai mekanisme standar Laravel (throttle middleware) — tidak perlu CAPTCHA pihak ketiga untuk v1.
- Status submission default "Baru" saat pertama kali masuk; admin yang mengubah status secara manual saat menindaklanjuti.
- Tidak ada retensi/auto-hapus otomatis untuk submission lama di v1 — dihapus manual oleh admin bila diperlukan (FR-007).
