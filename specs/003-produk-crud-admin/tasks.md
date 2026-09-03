---

description: "Task list for Produk CRUD Admin"
---

# Tasks: Produk CRUD Admin

**Input**: Design documents from `/specs/003-produk-crud-admin/`

**Prerequisites**: [plan.md](./plan.md), [spec.md](./spec.md), [research.md](./research.md), [data-model.md](./data-model.md), [contracts/admin-panel-surface.md](./contracts/admin-panel-surface.md), [quickstart.md](./quickstart.md)

**Tests**: Disertakan — Constitution Principle IV (Module Test Coverage) mewajibkan feature test dasar sebelum sebuah unit kerja dianggap selesai.

**Organization**: Tasks dikelompokkan per user story (US1–US5, sesuai prioritas P1/P2/P3 di spec.md) supaya masing-masing bisa dikerjakan, ditest, dan dirilis independen.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Bisa dikerjakan paralel (file berbeda, tidak saling bergantung)
- **[Story]**: User story terkait (US1–US5)
- Path file selalu eksplisit

## Path Conventions

Perluasan langsung admin panel Filament yang sudah ada — semua path relatif ke root repo (lihat [plan.md](./plan.md) § Project Structure).

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Verifikasi baseline sebelum perubahan dimulai.

- [X] T001 Jalankan `vendor/bin/pint --format agent` dan `php artisan test --compact` untuk memastikan baseline hijau sebelum mulai (tidak ada file diubah di task ini)

**Checkpoint**: Baseline project terverifikasi bersih sebelum perubahan dimulai.

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Skema data (`categories` baru, `products` diperluas + migrasi data existing) dan model dasar yang dipakai SEMUA user story. Tanpa phase ini, tidak ada Resource admin yang bisa dibangun.

**⚠️ CRITICAL**: T002–T007 harus selesai sebelum task apa pun di Phase 3–7 (US1–US5) dimulai.

