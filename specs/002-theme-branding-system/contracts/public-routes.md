# Contract: Public Frontend Routes

**Feature**: [spec.md](../spec.md) | **Date**: 2026-09-01

Kontrak routing & perilaku halaman publik (FR-001). Setiap baris harus bisa diverifikasi lewat feature test HTTP (`GET` → status code + elemen kunci ada di response), tanpa bergantung pada detail implementasi Blade.

| Halaman | Route | View dasar | Sumber data | Mockup referensi |
|---|---|---|---|---|
| Home | `GET /` | `pages.home` | Statis (copy) + `Product::query()->take(3)` untuk section "Produk Kami" | `home_suoer_html_calculator_results` |
| Produk (list) | `GET /produk` | `pages.produk.index` | `Product::all()` (urut `order`) | `produk_suoer_luminous_azure` |
| Produk Detail | `GET /produk/{product:slug}` | `pages.produk.show` | `Product` by slug (404 jika tidak ada); related = produk lain dengan `category` sama | `produk_detail_suoer_header_aligned` |
| Tentang Kami | `GET /tentang-kami` | `pages.tentang-kami` | Statis (copy dari mockup) | `tentang_kami_suoer_luminous_azure` |
| Kontak | `GET /kontak` | `pages.kontak` | Statis + form client-side (tanpa route POST, lihat research.md §7) | `kontak_suoer_proportional_fix` |
| Karir | `GET /karir` | `pages.karir` | `JobOpening::where('is_active', true)->get()` | `karir_suoer_header_consistent` |
| Artikel (list) | `GET /artikel` | `pages.artikel.index` | `Article::whereNotNull('published_at')->latest('published_at')->get()` | `artikel_suoer_consistent_header_footer` |
| Artikel Detail | `GET /artikel/{article:slug}` | `pages.artikel.show` | `Article` by slug (404 jika tidak ada) | *(turunan dari list, belum ada mockup detail terpisah — pakai layout card/section yang sama)* |
| FAQ | `GET /faq` | `pages.faq` | `FaqItem::orderBy('order')->get()` | `faq_suoer_100_consistent_header_footer` |

## Kontrak umum tiap halaman

| Aspek | Kontrak |
|---|---|
| Layout | Semua halaman MUST pakai `layouts.public` yang sama (header/footer/nav konsisten — FR-002) |
| Styling | Warna, font, radius, spacing MUST bersumber dari CSS variable Theme Settings, bukan hardcode per halaman (FR-002) |
| Data kosong | Jika query data seed kosong (mis. belum ada `Article`), halaman MUST tetap render 200 dengan empty state yang wajar, bukan error |
| OG meta | Tiap halaman MUST menyertakan meta tag OG (title, description, image) — image ikut kontrak [theme-settings-surface.md](./theme-settings-surface.md) jika halaman tidak set OG image sendiri |
| Kalkulator (khusus Home) | Elemen kalkulator MUST berfungsi tanpa request jaringan tambahan (FR-006) — diverifikasi lewat test JS/browser terpisah dari feature test HTTP dasar |
