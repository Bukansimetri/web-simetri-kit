# Feature Specification: Rapikan Epic 2 - Auth, White-labeling & Audit Trail

**Feature Branch**: `001-epic2-cleanup`

**Created**: 2026-08-16

**Status**: Draft

**Input**: User description: "Rapikan sisa Epic 2 (Auth & White-labeling) untuk starter kit company profile ini, mencakup tiga task Linear: AMC-227 (custom dashboard widget Google Analytics), AMC-204 (white-labeling admin panel), AMC-206 (activity log audit trail)."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Admin melihat ringkasan traffic website di dashboard (Priority: P1)

Sebagai admin klien, saat saya login ke admin panel, saya ingin langsung melihat ringkasan traffic website (pengunjung, pageviews, halaman populer) tanpa harus membuka Google Analytics secara terpisah, supaya saya bisa memantau performa website dari satu tempat.

**Why this priority**: Plugin Google Analytics sudah terinstall dan ter-register (AMC-226 selesai), tapi belum memberi nilai apa pun ke admin karena belum ada tampilan datanya. Ini adalah pekerjaan tersisa dengan effort terkecil dan langsung menaikkan nilai dari investasi yang sudah ada.

**Independent Test**: Login sebagai admin dengan kredensial GA4 valid yang sudah dikonfigurasi, buka halaman Dashboard, dan verifikasi widget analytics tampil dengan data traffic real dari properti GA4 yang dikonfigurasi.

**Acceptance Scenarios**:

1. **Given** kredensial GA4 sudah dikonfigurasi untuk instalasi ini, **When** admin membuka halaman Dashboard, **Then** admin melihat widget yang menampilkan metrik traffic (pengunjung, pageviews) untuk periode berjalan.
2. **Given** kredensial GA4 belum/salah dikonfigurasi, **When** admin membuka Dashboard, **Then** admin melihat pesan yang jelas bahwa data analytics belum tersedia, bukan error mentah atau halaman kosong tanpa penjelasan.
3. **Given** widget analytics tampil, **When** admin mengganti rentang tanggal yang tersedia, **Then** metrik yang ditampilkan diperbarui sesuai rentang yang dipilih.

---

### User Story 2 - Admin panel tidak membawa branding bawaan starter kit ke klien (Priority: P2)

Sebagai pemilik proyek yang men-deploy starter kit ini untuk klien baru, saya ingin admin panel menampilkan nama, logo, favicon, dan warna brand milik klien tersebut (bukan branding default Filament/starter kit), supaya setiap instalasi terasa seperti produk milik klien, bukan seperti template yang terlihat jelas.

**Why this priority**: Ini adalah kebutuhan mendasar sebelum starter kit ini bisa "dijual" atau dipakai berulang ke klien lain — tanpa ini, setiap instalasi baru terlihat identik dan tidak profesional di mata klien.

**Independent Test**: Set konfigurasi brand (nama, logo, favicon, warna) untuk sebuah instalasi, lalu buka admin panel dan verifikasi seluruh elemen branding bawaan Filament (nama, logo, favicon, warna primer) sudah tergantikan tanpa perlu mengubah kode aplikasi.

**Acceptance Scenarios**:

1. **Given** nama brand klien sudah dikonfigurasi, **When** admin membuka admin panel, **Then** nama tersebut muncul di judul browser, sidebar, dan halaman login (bukan "Laravel" atau "Filament").
2. **Given** logo dan favicon klien sudah diupload/dikonfigurasi, **When** admin membuka admin panel, **Then** logo tersebut tampil di sidebar/halaman login dan favicon tampil di tab browser.
3. **Given** warna brand klien sudah dikonfigurasi, **When** admin membuka admin panel, **Then** warna primer UI (tombol, aksen navigasi) mengikuti warna tersebut.
4. **Given** instalasi baru belum melakukan konfigurasi branding sama sekali, **When** admin membuka admin panel, **Then** sistem menampilkan branding default yang wajar (bukan error atau tampilan rusak).

---

### User Story 3 - Melacak siapa mengubah apa di admin panel (Priority: P3)

Sebagai admin/super admin, saya ingin bisa melihat riwayat perubahan yang dilakukan pengguna lain di admin panel (data apa yang diubah, oleh siapa, dan kapan), supaya saya bisa menelusuri penyebab masalah atau menyalahgunaan akses ketika dibutuhkan.

**Why this priority**: Ini adalah kapabilitas audit/keamanan yang penting untuk operasional jangka panjang, namun tidak menghalangi penggunaan dasar starter kit dalam waktu dekat, sehingga prioritasnya di bawah dua fitur di atas.

**Independent Test**: Lakukan perubahan pada salah satu record (misalnya ubah role user), lalu buka halaman activity log dan verifikasi perubahan tersebut tercatat dengan detail pelaku, waktu, dan nilai sebelum/sesudah.

**Acceptance Scenarios**:

1. **Given** seorang admin mengubah data pada resource yang diaudit, **When** perubahan disimpan, **Then** sistem mencatat log berisi pelaku, waktu, resource, dan nilai sebelum/sesudah perubahan.
2. **Given** log aktivitas sudah tercatat, **When** pengguna yang berwenang membuka halaman activity log, **Then** log dapat difilter/dicari berdasarkan pelaku, jenis resource, dan rentang waktu.
3. **Given** pengguna tanpa izin yang sesuai, **When** mencoba membuka halaman activity log, **Then** akses ditolak.

