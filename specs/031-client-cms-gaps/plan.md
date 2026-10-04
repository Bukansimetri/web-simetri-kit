# Implementation Plan: Lanjutan Penyesuaian Website dan Kelengkapan Admin CMS

**Branch**: `031-client-cms-gaps` | **Date**: 2026-10-04 | **Spec**: [spec.md](spec.md)

**Input**: Feature specification from `/specs/031-client-cms-gaps/spec.md`

## Summary

Menutup 13 item ❌/⚠️ dari dokumen klien, di atas spec 030 (PR #27).

- **Header seragam**: FAQ, Kontak, dan Tentang Kami memakai `x-sections.page-hero`. Gambar banner FAQ/Kontak diaktifkan lewat `PageBlockType::defaultImagePath()`, yang otomatis memunculkan kolom gambar di Banner Halaman.
- **Tipografi 700**: ganti `font-extrabold`/`font-black` di view publik dengan `font-bold`, set bobot token headline ke 700, dan seragamkan teks tombol CTA ke `font-bold`. Dijaga oleh test yang memindai HTML halaman publik.
- **FAQ admin**: tabel `faq_items` mendapat kolom `placement` (faq/produk/kontak) dan `is_active`. Ada `FaqItemResource` baru, dan FAQ Produk/Kontak dipindah dari Blade ke data bawaan lewat `PageContentInstaller`.
- **Detail lowongan**: rute `GET /karir/{jobOpening}` dan halaman detail. Kartu lowongan menaut ke detail.
- **Artikel**: paginasi kumulatif `?halaman=N` (6 per kelompok) dengan "Muat lebih banyak" (fetch + append, tetap jalan tanpa JS). Filter kategori dan tag pindah ke server (`?kategori=`, `?tag=`). Tambahan lain: Tag Populer, rute pratinjau untuk admin, `view_count`, `image_caption`, produk terkait berurutan, dan sidebar detail (terbaru + langganan).
- **Produk beranda**: kolom `show_on_home` dengan validasi maksimal 3.
- **Cache publik**: kunci cache diberi nomor versi. Model publik menaikkan versi saat `saved`/`deleted`, sehingga semua variasi filter langsung segar.
- **Perbaikan kecil**: CTA ke `/#kalkulator`, kutipan kosong disembunyikan, "Partner Kami" di Tentang Kami, latar Tim Kami penuh, filter Produk dihapus, dan `footer_description` di Pengaturan Umum.

## Technical Context

**Language/Version**: PHP 8.3, Laravel 13

**Primary Dependencies**: Filament 3.2, Spatie Settings, Spatie Tags (sudah dipakai `Article`), Alpine, Tailwind. Tanpa dependency baru

**Storage**: MySQL/SQLite. Migrasi baru: kolom `faq_items.placement` + `is_active`, `products.show_on_home`, `articles.view_count` + `image_caption`, tabel `article_product` (article_id, product_id, sort_order), dan settings `site.footer_description`

**Testing**: PHPUnit 12, Livewire test Filament, fixture HTML (`LegacyMarkup`)

**Target Platform**: Web server Linux. Produksi hanya menjalankan `migrate --force`

**Project Type**: Web application Laravel monolit (Blade publik + panel Filament)

**Performance Goals**: Daftar artikel memuat maksimal 6×N artikel per permintaan (bukan semua). Tag Populer adalah satu query agregat yang di-cache. Penghitung dilihat memakai satu `UPDATE` tanpa event model

**Constraints**: Tanpa page builder (Principle III). Halaman di luar cakupan tidak berubah kecuali bagian bersama (tipografi, hero). Perubahan admin harus langsung tampil (FR-024)

**Scale/Scope**: ±20 view publik disentuh (sebagian hanya kelas font), 6 migrasi, 1 Filament Resource baru (FAQ), 3 resource diperluas (Product, Article, JobOpening tidak berubah di admin), 1 halaman Settings diperluas, 2 rute publik baru

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Catatan |
|---|---|---|
| I. Multi-Client Reusability | ✅ | FAQ, teks footer, pilihan produk beranda, dan banner pindah ke DB/settings. Data bawaan generik tanpa nama klien (memakai `{app_name}` bila perlu). |
| II. White-Label by Default | ✅ | Tidak ada branding starter-kit baru. |
| III. No Page Builder (NON-NEGOTIABLE) | ✅ | Admin hanya mengedit isi (FAQ, teks, pilihan produk) pada tempat yang tetap. Tidak ada kontrol tata letak. |
| IV. Module Test Coverage | ✅ | Modul FAQ admin, detail lowongan, fitur artikel, dan invalidasi cache masing-masing mendapat feature test. |
| V. Simplicity & Dependency Discipline | ✅ | Tanpa dependency baru. Spatie Tags dan pola `PageContentInstaller` dipakai ulang. Cache diinvalidasi lewat nomor versi, bukan cache tags (driver file/database tidak mendukung tags). |
| Deployment: seeder demo tidak auto-run | ✅ | FAQ Produk/Kontak adalah konten tampil saat ini, bukan demo. Ditanam lewat `PageContentInstaller` (migrasi), bukan seeder. `FaqItemSeeder` tetap demo-only. |
| Deployment: dokumentasi | ✅ | `docs/manual-operator.md` diperbarui (FR-034). |

**Post-design re-check (setelah Phase 1)**: tetap lulus.

## Project Structure

### Documentation (this feature)

```text
specs/031-client-cms-gaps/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   ├── public-routes.md
│   └── admin.md
├── checklists/requirements.md
└── tasks.md             # /speckit-tasks
```

### Source Code (repository root)

```text
app/
├── Concerns/
│   ├── CachesPublicPages.php              # ubah: kunci cache ber-versi
│   └── FlushesPublicPageCache.php         # baru: trait model, naikkan versi saat saved/deleted
├── Enums/
│   ├── FaqPlacement.php                   # baru: Faq, Product, Contact
│   └── PageBlockType.php                  # ubah: gambar bawaan FaqHero & ContactHero
├── Models/
│   ├── FaqItem.php                        # ubah: placement, is_active, scope
│   ├── Product.php                        # ubah: show_on_home, scope forHome()
│   ├── Article.php                        # ubah: view_count, image_caption, relatedProducts()
│   ├── ArticleRelatedProduct.php          # baru: model baris article_product (untuk Repeater berurutan)
│   └── JobOpening.php                     # (rute detail, tanpa kolom baru)
├── Http/Controllers/Public/
│   ├── ArticleController.php              # ubah: paginasi, kategori/tag server-side, tag populer, preview, view count
│   ├── CareerController.php               # ubah: + show()
│   ├── HomeController.php                 # ubah: produk forHome()
│   ├── ProductController.php              # ubah: FAQ produk dari DB, tanpa kategori
│   ├── ContactController.php              # ubah: FAQ konsultasi dari DB
│   ├── FaqController.php                  # ubah: hanya placement faq & aktif
│   └── NewsletterController.php           # ubah: redirect ke halaman asal
├── Filament/Resources/
│   ├── FaqItemResource.php (+ Pages)      # baru
│   ├── ProductResource.php                # ubah: toggle Tampilkan di Beranda (maks 3)
│   └── ArticleResource.php (+ EditArticle)# ubah: caption, produk terkait, aksi Preview
├── Filament/Pages/ManageSiteSettings*.php # ubah: section Footer
├── Settings/SiteSettings.php              # ubah: footer_description
└── Support/PageContent/
    ├── DefaultPageContent.php             # ubah: FAQ Produk & Kontak bawaan, teks footer
    └── PageContentInstaller.php           # ubah: tanam FAQ bawaan (idempoten per placement)

database/
├── migrations/ (6 file: faq_items, products, articles, article_product, install faq defaults, ...)
└── settings/<ts>_add_footer_description.php

routes/web.php                             # + /karir/{jobOpening}, /artikel/{article:slug}/preview

resources/
├── css/app.css                            # ubah: bobot token headline 700
└── views/
    ├── components/sections/
    │   ├── page-hero.blade.php            # (dipakai FAQ, Kontak, Tentang Kami)
    │   ├── faq-list.blade.php             # baru: daftar akordeon dipakai Produk & Kontak
    │   ├── job-card.blade.php             # ubah: tautan detail
    │   ├── article-sidebar.blade.php      # ubah: + Tag Populer, ekstrak kartu langganan
    │   ├── newsletter-card.blade.php      # baru: dipakai daftar & detail artikel
    │   ├── team-members.blade.php         # ubah: latar penuh
    │   └── testimonials.blade.php         # ubah: TESTIMONI / Partner Kami
    ├── components/layout/footer.blade.php # ubah: teks dari settings
    └── pages/
        ├── faq.blade.php, kontak.blade.php, tentang-kami.blade.php   # hero seragam (+ FAQ dari DB)
        ├── produk/index.blade.php         # tanpa filter, FAQ dari DB
        ├── karir.blade.php, karir/show.blade.php (baru)
        ├── artikel/index.blade.php, artikel/show.blade.php
        └── home.blade.php                 # CTA ke /#kalkulator
        (+ semua view publik: font-extrabold/font-black → font-bold)

tests/ (Feature/Pages, Feature/Admin, Feature/Database, Support/LegacyMarkup, Fixtures)
docs/manual-operator.md
```

**Structure Decision**: Mengikuti struktur Laravel monolit yang ada. Halaman detail lowongan diletakkan di `pages/karir/show.blade.php`. `karir.blade.php` tetap di tempatnya agar rute dan view yang ada tidak berubah.

## Complexity Tracking

Tidak ada pelanggaran konstitusi.