- [X] T002 Buat migration `database/migrations/xxxx_create_categories_table.php` (kolom `name` unique, `order` default 0, timestamps) sesuai [data-model.md](./data-model.md#category-baru)
- [X] T003 Buat model & factory `app/Models/Category.php` + `database/factories/CategoryFactory.php` (relasi `hasMany(Product::class)`)
- [X] T004 Buat migration `database/migrations/xxxx_update_products_table_for_category_and_gallery.php`: tambah `category_id` (FK nullable dulu → `restrictOnDelete()`), tambah `images` (json, default `[]`); backfill `Category` dari nilai unik `products.category` existing lalu isi `category_id`; ubah `category_id` jadi `NOT NULL`; drop kolom `category` (string) dan `image_path` (lihat research.md §3 untuk urutan aman)
- [X] T005 Update `app/Models/Product.php`: tambah relasi `belongsTo(Category::class)`, cast `images` sebagai `array`, hapus referensi ke kolom `category`/`image_path` yang sudah di-drop
- [X] T006 Buat `database/seeders/CategorySeeder.php` (3 kategori: Residensial, Komersial & Industri, Pompa Air) dan daftarkan di `DatabaseSeeder` SEBELUM `ProductSeeder`
- [X] T007 Update `database/seeders/ProductSeeder.php` dan `database/factories/ProductFactory.php`: ganti `category` (string) jadi `category_id` (lookup/factory relasi ke `Category`), ganti `image_path` jadi `images` (array minimal 1 path placeholder)

**Checkpoint**: Skema `categories`/`products` siap, data existing ter-migrasi — US1–US5 bisa mulai dikerjakan.

---

## Phase 3: User Story 1 - Admin mengelola kategori produk (Priority: P1)

**Goal**: Admin bisa CRUD kategori produk lewat panel admin, dengan guard tidak bisa hapus kategori yang masih dipakai produk.

**Independent Test**: Login sebagai admin, buka menu Kategori Produk, buat kategori baru, verifikasi muncul di daftar. Edit nama kategori, verifikasi berubah. Coba hapus kategori yang masih dipakai produk, verifikasi ditolak dengan pesan jelas.

### Tests for User Story 1

- [X] T008 [P] [US1] Feature test: admin bisa membuat, mengedit kategori; nama duplikat ditolak dengan error jelas, di `tests/Feature/Admin/CategoryResourceTest.php`
- [X] T009 [P] [US1] Feature test: hapus kategori yang masih dipakai produk ditolak dengan notifikasi jelas (record tidak terhapus); kategori tanpa produk berhasil dihapus, di `tests/Feature/Admin/CategoryResourceTest.php`

### Implementation for User Story 1

- [X] T010 [US1] Buat `app/Filament/Resources/CategoryResource.php` + `Pages/{ListCategories,CreateCategory,EditCategory}.php` (form: `name` TextInput unique, `order` numeric input; table: kolom `name`, `order`, jumlah produk)
- [X] T011 [US1] Tambahkan guard hapus di `CategoryResource`: `DeleteAction`/`DeleteBulkAction` dengan `->before()` cek `products()->count() > 0`, batalkan + `Notification::make()->danger()` kalau masih dipakai (FR-004; depends on T010)
- [X] T012 [US1] Sesuaikan tab filter kategori di `resources/views/pages/produk/index.blade.php` supaya mengambil daftar dari `Category::orderBy('order')->get()` (bukan 4 tombol hardcode `Semua/Residensial/Komersial & Industri/Pompa Air`) — controller `ProductController@index` meneruskan `$categories` ke view (depends on T010; lihat FR-002 "urutan tab filter mengikuti `order`")

**Checkpoint**: User Story 1 selesai — kategori bisa dikelola penuh via admin, independen dari US2–US5.

---

## Phase 4: User Story 2 - Admin menambah & mengedit produk (Priority: P1) 🎯 MVP

**Goal**: Admin bisa membuat & mengedit produk dasar (nama, kategori, harga, deskripsi) lewat panel admin, langsung tercermin di halaman publik.

**Independent Test**: (Prasyarat: minimal satu kategori sudah ada dari seed/US1). Login sebagai admin, buat produk baru dengan field wajib terisi, simpan, verifikasi muncul di `/produk`. Edit produk tsb, verifikasi perubahan tampil di `/produk/{slug}`.

**Depends on**: Phase 2 (Foundational). Tidak bergantung pada US1 secara fungsional (kategori seed cukup untuk test independen), tapi T012 (US1) dan halaman ini berbagi controller `ProductController` — kerjakan T012 dulu untuk menghindari conflict kalau paralel.

### Tests for User Story 2

- [X] T013 [P] [US2] Feature test: admin bisa membuat produk baru (field wajib terisi) dan langsung muncul di `/produk`; slug kosong auto-generate dari nama, di `tests/Feature/Admin/ProductResourceTest.php`
- [X] T014 [P] [US2] Feature test: admin bisa mengedit produk, perubahan tercermin di `/produk/{slug}`; slug duplikat ditolak; submit tanpa field wajib (nama/kategori/harga) ditolak dengan pesan per field, di `tests/Feature/Admin/ProductResourceTest.php`

### Implementation for User Story 2

- [X] T015 [US2] Buat `app/Filament/Resources/ProductResource.php` + `Pages/{ListProducts,CreateProduct,EditProduct}.php` — form dasar: `name` TextInput (reactive → auto-fill slug), `slug` TextInput (unique, bisa di-override), `category_id` Select (opsi dari `Category::pluck('name','id')`, required), `short_description`/`description` Textarea, `price`/`strikethrough_price` TextInput numeric (depends on T005, T010)
- [X] T016 [US2] Tambahkan validasi & pesan error Filament untuk field wajib (`name`, `category_id`, `price`) dan slug unik (`ignoreRecord: true` saat edit) di `ProductResource` (FR-006, FR-007, FR-014; depends on T015)

**Checkpoint**: User Story 2 selesai — admin bisa kelola produk dasar penuh, bisa didemo/dirilis sebagai MVP bersama US1.

---

## Phase 5: User Story 3 - Admin mengelola galeri gambar produk (Priority: P2)

**Goal**: Admin upload beberapa gambar per produk, atur urutan (gambar pertama = cover), hapus gambar individual — dan halaman publik benar-benar merender gambar tsb (memperbaiki gap render dari 002, lihat research.md).

**Independent Test**: Edit produk, upload 3 gambar, ubah urutan salah satu ke posisi pertama, simpan, buka `/produk/{slug}` — verifikasi gambar di posisi pertama jadi gambar utama & seluruh galeri tampil sesuai urutan. Hapus satu gambar, verifikasi hilang dari publik.

**Depends on**: Phase 2 (Foundational) dan T015 (form Produk sudah ada dari US2) — field galeri ditambahkan ke form yang sama.

### Tests for User Story 3

- [X] T017 [P] [US3] Feature test: admin upload beberapa gambar untuk satu produk, urutan tersimpan sesuai input; gambar pertama jadi cover di kartu `/produk`, di `tests/Feature/Admin/ProductResourceTest.php`
- [X] T018 [P] [US3] Feature test: `/produk/{slug}` untuk produk tanpa gambar sama sekali menampilkan placeholder wajar (bukan error/gambar rusak), di `tests/Feature/Pages/ProductPageTest.php` (perluas file yang sudah ada dari 002)

### Implementation for User Story 3

- [X] T019 [US3] Tambahkan `FileUpload::make('images')->multiple()->reorderable()->image()->disk('public')->directory('products')` ke form `ProductResource` (FR-010, FR-011, FR-015; depends on T015)
- [X] T020 [P] [US3] Update `resources/views/components/sections/product-card.blade.php`: render `<img>` dari `$product->images[0] ?? null` dengan fallback placeholder wajar saat kosong (menggantikan div placeholder statis dari 002)
- [X] T021 [P] [US3] Update `resources/views/pages/produk/show.blade.php`: render galeri penuh dari `$product->images` (gambar pertama sebagai showcase utama, sisanya sebagai thumbnail/galeri di bawahnya), fallback placeholder wajar saat kosong

**Checkpoint**: User Story 3 selesai — galeri gambar berfungsi penuh dari admin sampai frontend publik, independen dari US4/US5.

---

## Phase 6: User Story 4 - Admin mengelola spesifikasi teknis & fitur unggulan (Priority: P2)

**Goal**: Admin mengisi daftar spesifikasi teknis dan fitur unggulan produk lewat UI repeater dinamis (tambah/hapus baris bebas).

**Independent Test**: Edit produk, tambah 3 baris spesifikasi dan 2 baris fitur unggulan, simpan, buka `/produk/{slug}`, verifikasi seluruh baris tampil sesuai urutan.

**Depends on**: Phase 2 (Foundational) dan T015 (form Produk dari US2).

### Tests for User Story 4

- [X] T022 [P] [US4] Feature test: admin menambah beberapa baris specs (label+value) dan features (icon+title+description) lewat repeater, tersimpan dan tampil di `/produk/{slug}` sesuai urutan; baris yang dihapus sebelum simpan tidak ikut tersimpan, di `tests/Feature/Admin/ProductResourceTest.php`

### Implementation for User Story 4

- [X] T023 [US4] Tambahkan `Repeater::make('specs')` (sub-field `label`, `value` — keduanya TextInput) ke form `ProductResource`, mapping ke struktur JSON yang sudah dikonsumsi `produk/show.blade.php` dari 002 (FR-012; depends on T015)
- [X] T024 [US4] Tambahkan `Repeater::make('features')` (sub-field `icon` TextInput/Select ikon Material Symbols, `title` TextInput, `description` Textarea) ke form `ProductResource`, mapping ke struktur JSON yang sama (FR-012; depends on T015)

**Checkpoint**: User Story 4 selesai — spesifikasi & fitur unggulan bisa dikelola penuh dari admin, independen dari US5.

---

## Phase 7: User Story 5 - Admin mengatur urutan tampil & menghapus produk (Priority: P3)

**Goal**: Admin mengubah urutan tampil produk dan menghapus produk yang sudah tidak dijual, dengan konfirmasi sebelum hapus.

**Independent Test**: Ubah `order` dua produk di admin, verifikasi urutan berubah di `/produk` & Home. Hapus satu produk (dengan konfirmasi), verifikasi hilang dari publik dan slug lama mengembalikan 404.

**Depends on**: Phase 2 (Foundational) dan T015 (Resource Produk dari US2).

### Tests for User Story 5

- [ ] T025 [P] [US5] Feature test: perubahan `order` di admin mengubah urutan tampil di `/produk` dan section "Produk Kami" Home, di `tests/Feature/Admin/ProductResourceTest.php`
- [ ] T026 [P] [US5] Feature test: hapus produk (via admin) membuat produk hilang dari `/produk`/Home dan `/produk/{slug-lama}` mengembalikan 404, di `tests/Feature/Admin/ProductResourceTest.php`

### Implementation for User Story 5

- [ ] T027 [US5] Tambahkan kolom `order` (numeric input, sortable di table) ke form & table `ProductResource`; pastikan table admin default terurut berdasarkan `order` (FR-013; depends on T015)
- [ ] T028 [US5] Pastikan `DeleteAction`/`DeleteBulkAction` bawaan Filament di `ProductResource` menampilkan dialog konfirmasi (default Filament sudah begitu — verifikasi & sesuaikan pesan konfirmasi bila perlu) (FR-009; depends on T015)

**Checkpoint**: User Story 5 selesai — seluruh scope AMC-207 (kategori, produk dasar, galeri, specs/fitur, urutan & hapus) lengkap.

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Verifikasi akhir lintas story sebelum dianggap selesai.

- [ ] T029 [P] Jalankan `vendor/bin/pint --dirty --format agent` untuk merapikan seluruh file PHP yang diubah
- [ ] T030 Jalankan test yang relevan: `php artisan test --compact --filter=CategoryResourceTest`, `--filter=ProductResourceTest`, `--filter=ProductPageTest`
- [ ] T031 Jalankan `php artisan test --compact` penuh untuk memastikan tidak ada regresi ke test lain (termasuk 001-epic2-cleanup dan 002-theme-branding-system)
- [ ] T032 `php artisan migrate:fresh --seed` di lokal untuk memastikan migration + seeder baru jalan bersih dari kosong; `npm run build` untuk memastikan asset tetap valid
- [ ] T033 Ikuti langkah verifikasi manual di [quickstart.md](./quickstart.md) untuk kelima user story

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: Tidak ada dependency — mulai langsung
- **Foundational (Phase 2)**: Memblokir SEMUA user story (skema `categories`/`products` dipakai di kelima story)
- **US1 (Phase 3)**: Butuh Phase 2 selesai; tidak bergantung fungsional pada US2–US5, tapi T012 sebaiknya selesai sebelum US2 menyentuh `ProductController` (hindari conflict file, bukan dependency fungsional)
- **US2 (Phase 4)**: Butuh Phase 2 selesai; MVP bersama US1
- **US3 (Phase 5)**: Butuh Phase 2 + T015 (form Resource dari US2) sudah ada
- **US4 (Phase 6)**: Butuh Phase 2 + T015
- **US5 (Phase 7)**: Butuh Phase 2 + T015
- **Polish (Phase 8)**: Setelah story yang ingin dirilis selesai

### Parallel Opportunities

- T008 & T009 (test US1) paralel — file sama tapi test case berbeda, tulis paralel eksekusi sequential
- T013 & T014 (test US2) sama seperti di atas
- T017 & T018 (test US3), T025 & T026 (test US5) — sama polanya
- T020 & T021 (Blade US3) paralel — file berbeda
- **Antar story**: Setelah T015 (form dasar `ProductResource` dari US2) ada, US3/US4/US5 bisa dikerjakan paralel oleh developer berbeda (masing-masing menambah section form yang berbeda ke resource yang sama — koordinasikan agar tidak saling menimpa edit di file yang sama)

---

## Parallel Example: Mengerjakan US3/US4/US5 sekaligus setelah US2 selesai

```bash
# Developer A — User Story 3 (Galeri)
Task: "T017–T021: FileUpload multi-gambar + render Blade card/show"

# Developer B — User Story 4 (Specs & Fitur)
Task: "T022–T024: Repeater specs + features"

# Developer C — User Story 5 (Urutan & Hapus)
Task: "T025–T028: kolom order + konfirmasi hapus"
```

---

## Implementation Strategy

### MVP First (User Story 1 + 2)

1. Phase 1: Setup
2. Phase 2: Foundational (skema categories + products)
3. Phase 3: User Story 1 (Kategori CRUD)
4. Phase 4: User Story 2 (Produk CRUD dasar)
5. **STOP & VALIDATE**: Test independen US1+US2 sesuai Independent Test di atas
6. Demo/rilis — admin sudah bisa kelola katalog produk dasar tanpa developer

### Incremental Delivery

1. Phase 1 → Phase 2 (Foundational) → Phase 3+4 (US1+US2) → validasi → rilis (MVP)
2. Phase 5 (US3, galeri) → validasi → rilis
3. Phase 6 (US4, specs/fitur) → validasi → rilis
4. Phase 7 (US5, urutan & hapus) → validasi → rilis
5. Phase 8: Polish setelah seluruh story rilis

### Catatan Constitution

- Setiap story WAJIB punya feature test sebelum ditandai selesai di tracker (Principle IV) — lihat T008/T009, T013/T014, T017/T018, T022, T025/T026.
- Tidak ada dependency baru ditambahkan (Principle V) — galeri gambar pakai `FileUpload` + kolom JSON yang sudah tersedia, bukan Spatie Media Library.
- Migration T004 WAJIB aman dijalankan di database yang sudah berisi data seed 002 (bukan hanya fresh install) — lihat research.md §3.

---

## Notes

- [P] = file berbeda / bagian independen, tidak saling bergantung
- [Story] memetakan task ke user story untuk traceability ke Linear (AMC-207 = seluruh fitur ini; US1–US5 adalah breakdown internal, bukan sub-tiket Linear terpisah)
- Commit setelah setiap task atau kelompok task logis
- Berhenti di checkpoint mana pun untuk validasi story secara independen
