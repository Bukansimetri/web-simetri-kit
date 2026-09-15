# Feature Specification: Banner Hero Slider

**Feature Branch**: `022-banner-hero-slider`

**Created**: 2026-09-14

**Status**: Draft

**Input**: User description: "Banner beranda saat ini menghilangkan seluruh isi hero begitu satu gambar diunggah — teks, tombol CTA, dan trust bar hilang, dan tidak ada kontrol warna. Naikkan banner menjadi hero slider: satu banner = satu slide hero utuh (gambar + badge + judul + subjudul + dua CTA + trust bar HTML bebas + preset tampilan), tampil sebagai slider bila lebih dari satu slide. Semua dapat diatur admin tanpa mengubah kode."

## Clarifications

### Session 2026-09-14

- Q: Sedetail apa isi teks tiap slide yang bisa diatur admin? → A: **Lengkap seperti hero yang ada sekarang** — badge, judul, subjudul, CTA utama, CTA sekunder. Semua opsional per slide.
- Q: Bagaimana trust bar ("500+ Pelanggan Puas" beserta avatar) diisi? → A: **Field HTML bebas** (editor kaya), bukan teks biasa, karena isinya memuat gambar/avatar, ikon, atau logo sertifikasi yang bisa berbeda tiap klien.
- Q: Sejauh mana kontrol warna per slide? → A: **Preset dari tema brand** — pilihan overlay dan posisi teks yang sudah ditentukan; warna tombol tetap mengikuti pengaturan brand. Tidak ada input warna bebas.
- Q: Data banner yang sudah ada diapakan? → A: **Diisi otomatis** dari teks hero yang berlaku saat ini, sehingga banner lama langsung tampil utuh dan tinggal diedit.
- Q: Bagaimana perilaku slider? → A: **Tanpa perpindahan otomatis.** Navigasi sepenuhnya manual, dengan panah yang beranimasi sebagai penanda bahwa slide bisa dipindah.

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Admin memasang banner tanpa kehilangan isi hero (Priority: P1) 🎯 MVP

Admin mengunggah satu gambar banner beserta judul, subjudul, dan tombol ajakan bertindak, lalu menyimpannya. Beranda menampilkan gambar tersebut sebagai hero lengkap dengan teks dan tombol yang baru saja diisi — bukan gambar polos.

**Why this priority**: Ini keluhan utama yang memicu fitur ini. Tanpa perbaikan ini, mengganti banner berarti menghapus seluruh pesan pemasaran dan jalur konversi di layar pertama beranda.

**Independent Test**: Login sebagai admin, buat satu banner berisi gambar + judul + subjudul + dua CTA, buka beranda, verifikasi seluruh elemen tampil di atas gambar dan kedua tombol menuju alamat yang diisi.

**Acceptance Scenarios**:

1. **Given** belum ada banner, **When** admin membuat satu banner lengkap dengan judul, subjudul, dan dua CTA, **Then** beranda menampilkan gambar banner tersebut beserta seluruh teks dan kedua tombol.
2. **Given** satu banner tayang, **When** pengunjung membuka beranda, **Then** tidak ada kontrol panah maupun titik navigasi karena hanya ada satu slide.
3. **Given** admin mengisi gambar saja tanpa teks apa pun, **When** pengunjung membuka beranda, **Then** banner tampil sebagai gambar penuh tanpa elemen teks kosong yang menggantung.
4. **Given** admin mengisi label CTA tanpa alamat tujuan, **When** admin menyimpan, **Then** sistem menolak dengan pesan yang menyebut pasangan field mana yang belum lengkap.
5. **Given** admin baru menyimpan perubahan banner, **When** admin membuka beranda, **Then** perubahan langsung terlihat tanpa perlu menunggu maupun membersihkan cache secara manual.

---

### User Story 2 - Pengunjung menelusuri beberapa slide penawaran (Priority: P1)

Pengunjung membuka beranda dan melihat slide pertama. Panah navigasi bergerak halus beberapa saat sehingga jelas bahwa ada slide lain yang bisa dibuka. Pengunjung menekan panah, menekan titik navigasi, atau menggeser layar di ponsel untuk berpindah slide.

