# Feature Specification: Deployment Documentation

**Feature Branch**: `021-deployment-documentation`

**Created**: 2026-09-13

**Status**: Draft

**Input**: User description: "Dokumentasi deployment (requirement server, langkah deploy, checklist go-live per klien). Dokumen instalasi & konfigurasi lengkap untuk pembeli/klien: requirement server, langkah deploy, setup .env, konfigurasi plugin, dan checklist go-live. Wajib include: checklist setup GA4 property + service account credentials (untuk dashboard Google Analytics di admin panel) sebagai bagian dari onboarding klien baru, karena dashboard akan kosong kalau klien belum pasang GA4 di website mereka (AMC-231). Harus mengcover deployment di cPanel shared hosting dan VPS yang umum dipakai di Indonesia seperti VPS Hostinger dan sejenisnya."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Deploy instalasi klien ke VPS (Priority: P1)

Seorang developer/ops yang sudah menyiapkan repositori klien (lihat 018-setup-client-command, 020-client-versioning-strategy) perlu men-deploy instalasi tsb ke sebuah VPS (mis. VPS Hostinger atau penyedia sejenis di Indonesia) yang baru — server kosong tanpa stack apa pun terpasang. Ia mengikuti satu dokumen yang menjelaskan requirement server, langkah instalasi stack, dan langkah deploy aplikasi, sehingga situs klien berhasil diakses publik di akhir proses.

**Why this priority**: VPS adalah target deployment dengan kontrol penuh (root access, bisa jalankan proses persisten) dan menjadi baseline paling lengkap — kebutuhan environment lain (shared hosting) adalah versi yang lebih terbatas darinya. Tanpa dokumentasi ini, tiap deploy VPS akan dikerjakan ad hoc dan rawan lupa langkah penting (mis. queue worker, cron scheduler).

**Independent Test**: Sediakan satu VPS baru (Ubuntu/Linux, tanpa stack apa pun), ikuti dokumen deployment VPS dari awal sampai akhir; verifikasi situs klien dapat diakses publik via domain/HTTPS, dan seluruh fitur yang bergantung pada proses latar belakang (mis. notifikasi form kontak, pembersihan log terjadwal) berfungsi.

**Acceptance Scenarios**:

1. **Given** VPS baru tanpa stack apa pun terpasang, **When** developer mengikuti dokumen requirement & langkah deploy VPS, **Then** seluruh prasyarat (versi PHP, ekstensi, database, web server) terpasang dan aplikasi dapat dijalankan.
2. **Given** aplikasi sudah ter-deploy di VPS, **When** developer menyelesaikan seluruh langkah dokumen (termasuk konfigurasi proses latar belakang untuk antrean/job dan tugas terjadwal), **Then** situs dapat diakses publik lewat domain dengan HTTPS aktif, dan fitur yang bergantung pada proses latar belakang (notifikasi, pembersihan terjadwal) benar-benar berjalan tanpa perlu campur tangan manual berkelanjutan.
3. **Given** dokumen deploy VPS diikuti oleh dua developer berbeda pada dua VPS berbeda, **When** keduanya membandingkan hasil, **Then** langkah dan hasil akhirnya konsisten (bukan improvisasi berbeda-beda per orang).

---

### User Story 2 - Deploy instalasi klien ke shared hosting cPanel (Priority: P1)

Seorang developer/ops perlu men-deploy instalasi klien ke paket **shared hosting berbasis cPanel** (jenis hosting yang umum ditawarkan penyedia hosting Indonesia untuk klien dengan anggaran terbatas) — lingkungan dengan akses jauh lebih terbatas dibanding VPS (tanpa akses root, sering tanpa SSH, tidak bisa menjalankan proses latar belakang persisten). Ia mengikuti dokumen yang secara eksplisit membedakan langkah-langkah yang berbeda dari VPS akibat keterbatasan ini.

**Why this priority**: Banyak klien target kit ini (UMKM/perusahaan kecil-menengah) memakai shared hosting karena biayanya lebih murah daripada VPS — mengabaikan jalur deployment ini berarti dokumentasi tidak berguna untuk sebagian besar klien sesungguhnya, sesuai permintaan eksplisit bahwa dokumen ini harus mencakup shared hosting cPanel.

**Independent Test**: Sediakan satu akun shared hosting cPanel baru (tanpa akses root/SSH persisten, seperti kondisi paket entry-level umum), ikuti dokumen deployment shared hosting dari awal sampai akhir; verifikasi situs klien dapat diakses publik, dan fitur yang pada VPS memakai proses latar belakang persisten tetap berfungsi lewat mekanisme alternatif yang sesuai keterbatasan shared hosting (mis. dijadwalkan lewat cron cPanel, bukan proses yang berjalan terus-menerus).

