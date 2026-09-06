# Contract: Admin Panel Surface & Public Routing (Custom Page)

**Feature**: [spec.md](../spec.md) | **Date**: 2026-09-07

Fitur ini tidak mengekspos API publik selain rendering halaman biasa. "Kontrak" di sini adalah permukaan admin panel dan routing publik yang harus konsisten dipenuhi implementasi, supaya bisa diverifikasi lewat feature test tanpa bergantung pada detail implementasi Filament.

## 1. Custom Page (User Story 1)

| Aspek | Kontrak |
|---|---|
| Route admin | `/admin/custom-pages` (Filament Resource: index, create, edit) |
| Akses | Semua role dengan akses panel admin (FR-010) |
| Field wajib | `title`, `content` — submit tanpa salah satunya MUST ditolak dengan pesan error per field (FR-008) |
| Slug | Kosong → auto-generate dari `title`; diisi manual → dipakai apa adanya; duplikat dengan Custom Page lain MUST ditolak (FR-003, FR-004) |
| Editor konten | `content` MUST diisi lewat rich text editor (WYSIWYG), hasil tersimpan sebagai HTML (FR-009) |
| Hapus halaman | MUST minta konfirmasi sebelum diproses (FR-007); URL halaman tsb MUST 404 setelahnya |
| Efek simpan ke publik | Create/update/delete Custom Page MUST tercermin di `/halaman/{slug}` pada request berikutnya, tanpa deploy ulang (FR-006) |

## 2. Routing Publik (User Story 1 & 2)

| Aspek | Kontrak |
|---|---|
| Route | `GET /halaman/{slug}` — route-model-binding by `slug` (pola sama seperti `Article`/`Product`) |
| Slug tidak ditemukan | MUST mengembalikan 404 (FR-011) — otomatis lewat route-model-binding, tanpa kode tambahan |
| Slug konvensi | Admin MUST memakai slug persis `kebijakan-privasi` dan `syarat-ketentuan` saat membuat kedua halaman tsb, supaya link footer (FR-012) berfungsi (research.md §4) |

## 3. Efek ke Frontend Publik (turunan dari CRUD)

| Aspek | Kontrak |
|---|---|
| Link footer "Kebijakan Privasi" | MUST mengarah ke `url('/halaman/kebijakan-privasi')` (FR-012) — bukan lagi ke `/tentang-kami` |
| Link footer "Syarat & Ketentuan" | MUST mengarah ke `url('/halaman/syarat-ketentuan')` (FR-012) — bukan lagi ke `/tentang-kami` |
| Halaman "Tentang Kami" (`/tentang-kami`) | TIDAK BERUBAH — tetap Blade khusus yang sudah ada, tidak disentuh fitur ini (FR-013) |
| Halaman belum dibuat admin | `GET /halaman/{slug-belum-ada}` MUST 404 (termasuk saat pengunjung klik link footer sebelum admin membuat halaman tsb — US2 Acceptance Scenario 2) |
