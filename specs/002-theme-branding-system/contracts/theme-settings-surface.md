# Contract: Theme Settings Surface (Admin Panel)

**Feature**: [spec.md](../spec.md) | **Date**: 2026-09-01

Fitur ini tidak mengekspos API publik untuk Theme Settings. "Kontrak" di sini adalah permukaan admin panel (route, form, perilaku) yang harus konsisten dipenuhi implementasi, supaya bisa diverifikasi lewat feature test tanpa bergantung pada detail implementasi. Menyambung kontrak Brand Settings yang sudah ada di [001-epic2-cleanup/contracts/admin-panel-surface.md §2](../../001-epic2-cleanup/contracts/admin-panel-surface.md).

## Theme Settings (User Story 3)

| Aspek | Kontrak |
|---|---|
| Route | Halaman Filament yang sama dengan Brand Settings Epic 2 (`BrandSettingsPage`, diperluas) — bukan halaman baru |
| Field baru | Warna sekunder (color picker), Font Heading (select, dropdown kurasi), Font Body (select, dropdown kurasi), OG Image (file upload gambar) |
| Nilai default instalasi baru | Semua field baru sudah terisi nilai default Luminous Azure (`#3a5f94`, `Manrope`, `Be Vietnam Pro`, OG image default) sejak sebelum admin menyimpan apa pun — FR-005 |
| Validasi Font | Submit dengan nilai font di luar daftar kurasi MUST ditolak dengan pesan error, tidak tersimpan |
| Validasi OG Image | Submit dengan file bukan gambar / melebihi ukuran maksimum MUST ditolak dengan pesan error, tidak tersimpan |
| Efek simpan | Setelah admin menyimpan, seluruh 8 halaman publik MUST merefleksikan warna sekunder/font baru pada request berikutnya (tanpa deploy ulang) — FR-002 |
| Reset ke kosong | Mengosongkan kembali field (mis. hapus warna sekunder) MUST membuat sistem fallback ke default Luminous Azure, bukan menyisakan tampilan rusak |

## OG Meta Tag (turunan dari Theme Settings)

| Aspek | Kontrak |
|---|---|
| Halaman tanpa OG image spesifik | Meta tag `<meta property="og:image">` MUST menunjuk ke `og_image` dari Theme Settings |
| `og_image` belum diatur admin | Meta tag MUST fallback ke asset default Luminous Azure (bukan tag kosong/rusak) — FR-010 |
