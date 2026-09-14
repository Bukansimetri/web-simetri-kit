# Feature Specification: Setup Client Command

**Feature Branch**: `018-setup-client-command`

**Created**: 2026-09-13

**Status**: Draft

**Input**: User description: "Artisan command \"app:setup-client\" (AMC-228). Command ini dipakai developer/ops saat menyiapkan instalasi baru Web Solarpanel Kit untuk klien baru. Command harus: generate file .env dari .env.example, set nama aplikasi (app name) sesuai input, generate application key baru, dan clear semua cache (config, route, view, application) — sehingga setup klien baru jadi satu langkah reproducible via artisan command, bukan langkah manual satu-satu. Ini bagian dari Epic 6 (Deployment & Client Setup Tooling) di project Web Solarpanel Kit."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Setup instalasi klien baru dalam satu langkah (Priority: P1)

Seorang developer/ops baru saja meng-clone/mem-fork repositori Web Solarpanel Kit untuk klien baru. Alih-alih mengedit file konfigurasi secara manual, mengetik ulang nilai-nilai boilerplate, dan menjalankan beberapa command satu per satu, ia menjalankan satu command tunggal dengan memasukkan nama aplikasi klien tersebut, dan mendapatkan instalasi yang siap dipakai (file konfigurasi lengkap dengan nama aplikasi klien dan kunci enkripsi unik) dalam satu langkah.

**Why this priority**: Ini inti dari kebutuhan Epic 6 — mengubah proses setup klien dari serangkaian langkah manual yang rawan lupa/salah menjadi satu langkah reproducible. Tanpa ini, tujuan utama fitur (mempercepat & menstandardisasi onboarding klien) tidak tercapai.

**Independent Test**: Pada repositori yang baru di-clone tanpa file konfigurasi environment, jalankan command dengan sebuah nama aplikasi contoh; verifikasi file konfigurasi ter-generate, berisi nama aplikasi yang dimasukkan, dan aplikasi memiliki kunci enkripsi yang valid & unik.

**Acceptance Scenarios**:

1. **Given** repositori baru tanpa file konfigurasi environment, **When** developer menjalankan command dengan nama aplikasi "PT Sinar Abadi", **Then** file konfigurasi environment dibuat dari templat proyek, berisi nama aplikasi "PT Sinar Abadi", dan aplikasi memiliki kunci enkripsi baru yang valid.
2. **Given** command telah selesai dijalankan, **When** developer membuka aplikasi (mis. lewat browser atau command lain), **Then** aplikasi berjalan tanpa error konfigurasi yang hilang.
3. **Given** developer menjalankan command tanpa memasukkan nama aplikasi, **When** command dieksekusi, **Then** command menolak melanjutkan dan memberi tahu bahwa nama aplikasi wajib diisi.

---

### User Story 2 - Cache bersih pasca-setup (Priority: P1)

Setelah konfigurasi baru dibuat, developer/ops perlu memastikan aplikasi benar-benar berjalan dengan konfigurasi klien yang baru, bukan sisa cache (konfigurasi, rute, tampilan, atau cache aplikasi lain) yang mungkin terbawa dari repositori templat atau proses build sebelumnya.

**Why this priority**: Cache yang basi adalah sumber bug klasik pasca-deploy (aplikasi tampak memakai konfigurasi/tema lama walau file sudah diubah) — sama pentingnya dengan pembuatan konfigurasi itu sendiri untuk memastikan instalasi klien benar-benar bersih sejak awal.

**Independent Test**: Pada instalasi yang sudah memiliki cache konfigurasi/rute/tampilan tersimpan dari sesi sebelumnya, jalankan command dan verifikasi seluruh cache tersebut sudah dikosongkan/diperbarui sehingga tidak ada sisa konfigurasi lama yang terpakai.

**Acceptance Scenarios**:

