# Contract: Admin Panel Surface (Banner Hero Slider)

**Feature**: [spec.md](../spec.md) | **Date**: 2026-09-14

Fitur ini tidak mengekspos API publik. Kontrak di sini adalah permukaan panel admin yang harus dipenuhi implementasi, terverifikasi lewat feature test. Kontrak modul banner sebelumnya ([012](../../012-banner-management-module/contracts/admin-panel-surface.md)) tetap berlaku kecuali yang dinyatakan berubah di bawah.

## 1. Struktur form

Form `BannerResource` MUST tersusun dalam section berurut berikut:

| # | Section | Field |
|---|---|---|
| 1 | Identitas & Gambar | `title`, `image_path`, `alt_text` |
| 2 | Konten Slide | `badge_text`, `heading`, `subheading` |
| 3 | Tombol CTA | `cta_primary_label`, `cta_primary_url`, `cta_secondary_label`, `cta_secondary_url` |
| 4 | Trust Bar | `trust_html` |
| 5 | Tampilan | `overlay_style`, `text_position` |
| 6 | Penjadwalan | `starts_at`, `ends_at`, `order`, `is_active` |

Seluruh label dan teks bantuan MUST berbahasa Indonesia, mengikuti modul lain.

## 2. Kontrak per field baru

| Field | Kontrak |
|---|---|
| `badge_text` | Opsional, maks 120 karakter. Helper menjelaskan bahwa ini pil kecil di atas judul. |
| `heading` | Opsional, maks 160 karakter. Helper menyebut bahwa ini judul yang dilihat pengunjung — berbeda dari Judul Internal. |
| `subheading` | Opsional, maks 400 karakter, area teks multi-baris. |
| `cta_primary_label` / `cta_primary_url` | Opsional berpasangan. Mengisi salah satu saja MUST ditolak dengan pesan pada field yang kosong (FR-003). URL menerima path internal berawalan `/` maupun URL absolut http/https (FR-004). |
| `cta_secondary_label` / `cta_secondary_url` | Aturan sama dengan pasangan utama. |
| `trust_html` | `RichEditor` opsional dengan toolbar terbatas: bold, italic, link, bullet list, gambar. Unggahan gambar MUST masuk disk `public` direktori `banners/trust` dan dikonversi WebP lewat `ImageUploads::storeAsWebp`. |
| `overlay_style` | `Select` wajib, default `dark`, opsi hanya dari `BannerOverlayStyle` dengan label Indonesia. Nilai bebas MUST ditolak. |
| `text_position` | `Select` wajib, default `left`, opsi hanya dari `BannerTextPosition`. Nilai bebas MUST ditolak. |

## 3. Kontrak field lama yang tetap

- `title`, `image_path`, `alt_text` tetap **wajib**.
- `link_url` tetap opsional dan tetap mewajibkan awalan `http://` atau `https://`.
- `ends_at` tetap tidak boleh lebih awal dari `starts_at`.
- Konversi gambar banner tetap WebP lebar maks 1600px.

## 4. Kontrak tabel

| Aspek | Kontrak |
|---|---|
| Kolom | Gambar (thumbnail), Judul Internal, **Judul Slide** (`heading`, placeholder "—" bila kosong), Status Tayang, Mulai, Selesai, Aktif |
| Urutan | Tabel MUST dapat diurutkan ulang dengan seret-dan-lepas yang menulis ke kolom `order` (FR-022). Kolom `order` numerik tidak lagi perlu diketik manual di form. |
| Urut bawaan | `order` menaik, pemecah seri `id` menaik |
| Status tayang | Tetap "Tayang" / "Terjadwal" / "Kedaluwarsa" / "Nonaktif" sesuai `displayStatus()` |
| Aksi | Edit, Hapus (dengan konfirmasi), Hapus massal — tidak berubah |

## 5. Kontrak efek samping

| Peristiwa | Kontrak |
|---|---|
| Banner disimpan (create/update) | Cache `public-page:home` MUST dibuang, sehingga beranda menampilkan perubahan pada permintaan berikutnya (FR-018) |
| Banner dihapus (tunggal atau massal) | Idem |
| Status aktif di-toggle dari tabel | Idem |
| Urutan diubah lewat seret-dan-lepas | Idem |

## 6. Kontrak akses

Tidak berubah: seluruh peran yang memiliki akses panel admin dapat mengelola banner. Fitur ini tidak menambah policy maupun permission baru.
