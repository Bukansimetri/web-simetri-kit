---

description: "Task list for Deskripsi Lowongan Karir dengan Editor Teks Berformat"
---

# Tasks: Deskripsi Lowongan Karir dengan Editor Teks Berformat

**Input**: Design documents from `/specs/035-career-description-editor/`

**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/public-page.md, quickstart.md

**Tests**: Disertakan (Principle IV): admin, render publik, sanitizer, dan migrasi data.

**Organization**: Per user story (US1–US4, sesuai spec.md).

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Dapat paralel (berkas berbeda, tanpa ketergantungan pada tugas yang belum selesai)
- **[Story]**: US1–US4

---

## Phase 1: Setup

- [x] T001 Pastikan branch `035-career-description-editor`, lalu jalankan `php artisan test --compact` sebagai baseline

---

## Phase 2: Foundational (memblokir semua story)

- [x] T002 Ubah `app/Support/HtmlSanitizer.php`: tambah `h2`, `h3`, `h4`, `blockquote` ke `ALLOWED_TAGS`; atribut tetap disaring seperti sebelumnya
- [x] T003 [P] Tambah tes di `tests/Unit/HtmlSanitizerTest.php`: `h2`–`h4` dan `blockquote` dipertahankan; `<h2 onclick="x">` kehilangan atribut `onclick`; `<h1>`/`<pre>` tetap di-unwrap; tes lama tetap lulus
- [x] T004 Tambah method `descriptionExcerpt(?int $limit = null): string` di `app/Models/JobOpening.php` sesuai research R3: beri spasi pada penutup blok (`</p>`, `</li>`, `</h2>`–`</h4>`, `</blockquote>`, `<br>`), `strip_tags`, `html_entity_decode`, rapatkan spasi, `Str::limit` bila `$limit` diberikan

**Checkpoint**: Sanitizer dan ringkasan teks siap dipakai semua story.

---

## Phase 3: User Story 1 - Admin Merapikan Deskripsi Lowongan (Priority: P1) 🎯 MVP

**Goal**: Deskripsi diedit dengan editor teks berformat dan tampil rapi di halaman detail.

**Independent Test**: Buat lowongan lewat form dengan judul, daftar, tebal, dan tautan; buka halaman detail.

### Tests for User Story 1

- [x] T005 [P] [US1] Tambah tes di `tests/Feature/Admin/JobOpeningResourceTest.php`:
  - deskripsi berformat (`<h2>`, `<ul><li>`, `<strong>`, `<a>`) tersimpan apa adanya
  - `<p></p>` dan spasi ditolak sebagai kosong, error pada `description`
  - tes lama (`'description' => 'x'`, string kosong) tetap lulus
- [x] T006 [P] [US1] Buat `tests/Feature/Public/JobOpeningRenderTest.php`:
  - halaman detail menampilkan `<h2>`, `<ul>`, `<strong>`, dan tautan dari deskripsi berformat
  - kelas gaya prose ada di kontainer deskripsi
  - perubahan deskripsi langsung terlihat di detail dan halaman Karir (tanpa cache usang)
  - bagian lain halaman tidak berubah (hero, badge tipe/lokasi, tombol Lamar dan Kembali)

### Implementation for User Story 1

- [x] T007 [US1] Di `app/Filament/Resources/JobOpeningResource.php`, ganti `Textarea::make('description')` dengan `RichEditor::make('description')`:
  - label **Deskripsi**, `required()`, `columnSpanFull()`
  - `toolbarButtons(['h2', 'h3', 'bold', 'italic', 'underline', 'link', 'bulletList', 'orderedList', 'blockquote', 'undo', 'redo'])` tanpa lampiran
  - aturan validasi kustom: setelah `strip_tags`, `html_entity_decode`, dan `trim` kosong → gagal dengan pesan "Deskripsi wajib diisi."
  - hapus import `Textarea` bila tak terpakai
