# Phase 1 Data Model: Banner Hero Slider

**Feature**: 022-banner-hero-slider | **Date**: 2026-09-14

## Entitas: Banner (Hero Slide)

Tabel `banners`. Kolom yang sudah ada dipertahankan seluruhnya; sepuluh kolom baru ditambahkan, semuanya `nullable` atau ber-default agar baris lama tetap valid.

### Kolom yang sudah ada (tidak berubah)

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint PK | |
| `title` | string, wajib | Judul internal, tidak pernah tampil ke pengunjung |
| `image_path` | string, wajib | Path relatif pada disk `public`, hasil WebP |
| `alt_text` | string, wajib | Teks alternatif gambar |
| `link_url` | string, nullable | Tautan seluruh area slide — hanya berlaku bila slide tidak punya CTA (R8) |
| `starts_at` | date, nullable | Kosong = tanpa batas awal |
| `ends_at` | date, nullable | Kosong = tanpa batas akhir |
| `order` | integer, default 0 | Menaik; pemecah seri `id` menaik |
| `is_active` | boolean, default true | |
| `created_at` / `updated_at` | timestamp | |

### Kolom baru

| Kolom | Tipe | Default | Aturan validasi |
|---|---|---|---|
| `badge_text` | string(120), nullable | null | max 120 |
| `heading` | string(160), nullable | null | max 160 |
| `subheading` | text, nullable | null | max 400 |
| `cta_primary_label` | string(60), nullable | null | max 60; wajib bila `cta_primary_url` terisi |
| `cta_primary_url` | string(255), nullable | null | max 255; wajib bila `cta_primary_label` terisi; tautan internal atau eksternal |
| `cta_secondary_label` | string(60), nullable | null | max 60; wajib bila `cta_secondary_url` terisi |
| `cta_secondary_url` | string(255), nullable | null | max 255; wajib bila `cta_secondary_label` terisi |
| `trust_html` | text, nullable | null | Disanitasi terhadap allowlist sebelum dirender (R4) |
| `overlay_style` | string(16), not null | `'dark'` | Salah satu dari `dark`, `light`, `none` |
| `text_position` | string(16), not null | `'left'` | Salah satu dari `left`, `center`, `right` |

**Catatan indeks**: tidak ada indeks baru. Kueri `scopeLive()` tidak berubah dan jumlah baris kecil.

### Cast pada model

```
starts_at      => 'date'
ends_at        => 'date'
order          => 'integer'
is_active      => 'boolean'
overlay_style  => BannerOverlayStyle::class
text_position  => BannerTextPosition::class
```

Seluruh kolom baru ditambahkan ke `$fillable`.

### Aturan validasi turunan (kontrak form)

- **CTA berpasangan** (FR-003): untuk masing-masing pasangan utama dan sekunder, label dan alamat harus terisi keduanya atau kosong keduanya. Melanggar → pesan error pada field yang kosong.
- **Alamat CTA** (FR-004): menerima URL absolut (`http://`, `https://`) maupun path relatif yang diawali `/`. Berbeda dari `link_url` lama yang mewajibkan awalan `http://`/`https://` — aturan `link_url` tidak diubah agar perilaku lama tetap sama.
- **`overlay_style` dan `text_position`**: dibatasi oleh enum; dropdown tidak menerima nilai bebas.
- Aturan validasi kolom lama tidak berubah.

### Perilaku turunan pada model

- `scopeLive()` — **tidak berubah**.
- `displayStatus()` — **tidak berubah**.
- `hasContent(): bool` — benar bila salah satu dari badge, heading, subheading, CTA, atau trust bar terisi. Dipakai view untuk memutuskan apakah blok konten dirender sama sekali (FR-002).
- `hasCta(): bool` — benar bila minimal satu pasangan CTA lengkap. Dipakai untuk aturan tautan majemuk (R8).
- `sanitizedTrustHtml(): ?string` — `trust_html` yang telah dilewatkan `HtmlSanitizer` (FR-007).
- `booted()` — listener `saved` dan `deleted` memanggil `Cache::forget('public-page:home')` (FR-018, R5).

### Transisi status

Tidak ada mesin status baru. Status tayang tetap turunan dari `is_active` dan periode tayang, seperti didefinisikan spesifikasi 012.

---

## Entitas: BannerOverlayStyle (enum)

Backed string enum. Setiap case memetakan ke satu set kelas Tailwind **literal** (R3).

| Case | Nilai | Label admin | Lapisan | Warna teks |
|---|---|---|---|---|
| `Dark` | `dark` | Lapisan gelap (untuk gambar terang) | gradasi gelap dari sisi teks | putih |
| `Light` | `light` | Lapisan terang (untuk gambar gelap) | gradasi putih dari sisi teks | gelap sesuai brand |
| `None` | `none` | Tanpa lapisan | tidak ada | putih + bayangan teks |

Metode: `label(): string`, `overlayClasses(BannerTextPosition $position): string`, `headingClasses(): string`, `bodyClasses(): string`.

## Entitas: BannerTextPosition (enum)

| Case | Nilai | Label admin | Perataan | Arah gradasi |
|---|---|---|---|---|
| `Left` | `left` | Kiri | rata kiri | kiri → kanan |
| `Center` | `center` | Tengah | rata tengah | dari kedua sisi |
| `Right` | `right` | Kanan | rata kanan | kanan → kiri |

Metode: `label(): string`, `containerClasses(): string`.

---

## Entitas referensi: BrandSettings

Hanya **dibaca** oleh fitur ini, tidak diubah. Menyediakan warna primer/sekunder dan tipografi yang menjadi sumber warna tombol dan aksen seluruh slide (FR-006), lewat variabel CSS yang sudah dipasang `layouts/partials/theme-vars`.

---

## Migrasi data

Migration `add_hero_fields_to_banners_table`:

1. `up()` menambahkan sepuluh kolom di atas.
2. Kemudian mengisi satu baris — banner dengan `order` terkecil, pemecah seri `id` terkecil — dengan konten hero yang berlaku sekarang, **hanya bila `heading` masih kosong**:
   - `badge_text`: "Solar Panel Terpercaya • Efisiensi Hingga 80%"
   - `heading`: "Nyalakan Rumah & Bisnis Anda dengan Energi Matahari"
   - `subheading`: "Solusi tata surya terdepan untuk efisiensi maksimal dan investasi jangka panjang tanpa mengorbankan estetika hunian Anda."
   - `cta_primary_label` / `cta_primary_url`: "Konsultasi Gratis" / `/kontak`
   - `cta_secondary_label` / `cta_secondary_url`: "Pelajari Cara Kerja" / `/#kalkulator`
   - `trust_html`: penanda tepercaya "500+ Pelanggan Puas" beserta tiga lencana bulat, sebagai HTML literal
   - `overlay_style`: `dark`, `text_position`: `left`
3. Bila tabel kosong, tidak ada yang diisi.
4. `down()` membuang kesepuluh kolom. Konten teks hilang saat rollback — konsekuensi yang diterima (R10).

Teks disalin sebagai literal di dalam migration, tidak dibaca dari berkas Blade, agar hasilnya deterministik.
