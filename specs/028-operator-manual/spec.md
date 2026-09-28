# Feature Specification: Manual Operator Panel Admin

**Feature Branch**: `028-operator-manual`

**Created**: 2026-09-26

**Status**: Draft

**Input**: User description: "User manual singkat untuk operator/admin panel (Linear AMC-234, bagian Epic AMC-196 QA, Dokumentasi & Rilis). Dokumen markdown berbahasa Indonesia di docs/ untuk operator klien non-teknis yang mengelola konten situs lewat panel admin di /admin: cara login, navigasi grup menu, tugas harian (banner, produk, artikel, portfolio, testimoni, lowongan, custom page, menu), menindaklanjuti pesan masuk dan lead kalkulator, pengaturan situs/tampilan/SEO/media sosial, mode pemeliharaan, pengguna & role, serta catatan penting seperti jeda cache halaman publik hingga 5 menit. Cakupan sesuai kondisi branch main saat ini. Berbeda dari dokumentasi teknis AMC-233 yang ditujukan untuk developer."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Operator baru mengelola konten situs sehari-hari (Priority: P1) 🎯 MVP

Operator dari pihak klien (misal staf marketing) baru menerima akun panel admin. Ia membuka manual, berhasil login, memahami susunan menu, lalu menyelesaikan tugas konten yang paling sering: mengganti banner di Home, menambah produk, menerbitkan artikel, menambah testimoni, dan membuka lowongan kerja. Setelah menyimpan, ia tahu kapan perubahan akan terlihat di situs dan apa yang harus dilakukan bila belum terlihat.

**Why this priority**: Mengelola konten adalah alasan utama klien diberi akses panel. Tanpa bagian ini, setiap perubahan konten kembali membebani tim developer.

**Independent Test**: Beri manual ke orang non-teknis yang belum pernah memakai panel, lalu minta ia menyelesaikan 5 tugas konten umum (ganti banner, tambah produk, terbitkan artikel, tambah testimoni, buka lowongan) hanya dengan bantuan manual.

**Acceptance Scenarios**:

1. **Given** operator punya email dan password akun, **When** ia mengikuti bagian login di manual, **Then** ia masuk ke panel dan mengenali tiap grup menu beserta isinya.
2. **Given** operator ingin mengubah sebuah konten, **When** ia mencari di manual, **Then** ia menemukan langkah untuk konten itu di bawah nama menu yang persis sama dengan yang terlihat di panel.
3. **Given** operator sudah menyimpan perubahan, **When** perubahan belum terlihat di situs, **Then** manual menjelaskan bahwa sebagian halaman butuh waktu hingga 5 menit dan apa yang perlu dicek sebelum menghubungi developer.
4. **Given** konten punya kolom yang wajib atau punya batasan (misal ukuran gambar, status terbit/draft), **When** operator mengisi form, **Then** manual sudah menyebutkan batasan penting tersebut.

---

### User Story 2 - Operator menindaklanjuti prospek (Priority: P2)

Operator sales atau admin membuka panel setiap hari untuk melihat prospek baru. Dengan manual, ia memahami arti angka dan tabel di halaman Dashboard, membuka pesan masuk dan lead kalkulator yang belum ditangani, membaca detailnya, lalu mengubah statusnya setelah menghubungi calon pelanggan.

**Why this priority**: Prospek yang tidak ditindaklanjuti adalah kerugian langsung bagi klien. Namun bagian ini dipakai oleh lebih sedikit orang dibanding pengelolaan konten, dan layarnya sudah cukup jelas dengan sedikit panduan.

**Independent Test**: Siapkan beberapa prospek contoh, lalu minta orang non-teknis memakai manual untuk menemukan semua prospek yang belum dihubungi, membuka satu, dan menandainya sudah dihubungi.

**Acceptance Scenarios**:

