# Implementation Plan: Penyesuaian Desain Website Sesuai Dokumen Klien

**Branch**: `030-client-design-update` | **Date**: 2026-10-04 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/030-client-design-update/spec.md`

## Summary

Delapan area tampilan publik disesuaikan dengan dokumen "Confirm Update Design" klien: tiga section Beranda (Mengapa Beralih, Sederhana dan Mulus, Testimoni), kartu Produk, halaman Artikel, halaman Portofolio, serta verifikasi footer Kontak dan section Solusi.

- **Murni tampilan** (Beranda, Produk): ubah Blade/class. Data dan pengelolaan admin tidak berubah.
- **Hero Artikel & Portofolio**: pakai komponen `x-sections.page-hero` yang sudah ada, dengan data dari `PageBlock` (`ArticlesHero`, `PortfolioHero`) yang sudah dibuat di pekerjaan banner admin (belum di-commit). Perlu melengkapi gambar bawaan.
- **Artikel**: layout 2 kolom, pencarian lewat query string (server-side), sidebar, dan formulir langganan. Artikel unggulan lama dihapus dari halaman daftar; CTA `ArticleIndex` dipertahankan di bawah grid agar menu CTA admin tetap berfungsi.
- **Langganan newsletter**: satu tabel baru `newsletter_subscribers`, endpoint POST ber-throttle dengan honeypot/token waktu yang sama dengan form Kontak, serta satu Filament Resource baca-saja untuk admin.
- **Portofolio**: kartu menampilkan ringkasan (diturunkan dari `description`, tanpa kolom baru) dan tautan detail.
- **Tes**: fixture markup lama untuk "Mengapa Beralih" dan "Sederhana dan Mulus" sengaja diganti karena desain memang berubah.

## Technical Context

**Language/Version**: PHP 8.3, Laravel 13

**Primary Dependencies**: Filament 3.2, Spatie Settings/Permission, Tailwind, Alpine (semua sudah terpasang). Tanpa dependency baru

**Storage**: MySQL/SQLite sesuai `.env`. 1 tabel baru: `newsletter_subscribers`. Tabel `page_blocks` dipakai ulang

**Testing**: PHPUnit 12 (`php artisan test`), Livewire test helper Filament, fixture HTML untuk kesetaraan markup

**Target Platform**: Web server Linux (deploy via `docs/deployment.md`, produksi hanya `migrate --force`)

**Project Type**: Web application Laravel monolit (Blade publik + panel Filament)

**Performance Goals**: Halaman Artikel tetap ≤ 3 query data (artikel, kategori, hero). Pencarian memakai satu query `LIKE` pada artikel terbit. Halaman publik tetap memakai cache 5 menit yang ada

**Constraints**: Tanpa page builder (Principle III). Tanpa mengubah halaman di luar cakupan (FR-027). Cache publik harus memakai kunci yang memuat parameter pencarian agar hasil tidak bocor antar kata kunci

**Scale/Scope**: 8 area tampilan, 1 tabel baru, 1 controller action baru, 1 Filament Resource baru, ± 8 file Blade diubah

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Catatan |
|---|---|---|
| I. Multi-Client Reusability | ✅ | Teks hero dan konten tetap di DB/admin. Judul "Partner Kami" mengikuti pola literal Blade yang sudah ada untuk judul testimoni, bukan data klien. Tidak ada nama klien baru di kode. |
| II. White-Label by Default | ✅ | Tidak ada branding starter-kit baru. Teks bawaan memakai `{app_name}` bila menyebut merek. |
| III. No Page Builder (NON-NEGOTIABLE) | ✅ | Hanya mengubah tampilan section bernama. Admin tetap hanya mengedit isi hero (judul, subjudul, gambar). |
| IV. Module Test Coverage | ✅ | Modul baru (Langganan Newsletter) mendapat test render, simpan, validasi, duplikat, throttle, dan admin. Test halaman Produk, Artikel, Portofolio, Beranda diperbarui. |
| V. Simplicity & Dependency Discipline | ✅ | Tanpa dependency baru. Pencarian `LIKE` sederhana, bukan mesin pencari. Ringkasan portofolio diturunkan, tanpa kolom baru. Memakai ulang `SubmissionGuard` dan pola `ContactController`. |
| Deployment: seeder demo tidak auto-run | ✅ | Gambar bawaan hero dan data awal lewat `PageContentInstaller`, bukan seeder. |
| Deployment: dokumentasi | ✅ | `docs/manual-operator.md` diperbarui (menu Langganan, hero Artikel dan Portofolio). |

**Post-design re-check (setelah Phase 1)**: tetap lulus. Tidak ada pelanggaran baru dari data-model maupun contracts.

## Project Structure

### Documentation (this feature)

```text
specs/030-client-design-update/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── public-routes.md
├── checklists/requirements.md
└── tasks.md             # /speckit-tasks
```

### Source Code (repository root)

```text
app/
├── Enums/PageBlockType.php                        # ubah: gambar bawaan Artikel & Portofolio
├── Models/NewsletterSubscriber.php                # baru
├── Http/Controllers/Public/
│   ├── ArticleController.php                      # ubah: pencarian + kunci cache + hapus 'featured'
│   ├── NewsletterController.php                   # baru: store()
│   └── PortfolioController.php                    # (tetap; ringkasan diturunkan di model)
├── Models/PortfolioProject.php                    # ubah: summary() turunan dari description
├── Filament/Resources/
│   └── NewsletterSubscriberResource.php (+ Pages/ListNewsletterSubscribers)  # baru, baca-saja
└── Support/PageContent/DefaultPageContent.php     # ubah bila perlu: subjudul bawaan hero

database/
├── migrations/<ts>_create_newsletter_subscribers_table.php   # baru
└── factories/NewsletterSubscriberFactory.php                 # baru

routes/web.php                                     # + POST /langganan (throttle)

resources/views/
├── components/sections/
│   ├── why-choose.blade.php                       # ubah: rata, tengah, ikon kecil
│   ├── how-it-works.blade.php                     # ubah: tanpa rotasi/offset
│   ├── product-card.blade.php                     # ubah: gambar + judul saja
│   ├── article-card.blade.php                     # ubah: badge di atas gambar, tanggal, ringkasan, tautan
│   ├── article-sidebar.blade.php                  # baru: cari + langganan
│   └── project-card.blade.php                     # baru: kartu portofolio
└── pages/
    ├── home.blade.php                             # ubah: blok testimoni "Partner Kami"
    ├── produk/index.blade.php                     # grid kartu baru
    ├── artikel/index.blade.php                    # page-hero + 2 kolom
    └── portfolio/index.blade.php                  # page-hero + kartu baru

tests/
├── Feature/Pages/{HomePage,ProductPage,ArticlePage,PortfolioPage,LegacyMarkupEquivalence}Test.php  # ubah
├── Feature/Pages/NewsletterSubscribeTest.php      # baru
├── Feature/Admin/NewsletterSubscriberResourceTest.php   # baru
├── Support/LegacyMarkup.php                       # ubah: lepas fragmen yang desainnya berubah
└── Fixtures/legacy-page-content/                  # hapus home-why-choose, home-how-it-works, artikel-hero, portofolio-hero (diganti)

docs/manual-operator.md                            # ubah
```

**Structure Decision**: Mengikuti struktur Laravel monolit yang ada. Tidak ada folder dasar baru. Komponen Blade baru ditaruh di `components/sections` seperti saudaranya.

## Complexity Tracking

Tidak ada pelanggaran konstitusi yang perlu dibenarkan.
