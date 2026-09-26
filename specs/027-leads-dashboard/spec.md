# Feature Specification: Dashboard Prospek Admin

**Feature Branch**: `027-leads-dashboard`

**Created**: 2026-09-26

**Status**: Draft

**Input**: User description: "Dashboard admin pengganti Google Analytics: hapus dua widget bawaan Filament (Account & Filament Info) dan ganti dengan (1) kartu statistik prospek — pesan masuk baru, lead kalkulator baru, total lead 30 hari dengan tren harian, dan tingkat konversi lead kalkulator (won); tiap kartu bisa diklik ke daftar terfilter, (2) tabel \"Perlu ditindaklanjuti\" berisi lead terbaru berstatus baru dari kedua sumber (kontak & kalkulator) dengan nama, sumber, area, umur lead, estimasi tagihan untuk lead kalkulator, dan penanda lead yang sudah lebih dari 2 hari, (3) grafik lead per minggu 8-12 minggu terakhir dipisah per sumber."

## Konteks

Dashboard Google Analytics sudah dihapus (branch `026-remove-ga-dashboard`), sehingga halaman awal panel admin kini hanya berisi dua widget bawaan (info akun dan info versi panel) yang tidak berguna bagi admin. Situs ini adalah company profile penjual panel surya; nilai bisnis utamanya adalah **prospek** yang masuk lewat dua sumber:

- **Pesan Masuk**: form Kontak, status `baru → sudah dihubungi → selesai`.
- **Lead Kalkulator**: form kalkulator estimasi hemat listrik, status `baru → dihubungi → qualified → won / lost`.

Dashboard baru harus menjawab satu pertanyaan saat admin login: *prospek mana yang perlu saya tindak lanjuti sekarang, dan bagaimana trennya?*

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Admin melihat ringkasan prospek sekilas (Priority: P1) 🎯 MVP

Admin membuka panel dan langsung melihat empat angka: jumlah pesan masuk yang belum dihubungi, jumlah lead kalkulator yang belum di-follow-up, total prospek 30 hari terakhir beserta tren hariannya, dan tingkat konversi lead kalkulator. Admin mengklik salah satu kartu dan langsung dibawa ke daftar yang sudah terfilter sesuai angka tersebut.

**Why this priority**: Ini pengganti langsung dashboard lama dan memberi gambaran paling cepat. Dua angka "belum ditindaklanjuti" adalah pemicu tindakan harian admin.

**Independent Test**: Siapkan data contoh (misal 3 pesan baru, 2 pesan sudah dihubungi, 4 lead kalkulator baru, 1 lead won dari 10 lead dalam periode konversi). Login, pastikan keempat angka sesuai. Klik kartu "Pesan masuk baru", pastikan daftar Pesan Masuk tampil hanya dengan 3 pesan berstatus baru.

**Acceptance Scenarios**:

1. **Given** ada 3 pesan masuk berstatus baru, **When** admin membuka dashboard, **Then** kartu "Pesan masuk baru" menampilkan 3.
2. **Given** ada 4 lead kalkulator berstatus baru, **When** admin membuka dashboard, **Then** kartu "Lead kalkulator baru" menampilkan 4.
3. **Given** ada prospek dari kedua sumber dalam 30 hari terakhir dan beberapa yang lebih lama, **When** admin membuka dashboard, **Then** kartu "Prospek 30 hari" hanya menghitung yang masuk dalam 30 hari terakhir dan menampilkan tren jumlah per hari.
4. **Given** dalam periode konversi ada 10 lead kalkulator dan 1 berstatus won, **When** admin membuka dashboard, **Then** kartu konversi menampilkan 10%.
5. **Given** admin melihat kartu "Pesan masuk baru", **When** admin mengkliknya, **Then** daftar Pesan Masuk terbuka dengan filter status baru sudah aktif. Hal yang sama berlaku untuk kartu "Lead kalkulator baru" ke daftar Lead Kalkulator.

---

### User Story 2 - Admin melihat daftar prospek yang perlu ditindaklanjuti (Priority: P2)