1. **Given** operator membuka Dashboard, **When** ia membaca bagian Dashboard di manual, **Then** ia bisa menjelaskan arti tiap kartu angka, tabel "Perlu ditindaklanjuti" (termasuk tanda "Terlambat"), dan grafik mingguan.
2. **Given** ada pesan masuk atau lead kalkulator baru, **When** operator mengikuti manual, **Then** ia bisa membuka detailnya, menghubungi calon pelanggan, dan mengubah statusnya.
3. **Given** operator bingung perbedaan "Pesan Masuk" dan "Lead Kalkulator", **When** ia membaca manual, **Then** ia memahami asal masing-masing (form kontak vs. form hitung estimasi) dan arti tiap status.

---

### User Story 3 - Admin klien mengatur identitas dan pengaturan situs (Priority: P3)

Admin di pihak klien perlu mengubah hal yang jarang berubah: nama situs, kontak perusahaan, logo, warna dan font, teks SEO, tautan media sosial, dan asumsi tarif di kalkulator estimasi. Sesekali ia perlu menutup situs sementara dengan mode pemeliharaan. Manual menjelaskan tiap halaman pengaturan, dampaknya ke situs, dan hal yang perlu hati-hati.

**Why this priority**: Pengaturan jarang diubah setelah situs live, tetapi kesalahan di sini (misal mode pemeliharaan lupa dimatikan, atau script analytics salah) berdampak ke seluruh situs.

**Independent Test**: Minta orang non-teknis memakai manual untuk mengganti nomor telepon perusahaan dan warna utama situs, lalu menyalakan dan mematikan kembali mode pemeliharaan, dan memeriksa hasilnya di situs.

**Acceptance Scenarios**:

1. **Given** admin ingin mengubah identitas atau tampilan situs, **When** ia membaca manual, **Then** ia tahu halaman pengaturan mana yang dipakai dan bagian situs mana yang terpengaruh.
2. **Given** admin menyalakan mode pemeliharaan, **When** ia membaca manual, **Then** ia tahu siapa yang masih bisa melihat situs, bagaimana memastikan situs tertutup bagi pengunjung, dan cara membukanya kembali.
3. **Given** admin diminta memasang kode dari pihak ketiga (misal analytics), **When** ia membaca bagian Scripts & Analytics, **Then** manual memperingatkan risikonya dan menyarankan berkoordinasi dengan developer.

---

### User Story 4 - Admin klien mengelola pengguna dan akun sendiri (Priority: P4)

Admin di pihak klien menambah akun untuk staf baru, memberi peran yang sesuai, menonaktifkan akses staf yang keluar, dan memeriksa log aktivitas. Setiap pengguna juga bisa mengganti nama dan password akunnya sendiri.

**Why this priority**: Dilakukan jarang dan hanya oleh sedikit orang, tetapi penting untuk keamanan akses.

**Independent Test**: Minta admin memakai manual untuk membuat akun baru dengan peran tertentu, login dengan akun itu, dan mengganti password akun tersebut.

**Acceptance Scenarios**:

1. **Given** admin perlu memberi akses ke staf baru, **When** ia mengikuti manual, **Then** ia bisa membuat akun, memilih peran, dan memberi tahu staf cara login.
2. **Given** pengguna ingin mengganti password, **When** ia mengikuti manual, **Then** ia bisa melakukannya lewat halaman profil tanpa bantuan admin.
3. **Given** staf tidak melihat menu yang ia butuhkan, **When** admin membaca manual, **Then** ia memahami bahwa menu yang tampil bergantung pada peran dan izin, dan tahu apa yang harus diperiksa.

### Edge Cases