- [x] T008 [US1] Di `resources/views/pages/karir/show.blade.php`, ganti baris deskripsi `whitespace-pre-line` dengan `{{ \App\Support\PageContent\PageContent::richText($job->description) }}` di dalam kontainer berkelas varian Tailwind (research R4): `[&_p]:mt-4 [&_p:first-child]:mt-0 [&_h2]:mt-8 [&_h2]:text-xl [&_h2]:font-bold [&_h2]:text-on-surface [&_h3]:mt-6 [&_h3]:text-lg [&_h3]:font-bold [&_h3]:text-on-surface [&_ul]:mt-4 [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:space-y-2 [&_ol]:mt-4 [&_ol]:list-decimal [&_ol]:pl-5 [&_ol]:space-y-2 [&_strong]:text-on-surface [&_a]:text-primary [&_a]:underline [&_blockquote]:mt-4 [&_blockquote]:border-l-4 [&_blockquote]:border-outline-variant [&_blockquote]:pl-4 [&_blockquote]:italic`

**Checkpoint**: Admin dapat menulis deskripsi berformat dan detail menampilkannya.

---

## Phase 4: User Story 2 - Lowongan Lama Tetap Tampil Benar (Priority: P1)

**Goal**: Teks polos lama dikonversi sehingga tampilan publik sama dan editor menampilkannya utuh.

**Independent Test**: Isi lowongan teks polos berbaris-baris, jalankan migrasi, lalu cek detail dan editor.

### Tests for User Story 2

- [x] T009 [P] [US2] Buat `tests/Feature/Database/JobDescriptionConversionTest.php` (jalankan konversi lewat kelas konverter, bukan lewat migrasi ulang):
  - teks dengan baris kosong → beberapa `<p>`; newline tunggal → `<br>`
  - `<`, `>`, `&` di-escape dan tampil sebagai teks biasa di halaman detail
  - `\r\n` dinormalisasi; teks satu baris → satu `<p>`; baris kosong ganda tidak menambah paragraf kosong
  - baris yang sudah HTML (`<p>…</p>`, `<ul>…`) tidak diubah; konversi dua kali sama dengan sekali
  - lowongan hasil konversi tampil di detail dengan paragraf dan jeda baris setara tampilan lama
  - di form admin, `assertFormSet(['description' => …])` memuat HTML hasil konversi utuh dan dapat disimpan ulang tanpa kehilangan teks

### Implementation for User Story 2

- [x] T010 [US2] Buat `app/Support/PageContent/JobDescriptionConverter.php` dengan `toHtml(string $text): string` (aturan di data-model.md: normalisasi newline, deteksi sudah-HTML lewat tag blok `<p`, `<ul`, `<ol`, `<h2`–`<h4`, `<blockquote`, `<br`, escape dengan `e()`, pecah paragraf pada baris kosong, `nl2br` di dalam paragraf)
- [x] T011 [US2] Buat migrasi data `php artisan make:migration convert_job_opening_descriptions_to_html --no-interaction`: lewati bila tabel `job_openings` tidak ada; ubah tiap baris lewat `DB::table` + `JobDescriptionConverter`; panggil pembuangan cache publik sekali di akhir (pakai mekanisme `CachesPublicPages` yang ada); `down()` kosong

**Checkpoint**: Lowongan lama aman di situs dan di editor.

---

## Phase 5: User Story 3 - Ringkasan Kartu Tetap Rapi (Priority: P2)

**Goal**: Kartu dan meta memakai teks polos ringkas.

**Independent Test**: Lowongan dengan deskripsi berformat panjang; periksa kartu dan meta.

### Tests for User Story 3

- [x] T012 [P] [US3] Tambah tes di `tests/Feature/Public/JobOpeningRenderTest.php` dan unit untuk model:
  - `descriptionExcerpt()` memisahkan kata antar paragraf/butir/judul dengan satu spasi, tanpa tag, entitas didekode, `$limit` dihormati
  - halaman Karir: kartu memuat ringkasan tanpa `<h2>`, `<ul>`, `<strong>` dan memakai `line-clamp-2`
  - `meta name="description"` di detail berupa teks polos ≤ 155 karakter tanpa tanda format

### Implementation for User Story 3

- [x] T013 [P] [US3] Di `resources/views/components/sections/job-card.blade.php`, ganti `{{ $job->description }}` dengan `{{ $job->descriptionExcerpt() }}`
- [x] T014 [US3] Di `resources/views/pages/karir/show.blade.php`, ganti `@section('meta_description', …)` agar memakai `$job->descriptionExcerpt(155)`