Di bawah kartu, admin melihat tabel "Perlu ditindaklanjuti" berisi prospek terbaru yang masih berstatus baru dari kedua sumber dalam satu daftar. Setiap baris menampilkan nama, sumber, area, umur prospek, dan untuk lead kalkulator estimasi tagihan listriknya. Prospek yang sudah menunggu lebih dari 2 hari diberi penanda mencolok. Admin mengklik sebuah baris untuk membuka detail prospek tersebut.

**Why this priority**: Ini widget yang paling sering dipakai sehari-hari: admin tidak perlu membuka dua menu terpisah untuk tahu siapa yang harus dihubungi dulu.

**Independent Test**: Siapkan 2 pesan baru (satu berumur 1 hari, satu 3 hari), 1 lead kalkulator baru, dan 1 pesan yang sudah dihubungi. Buka dashboard, pastikan tabel berisi tepat 3 baris, terurut dari yang terbaru, pesan berumur 3 hari diberi penanda, dan pesan yang sudah dihubungi tidak muncul.

**Acceptance Scenarios**:

1. **Given** ada prospek baru dari kedua sumber, **When** admin membuka dashboard, **Then** tabel menampilkan keduanya dalam satu daftar dengan kolom sumber yang membedakannya.
2. **Given** sebuah prospek sudah berstatus selain baru, **When** admin membuka dashboard, **Then** prospek itu tidak muncul di tabel.
3. **Given** sebuah prospek baru sudah berumur lebih dari 2 hari, **When** admin membuka dashboard, **Then** barisnya diberi penanda "terlambat" yang terlihat jelas.
4. **Given** baris lead kalkulator, **When** ditampilkan, **Then** kolom estimasi tagihan terisi; untuk baris pesan masuk, kolom itu kosong.
5. **Given** admin mengklik sebuah baris, **When** klik dilakukan, **Then** halaman detail prospek tersebut terbuka di menu sumbernya.
6. **Given** tidak ada prospek berstatus baru, **When** admin membuka dashboard, **Then** tabel menampilkan pesan kosong yang ramah (misal "Semua prospek sudah ditindaklanjuti").

---

### User Story 3 - Admin melihat tren prospek mingguan (Priority: P3)

Admin melihat grafik jumlah prospek per minggu selama 12 minggu terakhir, dipisah per sumber (Pesan Masuk dan Lead Kalkulator), untuk menilai apakah jumlah calon pembeli naik atau turun dan sumber mana yang lebih produktif.

**Why this priority**: Menggantikan fungsi "tren" yang dulu diberikan Google Analytics, tetapi dengan data yang lebih bermakna bagi bisnis. Tidak mendesak untuk tindakan harian, sehingga P3.

**Independent Test**: Siapkan prospek yang tersebar di beberapa minggu dari kedua sumber, termasuk satu yang lebih lama dari 12 minggu. Buka dashboard, pastikan grafik menampilkan 12 titik minggu dengan dua seri, jumlah per minggu sesuai data, dan prospek lebih lama dari 12 minggu tidak dihitung.

**Acceptance Scenarios**:

1. **Given** ada prospek dari kedua sumber dalam 12 minggu terakhir, **When** admin membuka dashboard, **Then** grafik menampilkan dua seri (Pesan Masuk, Lead Kalkulator) dengan 12 titik minggu.
2. **Given** sebuah minggu tanpa prospek, **When** grafik ditampilkan, **Then** minggu itu tetap muncul dengan nilai 0 (tidak dilewati).
3. **Given** prospek yang masuk lebih dari 12 minggu lalu, **When** grafik ditampilkan, **Then** prospek itu tidak dihitung.

### Edge Cases