1. **Given** aplikasi memiliki cache konfigurasi, rute, dan tampilan yang tersimpan, **When** command dijalankan, **Then** seluruh cache tersebut dibersihkan sebagai bagian dari proses setup.
2. **Given** command telah selesai dijalankan, **When** aplikasi diakses setelahnya, **Then** aplikasi menggunakan konfigurasi environment yang baru dibuat, bukan nilai yang sempat ter-cache sebelumnya.

---

### User Story 3 - Perlindungan dari penimpaan konfigurasi tanpa sengaja (Priority: P2)

Seorang developer/ops menjalankan command ini pada instalasi yang **sudah** memiliki konfigurasi environment (misalnya menjalankan ulang command secara tidak sengaja pada instalasi klien yang sudah live). Ia tidak ingin konfigurasi yang sudah ada — termasuk kunci enkripsi yang sudah dipakai untuk mengenkripsi data/sesi pengguna aktif — tertimpa atau berubah secara diam-diam.

**Why this priority**: Menimpa kunci enkripsi atau file konfigurasi pada instalasi yang sudah berjalan dapat merusak data terenkripsi dan sesi pengguna aktif di lingkungan produksi klien — risiko yang harus dicegah secara eksplisit, meski ini kasus sekunder dibanding alur setup awal (P1).

**Independent Test**: Pada instalasi yang sudah memiliki file konfigurasi dan kunci enkripsi, jalankan command tanpa opsi tambahan apa pun; verifikasi file konfigurasi dan kunci enkripsi yang sudah ada tidak berubah, dan developer diberi tahu bahwa langkah tersebut dilewati beserta cara melanjutkan secara sengaja bila memang diinginkan.

**Acceptance Scenarios**:

1. **Given** file konfigurasi environment sudah ada, **When** command dijalankan tanpa instruksi eksplisit untuk menimpa, **Then** file konfigurasi yang sudah ada tidak diubah, dan command memberi tahu bahwa langkah tersebut dilewati.
2. **Given** kunci enkripsi aplikasi sudah pernah di-generate, **When** command dijalankan tanpa instruksi eksplisit untuk menimpa, **Then** kunci enkripsi yang sudah ada tidak diganti.
3. **Given** developer secara eksplisit menyatakan ingin menimpa konfigurasi yang sudah ada, **When** command dijalankan dengan instruksi tersebut, **Then** command melanjutkan proses penimpaan sesuai permintaan.

---

### Edge Cases

- Apa yang terjadi jika templat konfigurasi environment proyek (sumber untuk file konfigurasi baru) tidak ditemukan di repositori? Command harus berhenti dengan pesan kesalahan yang jelas, bukan membuat file konfigurasi kosong/tidak lengkap.
- Bagaimana jika nama aplikasi yang dimasukkan mengandung karakter khusus (kutip, tanda sama dengan, baris baru) yang dapat merusak format file konfigurasi? Command harus menangani/menyisipkan nilai tersebut dengan aman tanpa merusak struktur file.
- Bagaimana jika proses dijalankan tanpa izin tulis pada direktori proyek? Command harus melaporkan kegagalan dengan jelas, bukan gagal secara diam-diam atau meninggalkan instalasi dalam kondisi setengah jadi.
- Bagaimana jika command dijalankan dua kali berturut-turut tanpa instruksi menimpa di antaranya? Command kedua harus melewati langkah pembuatan konfigurasi/kunci (sudah ada) namun tetap menjalankan pembersihan cache, dan tetap melaporkan hasil dengan jelas.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Command MUST membuat file konfigurasi environment aplikasi dari templat environment proyek ketika belum ada file konfigurasi environment pada instalasi tersebut.
- **FR-002**: Command MUST menerima nama aplikasi sebagai input dari operator yang menjalankannya, dan mewajibkan nilai tersebut tidak kosong/hanya spasi.
- **FR-003**: Command MUST menuliskan nama aplikasi yang diberikan ke dalam konfigurasi environment aplikasi, menggantikan nilai bawaan/templat.
- **FR-004**: Command MUST men-generate kunci enkripsi aplikasi yang baru dan unik ketika instalasi belum memiliki kunci enkripsi yang tersimpan.
- **FR-005**: Command MUST membersihkan seluruh cache konfigurasi, cache rute, cache tampilan, dan cache aplikasi umum sebagai bagian dari setiap eksekusinya (terlepas dari apakah langkah pembuatan konfigurasi/kunci dilewati).
- **FR-006**: Command MUST TIDAK menimpa file konfigurasi environment yang sudah ada secara default; penimpaan hanya boleh terjadi bila operator secara eksplisit menyatakan hal tersebut saat menjalankan command.
- **FR-007**: Command MUST TIDAK men-generate ulang kunci enkripsi yang sudah ada secara default; regenerasi hanya boleh terjadi bila operator secara eksplisit menyatakan hal tersebut.
- **FR-008**: Command MUST menampilkan ringkasan hasil yang jelas setelah selesai berjalan (langkah apa saja yang dijalankan, dilewati, atau gagal) sehingga operator dapat memastikan setup berhasil sebelum melanjutkan.
- **FR-009**: Command MUST berhenti dan melaporkan kesalahan yang jelas serta dapat ditindaklanjuti apabila prasyarat yang dibutuhkan tidak terpenuhi (mis. templat environment tidak ditemukan, tidak ada izin tulis), tanpa meninggalkan instalasi dalam kondisi konfigurasi yang tidak lengkap/ambigu.
- **FR-010**: Seluruh langkah setup (pembuatan konfigurasi, penetapan nama aplikasi, pembuatan kunci enkripsi, pembersihan cache) MUST dapat dijalankan lewat satu eksekusi command tunggal, tanpa langkah manual tambahan di luar penyediaan nama aplikasi (dan konfirmasi eksplisit bila memilih menimpa instalasi yang sudah ada).