### Edge Cases

- Apa yang terjadi jika kredensial GA4 valid tapi properti belum menerima traffic sama sekali (data kosong, bukan error)?
- Bagaimana sistem menangani logo/favicon dengan format atau ukuran file yang tidak didukung saat diupload?
- Apa yang terjadi pada log aktivitas jika record yang diubah kemudian dihapus (log harus tetap ada dan tetap bisa ditelusuri)?
- Bagaimana jika dua admin mengubah record yang sama secara bersamaan — log harus mencatat kedua perubahan secara terpisah dan berurutan.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Sistem MUST menampilkan widget ringkasan traffic (pengunjung dan pageviews minimal) di halaman Dashboard admin panel menggunakan data dari properti GA4 yang dikonfigurasi untuk instalasi tersebut.
- **FR-002**: Sistem MUST menampilkan pesan yang informatif (bukan error mentah) pada widget analytics ketika kredensial GA4 belum dikonfigurasi atau tidak valid.
- **FR-003**: Sistem MUST mengizinkan nama aplikasi, logo, favicon, dan warna primer admin panel dikonfigurasi per instalasi tanpa mengubah kode sumber.
- **FR-004**: Sistem MUST menerapkan konfigurasi branding (nama, logo, favicon, warna) ke seluruh elemen admin panel yang sebelumnya menampilkan branding default Filament/starter kit.
- **FR-005**: Sistem MUST menampilkan branding default yang wajar ketika instalasi belum melakukan konfigurasi branding apa pun.
- **FR-006**: Sistem MUST mencatat log setiap kali data pada resource yang diaudit dibuat, diubah, atau dihapus melalui admin panel, termasuk pelaku, waktu, dan nilai sebelum/sesudah.
- **FR-007**: Sistem MUST menyediakan halaman di admin panel untuk melihat, memfilter, dan mencari activity log.
- **FR-008**: Sistem MUST membatasi akses ke halaman activity log hanya untuk role Super Admin.
- **FR-009**: Sistem MUST menyimpan activity log selama 90 hari, dan menghapusnya secara otomatis setelah periode tersebut melalui scheduled job pembersihan.
- **FR-010**: Widget analytics di Dashboard MUST menampilkan pengunjung, pageviews, halaman terpopuler (top pages), dan sumber traffic, dengan admin dapat memilih rentang tanggal yang ingin dilihat.

### Key Entities

- **Brand Settings**: Representasi konfigurasi white-label per instalasi — nama aplikasi, logo, favicon, warna primer.
- **Activity Log Entry**: Satu catatan perubahan — pelaku, waktu, resource yang diubah, jenis aksi (create/update/delete), nilai sebelum dan sesudah.
- **Analytics Snapshot**: Data traffic yang diambil dari GA4 untuk ditampilkan di widget dashboard — periode, jumlah pengunjung, jumlah pageviews.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin dapat melihat data traffic website (pengunjung & pageviews) langsung dari Dashboard tanpa membuka aplikasi/tab lain, dalam waktu kurang dari 3 detik setelah halaman dimuat.
- **SC-002**: 100% instalasi baru starter kit menampilkan branding klien (nama, logo, favicon, warna) yang berbeda dari branding default starter kit setelah konfigurasi awal dilakukan, tanpa perubahan kode.
- **SC-003**: Setiap perubahan data oleh admin pada resource yang diaudit tercatat di activity log dengan tingkat keberhasilan pencatatan 100% (tidak ada perubahan yang lolos tanpa tercatat).
- **SC-004**: Pengguna berwenang dapat menemukan riwayat perubahan spesifik (siapa mengubah apa dan kapan) dalam waktu kurang dari 1 menit melalui pencarian/filter activity log.

## Assumptions

- Setiap klien memakai satu deployment/instalasi terpisah (bukan satu instance multi-tenant untuk banyak klien sekaligus), konsisten dengan rencana artisan command `app:setup-client` (AMC-228) yang men-generate `.env` per instalasi.
- Konfigurasi nama aplikasi mengikuti nilai `APP_NAME` yang sudah ada; logo, favicon, dan warna brand disimpan melalui mekanisme yang bisa diubah tanpa redeploy (memakai `spatie/laravel-settings` yang sudah terinstall), supaya klien/non-developer bisa menggantinya sendiri lewat admin panel.
- Kredensial GA4 (property ID & service account) dikonfigurasi per instalasi melalui environment/config, sejalan dengan cara `bezhansalleh/filament-google-analytics` bekerja saat ini.
- Resource yang diaudit oleh activity log pada tahap ini adalah resource yang sudah ada di admin panel (User, Role) plus resource baru yang akan dibangun di Epic-epic berikutnya — daftar spesifik resource yang diaudit dapat diperluas seiring waktu tanpa mengubah desain fitur ini.
- Widget dashboard GA menggunakan data dari `bezhansalleh/filament-google-analytics` yang sudah terinstall, bukan integrasi custom baru ke Google Analytics API.
