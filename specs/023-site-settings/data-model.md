# Data Model: Site Settings

**Feature**: 023-site-settings | **Date**: 2026-09-23 | **Plan**: [plan.md](./plan.md)

Seluruh pengaturan disimpan lewat `spatie/laravel-settings`. Satu kelas settings memetakan satu-satu ke satu halaman admin (FR-070), sehingga tiap halaman dapat disimpan tanpa menimpa nilai halaman lain.

Tidak ada tabel Eloquent baru. Persetujuan cookie pengunjung **tidak** disimpan di sisi server (FR-058).

---

## 1. `SiteSettings` — grup `site`

Halaman admin: **Pengaturan Umum**. Sumber pindahan: `brand.app_name`, `brand.whatsapp_number`, `brand.contact_notification_email`, `brand.career_module_enabled`.

| Properti | Tipe | Bawaan | Requirement | Titik penerapan |
| --- | --- | --- | --- | --- |
| `site_name` | `?string` | `null` → `config('app.name')` | FR-001 | Judul dokumen, `og:site_name`, logo teks header, footer |
| `tagline` | `?string` | `null` | FR-001 | Halaman kesalahan, halaman pemeliharaan |
| `site_description` | `?string` | `null` | FR-001 | Deskripsi footer |
| `company_name` | `?string` | `null` → `site_name` | FR-002 | Blok kontak footer, JSON-LD Organization |
| `company_email` | `?string` | `null` | FR-002, FR-004 | Baris email footer — baris disembunyikan bila kosong |
| `company_phone` | `?string` | `null` | FR-002, FR-004 | Baris telepon footer — disembunyikan bila kosong |
| `company_address` | `?string` | `null` | FR-002, FR-004 | Baris alamat footer — disembunyikan bila kosong |
| `default_language` | `string` | `'id'` | FR-005 | Atribut bahasa dokumen, menggantikan `lang="id"` yang kini ditulis mati |
| `timezone` | `string` | `'Asia/Jakarta'` | FR-005 | Acuan tampilan tanggal halaman publik |
| `copyright_text` | `?string` | `null` → nama situs + tahun berjalan | FR-006, FR-007 | Baris hak cipta footer |
| `terms_url` | `?string` | `null` | FR-006, FR-007 | Tautan legal footer — disembunyikan bila kosong |
| `privacy_url` | `?string` | `null` | FR-006, FR-007 | Tautan legal footer — disembunyikan bila kosong |
| `cookie_policy_url` | `?string` | `null` | FR-006, FR-007 | Tautan legal footer — disembunyikan bila kosong |
| `error_404_message` | `?string` | `null` → pesan bawaan | FR-015, FR-016 | `errors/404` |
| `error_500_message` | `?string` | `null` → pesan bawaan | FR-015, FR-016 | `errors/500` |
| `maintenance_mode` | `bool` | `false` | FR-010 sampai FR-014 | Middleware rute publik |
| `whatsapp_number` | `?string` | `null` | FR-066, FR-072 | Redirect `wa.me` setelah submit form kontak |
| `contact_notification_email` | `?string` | `null` | FR-066, FR-072 | Penerima notifikasi submission kontak |
| `career_module_enabled` | `bool` | `true` | FR-072 | Akses `/karir`, entri navigasi, isi peta situs |

**Aturan**: `company_email` wajib berformat email bila diisi. `default_language` dan `timezone` wajib salah satu dari daftar yang disediakan. `whatsapp_number` hanya angka. Metode `whatsappUrl(string $message): ?string` pindah utuh dari `BrandSettings`.

**Catatan**: `whatsapp_number` dan `contact_notification_email` adalah setelan operasional form kontak, **bukan** kontak publik yang tampil di footer. Keduanya sengaja tidak digabung dengan `company_phone` dan `company_email` (FR-066).

---

## 2. `AppearanceSettings` — grup `appearance`

Halaman admin: **Tampilan**. Sumber pindahan: seluruh field tema dari `brand.*`.

| Properti | Tipe | Bawaan | Requirement | Titik penerapan |
| --- | --- | --- | --- | --- |
| `logo_path` | `?string` | `null` | FR-071 | Logo header, logo footer, branding panel admin |
| `favicon_path` | `?string` | `null` | FR-071 | Ikon tab peramban |
| `primary_color` | `?string` | `#006397` | FR-071 | CSS variable halaman publik |
| `secondary_color` | `?string` | `#3a5f94` | FR-071 | CSS variable halaman publik |
| `font_heading` | `?string` | `Manrope` | FR-071 | CSS variable halaman publik |
| `font_body` | `?string` | `Be Vietnam Pro` | FR-071 | CSS variable halaman publik |

