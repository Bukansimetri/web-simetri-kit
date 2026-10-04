# Contract: Render Halaman Publik

**Feature**: `029-why-choose-admin`

Kontrak ini menetapkan bagaimana section & CTA dirender setelah sumber datanya dipindah ke database. Prinsip utamanya: **markup, class, dan struktur HTML identik dengan kode lama.** Yang berganti hanya literal teks/ikon menjadi variabel.

## 1. Akses data: `App\Support\PageContent\PageContent`

```php
PageContent::section(PageSection $section): ?SectionContent
// null jika tidak ada item aktif → section TIDAK dirender (FR-013)

PageContent::cta(CtaPlacement $placement): CallToAction
// selalu mengembalikan objek; jika baris DB hilang → model tak-tersimpan berisi DefaultPageContent (FR-024)

PageContent::multiline(?string $text): HtmlString
// e($text) lalu str_replace("\n", '<br>', …) — identik dengan <br> di markup lama (FR-010)

PageContent::withProductName(?string $body, string $productName): HtmlString
// e($body) lalu {produk} → e(Str::lower($productName)) (FR-025)
```

**Aturan**: teks dari DB dirender apa adanya lewat `{{ $x }}`, judul lewat `PageContent::multiline()`, dan CTA Detail Produk lewat `withProductName()`. Tidak ada token yang diganti saat render; nama merek di nilai bawaan sudah diisi Nama Situs saat instalasi (research R11).

`SectionContent` (readonly DTO): `title`, `subtitle` (?string), `items` (Collection<SectionItem> aktif, terurut).

- Tidak memakai `rememberPublicPage` (research R6). Query langsung per render.
- Semua teks dari admin dirender dengan `{{ }}` / `e()`. Tidak ada `{!! !!}` untuk teks mentah (FR-010, SC-005). Satu-satunya HTML yang disisipkan adalah `<br>` dari `multiline()`.

## 2. Section daftar item

| Komponen / view | Section | Perubahan |
|---|---|---|
| `resources/views/components/sections/why-choose.blade.php` | `WhyChoose` | Hapus array `$reasons`. Bungkus `<section>` dengan `@if ($content = PageContent::section(...))`. `emphasized` dibaca dari `$item->is_emphasized`. Judul lewat `multiline()` menggantikan literal `Mengapa Beralih<br>Bersama SUOER?`. |
| `resources/views/components/sections/how-it-works.blade.php` | `HowItWorks` | Hapus array `$steps`. Dekorasi posisi tetap di komponen: `$rotations = ['-rotate-1','rotate-2','-rotate-2','rotate-1']`, `$offsets = ['md:mt-0','md:mt-12','md:mt-4','md:mt-16']`, diindeks `$loop->index`. Nomor = `PageSection::HowItWorks->stepNumber($loop->iteration)` (`01`..`04`). |
| `resources/views/pages/karir.blade.php` (Values) | `CareerValues` | Hapus `$values`. Section dibungkus `@if`. |
| `resources/views/pages/karir.blade.php` (Recruitment) | `RecruitmentProcess` | Hapus `$process`. Seluruh `<section>` (termasuk kotak latar) dibungkus `@if`. Nomor = `stepNumber($loop->iteration)` (`1`..`4`). |

Aturan umum:

- Subjudul: jika `null`/kosong, elemen `<p>` subjudul tidak dirender.
- Hanya item aktif, urut `order` lalu `id`.
- Item ditonjolkan yang nonaktif tidak ikut dirender, sehingga tidak ada kartu yang tampil ditonjolkan.

## 3. CTA