**Acceptance Scenarios**:

1. **Given** akun shared hosting cPanel baru, **When** developer mengikuti dokumen requirement & langkah deploy shared hosting, **Then** ia dapat memastikan versi PHP & ekstensi yang tersedia di paket hosting tsb memenuhi kebutuhan aplikasi sebelum melangkah lebih jauh.
2. **Given** aplikasi berhasil di-deploy ke shared hosting, **When** developer mengonfigurasi document root/struktur folder sesuai panduan, **Then** situs dapat diakses publik tanpa membocorkan struktur folder aplikasi (mis. file konfigurasi/kode sumber tidak bisa diakses langsung lewat URL).
3. **Given** shared hosting tsb tidak mengizinkan proses latar belakang persisten, **When** developer mengikuti panduan alternatif berbasis cron cPanel untuk kebutuhan antrean/job dan tugas terjadwal, **Then** fitur yang bergantung padanya (mis. notifikasi form kontak) tetap berfungsi meski dengan mekanisme berbeda dari VPS.
4. **Given** dokumen deploy shared hosting sudah lengkap, **When** developer/ops yang belum pernah deploy ke shared hosting membacanya, **Then** ia memahami perbedaan mendasar dibanding deploy VPS tanpa harus bertanya langsung.

---

### User Story 3 - Setup Google Analytics sebagai bagian onboarding klien (Priority: P1)

Setelah situs klien live (di VPS maupun shared hosting), developer/ops perlu memastikan dashboard Google Analytics di admin panel menampilkan data — bukan kosong. Ia mengikuti checklist terpisah untuk membuat/mendapatkan properti GA4 milik klien dan kredensial service account, lalu memasangnya ke instalasi.

**Why this priority**: Ini secara eksplisit ditandai wajib oleh pemilik produk — dashboard Analytics yang kosong di hadapan klien terlihat seperti fitur yang rusak/belum jadi, padahal sekadar belum di-setup. Prioritasnya sejajar dengan deploy itu sendiri (P1) karena ini bagian tak terpisahkan dari "onboarding klien baru", bukan langkah opsional nice-to-have.

**Independent Test**: Pada satu instalasi klien yang sudah live (di VPS atau shared hosting), ikuti checklist setup GA4; verifikasi dashboard Google Analytics di admin panel menampilkan data (bukan kosong/pesan error) dalam waktu wajar setelah traffic mulai masuk.

**Acceptance Scenarios**:

1. **Given** situs klien baru live dan belum pernah dipasangi GA4, **When** developer mengikuti checklist setup GA4 (buat/hubungkan property GA4, buat service account, unduh kredensial, pasang ke instalasi), **Then** dashboard Google Analytics di admin panel dapat menampilkan data tanpa error konfigurasi.
2. **Given** klien sudah punya akun Google Analytics dari sebelumnya (bukan dibuatkan baru), **When** developer mengikuti checklist tsb, **Then** checklist tetap dapat diikuti dengan menyesuaikan langkah "gunakan property yang sudah ada" alih-alih "buat property baru".
3. **Given** checklist GA4 belum dijalankan sama sekali, **When** admin klien membuka dashboard Analytics di panel, **Then** dashboard MUST menampilkan pesan/kondisi yang jelas bahwa GA4 belum dikonfigurasi (bukan error teknis yang membingungkan) — mengonfirmasi kebutuhan checklist ini benar-benar dikomunikasikan.

---

### User Story 4 - Checklist go-live final lintas platform (Priority: P2)

Sebelum menyerahkan instalasi ke klien sebagai situs produksi resmi, developer/ops menjalankan satu checklist akhir (berlaku untuk deployment VPS maupun shared hosting) untuk memastikan tidak ada langkah penting yang terlewat — domain & DNS sudah benar, HTTPS aktif, mode debug produksi dimatikan, backup terjadwal, dan GA4 sudah terpasang (US3).

**Why this priority**: Ini jaring pengaman terakhir yang melengkapi (bukan menggantikan) US1/US2 — nilainya penuh baru terasa setelah deploy sesungguhnya sering dilakukan dan checklist ini menangkap hal-hal yang mudah lupa di tengah kesibukan (prioritas P2, sekunder dibanding kemampuan deploy itu sendiri).

**Independent Test**: Pada instalasi yang baru selesai di-deploy (via US1 atau US2) dan sudah menjalani US3, jalankan checklist go-live; verifikasi setiap butir checklist dapat diperiksa dengan jawaban ya/tidak yang jelas (tidak ambigu), dan instalasi yang lolos seluruh butir checklist tidak memiliki celah konfigurasi produksi yang jelas terlewat (mis. mode debug masih aktif).

