---

description: "Task list for Tampil/Sembunyi Section dari Admin"
---

# Tasks: Tampil/Sembunyi Section dari Admin

**Input**: Design documents from `/specs/032-section-visibility/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/section-catalog.md, contracts/admin.md, quickstart.md

**Tests**: Disertakan. Principle IV mewajibkan feature test untuk modul baru (halaman admin dan render publik).

**Organization**: Dikelompokkan per user story (US1–US3, sesuai spec.md).

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Dapat paralel (berkas berbeda, tanpa ketergantungan pada tugas yang belum selesai)
- **[Story]**: US1–US3
- Semua path relatif terhadap root repo. Jalankan `php artisan make:*` dengan `--no-interaction` untuk berkas baru.

---

## Phase 1: Setup

- [X] T001 Jalankan `php artisan test --compact` di branch `032-section-visibility` sebagai baseline (catat jumlah lulus/dilewati)

---

## Phase 2: Foundational (memblokir semua story)

**Purpose**: Katalog section, penyimpanan, dan helper baca yang dipakai situs dan admin.

- [X] T002 [P] Buat enum `app/Enums/PublicSection.php` (string-backed) berisi 25 case dengan kunci, halaman, dan label persis seperti tabel di `specs/032-section-visibility/contracts/section-catalog.md`. Sediakan:
  - `page(): string` (label halaman)
  - `label(): string`
  - `static pages(): array` (urutan halaman: Beranda, Tentang Kami, Karir, Produk, Detail Produk, Artikel, Detail Artikel, FAQ, Kontak)
  - `static forPage(string $page): array` (case per halaman, urut sesuai katalog)
  - `contentUrl(): ?string` (URL menu isi via `Resource::getUrl('index')`, untuk FAQ ditambah `?tableFilters[placement][value]=produk|kontak`)
  - `static fromCta(CtaPlacement $placement): self` (pemetaan di bagian bawah katalog)
  - `static fromPageSection(PageSection $section): self` (WhyChoose → `beranda.mengapa-beralih`, HowItWorks → `beranda.cara-kerja`, CareerValues → `karir.mengapa-bergabung`, RecruitmentProcess → `karir.proses-rekrutmen`, AboutMission → `tentang-kami.misi`, AboutValues → `tentang-kami.nilai`, AboutTrust → `tentang-kami.trust-strip`)
  - `static forFaqPlacement(FaqPlacement $placement): ?self` (Product → `produk.faq`, Contact → `kontak.faq`, Faq → null)
  - `static forPageBlock(PageBlockType $type): ?self` (AboutWhoWeAre → `tentang-kami.siapa-kami`, AboutVision → `tentang-kami.visi`, lainnya null)
- [X] T003 [P] Buat settings class `app/Settings/SectionVisibilitySettings.php` (grup `section_visibility`, `public array $hidden;`) dan settings migration `database/settings/2026_10_05_100000_create_section_visibility_settings.php` (`$this->migrator->add('section_visibility.hidden', [])`)
- [X] T004 Buat helper `app/Support/PageContent/SectionVisibility.php`:
  - `static shows(PublicSection $section): bool`
  - `static hiddenSections(): array<PublicSection>` (kunci tak dikenal diabaikan)
  - `static hide(array $sections)` / `static setHidden(array $sections): void` untuk menyimpan
  - Baca settings sekali per request (static memo, direset saat simpan dan via `flush()` untuk test). Tangkap `Spatie\LaravelSettings\Exceptions\MissingSettings` dan anggap semua tampil (research R3).
- [X] T005 [P] Buat `tests/Unit/PublicSectionTest.php` (atau Feature bila butuh app):
  - tepat 25 case
  - setiap `CtaPlacement` terpetakan ke case unik
  - setiap `PageSection` terpetakan
  - `forPage()` mencakup semua case tanpa duplikat
  - `contentUrl()` tidak null untuk semua case
- [X] T006 [P] Buat `tests/Feature/Settings/SectionVisibilityHelperTest.php`:
  - bawaan semua tampil
  - `setHidden` lalu `shows` false untuk yang disembunyikan dan true untuk lainnya
  - kunci tak dikenal di settings diabaikan
  - bila baris settings `section_visibility.hidden` dihapus dari tabel `settings`, `shows()` tetap true dan halaman `/` tetap 200

**Checkpoint**: Fondasi siap; situs belum berubah.

---

## Phase 3: User Story 1 - Menyembunyikan dan Menampilkan Section (Priority: P1) 🎯 MVP

**Goal**: Halaman Tampilan Section dengan 25 toggle; section tersembunyi tidak dirender.

**Independent Test**: Matikan Beranda → Testimoni; Beranda tanpa testimoni, Tentang Kami tetap; nyalakan lagi → isi identik.

### Tests for User Story 1

- [X] T007 [P] [US1] Buat `tests/Feature/Pages/SectionVisibilityRenderTest.php`:
  - Data provider 25 section, masing-masing `[PublicSection, path, penanda teks/XPath unik section]`. Gunakan `LegacyMarkup::seedDeterministicState()` plus testimoni, tim berfoto, logo klien berberkas, dan produk/artikel uji agar semua section punya isi.
  - Untuk setiap section: section tampil secara bawaan; setelah `SectionVisibility::setHidden([$section])` penanda hilang dari halaman sementara penanda section lain di halaman yang sama tetap ada; setelah dinyalakan lagi HTML fragmen identik dengan sebelum disembunyikan.
  - Uji khusus: menyembunyikan `beranda.testimoni` tidak memengaruhi `tentang-kami.testimoni`, dan sebaliknya.
  - Uji khusus: section tampil tetapi tanpa item aktif tetap tersembunyi.
- [X] T008 [P] [US1] Buat `tests/Feature/Settings/SectionVisibilitySettingsPageTest.php`:
  - halaman dapat dibuka super_admin
  - semua 25 toggle ada dan bawaan menyala, dikelompokkan per halaman
  - mematikan 2 toggle lalu simpan menyimpan tepat 2 kunci di `hidden` dan menampilkan notifikasi
  - menyalakan kembali mengosongkan `hidden`
  - menyimpan tidak mengubah settings grup lain (`site`)
- [X] T009 [US1] Jalankan `LegacyMarkupEquivalenceTest` setelah T011–T018 untuk memastikan markup identik saat semua tampil (SC-002)

### Implementation for User Story 1

- [X] T010 [US1] Buat `app/Filament/Pages/SectionVisibilitySettingsPage.php` mengikuti pola `app/Filament/Pages/SiteSettingsPage.php`:
  - grup `Pengaturan Situs`, sort 7, label "Tampilan Section", judul "Tampilan Section", view `filament.pages.settings-form-page`
  - `mount()` mengisi `visible.{kunci}` = `SectionVisibility::shows()` untuk tiap case
  - form: satu `Section` per halaman (urut `PublicSection::pages()`) berisi `Toggle::make('visible.'.$case->value)->label($case->label())` (catatan: titik di kunci enum harus di-escape atau diganti, mis. pakai `str_replace('.', '__', $case->value)` sebagai nama field)
  - `save()` menyimpan kunci yang toggle-nya mati via `SectionVisibility::setHidden()` lalu notifikasi "Tampilan section tersimpan"
- [X] T011 [P] [US1] Di `resources/views/pages/home.blade.php`, bungkus dengan `@if (\App\Support\PageContent\SectionVisibility::shows(\App\Enums\PublicSection::X)) ... @endif` (di luar markup section): `<x-sections.why-choose />` (BerandaMengapaBeralih), `<x-sections.how-it-works />` (BerandaCaraKerja), blok `{{-- Produk Kami --}}` (BerandaSolusi), blok `{{-- Testimoni --}}` (BerandaTestimoni), blok `{{-- CTA Penutup --}}` (BerandaCta)
- [X] T012 [P] [US1] Di `resources/views/pages/tentang-kami.blade.php`, bungkus Siapa Kami, Visi, Misi, Nilai, Trust Strip, `<x-sections.team-members>`, `<x-sections.testimonials>`, dan `<x-sections.client-logos>` dengan kondisi masing-masing. CTA ditangani T015
- [X] T013 [P] [US1] Di `resources/views/pages/karir.blade.php`, bungkus Values (Mengapa Bergabung) dan Recruitment Process
- [X] T014 [P] [US1] Di `resources/views/pages/produk/index.blade.php`, bungkus CTA Kalkulator, `<x-sections.faq-list>` (ProdukFaq), dan CTA Penutup. Di `resources/views/pages/produk/show.blade.php` bungkus CTA detail. Di `resources/views/pages/artikel/index.blade.php` bungkus `{{-- CTA --}}`. Di `resources/views/pages/kontak.blade.php` bungkus `<x-sections.faq-list>` (KontakFaq)
- [X] T015 [US1] Di `resources/views/components/sections/cta-band.blade.php`, bungkus seluruh output dengan `@if (\App\Support\PageContent\SectionVisibility::shows(\App\Enums\PublicSection::fromCta($placement)))` (dipakai Tentang Kami, Karir, FAQ, Detail Artikel)
- [X] T016 [US1] Pastikan tidak ada pembungkus yang menambah whitespace/markup saat tampil, lalu jalankan T009 dan perbaiki bila fixture berbeda
- [X] T017 [US1] Perbarui `tests/Feature/Settings/SettingsPagesAccessTest.php` agar mencakup halaman baru (super_admin dapat membuka) bila test itu menghitung halaman pengaturan, dan `tests/Feature/Admin/NavigationStructureTest.php` bila menyebut menu Pengaturan Situs
- [X] T018 [US1] Tambahkan halaman ke `docs/manual-operator.md`: label **Tampilan Section** di peta menu (grup Pengaturan Situs) dan bagian "### Tampilan Section" yang menjelaskan toggle, bawaan tampil, isi tidak terhapus, dan section kosong tetap tersembunyi. Jalankan `OperatorManualTest`

**Checkpoint**: Admin bisa menyembunyikan/menampilkan 25 section.

---

## Phase 4: User Story 2 - Penanda di Menu Isi Section (Priority: P2)

**Goal**: Menu isi menampilkan peringatan dan badge bila section terkait tersembunyi.

**Independent Test**: Sembunyikan Cara Kerja → menu Cara Kerja menampilkan penanda; tampilkan lagi → penanda hilang.

### Tests for User Story 2

- [X] T019 [P] [US2] Buat `tests/Feature/Admin/HiddenSectionNoticeTest.php`:
  - untuk masing-masing List page (WhyChoose, HowItWorks, CareerValues, RecruitmentSteps, AboutMission, AboutValue, AboutTrust, Testimonial, TeamMember, ClientLogo, Product, CallToAction, PageBlock, FaqItem), penanda tidak tampil saat semua tampil
  - setelah section terkait disembunyikan, penanda tampil menyebut "Halaman – Section" dan menautkan ke `SectionVisibilitySettingsPage::getUrl()`
  - Testimoni menyebut keduanya bila keduanya tersembunyi dan hanya satu bila satu
  - kolom **Tayang** pada CTA, Blok Halaman, FAQ menampilkan "Disembunyikan" hanya untuk baris terkait

### Implementation for User Story 2

- [X] T020 [US2] Buat trait `app/Filament/Concerns/ShowsHiddenSectionNotice.php`:
  - abstract/static `relatedPublicSections(): array<PublicSection>`
  - `getSubheading(): string|Htmlable|null` mengembalikan `HtmlString` peringatan (ikon/teks warna warning) "Section ini sedang disembunyikan dari situs: {Halaman – Label}[, …]. <a href=…>Atur di Tampilan Section</a>"
  - null bila tidak ada yang tersembunyi
  - teks di-escape
- [X] T021 [US2] Pakai trait di `app/Filament/Support/Pages/ListSectionItems.php` dengan `relatedPublicSections()` = `[PublicSection::fromPageSection(static::getResource()::section())]` (mencakup 7 menu section item)
- [X] T022 [P] [US2] Pakai trait di halaman List:
  - `app/Filament/Resources/TestimonialResource/Pages/ListTestimonials.php` (BerandaTestimoni, TentangKamiTestimoni)
  - `TeamMemberResource/Pages/ListTeamMembers.php` (TentangKamiTim)
  - `ClientLogoResource/Pages/ListClientLogos.php` (TentangKamiLogoKlien)
  - `ProductResource/Pages/ListProducts.php` (BerandaSolusi)
  - `CallToActionResource/Pages/ListCallToActions.php` (9 CTA)
  - `PageBlockResource/Pages/ListPageBlocks.php` (Siapa Kami, Visi)
  - `FaqItemResource/Pages/ListFaqItems.php` (ProdukFaq, KontakFaq)
- [X] T023 [P] [US2] Tambahkan kolom `TextColumn::make('visibility')->label('Tayang')` ber-`state` dan `badge` di:
  - `app/Filament/Resources/CallToActionResource.php` (via `PublicSection::fromCta`)
  - `app/Filament/Resources/PageBlockResource.php` (via `forPageBlock`, "—" bila null)
  - `app/Filament/Resources/FaqItemResource.php` (via `forFaqPlacement`, "—" bila null)

  Nilai "Disembunyikan" berwarna `warning`, selain itu "—" abu-abu
- [X] T024 [US2] Tambahkan keterangan penanda dan kolom Tayang ke bagian Tampilan Section di `docs/manual-operator.md`

**Checkpoint**: Operator langsung tahu bila isi yang diedit sedang tidak tayang.

---

## Phase 5: User Story 3 - Status Sekilas di Halaman Tampilan Section (Priority: P3)

**Goal**: Jumlah tersembunyi per halaman dan tautan "Edit isi".

**Independent Test**: Sembunyikan 2 section Tentang Kami → judul kelompok "Tentang Kami · 2 disembunyikan"; tiap baris punya tautan Edit isi.

### Tests for User Story 3

- [X] T025 [P] [US3] Tambah test di `tests/Feature/Settings/SectionVisibilitySettingsPageTest.php`:
  - judul kelompok memuat "· N disembunyikan" sesuai data tersimpan
  - tidak ada akhiran bila 0
  - setiap toggle menampilkan tautan "Edit isi" ke `PublicSection::contentUrl()`
  - tautan FAQ memuat filter tempat yang benar

### Implementation for User Story 3

- [X] T026 [US3] Di `app/Filament/Pages/SectionVisibilitySettingsPage.php`:
  - judul `Section` per halaman memakai closure yang menghitung jumlah tersembunyi dari data tersimpan
  - tambahkan `hintAction(Action::make(...)->label('Edit isi')->url($case->contentUrl())->openUrlInNewTab(false))` atau `hint(new HtmlString('<a …>Edit isi</a>'))` pada tiap toggle

**Checkpoint**: Semua story selesai.

---

## Phase 6: Polish & Cross-Cutting

- [X] T027 Jalankan `vendor/bin/pint --dirty --format agent`
- [X] T028 Jalankan `php artisan test --compact`; semua lulus (SC-006)
- [X] T029 Verifikasi visual sesuai `specs/032-section-visibility/quickstart.md` dengan database sementara: halaman Tampilan Section di admin, beberapa section disembunyikan di situs, penanda di menu isi
- [X] T030 Catat di `docs/deployment.md` (bila ada bagian urutan deploy) bahwa migrasi settings baru aman dijalankan setelah kode, karena helper menganggap semua section tampil sampai migrasi selesai

---

## Dependencies & Execution Order

- **Phase 1 → Phase 2** (T002–T006) memblokir semua story.
- **US1** setelah Phase 2. **US2** setelah Phase 2 dan T010 (tautan ke halaman Tampilan Section). **US3** setelah T010.
- Berkas yang disentuh lebih dari satu tugas (kerjakan berurutan):
  - `SectionVisibilitySettingsPage.php`: T010 → T026
  - `docs/manual-operator.md`: T018 → T024
  - `SectionVisibilitySettingsPageTest.php`: T008 → T025
- **Paralel**:
  - T002/T003 dan T005/T006
  - T007/T008
  - T011–T014 (view berbeda)
  - T022/T023 (berkas berbeda)

### Contoh Paralel: User Story 1

```text
T011 home.blade.php   T012 tentang-kami.blade.php   T013 karir.blade.php   T014 produk/artikel/kontak
```

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 (T001–T018). Admin sudah bisa menyembunyikan section.
2. Tambah US2 (penanda), lalu US3 (status sekilas).
3. Polish: Pint, uji penuh, verifikasi visual, catatan deploy.
4. Commit per story.