**Checkpoint**: Kartu dan meta bersih dari tanda format.

---

## Phase 6: User Story 4 - Konten Aman (Priority: P1)

**Goal**: Tidak ada skrip atau tautan berbahaya dari deskripsi yang lolos ke halaman publik.

**Independent Test**: Simpan deskripsi berisi skrip, atribut peristiwa, dan `javascript:`; buka detail.

### Tests for User Story 4

- [x] T015 [P] [US4] Tambah tes di `tests/Feature/Public/JobOpeningRenderTest.php`:
  - `<script>alert(1)</script>`, `<img onerror>`, `<p onclick>`, `<a href="javascript:…">` dari data yang disimpan langsung (tanpa lewat form) tidak muncul di halaman detail
  - `<iframe>` dan `<style>` tidak lolos
  - format wajar (`h2`, `ul`, `strong`, `em`, `a https`, `blockquote`) dipertahankan
  - kartu dan meta tidak memuat HTML mentah dari deskripsi

### Implementation for User Story 4

- [x] T016 [US4] Verifikasi tidak ada jalur render deskripsi yang melewati `PageContent::richText` (`grep -rn "->description" resources/views` untuk lowongan); perbaiki bila ada. Kartu dan meta sudah memakai `descriptionExcerpt()` yang menghapus tag

**Checkpoint**: Semua jalur render aman.

---

## Phase 7: Polish

- [x] T017 [P] Ubah `database/seeders/JobOpeningSeeder.php` agar deskripsi contoh berupa HTML (`<p>…</p>`); pastikan seeder tetap idempoten
- [x] T018 [P] Perbarui `docs/manual-operator.md` bagian Karir: kolom **Deskripsi** kini editor teks berformat (judul, daftar, tebal, tautan), lowongan lama dikonversi otomatis saat pembaruan; tanpa tanda kutip balik dan kata terlarang (ikuti `OperatorManualTest`)
- [x] T019 Jalankan `vendor/bin/pint --dirty --format agent`
- [x] T020 Jalankan `php artisan test --compact`; semua lulus (periksa tes lowongan lain: `CareerPageTest`, `JobOpeningDetailTest`, `CareerModuleToggleTest`, `TypographyConsistencyTest`, `LegacyMarkup`)
- [x] T021 Jalankan `npm run build`, lalu verifikasi visual dengan database sementara (hanya migrasi):
  - detail lowongan berformat dan lowongan lama di 360 dan 1440 px, tanpa gulir horizontal
  - kartu di halaman Karir seragam
  - form admin: editor, toolbar terbatas, validasi kosong

---

## Dependencies & Execution Order

- Phase 1 → Phase 2 (T002–T004) memblokir semua story.
- **US1** setelah Phase 2. **US2** bebas dari US1 (berkas berbeda), tetapi tes tampil lowongan lama memakai view US1 (T008).
- **US3** setelah T004 dan T008 (berkas `show.blade.php` yang sama: T008 → T014).
- **US4** setelah T008 dan T013.
- **Berkas bersama (berurutan)**:
  - `show.blade.php`: T008 → T014
  - `JobOpeningRenderTest.php`: T006 → T012 → T015
- **Paralel**: T003; T005/T006/T009; T010 dengan T007/T008; T013; T017/T018.

### Contoh Paralel

```text
T003 HtmlSanitizerTest    T005 JobOpeningResourceTest    T006 JobOpeningRenderTest    T009 JobDescriptionConversionTest
T007 JobOpeningResource   T010 JobDescriptionConverter   T013 job-card.blade.php
```

## Implementation Strategy

1. **MVP**: Phase 1–2 + US1 + US2. Admin menulis deskripsi berformat dan lowongan lama tidak rusak. US2 wajib ikut rilis yang sama dengan US1 karena editor akan menampilkan teks lama.
2. Lalu US3 (kartu dan meta) dan US4 (verifikasi keamanan), lalu Polish.
3. Commit per story.

## Notes

- Jangan merilis T007 tanpa T011: tanpa konversi, lowongan lama tampak rusak di editor.
- Perubahan sanitizer (T002) berlaku untuk semua pemanggil `richText`; T020 memastikan tak ada regresi.
