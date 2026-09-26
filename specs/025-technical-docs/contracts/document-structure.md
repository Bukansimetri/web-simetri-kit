# Contract: Struktur Dokumen Teknis

Kontrak ini mendefinisikan bagian wajib tiap dokumen. Implementasi (tasks) dan review memakai kontrak ini sebagai acuan; test `tests/Feature/Docs/TechnicalDocsPathsTest.php` menjaga validitas path dan tautan.

## Aturan umum (semua dokumen)

- Bahasa Indonesia.
- Baris kedua setelah judul: `**Terakhir diperbarui**: YYYY-MM-DD`.
- Paragraf pembuka menyebut pembaca (developer yang mengerjakan atau meng-clone kit untuk klien baru) dan kapan dokumen ini dipakai.
- Path file repo yang *sudah ada* ditulis sebagai inline code dan MUST valid.
- Path file *hipotetis* (contoh) hanya boleh muncul di dalam fenced code block.
- Tidak menyalin isi `docs/deployment.md`, `docs/versioning-strategi-klien.md`, `docs/checklist-ga4-setup.md`, `docs/checklist-go-live.md`; cukup tautan relatif.

## `docs/arsitektur.md` (US1, FR-001 s.d. FR-005)

| Bagian | Isi minimum |
|---|---|
| Gambaran umum | Tujuan kit, stack dan versi aktual, prinsip constitution dalam satu kalimat masing-masing + tautan ke `.specify/memory/constitution.md`. |
| Lapisan & alur data | Diagram teks (ASCII atau Mermaid) alur: admin panel → model/pengaturan → cache → controller publik → view/section. Penjelasan cache dan aturan invalidasinya. |
| Peta direktori | Tabel folder penting → fungsi → contoh file. |
| Daftar modul konten | Tabel modul → model → resource admin → halaman/section publik → test → key cache yang diinvalidasi. |
| Pengaturan situs | Tabel kelas Settings → halaman admin → dipakai di mana. |
| Konvensi | Bahasa label admin, grup navigasi admin, nama route, kewajiban test per modul, format kode (Pint), white-label (`NoHardcodedClientDataTest`). |
| Hal yang perlu diperhatikan | Temuan sampingan dari research.md §4 dan jebakan umum. |
| Dokumen terkait | Tautan ke panduan section, panduan tema, dan dokumen `docs/` lainnya. |

## `docs/panduan-section.md` (US2, FR-006 s.d. FR-008, FR-011)

| Bagian | Isi minimum |
|---|---|
| Kapan pakai panduan ini | Menambah section baru atau varian section. |
| Batasan saat ini | AMC-221 (pemilih varian) ditunda; larangan page builder (Principle III). |
| Pilih sumber data | Kriteria: konten tunggal/sedikit & jarang berubah → pengaturan situs; daftar item yang dikelola admin (CRUD, urutan, aktif/nonaktif) → modul konten. |
| Langkah-langkah | (1) buat komponen di `resources/views/components/sections/`, (2) pakai class token tema, (3) hubungkan data lewat `@props` dari controller atau dari Settings, (4) pasang di halaman, (5) empty state, (6) cache & invalidasi, (7) test, (8) Pint & build aset. |
| Contoh lengkap: section "Sertifikasi" | Contoh berbasis pengaturan situs dari awal sampai test, semua path hipotetis di fenced code block. |
| Contoh modul nyata: Testimonials | Rujukan ke file-file Testimonials sebagai pola jalur modul konten. |
| Checklist selesai | Daftar cek sebelum PR. |

## `docs/panduan-tema.md` (US3, FR-009 s.d. FR-011)

| Bagian | Isi minimum |
|---|---|
| Tanpa kode (panel admin) | Apa saja yang bisa diatur admin di halaman Tampilan dan efeknya. |
| Alur token | Empat langkah dari research.md §5. |
| Menambah font | Dua tempat wajib (`FONT_OPTIONS` + `vite.config.js`), build ulang, cara cek. |
| Mengubah default tema | `DEFAULT_*` + fallback `:root` di `resources/css/app.css`. |
| Menambah token baru | Langkah lengkap dari research.md §5 dengan contoh token hipotetis di fenced code block. |
| Batasan saat ini | AMC-222 (live preview) ditunda; token turunan tidak diekspos ke admin. |
| Checklist selesai | Daftar cek sebelum PR. |

## README

Bagian baru "Dokumentasi Developer" yang menautkan ketiga dokumen di atas, diletakkan dekat tautan dokumen `docs/` yang sudah ada. Versi Laravel/Filament di bagian Tech Stack diperbarui ke versi aktual.