**Why this priority**: Menampilkan lebih dari satu penawaran adalah alasan kedua fitur ini ada, dan tanpa perpindahan otomatis penandanya harus benar-benar terlihat agar slide kedua dan seterusnya tidak mubazir.

**Independent Test**: Buat tiga banner tayang, buka beranda, verifikasi slide pertama tampil, panah beranimasi saat slider masuk layar, dan seluruh slide dapat dicapai lewat panah, titik, papan ketik, maupun geser di layar sentuh.

**Acceptance Scenarios**:

1. **Given** tiga banner tayang, **When** pengunjung membuka beranda, **Then** slide pertama (urutan terkecil) yang tampil, disertai panah, titik navigasi, dan penunjuk posisi slide.
2. **Given** slider tampil di layar, **When** slider pertama kali masuk area pandang, **Then** panah navigasi memainkan animasi penanda singkat lalu berhenti dengan sendirinya.
3. **Given** pengunjung berada di slide terakhir, **When** pengunjung menekan panah berikutnya, **Then** slider kembali ke slide pertama.
4. **Given** pengunjung tidak melakukan apa pun, **When** waktu berlalu, **Then** slide **tidak** berpindah sendiri.
5. **Given** pengunjung memakai ponsel, **When** pengunjung menggeser slide ke samping, **Then** slide berpindah dan petunjuk geser berhenti tampil.
6. **Given** pengunjung mengaktifkan preferensi pengurangan gerak pada perangkatnya, **When** beranda dibuka, **Then** animasi penanda dan transisi slide tidak dijalankan namun navigasi tetap berfungsi.
7. **Given** pengunjung menavigasi dengan papan ketik, **When** pengunjung menekan tombol panah kiri/kanan pada slider, **Then** slide berpindah dan hanya elemen pada slide aktif yang dapat dijangkau fokus.

---

### User Story 3 - Admin mengatur tampilan slide agar teks tetap terbaca (Priority: P2)

Admin memilih gaya lapisan (overlay) dan posisi teks untuk tiap slide dari daftar pilihan yang tersedia, sehingga teks tetap terbaca di atas gambar terang maupun gelap tanpa perlu mengedit kode atau mengunggah ulang gambar yang sudah digelapkan.

**Why this priority**: Bernilai tinggi tetapi bukan penghalang tayang — slide tetap bisa dipakai dengan pengaturan bawaan. Diletakkan setelah dua cerita P1.

**Independent Test**: Buat dua slide dengan gambar terang dan gelap, atur masing-masing dengan gaya lapisan dan posisi teks berbeda, verifikasi teks terbaca pada keduanya dan posisinya sesuai pilihan.

**Acceptance Scenarios**:

1. **Given** admin membuka form banner, **When** admin memilih gaya lapisan, **Then** pilihan terbatas pada daftar preset yang disediakan, tanpa input warna bebas.
2. **Given** slide memakai gambar terang, **When** admin memilih preset lapisan terang, **Then** teks slide ditampilkan dengan warna gelap sehingga tetap terbaca.
3. **Given** admin memilih posisi teks tengah, **When** pengunjung membuka beranda, **Then** blok teks dan tombol slide tersebut tampil di tengah, dan arah gradasi lapisan menyesuaikan.
4. **Given** admin mengganti warna brand di pengaturan tema, **When** beranda dibuka, **Then** warna tombol pada seluruh slide ikut berubah tanpa perlu mengedit banner satu per satu.

---

### User Story 4 - Admin menyusun trust bar sendiri (Priority: P3)

Admin mengisi area bukti sosial di bawah tombol dengan konten buatannya sendiri — bisa berisi teks, ikon, avatar pelanggan, atau logo sertifikasi — memakai editor kaya, lalu melihat hasilnya tampil pada slide tersebut.

**Why this priority**: Melengkapi kesetaraan dengan hero lama dan berguna lintas klien, tetapi slide sudah bernilai penuh tanpanya.

**Independent Test**: Isi trust bar satu slide dengan teks bercampur gambar, verifikasi tampil pada slide tersebut saja, dan verifikasi slide tanpa trust bar tidak menyisakan ruang kosong.

**Acceptance Scenarios**:

