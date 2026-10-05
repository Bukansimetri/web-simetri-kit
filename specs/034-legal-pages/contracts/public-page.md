# Contract: Tampilan Publik Halaman Legal

## GET /halaman/{slug}

- **Template Standar**: markup sama persis dengan sebelum fitur ini.
- **Template Dokumen Legal**: 200 dengan susunan berikut (urutan DOM):
  1. Hero (`page-hero`): breadcrumb "Beranda / {judul}", judul, subjudul (bila ada). Tanpa label tanggal.
  2. Grid: sidebar (kiri di ≥ lg, di atas isi di < lg) dan isi.
     - **Sidebar**:
       - "Daftar Isi": satu tautan `#bagian-{n}` per bagian; tautan aktif ditandai saat menggulir.
       - Tombol PDF (`download`), hanya bila berkas ada.
       - Kotak kontak bila judul atau teks diisi: tombol WhatsApp (hanya bila nomor bisnis diisi di Pengaturan Umum) dan tautan email (bila diisi).
     - **Isi**:
       - Pembuka (rich text dibersihkan) dan kotak sorotan (bila judul atau isi diisi).
       - Setiap bagian `<section id="bagian-{n}">` berisi nomor `n`, label "PASAL 0n · {LABEL}" (bila label diisi), judul `<h2>`, isi (rich text dibersihkan), grid kartu (bila ada), dan catatan (bila ada).
  3. CTA penutup (bila judul diisi): judul, teks, tombol ke URL (default `/kontak`).
- Tidak ada `<script>`, atribut `on*`, atau `javascript:` dari isi admin.
- Tanpa gulir horizontal pada 360–1440 px.

## Footer

Tautan "Kebijakan Privasi" dan "Syarat & Ketentuan" (bawaan `/halaman/kebijakan-privasi`, `/halaman/syarat-ketentuan`) mengembalikan 200 setelah migrasi.