- **Instalasi baru tanpa prospek sama sekali**: semua kartu menampilkan 0, konversi menampilkan tanda "belum ada data" (bukan 0% atau error pembagian nol), tabel menampilkan pesan kosong, grafik menampilkan garis 0. Dashboard tidak boleh error.
- **Konversi tanpa lead dalam periode**: tampil "belum ada data", bukan NaN atau 0%.
- **Prospek tanpa area** (field opsional kosong): kolom area tampil "-".
- **Lead kalkulator tanpa estimasi tagihan**: kolom estimasi tagihan tampil "-".
- **Batas hari dan minggu**: pengelompokan per hari dan per minggu mengikuti zona waktu situs (Pengaturan Umum, default WIB), bukan UTC, supaya prospek yang masuk pukul 01.00 WIB tidak dihitung ke hari sebelumnya.
- **Volume tinggi**: jika ada ratusan prospek baru, tabel tetap hanya menampilkan 10 terbaru; kartu tetap menampilkan jumlah total yang benar.
- **Prospek yang dihapus**: tidak dihitung di kartu, tabel, maupun grafik.

## Requirements *(mandatory)*

### Functional Requirements

**Umum**

- **FR-001**: Halaman awal panel admin MUST NOT lagi menampilkan widget info akun dan widget info versi panel bawaan.
- **FR-002**: Halaman awal panel admin MUST menampilkan, berurutan dari atas: kartu statistik prospek (FR-010–FR-015), tabel "Perlu ditindaklanjuti" (FR-020–FR-026), dan grafik tren mingguan (FR-030–FR-033).
- **FR-003**: Seluruh widget dashboard MUST hanya terlihat oleh pengguna yang juga berhak membuka menu Pesan Masuk dan Lead Kalkulator (aturan akses yang sama dengan menu tersebut). Pengguna tanpa hak itu melihat dashboard tanpa widget prospek, tanpa error.
- **FR-004**: Semua label, judul, dan pesan di dashboard MUST berbahasa Indonesia.
- **FR-005**: Pengelompokan dan batas waktu ("hari ini", "30 hari terakhir", "per minggu", umur prospek) MUST dihitung dalam zona waktu situs yang diatur admin di Pengaturan Umum (default Asia/Jakarta / WIB), bukan zona waktu server.

**Kartu statistik (US1)**

- **FR-010**: Kartu "Pesan masuk baru" MUST menampilkan jumlah seluruh pesan masuk berstatus baru.
- **FR-011**: Kartu "Lead kalkulator baru" MUST menampilkan jumlah seluruh lead kalkulator berstatus baru.
- **FR-012**: Kartu "Prospek 30 hari" MUST menampilkan jumlah gabungan pesan masuk dan lead kalkulator yang masuk dalam 30 hari terakhir (semua status), disertai grafik mini jumlah per hari selama 30 hari itu.
- **FR-013**: Kartu "Konversi lead kalkulator" MUST menampilkan persentase lead kalkulator berstatus won dibanding seluruh lead kalkulator yang masuk dalam 90 hari terakhir, dibulatkan ke 1 angka desimal. Jika tidak ada lead dalam periode itu, MUST menampilkan "belum ada data".
- **FR-014**: Kartu "Pesan masuk baru" MUST dapat diklik dan membuka daftar Pesan Masuk dengan filter status baru aktif. Kartu "Lead kalkulator baru" MUST dapat diklik dan membuka daftar Lead Kalkulator dengan filter status baru aktif.
- **FR-015**: Kartu "Prospek 30 hari" dan "Konversi lead kalkulator" tidak wajib dapat diklik.

**Tabel "Perlu ditindaklanjuti" (US2)**

- **FR-020**: Tabel MUST menggabungkan pesan masuk dan lead kalkulator yang berstatus baru dalam satu daftar, diurutkan dari yang paling baru masuk.
- **FR-021**: Tabel MUST menampilkan maksimal 10 baris.
- **FR-022**: Setiap baris MUST menampilkan: nama, sumber (Pesan Masuk / Lead Kalkulator), area, umur prospek dalam bentuk relatif (misal "3 jam lalu", "2 hari lalu"), dan estimasi tagihan listrik bulanan dalam Rupiah (khusus lead kalkulator; "-" untuk pesan masuk atau bila kosong).
- **FR-023**: Baris yang umurnya lebih dari 2 hari (48 jam) MUST diberi penanda "terlambat" yang terlihat jelas.
- **FR-024**: Mengklik sebuah baris MUST membuka halaman detail prospek tersebut di menu sumbernya.
- **FR-025**: Jika tidak ada prospek berstatus baru, tabel MUST menampilkan pesan kosong "Semua prospek sudah ditindaklanjuti".
- **FR-026**: Tabel MUST menyediakan tautan untuk membuka daftar lengkap masing-masing sumber (dengan filter status baru), karena tabel hanya memuat 10 baris.