1. **Given** admin mengisi trust bar dengan teks dan gambar, **When** pengunjung membuka beranda, **Then** konten tersebut tampil apa adanya di bawah tombol pada slide itu.
2. **Given** admin mengosongkan trust bar, **When** pengunjung membuka beranda, **Then** area tersebut tidak dirender sama sekali.
3. **Given** konten trust bar memuat elemen yang tidak diizinkan, **When** halaman dirender, **Then** elemen tersebut dibuang dan sisa konten tetap tampil.

---

### Edge Cases

- **Tidak ada banner tayang sama sekali** (semua nonaktif, kedaluwarsa, atau belum dimulai): beranda kembali memakai hero bawaan sehingga layar pertama tidak pernah kosong.
- **Berkas gambar hilang dari penyimpanan** meski recordnya ada: slide tersebut dilewati, slide lain tetap tampil, dan jumlah titik navigasi menyesuaikan.
- **Judul atau subjudul sangat panjang**: teks dipotong secara rapi atau tetap terbaca tanpa menutupi tombol maupun merusak tinggi slide.
- **Hanya CTA sekunder yang diisi**, CTA utama kosong: tombol yang ada tetap tampil tanpa menyisakan celah tombol kosong.
- **Banner lama yang hanya punya alamat tautan** dan tanpa CTA: seluruh area gambar tetap dapat diklik seperti perilaku sebelumnya.
- **Slide punya alamat tautan sekaligus tombol CTA**: tombol yang menang; gambar tidak lagi dibungkus tautan agar tidak ada tautan bersarang.
- **Gambar berorientasi potret atau rasio tidak lazim**: slide tetap pada tinggi yang konsisten dan gambar tidak gepeng.
- **Dua banner memiliki nilai urutan sama**: urutan tampil tetap konsisten antar pemuatan halaman.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: Sistem MUST memungkinkan admin mengisi badge, judul, subjudul, label dan alamat CTA utama, label dan alamat CTA sekunder, serta konten trust bar untuk setiap banner.
- **FR-002**: Setiap elemen konten pada FR-001 MUST bersifat opsional; elemen yang dikosongkan tidak boleh dirender maupun menyisakan ruang kosong pada slide.
- **FR-003**: Sistem MUST menolak penyimpanan bila label CTA diisi tanpa alamat tujuan, atau sebaliknya, dengan pesan validasi yang menyebut field bersangkutan.
- **FR-004**: Sistem MUST menerima alamat CTA berupa tautan internal maupun eksternal.
- **FR-005**: Sistem MUST menyediakan pilihan gaya lapisan dan posisi teks per banner dalam bentuk daftar preset tertutup, tanpa input warna bebas.
- **FR-006**: Warna tombol dan aksen pada slide MUST mengikuti pengaturan brand yang berlaku, bukan nilai yang disimpan per banner.
- **FR-007**: Konten trust bar MUST dapat memuat elemen visual (gambar/ikon) dan MUST dibersihkan terhadap daftar elemen dan atribut yang diizinkan sebelum dirender ke halaman publik.
- **FR-008**: Beranda MUST menampilkan seluruh banner yang memenuhi syarat tayang sebagai hero, satu slide per banner, terurut menurut urutan tampil.
- **FR-009**: Bila hanya ada satu banner tayang, sistem MUST menampilkannya tanpa kontrol navigasi.
- **FR-010**: Bila tidak ada banner tayang, sistem MUST menampilkan hero bawaan sebagai cadangan.
- **FR-011**: Slider MUST TIDAK berpindah slide secara otomatis.
- **FR-012**: Slider MUST menampilkan penanda visual beranimasi pada kontrol navigasi yang mengomunikasikan bahwa slide dapat dipindah, dan animasi tersebut MUST berhenti dengan sendirinya setelah beberapa saat.
- **FR-013**: Slider MUST dapat dinavigasi lewat panah, titik navigasi, papan ketik, dan gerakan geser pada layar sentuh.
- **FR-014**: Slider MUST menghentikan seluruh animasi bila perangkat pengunjung meminta pengurangan gerak, tanpa mengurangi fungsi navigasi.
- **FR-015**: Sistem MUST memberi tahu pengunjung pemakai teknologi bantu mengenai jumlah slide dan posisi slide aktif, serta MUST mencegah elemen pada slide non-aktif dijangkau fokus.
- **FR-016**: Banner yang sudah ada sebelum fitur ini MUST tetap tayang, dan banner dengan urutan terkecil MUST terisi otomatis dengan konten hero yang berlaku saat ini.
- **FR-017**: Banner yang hanya memiliki alamat tautan dan tanpa CTA MUST tetap dapat diklik pada seluruh area gambarnya.
- **FR-018**: Perubahan banner di panel admin MUST terlihat di beranda pada pemuatan halaman berikutnya, tanpa tindakan manual dari admin.
- **FR-019**: Slide pertama MUST diprioritaskan pemuatannya, sementara slide berikutnya MUST dimuat tertunda.
- **FR-020**: Banner yang berkas gambarnya tidak ditemukan MUST dilewati tanpa menggagalkan render beranda.
- **FR-021**: Aturan syarat tayang yang berlaku saat ini (status aktif dan periode tayang) MUST tetap berlaku tanpa perubahan perilaku.
- **FR-022**: Admin MUST dapat mengubah urutan slide dari daftar banner tanpa mengetik ulang nilai urutan satu per satu.

