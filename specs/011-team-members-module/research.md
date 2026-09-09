# Research: Modul Team Members

**Date**: 2026-09-08 | **Feature**: [spec.md](./spec.md)

## 1. Entity `TeamMember` baru — pola `Testimonial` (008), tanpa relasi

**Decision**: Tabel `team_members` baru: `id`, `name` (string, wajib), `position` (string, wajib — jabatan), `photo_path` (string, wajib), `bio` (text, wajib), `linkedin_url` (string nullable), `order` (integer default 0), `is_active` (boolean default true), timestamps. Tidak ada relasi, grouping, atau halaman detail.

**Rationale**: Struktur minimal yang memenuhi FR-002. `is_active` + `order` meniru `Testimonial` yang sudah terbukti. `bio` sebagai `text` (bukan longText/HTML) karena teks biasa (Assumptions). `linkedin_url` satu kolom nullable (klarifikasi Q1).

**Alternatives considered**: Repeater multi-platform `{platform, url}` — ditolak klarifikasi Q1 (satu field LinkedIn cukup, Principle V). Kolom `department`/`group` — ditolak Assumptions (satu daftar flat v1). `bio` rich text — ditolak Assumptions (deskripsi singkat cukup textarea).

## 2. Foto wajib + resize 800px + WebP: `FileUpload` + `ImageUploads::storeAsWebp(maxWidth: 800)`

**Decision**: `FileUpload::make('photo_path')->image()->required()->disk('public')->directory('team')->acceptedFileTypes(['image/png','image/jpeg','image/webp'])->helperText('Wajib. Rekomendasi 800×800px (potret). Gambar besar otomatis dikecilkan ke lebar 800px & dikonversi ke WebP.')->saveUploadedFileUsing(fn ($file) => ImageUploads::storeAsWebp($file, 'team', maxWidth: 800))`. Di Blade, guard `Storage::disk('public')->exists($member->photo_path)` — bila file hilang, tampilkan placeholder inisial (huruf pertama nama) alih-alih gambar rusak.

**Rationale**: `ImageUploads::storeAsWebp` sudah punya parameter `maxWidth` (ditambahkan di modul 010) — nol perubahan helper. Batas 800px cukup untuk foto potret kepala/bahu di kartu tim (biasanya ≤400px tampil), menghemat storage. Foto wajib (klarifikasi Q3) — beda dari Testimonials. `acceptedFileTypes` mengarahkan ke raster (GD tidak resize SVG).

**Alternatives considered**: 1200px seperti Portfolio — kelebihan untuk foto potret kecil; 800px lebih tepat. Foto opsional dengan fallback inisial seperti Testimonials — ditolak klarifikasi Q3.

## 3. `linkedin_url` opsional: validasi http/https, boleh kosong

**Decision**: `TextInput::make('linkedin_url')->url()->nullable()->maxLength(255)->rule('starts_with:http://,https://')->helperText('Opsional. URL profil LinkedIn lengkap (https://...).')`. Kosong → tersimpan `null`, tanpa error (FR-003, FR-004). Di Blade: `@if($member->linkedin_url)` → render ikon LinkedIn sebagai `<a href="..." target="_blank" rel="noopener noreferrer nofollow">`.

**Rationale**: Rule `url` + `starts_with` menegakkan URL absolut (FR-004, edge case "linkedin.com/in/nama" ditolak). `nullable` memastikan submit tanpa nilai lolos (permintaan eksplisit user: "pastikan bisa di isi kosong"). Pola sama `project_url` di modul 010 & `link_url` di modul 009.

**Alternatives considered**: Auto-prefix `https://` bila skema hilang — ditolak, menebak intent; tolak dengan pesan jelas lebih aman.

## 4. Urutan tampil: kolom `order` integer + `defaultSort`, tie-break `id`

**Decision**: Kolom `order` (integer, default 0). Form `TextInput::make('order')->numeric()->default(0)`. Tabel admin `->defaultSort('order')`. Query publik `->orderBy('order')->orderBy('id')` — deterministik saat `order` sama (FR-006, edge case).

**Rationale**: Identik pola `Testimonial`/`ClientLogo`. Tie-break `id` = urutan sekunder stabil.

**Alternatives considered**: Drag-reorder plugin — ditolak (dependency, Principle V).

## 5. Render di halaman Tentang Kami: section component setelah "Nilai-Nilai Kami", data via `AboutController`

**Decision**: `AboutController::__invoke` (sejak modul 008 & 009 mengirim `$testimonials` + `$clientLogos`) ditambah `$teamMembers = TeamMember::query()->where('is_active', true)->orderBy('order')->orderBy('id')->get();` dan dikirim ke view sebagai `teamMembers`. Buat `resources/views/components/sections/team-members.blade.php` (anonymous component, `@props(['members'])`), membungkus output dalam `@if($members->isNotEmpty())`. Di `tentang-kami.blade.php`, sisipkan `<x-sections.team-members :members="$teamMembers" />` tepat setelah penutup `</section>` blok `{{-- Nilai --}}` dan sebelum `<x-sections.testimonials :testimonials="$testimonials" />`.

**Rationale**: Konsisten penuh dengan modul 008 & 009 (folder, pola component, controller-level query, empty-state di dalam component). Penempatan setelah "Nilai-Nilai Kami" sesuai klarifikasi Q2 — alur naratif: nilai perusahaan → orang di baliknya → bukti sosial (testimoni, logo).

**Alternatives considered**: View Composer global — ditolak, hanya satu halaman butuh. Query di Blade `@php` — ditolak, konsisten `AboutController`.

## 6. Kartu tim: foto + nama + jabatan + bio + ikon LinkedIn

**Decision**: Grid responsif (`grid-cols-2 md:grid-cols-3 lg:grid-cols-4`), per kartu: foto dalam bingkai `aspect-square object-cover rounded-lg` (atau placeholder inisial bila file hilang), nama (`font-headline`), jabatan (`text-secondary`), bio (`text-sm text-on-surface-variant`), dan bila `linkedin_url` ada — ikon LinkedIn kecil sebagai tautan. Match gaya `components/sections/testimonials.blade.php` & palet halaman Tentang Kami.

**Rationale**: Foto potret persegi konsisten meski rasio sumber beda (edge case). Ikon LinkedIn sebagai satu-satunya sosial (bukan baris ikon) — sederhana.

**Alternatives considered**: Foto lingkaran vs persegi — pilihan visual, persegi lebih netral untuk berbagai rasio; final di implementasi. Lightbox bio — ditolak Assumptions.

## 7. Akses CRUD: tanpa policy (semua role panel)

**Decision**: `TeamMemberResource` tanpa policy/`canAccess()` — mengikuti `TestimonialResource`, `ClientLogoResource`, dll (FR-012).

**Rationale**: Konsistensi; spec eksplisit. Pembatasan role = AMC-203 terpisah.
