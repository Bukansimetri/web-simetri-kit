# Feature Specification: Client Versioning Strategy

**Feature Branch**: `020-client-versioning-strategy`

**Created**: 2026-09-13

**Status**: Draft

**Input**: User description: "Tentukan strategi git/versioning lintas klien: template repo + upstream remote atau composer package private (AMC-230)."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Membuat repositori klien baru dari Simetri (Priority: P1)

Seorang developer/ops mendapat proyek klien baru dan perlu memulai instalasi dari Web Solarpanel Kit (Simetri). Ia mengikuti satu prosedur yang jelas dan terdokumentasi untuk membuat repositori khusus klien tersebut dari kit ini, tanpa harus menebak-nebak cara yang benar atau berimprovisasi setiap kali ada klien baru.

**Why this priority**: Ini titik awal dari setiap proyek klien — tanpa prosedur yang jelas dan konsisten, setiap developer bisa membuat repositori klien dengan cara berbeda-beda, yang berakibat sulit melakukan pembaruan lintas klien di kemudian hari (masalah yang secara eksplisit ingin dicegah AMC-230).

**Independent Test**: Ikuti prosedur yang didokumentasikan untuk membuat repositori klien baru dari Simetri; verifikasi hasilnya adalah repositori kerja yang independen (perubahan di repositori klien tidak otomatis memengaruhi Simetri) namun tetap tertaut ke Simetri sebagai sumber pembaruan di masa depan.

**Acceptance Scenarios**:

1. **Given** developer mengikuti prosedur yang didokumentasikan untuk klien baru, **When** repositori klien selesai dibuat, **Then** repositori tersebut berisi seluruh kode Simetri saat ini dan siap dikustomisasi khusus untuk klien tsb.
2. **Given** repositori klien baru sudah dibuat, **When** developer melakukan commit perubahan khusus klien (mis. branding, konten), **Then** perubahan tsb tidak mengubah/mempengaruhi repositori Simetri.
3. **Given** dua repositori klien berbeda dibuat dari Simetri pada waktu yang berbeda, **When** keduanya diperiksa, **Then** proses pembuatannya mengikuti langkah yang sama persis (bukan improvisasi ad hoc per klien).

---

### User Story 2 - Menerapkan pembaruan Simetri ke instalasi klien yang sudah berjalan (Priority: P1)

Setelah Simetri mendapat perbaikan/fitur baru (mis. perbaikan bug, modul baru, patch keamanan), seorang developer/ops perlu menerapkan pembaruan tersebut ke satu atau beberapa instalasi klien yang sudah live, tanpa kehilangan kustomisasi khusus klien yang sudah dilakukan sebelumnya (branding, konten, penyesuaian kecil).

**Why this priority**: Ini alasan utama mengapa strategi versioning dibutuhkan sejak awal — tanpa jalur pembaruan yang jelas, perbaikan/keamanan yang ditemukan di satu instalasi tidak bisa disebarkan secara efisien ke instalasi klien lain, memaksa perbaikan manual berulang di setiap klien (rawan kelewat, terutama untuk patch keamanan).

**Independent Test**: Buat satu perubahan contoh di Simetri (mis. perbaikan kecil), lalu terapkan ke sebuah instalasi klien yang sudah dikustomisasi menggunakan prosedur yang didokumentasikan; verifikasi perubahan tsb masuk ke instalasi klien tanpa menghapus/menimpa kustomisasi klien yang sudah ada.

**Acceptance Scenarios**:

1. **Given** instalasi klien yang sudah dikustomisasi (branding, konten) dan sudah live, **When** developer menerapkan pembaruan terbaru dari Simetri mengikuti prosedur yang didokumentasikan, **Then** perbaikan/fitur baru tersedia di instalasi klien, dan kustomisasi klien yang sudah ada tetap utuh.
2. **Given** proses penerapan pembaruan menghasilkan konflik antara perubahan Simetri dan kustomisasi klien pada bagian kode yang sama, **When** developer menjalankan prosedur tsb, **Then** konflik tersebut terlihat jelas dan harus diselesaikan secara sadar oleh developer (bukan salah satu sisi tertimpa diam-diam).
3. **Given** beberapa instalasi klien berbeda perlu menerima pembaruan yang sama, **When** developer mengulangi prosedur penerapan pembaruan pada tiap instalasi, **Then** hasilnya konsisten di semua instalasi (prosedur yang sama menghasilkan efek yang sama).