### Key Entities

Tidak ada entitas data bisnis yang persisten terlibat — fitur ini adalah command-line tooling yang beroperasi pada file konfigurasi environment dan status cache aplikasi, bukan data yang tersimpan di database.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Developer/ops dapat menyiapkan instalasi siap pakai untuk klien baru, dari repositori yang baru di-clone hingga aplikasi dapat diakses tanpa error konfigurasi, dalam waktu kurang dari 2 menit menggunakan satu command.
- **SC-002**: 100% instalasi klien baru yang disiapkan lewat command ini memiliki nama aplikasi yang benar dan kunci enkripsi yang valid tanpa perlu satu pun pengeditan manual file konfigurasi untuk kedua hal tersebut.
- **SC-003**: Menjalankan command pada instalasi yang sudah dikonfigurasi sebelumnya tidak pernah mengubah konfigurasi environment atau kunci enkripsi yang sudah ada tanpa konfirmasi eksplisit dari operator — diverifikasi 100% pada pengujian pengulangan command.
- **SC-004**: Proses onboarding instalasi klien baru menghilangkan kebutuhan akan langkah-langkah manual terpisah (menyalin file templat, mengedit nama aplikasi, menjalankan pembuatan kunci, membersihkan tiap jenis cache satu per satu) menjadi satu langkah tunggal yang reproducible.

## Assumptions

- "Nama aplikasi" dalam lingkup fitur ini merujuk pada nilai konfigurasi environment tingkat aplikasi (dipakai sebagai judul/branding default di seluruh sistem), bukan pengaturan white-label yang tersimpan di database admin panel — karena command ini dapat dijalankan sebelum database instalasi klien tersambung/siap.
- Command ini ditujukan untuk dijalankan oleh developer/ops lewat command-line pada saat provisioning instalasi baru, bukan oleh pengguna akhir/klien lewat antarmuka web.
- Command dapat dijalankan berkali-kali dengan aman (idempoten untuk langkah pembersihan cache); langkah pembuatan konfigurasi dan kunci enkripsi bersifat sekali-jalan kecuali operator secara eksplisit meminta penimpaan.
- Tidak ada kebutuhan mengelola banyak profil/nama klien sekaligus dalam satu eksekusi — command menangani satu instalasi (satu klien) per eksekusi, sesuai dengan pola satu instalasi per klien yang sudah dipakai kit ini.
