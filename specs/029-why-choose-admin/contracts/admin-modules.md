# Contract: Modul Panel Admin

**Feature**: `029-why-choose-admin`

Semua modul berada di grup navigasi **`Konten Halaman`**. Tidak ada policy baru, sama seperti `TestimonialResource`.

## 1. Resource section (4 buah)

Kelas dasar abstrak di luar folder discovery: `app/Filament/Support/SectionItemResource.php` (extends `Filament\Resources\Resource`), dengan `abstract public static function section(): PageSection`.

| Resource (app/Filament/Resources/…) | `section()` | Navigation label | Model label |
|---|---|---|---|
| `WhyChooseItemResource` | `WhyChoose` | Beranda – Mengapa Beralih | Kartu Alasan |
| `HowItWorksStepResource` | `HowItWorks` | Beranda – Cara Kerja | Langkah |
| `CareerValueResource` | `CareerValues` | Karir – Mengapa Bergabung | Kartu Nilai |
| `RecruitmentStepResource` | `RecruitmentProcess` | Karir – Proses Rekrutmen | Langkah |

Semuanya memakai `$model = SectionItem::class` dan `getEloquentQuery()` → `->where('section', static::section())`. Halaman: `List…`, `Create…`, `Edit…`. `Create` mengisi `section` lewat `mutateFormDataBeforeCreate`.

### Form

| Field | Komponen | Tampil jika | Validasi |
|---|---|---|---|
| `icon` | `Select` searchable, `allowHtml`, opsi `IconOptions::options()` (pratinjau `<span class="material-symbols-outlined">`) | `hasIcon()` | required, `in:IconOptions::keys()` |
| `title` | `TextInput` | selalu | required, max 60 |
| `description` | `Textarea` rows 3 | selalu | required, max 200 |
| `is_emphasized` | `Toggle` "Tonjolkan" + helper "Hanya satu item yang bisa ditonjolkan; item lain otomatis dilepas." | `supportsEmphasis()` | boolean |
| `is_active` | `Toggle` "Aktif" + helper "Maksimal {n} item aktif." | selalu | `WithinActiveItemLimit(section, recordId)` |

`order` tidak ada di form. Diatur lewat seret di tabel, dan item baru mendapat `max(order)+1`.

### Tabel

- `->reorderable('order')`, `->defaultSort('order')`, `->paginated(false)`.
- Kolom: `icon` (render ikon, hanya jika `hasIcon()`), `title`, `is_emphasized` (IconColumn, hanya jika `supportsEmphasis()`), `ToggleColumn::make('is_active')->rules([new WithinActiveItemLimit(...)])`.
- Aksi baris: Edit, Delete (konfirmasi). Bulk: Delete.
- Deskripsi tabel: "Maksimal {n} item aktif tampil di halaman {halaman}. Urutan di sini = urutan tampil."

### Header action "Ubah Judul Section" (halaman List)

Modal form yang mengisi/menyimpan `SectionHeading::firstOrNew(['section' => …])`:

| Field | Tampil jika | Validasi |
|---|---|---|
| `title` (`Textarea` rows 2, helper "Tekan Enter untuk pindah baris seperti desain.") | selalu | required, max 80 |
| `subtitle` (`Textarea` rows 2) | `hasSubtitle()` | nullable, max 250 |

Notifikasi sukses: "Judul section disimpan."

### Pesan validasi

- Batas aktif: `Section "{label}" maksimal {n} item aktif. Nonaktifkan item lain terlebih dahulu.`
- Ikon tidak valid: `Pilih ikon dari daftar yang tersedia.`

## 2. `CallToActionResource`

- Navigation label **CTA**, model label **CTA**.
- Halaman: `List`, `Edit` saja. `canCreate()` false, `canDelete()` false, tanpa bulk action.
- Tabel: `placement` (label enum), `title` (dipotong), tanpa reorder, `->paginated(false)`, urut berdasarkan urutan case enum.

### Form Edit

| Field | Label | Tampil jika | Validasi |
|---|---|---|---|
| `placement` | Penempatan | selalu (disabled, tidak ikut disimpan) | – |
| `title` | Judul (`Textarea` rows 2) | selalu | required, max 80 |
| `body` | `placement->bodyLabel()` (`Textarea` rows 3) | selalu | nullable, max 300 |
| `primary_label` | Label Tombol / Label Tombol WhatsApp (Home) | selalu | required, max 40 |
| `secondary_label` | Label Tombol Form | `hasSecondaryButton()` | required jika tampil, max 40 |

Helper text:

- `ProductDetail.body`: "Tulis {produk} untuk menyisipkan nama produk yang sedang dibuka."
- Semua: "Tujuan tombol mengikuti pengaturan situs dan tidak bisa diubah di sini."

Teks yang diketik admin tampil apa adanya; tidak ada token nama situs.

## Addendum 2026-10-02

### Resource section Tentang Kami

| Resource | `section()` | Navigation label |
|---|---|---|
| `AboutMissionResource` | `AboutMission` | Tentang Kami – Misi |
| `AboutValueResource` | `AboutValues` | Tentang Kami – Nilai |
| `AboutTrustResource` | `AboutTrust` | Tentang Kami – Trust Strip |

- Label & batas kolom item diambil dari enum (`itemTitleLabel()`, `itemTitleMaxLength()`, dst.).
- "Ubah Judul Section": tidak tampil bila `!hasHeading()` (Trust Strip). Menampilkan `eyebrow` bila `hasEyebrow()` (Misi), dan bagian **Kartu Besar** (gambar, ikon, judul, deskripsi; ikon/judul/deskripsi wajib) bila `hasFeaturedCard()` (Nilai).
- Ikon memakai `MaterialSymbolsIcons::selectOptions()`.

### `PageBlockResource`

- Label navigasi **Blok Halaman**, grup Konten Halaman. List + Edit saja; `canCreate`/`canDelete` false.
- Form dirakit dari `PageBlockType` (field berprefix `data.`): FileUpload gambar (WebP, folder `about-page`), TextInput/Textarea, RichEditor (`bold`, `italic`) untuk isi & kutipan Siapa Kami.
- Halaman pengaturan lama **Halaman Tentang Kami** dihapus dari panel.
