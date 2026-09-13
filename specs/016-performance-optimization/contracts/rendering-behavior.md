# Contract: Perilaku Rendering & Caching Halaman Publik

**Feature**: [spec.md](../spec.md) | **Date**: 2026-09-13

Tidak ada API publik baru. "Kontrak" di sini adalah perilaku HTML/HTTP yang harus dipenuhi implementasi, diverifikasi lewat feature test.

## 1. Lazy-load gambar (User Story 1)

| Aspek | Kontrak |
|---|---|
| Gambar di dalam komponen hero/cover teratas (research.md §2, kolom "Eager") | Tag `<img>` MUST TIDAK punya atribut `loading` (default browser = eager) |
| Seluruh gambar lain (kolom "Lazy") | Tag `<img>` MUST punya `loading="lazy"` dan `decoding="async"` |
| Konsistensi | Aturan berlaku di SEMUA halaman publik yang render gambar — tidak ada file yang dikecualikan tanpa alasan di research.md §2 |
| Ketergantungan JS | Atribut `loading="lazy"` MUST berupa atribut HTML native pada tag `<img>` — TIDAK boleh gambar hanya render lewat JavaScript murni (kecuali yang sudah begitu sebelum fitur ini, mis. galeri Alpine `:src` di produk/show, yang TIDAK disentuh fitur ini) |

## 2. Caching halaman publik read-only (User Story 2)

| Aspek | Kontrak |
|---|---|
| 8 method controller (data-model.md § Skema cache key) | MUST membungkus hasil query lewat `rememberPublicPage()`, TTL 300 detik |
| Permintaan kedua dalam TTL yang sama | MUST mengembalikan konten identik dengan permintaan pertama tanpa query DB ulang (diverifikasi via query count assertion di test — `assertQueryCountLessThan` / hitung manual) |
| Route admin (`/admin/*`, Filament Resource) | MUST TIDAK pernah membaca dari cache ini — selalu query langsung (FR-008) |
| `PortfolioController::index` dengan `?kategori=` berbeda | MUST menghasilkan entri cache terpisah per nilai kategori — pengunjung kategori A TIDAK PERNAH melihat cache hasil kategori B |
| Halaman dengan state per-pengunjung (`/kontak`, endpoint kalkulator) | MUST TIDAK dibungkus cache apa pun (FR-006) |
| Kedaluwarsa cache | MUST otomatis (TTL 300 detik) — TIDAK ada mekanisme invalidasi manual/event yang perlu diverifikasi (Assumptions spec.md) |

## 3. Pemuatan non-blocking stylesheet font ikon (User Story 3)

| Aspek | Kontrak |
|---|---|
| Tag stylesheet Material Symbols di `layouts/public.blade.php` | MUST memakai pola `media="print" onload="this.media='all'"` (bukan `rel="stylesheet"` polos) |
| Fallback tanpa JavaScript | MUST ada `<noscript><link rel="stylesheet" href="...">...</noscript>` yang memuat stylesheet sama secara normal |
| Hasil akhir tampilan | Ikon MUST tetap tampil benar di semua halaman setelah stylesheet siap — nol regresi visual (dites lewat assertion bahwa markup ikon `material-symbols-outlined` tidak berubah, hanya cara pemuatan CSS-nya) |

## 4. Non-goal eksplisit

- Tidak ada endpoint/toggle admin baru untuk mengatur TTL cache atau perilaku lazy-load per klien (Assumptions spec.md).
- Tidak ada perubahan pada proses build Vite/Tailwind itu sendiri (Assumptions spec.md) — hanya cara satu stylesheet pihak ketiga dimuat.
- Tidak ada invalidasi cache instan berbasis event saat admin menyimpan (Assumptions spec.md).