### Key Entities

- **Banner (Hero Slide)**: Satu slide hero utuh. Selain atribut yang sudah ada (judul internal, gambar, teks alt, alamat tautan, periode tayang, urutan, status aktif), kini membawa konten tampil: badge, judul, subjudul, sepasang label+alamat untuk dua CTA, konten trust bar, serta pilihan gaya lapisan dan posisi teks.
- **Pengaturan Brand**: Sumber warna dan tipografi yang dipakai seluruh slide; tidak diubah oleh fitur ini, hanya dibaca.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Admin dapat mengganti gambar banner beranda tanpa kehilangan satu pun elemen teks atau tombol yang sudah tampil sebelumnya — nol elemen hilang, dibanding seluruh elemen hilang pada perilaku saat ini.
- **SC-002**: Admin non-teknis dapat menyusun satu slide hero lengkap (gambar, teks, dua tombol, tampilan) dari nol dalam waktu di bawah 5 menit tanpa bantuan developer.
- **SC-003**: Mengubah jumlah, isi, urutan, maupun tampilan slide beranda tidak memerlukan perubahan kode sama sekali.
- **SC-004**: Perubahan banner yang disimpan admin terlihat di beranda dalam pemuatan halaman berikutnya, bukan setelah menunggu beberapa menit.
- **SC-005**: Pada beranda dengan tiga slide, pengunjung dapat mencapai seluruh slide memakai tetikus, papan ketik, maupun sentuhan.
- **SC-006**: Beranda lolos pemeriksaan aksesibilitas dasar untuk kontras teks dan penamaan kontrol pada seluruh preset lapisan yang tersedia.
- **SC-007**: Waktu tampil elemen terbesar di beranda tidak memburuk dibanding sebelum fitur ini, meski jumlah slide bertambah.
- **SC-008**: Seluruh banner yang ada di basis data sebelum pembaruan tetap tayang setelah pembaruan, tanpa kehilangan data.

## Assumptions

- Pengisi banner adalah admin terautentikasi yang tepercaya; konten trust bar tetap dibersihkan sebagai jaring pengaman terhadap tempelan dari sumber luar, bukan sebagai pertahanan terhadap admin yang berniat jahat.
- Satu gambar dipakai untuk semua ukuran layar; gambar terpisah untuk ponsel di luar cakupan.
- Latar slide berupa gambar diam; video di luar cakupan.
- Jumlah slide yang wajar di beranda adalah segelintir (kurang dari sepuluh); tidak diperlukan pemuatan bertahap.
- Hero bawaan yang ada sekarang tetap dipertahankan sebagai cadangan dan tetap dipakai halaman lain.
- Aturan syarat tayang, penyimpanan berkas, dan konversi gambar mengikuti modul banner yang sudah berjalan (spesifikasi 012).
- Bahasa antarmuka panel admin mengikuti modul lain, yaitu Bahasa Indonesia.