- Operator lupa password: panel tidak menyediakan reset password mandiri. Manual MUST menjelaskan bahwa password direset oleh admin lewat menu Pengguna, atau oleh developer bila admin sendiri yang lupa.
- Menu yang dijelaskan manual tidak terlihat di panel operator: manual MUST menjelaskan bahwa menu yang tampil bergantung pada peran dan izin, dan sebagian menu (misal grup Sistem, Log Aktivitas) hanya untuk admin utama.
- Perubahan sudah disimpan tetapi belum tampil di situs: manual MUST menjelaskan jeda hingga 5 menit untuk sebagian halaman, dan halaman mana yang langsung berubah.
- Operator menghapus data secara tidak sengaja: manual MUST memperingatkan bahwa penghapusan tidak bisa dibatalkan dari panel dan menyarankan memakai status (draft/nonaktif) bila ragu.
- Mode pemeliharaan lupa dimatikan: manual MUST menjelaskan tanda yang terlihat di panel saat mode pemeliharaan aktif dan cara mematikannya.
- Situs klien memakai nama, logo, dan warna sendiri: manual MUST ditulis netral (tanpa nama klien atau nama starter kit) agar bisa dipakai di semua instalasi.
- Manual tertinggal dari kondisi panel karena project terus berkembang: manual MUST mencantumkan tanggal terakhir diperbarui, dan nama menu di manual MUST sama persis dengan label di panel.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Dokumentasi MUST menyediakan satu manual operator panel admin di folder dokumentasi project, terpisah dari dokumentasi teknis developer.
- **FR-002**: Manual MUST ditulis dalam Bahasa Indonesia untuk pembaca non-teknis: tanpa perintah terminal, potongan kode, path file, atau istilah pemrograman. Istilah yang tak terhindarkan (misal "slug", "SEO", "meta description") MUST dijelaskan dengan bahasa sehari-hari.
- **FR-003**: Manual MUST menjelaskan cara login, cara keluar, dan cara mengganti nama serta password akun sendiri.
- **FR-004**: Manual MUST memuat peta menu panel: Dasbor, lalu tiap grup (Content, Konten Halaman, Katalog, Prospek & Pesan, Blog, Portfolio, Karir, Menu Builder, Pengaturan Situs, Sistem) beserta fungsi singkat tiap menu di dalamnya.
- **FR-005**: Manual MUST memuat langkah berbasis tugas untuk setiap menu konten yang ada di panel: Media Manager, Banner, Halaman (custom page), Halaman Tentang Kami, Tim, Testimoni, Logo Klien, Produk, Kategori Produk, Peralatan Listrik, Artikel, Kategori Artikel, Portfolio, Kategori Portfolio, Lowongan Kerja, Lokasi Menu, dan Item Menu.
- **FR-006**: Untuk tiap menu konten, manual MUST menyebut kolom penting yang wajib diisi, pilihan status tayang (misal draft/terbit, aktif/nonaktif) bila ada, batasan gambar bila ada, dan di bagian situs mana konten itu tampil.
- **FR-007**: Manual MUST menjelaskan halaman Dashboard: arti tiap kartu angka, tabel "Perlu ditindaklanjuti" termasuk tanda "Terlambat", dan grafik prospek per minggu.
- **FR-008**: Manual MUST menjelaskan alur tindak lanjut Pesan Masuk dan Lead Kalkulator: asal masing-masing, cara menyaring yang belum ditangani, arti tiap status, dan cara mengubah status serta mencatat hasil follow-up.
- **FR-009**: Manual MUST menjelaskan setiap halaman di grup Pengaturan Situs (Pengaturan Umum, Tampilan, SEO, Media Sosial, Scripts & Analytics, Kalkulator Estimasi): apa yang diatur dan bagian situs mana yang terpengaruh.
- **FR-010**: Manual MUST menjelaskan mode pemeliharaan: cara menyalakan dan mematikan, siapa yang masih bisa melihat situs, dan tanda yang tampil di panel saat mode aktif.
- **FR-011**: Manual MUST menjelaskan pengelolaan pengguna dan peran (membuat akun, memilih peran, mereset password staf, mencabut akses) dan Log Aktivitas, serta menandai bagian yang hanya bisa diakses admin utama.
- **FR-012**: Manual MUST memuat bagian "Masalah umum" yang menjawab minimal: lupa password, menu tidak terlihat, perubahan belum tampil di situs, data terhapus tidak sengaja, dan kapan harus menghubungi developer.
- **FR-013**: Manual MUST memuat peringatan untuk tindakan berisiko: menghapus data, mengubah Scripts & Analytics, mengubah asumsi Kalkulator Estimasi, mengubah slug/alamat halaman yang sudah tayang, dan menyalakan mode pemeliharaan.
- **FR-014**: Manual MUST ditulis netral terhadap klien: tidak menyebut nama klien tertentu, nama starter kit, atau data kontak nyata, dan merujuk situs sebagai "situs Anda".
- **FR-015**: Nama menu, judul halaman, nama kolom, nama tombol, dan nama status yang disebut di manual MUST sama persis dengan label yang tampil di panel pada saat manual ditulis.
- **FR-016**: Manual MUST mencantumkan tanggal terakhir diperbarui, dapat ditemukan dari README project, dan dirujuk dari dokumentasi teknis sebagai dokumen untuk operator.
- **FR-017**: Manual MUST "singkat": tiap tugas dijelaskan dalam langkah bernomor yang ringkas, dan pembaca bisa melompat langsung ke tugas yang dicari lewat daftar isi.