**Acceptance Scenarios**:

1. **Given** instalasi baru selesai di-deploy, **When** developer menjalankan checklist go-live, **Then** setiap butir checklist dapat dijawab tegas ya/tidak berdasarkan kondisi instalasi saat itu (bukan butir yang samar/subjektif).
2. **Given** salah satu butir checklist gagal (mis. mode debug produksi masih aktif), **When** developer memeriksa instalasi, **Then** checklist secara eksplisit menandai kondisi tsb sebagai blocker sebelum situs diserahkan ke klien.
3. **Given** checklist go-live sudah lolos seluruhnya, **When** situs diserahkan ke klien, **Then** tidak ada satu pun butir checklist yang dilewati/diabaikan tanpa alasan yang didokumentasikan.

---

### Edge Cases

- Apa yang terjadi bila paket shared hosting yang dipakai TIDAK menyediakan versi PHP yang dibutuhkan aplikasi? Dokumen MUST menyertakan cara memeriksa & mengganti versi PHP di cPanel sebelum melangkah lebih jauh, dan menyatakan dengan jelas bahwa deployment tidak boleh dilanjutkan bila versi PHP tidak memenuhi syarat.
- Apa yang terjadi bila paket shared hosting tidak menyediakan akses SSH/terminal untuk menjalankan perintah instalasi (mis. Composer)? Dokumen MUST menyertakan jalur alternatif (mis. menyiapkan dependency di lokal lalu mengunggah hasilnya) untuk kasus ini.
- Bagaimana bila document root/struktur folder default hosting tidak sesuai dengan struktur folder aplikasi (folder publik terpisah dari kode aplikasi)? Dokumen MUST menjelaskan cara menyesuaikan konfigurasi hosting (atau struktur file) agar folder publik yang benar-benar diakses publik, tanpa mengekspos kode sumber/konfigurasi sensitif ke internet.
- Apa yang terjadi bila klien menolak/tidak bisa memberi akses ke akun Google Analytics miliknya untuk membuat service account? Checklist GA4 MUST mendokumentasikan kondisi ini sebagai item yang secara sah bisa ditunda (bukan blocker go-live mutlak), dengan catatan dashboard akan tetap kosong sampai diselesaikan.
- Bagaimana bila sertifikat HTTPS perlu diperbarui secara berkala (mis. Let's Encrypt yang punya masa berlaku)? Dokumen MUST menyebutkan kebutuhan perpanjangan otomatis/berkala, bukan hanya langkah pemasangan pertama kali.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Dokumentasi MUST mencantumkan requirement server (versi PHP, ekstensi PHP yang dibutuhkan, jenis & versi database, kebutuhan web server) yang berlaku umum untuk aplikasi ini, terlepas dari jenis hosting yang dipakai.
- **FR-002**: Dokumentasi MUST menyediakan langkah deploy yang terpisah dan lengkap untuk VPS (server dengan akses penuh/root) dan untuk shared hosting berbasis cPanel (akses terbatas), bukan satu langkah generik yang diasumsikan berlaku sama untuk keduanya.
- **FR-003**: Untuk jalur shared hosting cPanel, dokumentasi MUST membahas secara eksplisit keterbatasan khas lingkungan tsb: kemungkinan tidak ada akses SSH/terminal, tidak bisa menjalankan proses latar belakang persisten, dan kebutuhan menyesuaikan document root/struktur folder agar folder publik aplikasi yang benar-benar ter-expose ke internet (bukan seluruh kode aplikasi).
- **FR-004**: Untuk jalur VPS, dokumentasi MUST membahas instalasi stack dari nol (web server, PHP, database) serta konfigurasi proses latar belakang persisten untuk kebutuhan antrean/job dan tugas terjadwal aplikasi.
- **FR-005**: Dokumentasi MUST menyediakan mekanisme alternatif untuk kebutuhan antrean/job dan tugas terjadwal pada shared hosting yang tidak mendukung proses persisten (mis. berbasis cron), sehingga fitur yang bergantung padanya tetap berfungsi di kedua jenis hosting.
- **FR-006**: Dokumentasi MUST mencakup langkah konfigurasi `.env` untuk lingkungan produksi (termasuk penonaktifan mode debug) sebagai bagian dari proses deploy, bukan dianggap sudah jelas dengan sendirinya.
- **FR-007**: Dokumentasi MUST menyertakan langkah/checklist pemasangan HTTPS yang berlaku untuk kedua jenis hosting, termasuk kebutuhan pembaruan sertifikat secara berkala.
- **FR-008**: Dokumentasi MUST menyertakan checklist tersendiri untuk setup Google Analytics (GA4) — mencakup pembuatan/penggunaan property GA4 milik klien, pembuatan service account, serta pemasangan kredensialnya ke instalasi — sebagai bagian wajib dari proses onboarding klien baru.
- **FR-009**: Checklist GA4 MUST mengakomodasi baik kasus klien belum punya akun Google Analytics (dibuatkan baru) maupun sudah punya (dipakai propertinya yang sudah ada).
- **FR-010**: Dokumentasi MUST menyediakan satu checklist go-live final yang berlaku lintas jenis hosting (VPS maupun shared hosting), dengan setiap butirnya dapat diperiksa secara tegas (ya/tidak), mencakup minimal: domain & DNS, HTTPS aktif, mode debug produksi nonaktif, status backup, dan status setup GA4 (US3).
- **FR-011**: Dokumentasi MUST menyatakan dengan jelas kapan sebuah kondisi (mis. versi PHP tidak sesuai, akses SSH tidak tersedia) menjadi blocker yang harus diselesaikan sebelum melanjutkan, versus kondisi yang boleh ditangguhkan (mis. GA4 belum dipasang) tanpa menghalangi go-live.
- **FR-012**: Dokumentasi ini MUST menjadi rujukan yang dapat diikuti developer/ops yang belum pernah melakukan deployment untuk kit ini sebelumnya, tanpa perlu bantuan langsung dari orang lain, konsisten dengan pola dokumentasi proses lain di proyek ini (lihat 020-client-versioning-strategy).

### Key Entities

Tidak ada entitas data aplikasi (database) yang terlibat — fitur ini adalah dokumentasi proses deployment & checklist operasional, bukan fitur yang berjalan di dalam aplikasi.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Developer/ops dapat men-deploy instalasi klien baru ke VPS kosong hingga dapat diakses publik dengan HTTPS aktif dalam satu sesi kerja, mengikuti dokumen tanpa harus mencari-cari informasi tambahan di luar dokumen tsb.
- **SC-002**: Developer/ops dapat men-deploy instalasi klien baru ke akun shared hosting cPanel hingga dapat diakses publik, mengikuti dokumen yang sama sekali tidak mengasumsikan akses root/SSH persisten tersedia.
- **SC-003**: 100% fitur yang bergantung pada proses latar belakang (antrean/job, tugas terjadwal) tetap berfungsi baik di deployment VPS maupun shared hosting, meski lewat mekanisme teknis yang berbeda antara keduanya.
- **SC-004**: Dashboard Google Analytics di admin panel menampilkan data pada 100% instalasi klien yang telah menyelesaikan checklist GA4 (bukan kosong/error), diverifikasi pada pengujian skenario setup GA4.
- **SC-005**: Setiap butir pada checklist go-live final dapat diperiksa dan dijawab tegas ya/tidak tanpa ambiguitas oleh siapa pun yang menjalankannya, terlepas dari jenis hosting yang dipakai instalasi tsb.

## Assumptions

- "Shared hosting" dalam dokumen ini secara spesifik merujuk pada **shared hosting berbasis cPanel** — panel kontrol paling umum dipakai penyedia hosting Indonesia untuk paket shared hosting (mis. Niagahoster, DomaiNesia, Hostinger, dan sejenisnya) — bukan panel kontrol lain (Plesk, DirectAdmin, dsb.), yang di luar cakupan spesifikasi ini kecuali langkahnya kebetulan sama.
- "VPS" dalam dokumen ini mengasumsikan server Linux (distribusi umum seperti Ubuntu) yang disewa dari penyedia mana pun (Hostinger VPS disebut sebagai contoh representatif, bukan satu-satunya penyedia yang didukung) — langkah deploy VPS bersifat generik untuk kelas lingkungan "server dengan akses root", bukan instruksi spesifik-vendor per penyedia VPS.
- Dokumen ini ditujukan untuk developer/ops internal tim yang melakukan deployment atas nama klien, bukan panduan bagi klien untuk melakukan deployment sendiri.
- Prasyarat non-teknis (klien sudah membeli/memiliki domain dan paket hosting/VPS sendiri) diasumsikan sudah terpenuhi sebelum dokumen ini mulai diikuti — proses pembelian domain/hosting itu sendiri di luar cakupan spesifikasi ini.
- Untuk kebutuhan GA4, diasumsikan klien bersedia memberi akses (atau membuatkan) akun Google Analytics untuk keperluan pemasangan service account — bila tidak, ini menjadi item tertunda yang didokumentasikan (lihat Edge Cases), bukan kegagalan proses deployment secara keseluruhan.
