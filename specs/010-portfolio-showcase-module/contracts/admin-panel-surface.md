# Contract: Admin Panel Surface & Public Routing (Portfolio)

**Feature**: [spec.md](../spec.md) | **Date**: 2026-09-07

Fitur ini tidak mengekspos API publik selain rendering halaman biasa. "Kontrak" di sini adalah permukaan admin panel dan routing publik yang harus dipenuhi implementasi, agar terverifikasi lewat feature test tanpa bergantung pada detail internal Filament.

## 1. Kategori Portfolio CRUD (User Story 1)

| Aspek | Kontrak |
|---|---|
| Route admin | `/admin/portfolio-categories` (Filament Resource: index, create, edit) |
| Navigasi | Label menu "Kategori Portfolio" |
| Akses | Semua role dengan akses panel admin (FR-015) — tanpa policy khusus |
| Field wajib | `name` — wajib, unik antar kategori portfolio (FR-002) |
| Slug | `slug` auto dari `name`, unik; dipakai di URL filter publik |
| Hapus | Kategori yang masih dipakai ≥1 proyek MUST dicegah/diperingatkan (FR-003); kategori tak terpakai bisa dihapus (dengan konfirmasi) |

## 2. Proyek Portfolio CRUD (User Story 2)

| Aspek | Kontrak |
|---|---|
| Route admin | `/admin/portfolio-projects` (Filament Resource: index, create, edit) |
| Navigasi | Label menu "Portfolio" |
| Akses | Semua role dengan akses panel admin (FR-015) |
| Field wajib | `title`, `portfolio_category_id`, `description`, `images` (≥1 file) — submit tanpa salah satunya MUST ditolak dengan error per field (FR-008) |
| Slug | Kosong → auto dari `title`; manual → dipakai apa adanya; duplikat antar proyek MUST ditolak (FR-006, FR-007) |
| Deskripsi | `description` via rich text editor, disimpan HTML (FR-005) |
| Galeri | `images` multiple + reorderable; tiap file diproses saat simpan → disimpan `.webp`, lebar ≤1200px (downscale bila lebih besar, tanpa upscale) (FR-010, FR-010b); form menampilkan helper rekomendasi ukuran (FR-010a); urutan file MUST tersimpan (FR-010, edge case reorder) |
| URL proyek | `project_url` opsional; bila diisi MUST URL absolut http/https — nilai tak valid MUST ditolak (FR-009) |
| Opsional lain | `client_name`, `completed_at` opsional — kosong MUST tetap valid |
| Urutan / Aktif | `order` integer default 0; `is_active` boolean default true, dapat di-toggle (FR-011, FR-012) |
| Hapus | MUST minta konfirmasi (FR-013); setelah dihapus URL detail MUST 404 |
| Efek simpan ke publik | Create/update/delete/toggle/reorder MUST tercermin di `/portfolio` & `/portfolio/{slug}` pada request berikutnya tanpa deploy ulang (FR-014) |

## 3. Routing & Halaman Publik (User Story 3)

| Aspek | Kontrak |
|---|---|
| Listing | `GET /portfolio` — proyek `is_active=true`, urут `order` asc lalu `id` asc; per kartu: gambar sampul (`images[0]`), judul, nama kategori (FR-016) |
| Filter kategori | `GET /portfolio?kategori={slug-kategori}` — hanya proyek aktif kategori tsb; kondisi filter tercermin di URL sehingga bisa dibagikan (FR-017); slug kategori tidak dikenal → filter diabaikan (tampilkan semua), tanpa error mentah (edge case) |
| Empty-state | `/portfolio` (atau kategori terfilter) tanpa proyek aktif MUST HTTP 200 + pesan "Belum ada proyek" — BUKAN 404 (FR-018) |
| Detail | `GET /portfolio/{slug}` untuk proyek aktif → 200, menampilkan judul, kategori, semua gambar galeri (urут), deskripsi HTML, dan — bila diisi — `client_name`, `completed_at` (format tanggal lokal), `project_url` sebagai tautan `target="_blank" rel="noopener noreferrer nofollow"` (FR-019) |
| Detail — field opsional kosong | MUST tidak render label/section untuk `client_name`/`completed_at`/`project_url` yang tidak diisi (FR-021) |
| Detail — 404 | `GET /portfolio/{slug}` untuk proyek `is_active=false` atau slug tidak ada MUST 404 (FR-020) |
| Navigasi/footer | TIDAK diubah oleh fitur ini (di luar scope inti — spec Assumptions) |