**Grafik tren mingguan (US3)**

- **FR-030**: Grafik MUST menampilkan jumlah prospek per minggu untuk 12 minggu terakhir (termasuk minggu berjalan), dengan dua seri terpisah: Pesan Masuk dan Lead Kalkulator.
- **FR-031**: Minggu MUST dimulai hari Senin; label sumbu menunjukkan tanggal awal minggu.
- **FR-032**: Minggu tanpa prospek MUST tetap ditampilkan dengan nilai 0.
- **FR-033**: Grafik menghitung prospek semua status berdasarkan waktu masuk.

### Key Entities

- **Pesan Masuk**: prospek dari form Kontak. Atribut relevan: nama, area, status (baru / sudah dihubungi / selesai), waktu masuk.
- **Lead Kalkulator**: prospek dari kalkulator estimasi. Atribut relevan: nama, area, status (baru / dihubungi / qualified / won / lost), estimasi tagihan listrik bulanan, waktu masuk.
- **Prospek**: sebutan gabungan untuk Pesan Masuk dan Lead Kalkulator di dashboard. Bukan data baru; hanya tampilan gabungan dari dua sumber di atas.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin dapat mengetahui jumlah prospek yang belum ditindaklanjuti dari kedua sumber dalam waktu kurang dari 5 detik setelah login, tanpa membuka menu lain.
- **SC-002**: Admin dapat membuka detail prospek yang paling lama menunggu dengan maksimal 1 klik dari dashboard.
- **SC-003**: Angka di kartu, isi tabel, dan grafik 100% sesuai dengan data di menu Pesan Masuk dan Lead Kalkulator untuk data uji yang disiapkan.
- **SC-004**: Dashboard tetap terbuka tanpa error pada instalasi baru tanpa data prospek maupun dengan ribuan prospek.
- **SC-005**: Dashboard terbuka dalam waktu kurang dari 2 detik dengan 5.000 prospek di database.

## Assumptions

- **Akses**: saat ini semua pengguna yang bisa login ke panel dapat membuka menu Pesan Masuk dan Lead Kalkulator (tidak ada pembatasan per role). FR-003 mengikuti aturan tersebut, sehingga dalam kondisi sekarang semua pengguna panel melihat dashboard prospek. Jika kelak menu lead dibatasi per role, dashboard otomatis ikut terbatas.
- **Periode konversi 90 hari**: dipilih agar lead punya waktu untuk diproses sampai won/lost; periode 30 hari akan menurunkan angka konversi secara semu karena banyak lead masih dalam proses.
- **12 minggu** dipilih dari rentang 8–12 minggu yang diminta, karena memberi gambaran satu kuartal penuh.
- **Batas "terlambat" 2 hari** dihitung 48 jam sejak prospek masuk, tanpa memperhitungkan hari libur.
- **Estimasi tagihan** yang ditampilkan adalah estimasi tagihan bulanan hasil kalkulator untuk semua metode perhitungan (berdasarkan tagihan maupun berdasarkan peralatan).
- **Pesan Masuk tidak punya status "won"**, sehingga konversi hanya dihitung untuk lead kalkulator.
- Dashboard tidak memerlukan pembaruan otomatis tanpa reload; admin memuat ulang halaman untuk melihat data terbaru.
- Fitur ini dibangun di atas penghapusan dashboard Google Analytics (branch `026-remove-ga-dashboard`) dan tidak menambah layanan eksternal.
- Di luar scope: status konten (banner kedaluwarsa, artikel terjadwal) dan ringkasan log aktivitas (opsi 4 dan 5 yang tidak dipilih).
