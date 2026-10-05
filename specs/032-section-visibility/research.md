# Research: Tampil/Sembunyi Section dari Admin

Tidak ada `NEEDS CLARIFICATION`. Berikut keputusan teknisnya.

## R1. Penyimpanan status tampil

- **Decision**: Spatie Settings grup `section_visibility` dengan `public array $hidden = []` berisi kunci `PublicSection` yang disembunyikan.
- **Rationale**:
  - Daftar "tersembunyi" (bukan "tampil") membuat section baru otomatis tampil, dan instalasi lama tanpa nilai berarti semua tampil (FR-004).
  - Pola settings sudah dipakai halaman pengaturan lain.
- **Alternatives**:
  - 25 properti boolean: migrasi setiap kali section bertambah, ditolak.
  - Kolom `is_visible` di `section_headings`/`call_to_actions`/`page_blocks`: tersebar, dan Solusi/Testimoni/Tim/Logo/FAQ tidak punya baris induk, ditolak.

## R2. Katalog section

- **Decision**: Enum `App\Enums\PublicSection` string-backed, kunci berformat `{halaman}.{section}` (mis. `beranda.testimoni`, `tentang-kami.testimoni`, `produk.cta-kalkulator`). Metode:
  - `page(): string` → label halaman ("Beranda", "Tentang Kami", ...)
  - `label(): string`
  - `contentUrl(): ?string` → URL menu isi, dengan filter bila perlu, mis. FAQ `?tableFilters[placement][value]=produk`
  - `static forPage(): array` → dikelompokkan per halaman, urut sesuai tampilan situs
  - Relasi ke enum lama: `fromCta(CtaPlacement)`, `fromPageSection(PageSection)`, `forFaqPlacement(FaqPlacement)`, `forPageBlock(PageBlockType)` untuk penanda admin.
- **Rationale**: Daftar tetap di kode (Principle III), satu sumber kebenaran untuk Blade dan admin.

## R3. Pembacaan di Blade, aman saat deploy

- **Decision**: `App\Support\PageContent\SectionVisibility::shows(PublicSection $section): bool` membaca `SectionVisibilitySettings::hidden` sekali per request (static memo). Saat `Spatie\LaravelSettings\Exceptions\MissingSettings` dilempar (migrasi belum jalan), semua section dianggap tampil.
- **Rationale**: Insiden spec 031 (`footer_description` missing → seluruh situs 500) tidak boleh terulang untuk fitur yang bawaannya "tidak mengubah apa pun".
- **Penerapan di view**: section dibungkus `@if (\App\Support\PageContent\SectionVisibility::shows(PublicSection::X)) ... @endif`, di luar markup section sehingga markup saat tampil identik (fixture tetap lulus).
  - Untuk komponen yang dipakai di banyak halaman (`testimonials`, `team-members`, `client-logos`, `cta-band`, `faq-list`), pembungkus diletakkan di pemanggil (view halaman), bukan di komponen.
  - `cta-band` menerima `placement`, sehingga kondisi bisa memakai `PublicSection::fromCta($placement)` di dalam komponen. Ini aman karena satu placement = satu halaman.
  - CTA inline (Beranda, Produk ×2, Artikel) dibungkus langsung di view.

## R4. Kesegaran tampilan

- **Decision**: Tidak perlu invalidasi cache tambahan. Data halaman yang di-cache (`rememberPublicPage`) hanya berisi data model; kondisi tampil dievaluasi saat Blade dirender pada setiap request, jadi perubahan toggle langsung terlihat (FR-010). Spatie settings cache (bila diaktifkan) diperbarui otomatis saat `save()`.

## R5. Halaman admin Tampilan Section

- **Decision**:
  - Filament Page `SectionVisibilitySettingsPage`: grup **Pengaturan Situs**, sort 7, label "Tampilan Section", view `filament.pages.settings-form-page`.
  - Form berisi satu `Section` per halaman situs (judul = nama halaman + "· N disembunyikan" bila N > 0).
  - Isinya `Toggle::make("visible.{key}")` berlabel nama section, dengan `hintAction` / `hint` berupa tautan "Edit isi" bila `contentUrl()` ada.
  - Simpan: `hidden` = kunci yang toggle-nya mati, lalu tampilkan notifikasi "Tampilan section tersimpan".
- **Akses**: mengikuti halaman pengaturan lain (tanpa `canAccess` khusus, sama seperti `SiteSettingsPage`).
- **Alternatives**: Satu `CheckboxList` (kurang jelas per halaman, ditolak).

## R6. Penanda di menu isi

- **Decision**: Trait `App\Filament\Concerns\ShowsHiddenSectionNotice` untuk halaman List.
  - Kontrak trait: halaman mendefinisikan `protected static function relatedPublicSections(): array`.
  - Trait meng-override `getSubheading()` → `HtmlString` berisi peringatan berwarna (kelas Filament `text-warning-600`) "Section ini sedang disembunyikan dari situs: Beranda – Testimoni, …" beserta tautan "Atur di Tampilan Section".
  - Null bila tidak ada yang tersembunyi (FR-014).

Pemetaan menu → section:

| Menu (List page) | Section terkait |
|---|---|
| Mengapa Beralih, Cara Kerja, Mengapa Bergabung, Proses Rekrutmen, Misi, Nilai, Trust Strip (lewat `ListSectionItems` + resource lama Misi/Nilai/Trust) | `PublicSection::fromPageSection($section)` |
| Testimoni | Beranda – Testimoni, Tentang Kami – Testimoni |
| Tim | Tentang Kami – Tim |
| Logo Klien | Tentang Kami – Logo Klien |
| Produk | Beranda – Solusi Untuk Setiap Kebutuhan |
| CTA | 9 CTA |
| Blok Halaman | Tentang Kami – Siapa Kami, Tentang Kami – Visi |
| FAQ | Produk – FAQ, Kontak – FAQ Konsultasi |

- **Badge per baris** (FR-013): pada CTA, Blok Halaman, dan FAQ, ditambahkan kolom `TextColumn` "Tayang" berstatus badge "Disembunyikan" (warna warning) bila section baris itu tersembunyi, dan "—" bila tidak. Untuk FAQ, badge mengikuti tempat tampil (Halaman FAQ tidak punya toggle, jadi selalu "—").

## R7. Dampak tes

- Fixture `LegacyMarkup` tidak berubah karena pembungkus `@if` tidak menghasilkan markup saat tampil.
- Test baru:
  - `SectionVisibilityRenderTest`: data provider 25 section × (sembunyikan → section hilang, section lain tetap, tampilkan → isi identik).
  - `SectionVisibilitySettingsPageTest`: render, toggle bawaan menyala, simpan, jumlah per kelompok, tautan Edit isi.
  - `HiddenSectionNoticeTest`: penanda dan badge.
  - `SectionVisibilityFallbackTest`: settings hilang → semua tampil.