**Aturan**: `font_heading` dan `font_body` wajib salah satu dari daftar font kurasi. Konstanta `DEFAULT_PRIMARY_COLOR`, `DEFAULT_SECONDARY_COLOR`, `DEFAULT_FONT_HEADING`, `DEFAULT_FONT_BODY`, dan `FONT_OPTIONS` pindah utuh dari `BrandSettings`.

---

## 3. `SeoSettings` — grup `seo`

Halaman admin: **SEO**. Sumber pindahan: `brand.meta_description`.

| Properti | Tipe | Bawaan | Requirement | Titik penerapan |
| --- | --- | --- | --- | --- |
| `title_separator` | `string` | `'\|'` | FR-025 | Penyusun judul |
| `default_title_format` | `string` | `'{page_title} {separator} {site_name}'` | FR-025, FR-027 | Judul dokumen seluruh halaman |
| `page_title_formats` | `array` | `[]` | FR-026 | Judul per jenis halaman |
| `default_meta_description` | `?string` | `null` | FR-029 | `meta description`, `og:description`, `twitter:description` |
| `meta_keywords` | `array` | `[]` | FR-029 | `meta keywords` |
| `default_canonical_url` | `?string` | `null` → URL berjalan | FR-029 | `link rel=canonical` |
| `allow_indexing` | `bool` | `true` | FR-030, FR-040 | `meta robots`, keselarasan `robots.txt` |
| `allow_following` | `bool` | `true` | FR-030 | `meta robots` |
| `twitter_handle` | `?string` | `null` | FR-031 | `twitter:site` |
| `additional_head_meta` | `?string` | `null` | FR-033 | Kepala dokumen |
| `verification_google` | `?string` | `null` | FR-034 | `meta google-site-verification` |
| `verification_bing` | `?string` | `null` | FR-034 | `meta msvalidate.01` |
| `verification_yandex` | `?string` | `null` | FR-034 | `meta yandex-verification` |
| `verification_baidu` | `?string` | `null` | FR-034 | `meta baidu-site-verification` |
| `robots_txt_content` | `?string` | `null` → aturan bawaan aman | FR-035, FR-037 | `/robots.txt` |
| `sitemap_enabled` | `bool` | `true` | FR-038 | `/sitemap.xml` |
| `sitemap_include_pages` | `bool` | `true` | FR-038 | Entri halaman kustom |
| `sitemap_include_articles` | `bool` | `true` | FR-038 | Entri artikel |
| `sitemap_include_products` | `bool` | `true` | FR-038 | Entri produk |
| `sitemap_include_portfolio` | `bool` | `true` | FR-038 | Entri proyek portfolio |
| `sitemap_changefreq` | `?string` | `'weekly'` | FR-039 | Atribut tiap entri |
| `sitemap_priority` | `?string` | `'0.8'` | FR-039 | Atribut tiap entri |

**Jenis halaman yang sah untuk `page_title_formats`** (FR-026, dibatasi rute publik yang benar-benar ada — research.md R0): `home`, `artikel_index`, `artikel_show`, `produk_index`, `produk_show`, `portfolio_index`, `portfolio_show`, `halaman`, `faq`, `karir`, `kontak`, `tentang_kami`.

**Penanda isian yang dikenali**: `{page_title}`, `{site_name}`, `{separator}`. Penanda lain dibuang, lalu pemisah menggantung dan spasi ganda dirapikan (FR-027).

**Catatan JSON-LD (FR-032)**: `App\Support\Seo\JsonLd` menyusun data Organization dari `SiteSettings` (nama, email, telepon, alamat perusahaan) dan `SocialSettings` (daftar profil sebagai `sameAs`). Tidak ada properti tersendiri untuk ini — data yang sama tidak boleh punya dua tempat pengisian (FR-064).

---

## 4. `ScriptSettings` — grup `script`

Halaman admin: **Scripts & Analytics**. **Hanya dapat dibuka dan disimpan `super_admin`** (FR-045). Tidak ada sumber pindahan — seluruhnya baru.

