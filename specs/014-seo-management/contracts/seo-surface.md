# Contract: SEO Rendering & Admin Surface

**Feature**: [spec.md](../spec.md) | **Date**: 2026-09-12

Fitur ini tidak mengekspos API publik. "Kontrak" di sini adalah tag `<head>` yang harus muncul di setiap halaman publik, form admin "SEO" per resource, dan struktur JSON-LD per tipe halaman — semua harus terverifikasi lewat feature test.

## 1. Meta tag global (User Story 1) — berlaku di SEMUA halaman publik

| Aspek | Kontrak |
|---|---|
| `<title>` | MUST terisi (existing `@yield('title', $appName)`) |
| `<meta name="description">` | MUST terisi — fallback `BrandSettings::meta_description` → hardcoded default (FR-001, FR-003) |
| `<link rel="canonical">` | MUST ada, `href` = `url()->current()` (FR-002) |
| `og:type`, `og:site_name`, `og:title`, `og:description`, `og:image` | MUST semua terisi, tidak boleh empty string (FR-001) |
| `twitter:card` | MUST `summary_large_image` |
| `twitter:title`, `twitter:description`, `twitter:image` | MUST terisi, nilai sama dengan `og:*` yang bersangkutan |
| JSON-LD `Organization` | MUST ada di setiap halaman — `name` = `$brand->app_name`, `url` = `url('/')`, `logo` disertakan hanya bila `logo_path` terisi (FR-008) |
| Halaman tanpa override apapun (mis. Beranda, Kontak) | MUST tetap lengkap — seluruh nilai di atas jatuh ke `BrandSettings` (FR-013) |

## 2. Override per konten (User Story 2) — Product, Article, CustomPage, PortfolioProject

| Aspek | Kontrak |
|---|---|
| Form admin | Section collapsed "SEO" berisi: `meta_title` (TextInput, opsional, hint jumlah karakter), `meta_description` (Textarea, opsional, hint jumlah karakter), `meta_image_path` (FileUpload gambar, opsional) (FR-004, FR-005, FR-007) |
| Simpan dengan field SEO kosong | MUST diterima (tidak ada validasi wajib) (FR-005) |
| Halaman publik konten — field diisi | `<title>`/`og:title`/`twitter:title` = `meta_title`; `og:description`/dst = `meta_description`; `og:image`/dst = URL `meta_image_path` (FR-004) |
| Halaman publik konten — field kosong | Tiap field MUST fallback independen sesuai `data-model.md` § trait `HasSeoMetadata` (judul→nama konten, deskripsi→ringkasan dipotong 160 karakter, gambar→gambar utama konten → gambar OG default situs) (FR-006) |
| Upload gambar SEO | Disimpan WebP via `ImageUploads::storeAsWebp(maxWidth: 1200)`, disk `public` (konsisten pola modul lain) |
| Ubah `BrandSettings::app_name`/`og_image_path`/`meta_description` | MUST langsung berlaku ke semua halaman TANPA override kustom pada request berikutnya, tanpa edit ulang konten (FR-012) |

## 3. JSON-LD per tipe halaman (User Story 3)

| Halaman | `@type` | Field wajib | Kondisi tidak disisipkan |
|---|---|---|---|
| Semua halaman publik | `Organization` | `name`, `url`; `logo` bila ada | — (selalu ada) |
| `GET /faq` | `FAQPage` | `mainEntity[]` = tiap `FaqItem` → `Question` + `acceptedAnswer.Answer` (urut `order`) | `$faqItems` kosong → schema TIDAK disisipkan (FR-009) |
| `GET /artikel/{slug}` | `Article` | `headline` (title), `image`, `datePublished` (`published_at`), `author` (`redaksi`) | Halaman hanya bisa diakses bila `isPublished()` (existing behavior) — draft/scheduled tidak pernah sampai sini |
| `GET /produk/{slug}` | `Product` | `name`, `description` (`seoDescription()`), `image` | `offers.price` MUST hanya disertakan bila `$product->price` terisi (FR-011); `offers.availability` TIDAK disertakan (Assumptions — bukan situs checkout) |

## 4. Validasi eksternal (acceptance untuk SC-003)

Markup JSON-LD hasil generate MUST lolos Google Rich Results Test / Schema Markup Validator tanpa error untuk keempat `@type` di atas — divalidasi manual saat Phase Polish (bukan otomatis dalam test suite PHPUnit).