---

### User Story 3 - Prosedur terdokumentasi tersedia sebelum klien pertama dibuat (Priority: P2)

Sebelum tim mulai memotong (cut) repositori klien pertama dari Simetri, keputusan strategi dan langkah-langkahnya sudah didokumentasikan di satu tempat yang bisa dirujuk siapa pun di tim — bukan pengetahuan tak tertulis yang hanya diketahui satu orang.

**Why this priority**: Ini melengkapi (bukan blocker mutlak untuk) User Story 1 & 2 — keduanya bisa dijalankan begitu keputusan dibuat, tapi tanpa dokumentasi tertulis, konsistensi jangka panjang (poin utama AMC-230) berisiko luntur begitu personel tim berganti atau lupa detail prosedurnya.

**Independent Test**: Seorang anggota tim yang belum pernah terlibat dalam pembuatan repositori klien sebelumnya dapat mengikuti dokumentasi yang ada dan berhasil membuat repositori klien baru serta menerapkan satu pembaruan contoh, tanpa bertanya langsung ke orang yang membuat keputusan awal.

**Acceptance Scenarios**:

1. **Given** dokumentasi strategi versioning klien sudah ditulis, **When** anggota tim baru membacanya, **Then** ia memahami satu strategi yang dipilih (bukan beberapa opsi yang belum diputuskan) beserta langkah konkret untuk kedua skenario utama (buat klien baru, terapkan pembaruan).
2. **Given** dokumentasi tsb sudah ada, **When** dicari di repositori proyek, **Then** dokumentasi mudah ditemukan dan tidak tercampur/tersembunyi di lokasi yang tidak jelas.

---

### Edge Cases

- Apa yang terjadi bila developer memodifikasi bagian kode yang sama persis dengan perubahan yang dibawa pembaruan Simetri (mis. developer mengubah langsung sebuah file inti alih-alih lewat mekanisme kustomisasi yang disediakan kit ini)? Prosedur MUST membuat konflik semacam ini terlihat jelas, bukan menyelesaikannya secara otomatis dengan menimpa salah satu sisi.
- Bagaimana bila sebuah instalasi klien sengaja TIDAK ingin menerima pembaruan tertentu dari Simetri (mis. klien menolak perubahan UI tertentu)? Strategi MUST memungkinkan instalasi klien berjalan dengan versi Simetri yang berbeda dari klien lain (tidak dipaksa selalu update serentak).
- Bagaimana bila sebuah perbaikan awalnya ditemukan/dibuat langsung di repositori klien (bukan di Simetri)? Perlu ada jalur yang jelas (didokumentasikan) untuk membawa perbaikan tsb kembali ke Simetri agar klien lain juga mendapat manfaatnya, meski ini bukan alur utama yang wajib otomatis.
- Bagaimana bila riwayat commit Simetri sendiri berubah drastis (mis. rewrite sejarah)? Prosedur pembaruan MUST tetap dapat dijalankan tanpa merusak riwayat repositori klien yang sudah ada.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Tim MUST memiliki satu strategi versioning lintas klien yang sudah diputuskan secara eksplisit dan didokumentasikan tertulis, bukan sekadar kesepakatan lisan/tak tertulis.
- **FR-002**: Strategi tsb MUST mencakup prosedur konkret dan dapat diikuti langkah demi langkah untuk membuat repositori/instalasi klien baru dari kondisi Simetri saat ini.
- **FR-003**: Strategi tsb MUST mencakup prosedur konkret untuk menerapkan pembaruan dari Simetri ke instalasi klien yang sudah ada dan sudah dikustomisasi, tanpa menghapus kustomisasi klien tsb.
- **FR-004**: Prosedur pembaruan MUST membuat konflik antara perubahan Simetri dan kustomisasi klien terlihat jelas kepada developer, dan MUST mewajibkan penyelesaian konflik tsb secara sadar sebelum pembaruan dianggap selesai diterapkan.
- **FR-005**: Setiap instalasi klien MUST dapat berjalan pada versi Simetri yang berbeda dari instalasi klien lain (pembaruan bersifat per-instalasi, bukan serempak dipaksa untuk seluruh klien sekaligus).
- **FR-006**: Perubahan yang dilakukan pada satu instalasi klien MUST TIDAK secara otomatis memengaruhi Simetri maupun instalasi klien lain, kecuali melalui langkah eksplisit yang sengaja dilakukan developer untuk membawa perubahan tsb kembali ke Simetri.
- **FR-007**: Dokumentasi strategi ini MUST tersedia dan mudah ditemukan di repositori proyek sebelum repositori klien pertama dibuat menggunakan Simetri.
- **FR-008**: Dokumentasi MUST mencakup panduan mengenai kapan/bagaimana sebuah perbaikan yang ditemukan di level klien sebaiknya dibawa kembali ke Simetri, agar klien lain turut mendapat manfaatnya.