### Key Entities

- **Manual Operator**: dokumen tunggal untuk operator non-teknis, berisi orientasi panel, langkah per tugas, pengaturan, pengguna, dan penanganan masalah umum.
- **Peran pengguna**: menentukan menu yang terlihat oleh operator; manual menjelaskan dampaknya, bukan cara mengonfigurasi izin secara teknis.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Orang non-teknis yang belum pernah memakai panel dapat menyelesaikan minimal 4 dari 5 tugas konten umum (ganti banner, tambah produk, terbitkan artikel, tambah testimoni, buka lowongan) hanya dengan bantuan manual, dalam total waktu kurang dari 45 menit.
- **SC-002**: Operator dapat menemukan langkah untuk tugas apa pun yang ada di manual dalam waktu kurang dari 1 menit lewat daftar isi.
- **SC-003**: 100% menu yang tampil untuk admin utama di panel tercakup di manual.
- **SC-004**: 100% nama menu, halaman, kolom, tombol, dan status yang disebut di manual sesuai dengan label di panel saat manual dirilis.
- **SC-005**: Manual tidak memuat perintah terminal, potongan kode, maupun path file.
- **SC-006**: Pertanyaan operator ke developer tentang cara memakai panel berkurang setelah manual diserahkan ke klien (diukur secara kualitatif lewat umpan balik tim pada 1 bulan pertama).

## Assumptions

- Pembaca adalah staf klien yang terbiasa memakai komputer dan browser, tetapi tidak punya latar belakang teknis.
- Manual disimpan sebagai file di repositori (folder dokumentasi yang sudah ada) agar ikut ter-versioning dan ikut ter-clone ke repo klien; bila dibutuhkan, developer dapat mengekspornya ke PDF sebelum diserahkan ke klien. Ekspor PDF di luar scope fitur ini.
- Manual tidak memakai screenshot pada versi ini: tampilan panel berbeda per klien (logo dan warna) dan screenshot cepat usang. Sebagai gantinya, manual menyebut label menu dan tombol secara persis. Screenshot bisa ditambahkan belakangan.
- Cakupan mengikuti kondisi branch main saat spec ditulis, termasuk Dashboard prospek (027-leads-dashboard) yang sudah di-merge.
- Instalasi, deployment, pembuatan akun admin pertama, dan konfigurasi izin per peran adalah tugas developer dan berada di luar scope; manual hanya menyebut kapan operator perlu menghubungi developer.
- Fitur yang ditunda (pemilih varian section AMC-221, live preview tema AMC-222) tidak dibahas karena belum ada di panel.
- Pengukuran success criteria dilakukan manual lewat uji baca oleh orang non-teknis, bukan otomatis.