| Properti | Tipe | Bawaan | Requirement | Titik penerapan |
| --- | --- | --- | --- | --- |
| `head_scripts` | `?string` | `null` | FR-041 | Di dalam `<head>` halaman publik |
| `body_start_scripts` | `?string` | `null` | FR-041 | Tepat setelah `<body>` dibuka |
| `body_end_scripts` | `?string` | `null` | FR-041 | Tepat sebelum `</body>` |
| `footer_scripts` | `?string` | `null` | FR-041 | Bagian footer |
| `custom_css` | `?string` | `null` | FR-042 | Blok gaya di `<head>` |
| `custom_js` | `?string` | `null` | FR-043 | Sebelum `</body>` |
| `head_scripts_consent` | `string` | `'none'` | FR-053 | Kategori pengikat slot |
| `body_start_scripts_consent` | `string` | `'none'` | FR-053 | Kategori pengikat slot |
| `body_end_scripts_consent` | `string` | `'none'` | FR-053 | Kategori pengikat slot |
| `footer_scripts_consent` | `string` | `'none'` | FR-053 | Kategori pengikat slot |
| `custom_css_consent` | `string` | `'none'` | FR-053 | Kategori pengikat slot |
| `custom_js_consent` | `string` | `'none'` | FR-053 | Kategori pengikat slot |
| `cookie_consent_enabled` | `bool` | `false` | FR-050, FR-057 | Bilah persetujuan, tautan footer |
| `cookie_banner_message` | `?string` | `null` → pesan bawaan | FR-051 | Isi bilah persetujuan |

**Nilai kategori persetujuan**: `none`, `analytics`, `marketing`. `none` berarti slot selalu dijalankan. Kategori yang diperlukan agar situs berfungsi tidak pernah menjadi pilihan di sini karena selalu aktif (FR-052).

**Aturan**: Setiap slot dibatasi ukurannya saat disimpan dan admin diberi tahu batasnya (FR-048). Slot kosong tidak boleh meninggalkan elemen kosong pada halaman (FR-047).

**Catatan `custom_css_consent`**: kategori disediakan untuk seluruh slot secara seragam demi memenuhi FR-053 tanpa pengecualian khusus. Dalam praktiknya gaya jarang melacak apa pun, sehingga bawaannya `none`.

---

## 5. `SocialSettings` — grup `social`

Halaman admin: **Media Sosial**. Sumber pindahan: `brand.og_image_path`.

| Properti | Tipe | Bawaan | Requirement | Titik penerapan |
| --- | --- | --- | --- | --- |
| `facebook_url` | `?string` | `null` | FR-018 sampai FR-021 | Ikon sosial header, `sameAs` JSON-LD |
| `twitter_url` | `?string` | `null` | FR-018 sampai FR-021 | Ikon sosial header, `sameAs` JSON-LD |
| `instagram_url` | `?string` | `null` | FR-018 sampai FR-021 | Ikon sosial header, `sameAs` JSON-LD |
| `linkedin_url` | `?string` | `null` | FR-018 sampai FR-021 | Ikon sosial header, `sameAs` JSON-LD |
| `youtube_url` | `?string` | `null` | FR-018 sampai FR-021 | Ikon sosial header, `sameAs` JSON-LD |
| `pinterest_url` | `?string` | `null` | FR-018 sampai FR-021 | Ikon sosial header, `sameAs` JSON-LD |
| `tiktok_url` | `?string` | `null` | FR-018 sampai FR-021 | Ikon sosial header, `sameAs` JSON-LD |
| `share_buttons_enabled` | `bool` | `false` | FR-022, FR-023 | Tombol berbagi artikel & produk |
| `share_platforms` | `array` | `[]` | FR-022, FR-023 | Platform yang dirender |
| `default_share_image_path` | `?string` | `null` → aset bawaan | FR-024 | `og:image`, `twitter:image` |

**Aturan**: Setiap URL profil wajib berupa alamat web sah bila diisi, dengan pesan kesalahan yang menyebut platformnya (FR-021). Ikon platform tanpa URL tidak dirender, dan seluruh kelompok ikon tidak dirender bila tidak ada satu pun URL terisi (FR-020). Metode `ogImageUrl(): string` pindah dari `BrandSettings` dengan fallback ke aset bawaan.

**Nilai sah `share_platforms`**: `facebook`, `twitter`, `linkedin`, `pinterest`, `reddit`, `whatsapp`, `telegram`, `email`.

---

## Persetujuan cookie pengunjung (bukan entitas sisi server)

Disimpan di `localStorage` peramban pengunjung (FR-058). Bentuknya: peta kategori ke keputusan, beserta waktu pemilihan agar kunjungan berikutnya tidak menampilkan bilah lagi (FR-055).

Tidak ada tabel, tidak ada catatan di sisi server, dan tidak ada halaman tinjauan di panel admin — batas ini diputuskan sadar pada sesi klarifikasi 2026-09-22 dan tercatat di Assumptions spec.

---

## Grup `brand` setelah migrasi

Kosong dan tidak lagi dipakai. `App\Settings\BrandSettings` dan `App\Filament\Pages\BrandSettingsPage` dihapus setelah seluruh rujukan berpindah (FR-063). Tidak boleh ada kelas pembaca lama yang disisakan, karena itu akan menciptakan tempat kedua untuk nilai yang sama (FR-064).