| Placement | Lokasi render | Yang diganti variabel | Yang TETAP di Blade |
|---|---|---|---|
| `Home` | `pages/home.blade.php` (CTA Penutup) | judul (`multiline`), paragraf, label tombol WA, label tombol form | URL WA `$site->whatsappUrl(...) ?: url('/kontak')`, `url('/kontak')`, ikon SVG WA, semua class |
| `ProductCalculator` | `pages/produk/index.blade.php` | judul, paragraf, label tombol | `url('/#kalkulator')`, ikon `arrow_forward` |
| `ProductClosing` | `pages/produk/index.blade.php` | judul, paragraf, label tombol | `url('/kontak')` |
| `ProductDetail` | `pages/produk/show.blade.php` | judul, paragraf (token `{produk}` → `Str::lower($product->name)`, keduanya di-escape), label tautan | `url('/kontak')`, ikon `arrow_forward`, gambar produk |
| `ArticleIndex` | `pages/artikel/index.blade.php` (blok "Newsletter") | judul, paragraf, label tombol | ikon `forum`, `url('/kontak')`, ikon `arrow_forward` |
| `ArticleDetail`, `About`, `Faq`, `Career` | `components/sections/cta-band.blade.php` | `title`, `subtitle`, `buttonLabel` | `buttonHref`, `buttonIcon` (tetap dikirim dari halaman seperti sekarang) |

`cta-band` mendapat prop baru `placement` (wajib, `CtaPlacement`). Prop `title`/`subtitle`/`buttonLabel` dihapus dari pemanggil. Prop `buttonHref`/`buttonIcon` tetap dengan default yang sama.

- Paragraf/subjudul kosong → baris/elemen tidak dirender. Untuk `cta-band`, `<br>{{ $subtitle }}` dilewati seperti logika `@if ($subtitle)` sekarang.
- Perubahan satu placement tidak memengaruhi placement lain (FR-023).

## 4. Jaminan kesetaraan markup (SC-001, SC-006)

1. **Fixture** `tests/Fixtures/legacy-page-content/{nama}.html`: HTML tiap section/CTA yang dirender dari kode **lama**, diambil sebelum Blade diubah. Nama fixture: `home-why-choose`, `home-how-it-works`, `home-cta`, `karir-values`, `karir-recruitment`, `karir-cta`, `produk-cta-kalkulator`, `produk-cta-penutup`, `produk-detail-cta`, `artikel-index-cta`, `artikel-detail-cta`, `tentang-kami-cta`, `faq-cta`.
2. **`LegacyMarkupEquivalenceTest`**: setelah migrasi (data awal) dan `LegacyMarkup::seedDeterministicState()`, GET setiap halaman lalu `assertSame(file_get_contents($fixture), LegacyMarkup::extract($html, $xpath))`. `extract()` dipakai di kedua sisi (saat membuat fixture & saat test): memuat HTML dengan prefiks `<?xml encoding="UTF-8">`, mengambil node terakhir yang cocok dengan XPath, lalu `normalize()` (merapatkan whitespace berurutan jadi satu spasi dan menghapus spasi di antara `>` dan `<`).
3. Halaman dengan data dinamis (produk/artikel detail) memakai factory dengan nama/slug tetap, dan `site_name = 'SUOER'`, agar fixture deterministik.
4. Keterbatasan: atribut Alpine (`@click`, `:class`) dibuang parser libxml. Ke-13 fragmen tidak memuatnya. Fragmen baru yang memuatnya wajib diverifikasi lewat tangkapan layar.

## 5. Addendum 2026-10-02: Tentang Kami & Kontak

- `PageContent::block(PageBlockType $type): PageBlock` — selalu mengembalikan blok (fallback nilai bawaan bila baris hilang). Helper: `imageUrl(string $key, string $default)` (gambar hilang dari disk → bawaan), `sanitized(string $key)` (rich text via `HtmlSanitizer::clean`).
- `PageContent::whatsappMessage(): string` — dari blok `ContactInfo`.
- `tentang-kami.blade.php`: Hero/Siapa Kami/Visi membaca blok; Misi/Nilai/Trust membaca `PageContent::section(...)`. Markup tidak berubah. Misi: item 1–3 kolom kiri, sisanya kolom kanan. Nilai: kartu besar dari heading. Judul hero tetap `Mengenal {{ $appName }} Lebih Dekat`.
- `kontak.blade.php`: label WhatsApp & jam operasional dari blok `ContactInfo`; pesan WhatsApp dari `whatsappMessage()` (juga di `home.blade.php` & `cta-band.blade.php`).
- Fragmen kesetaraan baru: `tentang-kami-hero`, `tentang-kami-siapa-kami`, `tentang-kami-visi`, `tentang-kami-misi`, `tentang-kami-nilai`, `tentang-kami-trust`, `kontak-info`.
