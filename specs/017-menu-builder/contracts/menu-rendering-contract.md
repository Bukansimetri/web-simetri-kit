# Contract: Menu Rendering (Blade Component)

**Feature**: 017-menu-builder

Aplikasi ini adalah monolith Laravel + Blade (bukan layanan API terpisah), sehingga "contract" di sini adalah antarmuka internal antara admin panel (penulis data) dan frontend publik (pembaca data): komponen Blade untuk render menu, dan kontrak yang harus dipenuhi model manapun yang ingin bisa dijadikan tujuan tautan internal.

## 1. Komponen Blade `<x-layout.menu>`

**Usage**:

```blade
<x-layout.menu location="navbar-utama" />
<x-layout.menu location="footer" as="footer-columns" />
```

**Props**:

| Prop | Type | Required | Deskripsi |
|---|---|---|---|
| `location` | string | ya | Slug `MenuLocation` yang akan dirender. Jika slug tidak ditemukan, komponen merender kosong (tidak error). |

**Output**: Daftar item menu aktif (`is_active = true`) pada lokasi tersebut, terurut sesuai `order_column`, termasuk sub-item (1 tingkat) bila ada, masing-masing dengan:
- `label`: string tampil.
- `href`: URL final (hasil resolusi internal/eksternal), atau `null` bila `link_type = none` atau tautan internal tidak lagi valid (lihat §3).
- `target`: `_blank` jika `open_in_new_tab = true`, selain itu `_self`.
- `children`: array item anak (kosong jika tidak ada).

**Guarantee**: Pemanggilan komponen ini TIDAK PERNAH melempar exception akibat data menu yang tidak lengkap/rusak (lokasi tidak ada, tautan internal terhapus, dsb.) — kegagalan resolusi selalu di-degrade menjadi `href = null`, bukan error halaman (memenuhi FR-011 & SC-005).

## 2. Kontrak model "internal linkable"

Model manapun yang ingin muncul sebagai opsi "halaman internal" di form `MenuItemResource` (mis. `CustomPage`, dan modul publik lain yang relevan) HARUS:

1. Memiliki method `getPublicUrl(): ?string` yang mengembalikan URL publik saat ini berdasarkan slug/rute terkini, atau `null` jika record tidak lagi punya representasi publik (mis. draft/unpublished).
2. Memiliki accessor tampilan (`getMenuLabelAttribute()` atau method setara) yang dipakai sebagai opsi pilihan di dropdown pemilih Filament, agar admin dapat membedakan antar record dengan jelas (mis. judul halaman).

**Resolver `MenuItem::resolveUrl()`** (dipakai oleh komponen di §1):

```text
if link_type == 'none': return null
if link_type == 'external': return external_url
if link_type == 'internal':
    if linkable relation is null (record terhapus / tidak ditemukan): return null
    return linkable->getPublicUrl()   // null-safe, dapat mengembalikan null jika konten belum/tidak publik
```

Daftar model yang mengimplementasikan kontrak ini didaftarkan secara eksplisit di konfigurasi `MenuItemResource` (mis. array `linkable_types`), bukan auto-discovery, agar admin per klien hanya melihat jenis konten yang relevan/aktif untuk instalasi tersebut (selaras Prinsip I — modul yang di-nonaktifkan seperti Career tidak boleh muncul sebagai opsi tautan jika modulnya dimatikan).

## 3. Penanganan tautan rusak di admin panel

Kolom "Tautan" pada tabel `MenuItemResource` MENAMPILKAN badge peringatan (mis. label "Tautan tidak valid") ketika `resolveUrl()` mengembalikan `null` untuk item dengan `link_type = internal`, agar admin dapat menemukan dan memperbaikinya — tanpa memblokir penyimpanan atau merender error di frontend.