### Key Entities

Tidak ada entitas data aplikasi (database) yang terlibat — fitur ini adalah keputusan proses/tooling pengembangan (strategi kontrol versi) beserta dokumentasinya, bukan fitur yang berjalan di dalam aplikasi itu sendiri.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Developer dapat membuat repositori klien baru yang siap dikustomisasi dari kondisi Simetri terkini dalam waktu kurang dari 15 menit mengikuti prosedur yang didokumentasikan.
- **SC-002**: Sebuah pembaruan/perbaikan di Simetri dapat diterapkan ke instalasi klien yang sudah dikustomisasi tanpa kehilangan satu pun kustomisasi klien tsb — diverifikasi 100% pada pengujian skenario penerapan pembaruan.
- **SC-003**: Anggota tim yang belum pernah membuat repositori klien sebelumnya dapat menyelesaikan proses pembuatan repositori klien baru dan penerapan satu pembaruan contoh hanya dengan membaca dokumentasi yang tersedia, tanpa bantuan langsung dari orang lain.
- **SC-004**: Dua atau lebih instalasi klien yang dibuat pada waktu berbeda dapat menerima pembaruan yang sama secara independen, masing-masing tetap mempertahankan kustomisasinya sendiri.

## Assumptions

- "Klien" dalam konteks fitur ini berarti satu instalasi/deployment terpisah dari kit ini untuk satu pelanggan/perusahaan, konsisten dengan pola satu-instalasi-per-klien yang sudah dipakai kit ini di seluruh fitur lain (lihat 018-setup-client-command).
- Mekanisme teknis persis (mis. jenis remote git, workflow branching, atau pendekatan package) TIDAK ditentukan dalam spesifikasi ini — spesifikasi ini berfokus pada hasil yang harus dicapai (dapat membuat klien baru, dapat menerapkan pembaruan tanpa kehilangan kustomisasi, terdokumentasi); pilihan mekanisme spesifik diserahkan ke fase perencanaan teknis, dengan pertimbangan dua opsi yang disebut di judul task ini (template repo + upstream remote, atau composer package private).
- Audiens dokumentasi adalah developer/ops internal tim yang mengerjakan proyek klien menggunakan kit ini, bukan klien akhir/pengguna situs.
- Fitur ini menghasilkan keputusan dan dokumentasi proses; tidak dibutuhkan perubahan pada kode aplikasi (model, migration, UI) itu sendiri.
- Repositori Simetri saat ini (`web-simetri-kit`) berperan sebagai sumber kebenaran (source of truth) tempat perbaikan/fitur baru terus dikembangkan, dan menjadi rujukan pembaruan bagi seluruh repositori klien.
