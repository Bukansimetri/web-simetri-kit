# Implementation Plan: Site Settings

**Branch**: `023-site-settings` | **Date**: 2026-09-23 | **Spec**: [spec.md](./spec.md)

**Input**: Feature specification from `/specs/023-site-settings/spec.md`

## Summary

Memindahkan seluruh identitas situs, SEO, skrip pihak ketiga, dan media sosial dari kode ke pengaturan yang dapat diubah admin — dan memastikan setiap nilai benar-benar berpengaruh pada keluaran halaman publik, dibuktikan feature test (FR-059, FR-060).

Pendekatan teknis: memecah `BrandSettings` yang sekarang mencampur tiga urusan menjadi **lima kelas settings** yang memetakan satu-satu ke lima halaman admin, memindahkan nilai lama secara otomatis lewat `SettingsMigrator::rename()` sehingga instalasi yang sudah tayang tidak kehilangan apa pun (FR-068), lalu menyambungkan tiap pengaturan ke titik keluaran yang sudah ada: footer, header, kepala dokumen, `robots.txt`, `sitemap.xml`, halaman kesalahan, dan halaman pemeliharaan. Tidak ada dependensi baru.

## Technical Context

**Language/Version**: PHP 8.3

**Primary Dependencies**: Laravel 13.8, Filament 3.2, spatie/laravel-settings 3.9, bezhansalleh/filament-shield 3.9, Alpine.js + Tailwind (via Vite). **Tidak ada dependensi baru** — seluruh keputusan riset menolak penambahan paket (research.md R4, R8, R13).

**Storage**: Tabel settings milik spatie/laravel-settings untuk seluruh pengaturan; `localStorage` peramban untuk persetujuan cookie pengunjung (FR-058 — tidak ada catatan di sisi server).

**Testing**: PHPUnit 12 feature test. Setiap pengaturan wajib punya test yang memeriksa keluaran halaman publik, bukan sekadar nilai tersimpan (FR-060, Prinsip IV konstitusi).

**Target Platform**: Web — VPS maupun shared hosting cPanel (mengikuti spec 021). Konsekuensi: mode pemeliharaan tidak boleh bergantung pada penulisan file penanda (research.md R5).

**Project Type**: Monolit Laravel di root repositori.

**Performance Goals**: Waktu muat halaman publik tidak memburuk secara terukur dibanding sebelum fitur ini saat seluruh pengaturan terisi wajar (SC-009).

**Constraints**: Perubahan pengaturan harus langsung terlihat tanpa pembersihan cache manual (FR-061). Nilai instalasi yang sudah tayang tidak boleh hilang maupun mengubah tampilan (FR-068, FR-069). Setiap pengaturan hanya boleh punya satu tempat pengubahan (FR-064).

**Scale/Scope**: 5 halaman pengaturan admin, 75 functional requirement, 9 user story, 12 jenis halaman publik. **40 berkas** saat ini merujuk `BrandSettings` dan seluruhnya harus ikut dipindahkan.

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Prinsip | Status | Catatan |
| --- | --- | --- |
| I. Multi-Client Reusability | **PASS** | Fitur ini justru memperbaiki pelanggaran yang ada sekarang: alamat, email, dan telepon klien lain tertulis mati di footer. FR-062 mewajibkan seluruh pengaturan dapat diubah per instalasi tanpa perubahan kode. |
| II. White-Label by Default | **PASS** | FR-009 mewajibkan tidak ada lagi data identitas klien mana pun sebagai nilai tertulis di kode, dan FR-008 mewajibkan nilai bawaan yang netral. |
| III. Settings-Driven Theming, No Page Builder | **PASS dengan catatan** | Tidak ada kemampuan menyusun tata letak secara bebas. Slot skrip dan CSS khusus adalah titik pemasangan alat pihak ketiga, bukan primitif penyusun halaman — lihat Complexity Tracking, karena ini keputusan batas yang harus diterima sadar oleh peninjau. |
| IV. Module Test Coverage | **PASS** | FR-060 menjadikan feature test syarat selesai untuk setiap titik penerapan, bukan tambahan opsional. |
| V. Simplicity & Dependency Discipline | **PASS** | Nol dependensi baru. Empat peluang menambah paket (editor kode, cookie consent, tombol berbagi, penahan skrip) seluruhnya ditolak di research.md dengan alasan tertulis. R0 juga membuang tiga jenis halaman spekulatif yang tidak ada di situs ini. |
| Deployment & Client Setup Standards | **PERLU TINDAK LANJUT** | Menambah lima halaman pengaturan membuat dokumentasi deployment dan checklist go-live dari spec 021 menjadi usang. Pembaruan dokumen tersebut adalah bagian dari definisi selesai, bukan pekerjaan terpisah. |

**Hasil gate (sebelum Phase 0)**: Lolos. Satu catatan batas dicatat di Complexity Tracking, satu tindak lanjut dokumentasi dicatat sebagai bagian definisi selesai.

**Pemeriksaan ulang (setelah Phase 1 design)**: Lolos, tanpa pelanggaran baru. Desain akhir tidak menambah satu pun dependensi, tidak menambah tabel, dan tidak menambah folder dasar baru. Phase 0 justru **mengurangi** cakupan lewat R0 — tiga jenis halaman yang tidak ada di situs ini dibuang dari FR-026 dan FR-038, sehingga Prinsip V terpenuhi lebih baik daripada saat spec ditulis. Dua baris di Complexity Tracking tetap berlaku dan tidak bertambah.

## Project Structure

### Documentation (this feature)

```text
specs/023-site-settings/
├── plan.md              # Berkas ini
├── research.md          # Phase 0 — 14 keputusan teknis beserta alternatif yang ditolak
├── data-model.md        # Phase 1 — lima kelas settings beserta field dan aturan
├── quickstart.md        # Phase 1 — cara memverifikasi fitur ini secara manual
├── contracts/           # Phase 1 — kontrak keluaran publik
│   ├── settings-application-contract.md
│   └── consent-gating-contract.md
├── checklists/
│   └── requirements.md  # Checklist kualitas spec (seluruh item lolos)
└── tasks.md             # Phase 2 — dibuat /speckit-tasks, BUKAN oleh /speckit-plan
```

### Source Code (repository root)

```text
app/
├── Settings/
│   ├── SiteSettings.php              # BARU — identitas, perusahaan, regional, legal, error, pemeliharaan, operasional
│   ├── AppearanceSettings.php        # BARU — warna, font, logo, favicon
│   ├── SeoSettings.php               # BARU — pola judul, meta, pengindeksan, verifikasi, robots, sitemap
│   ├── ScriptSettings.php            # BARU — empat slot kode, CSS/JS khusus, persetujuan cookie
│   ├── SocialSettings.php            # BARU — profil, tombol berbagi, gambar berbagi default
│   └── BrandSettings.php             # DIHAPUS setelah seluruh rujukan berpindah
├── Filament/Pages/
│   ├── SiteSettingsPage.php          # BARU
│   ├── AppearanceSettingsPage.php    # BARU
│   ├── SeoSettingsPage.php           # BARU
│   ├── ScriptSettingsPage.php        # BARU — dibatasi super_admin
│   ├── SocialSettingsPage.php        # BARU
│   └── BrandSettingsPage.php         # DIHAPUS
├── Http/Middleware/
│   └── MaintenanceMode.php           # BARU
├── Support/Seo/
│   ├── JsonLd.php                    # ADA — sumber pindah ke SeoSettings
│   └── PageTitle.php                 # BARU — penyusun judul berbasis pola
├── Http/Controllers/Public/
│   └── SitemapController.php         # DIUBAH — robots.txt & sitemap dari pengaturan
└── Providers/Filament/
    └── AdminPanelProvider.php        # DIUBAH — branding panel dari AppearanceSettings

database/settings/
├── ..._create_site_settings.php
├── ..._create_appearance_settings.php
├── ..._create_seo_settings.php
├── ..._create_script_settings.php
├── ..._create_social_settings.php
└── ..._move_brand_settings_to_new_groups.php   # rename lintas grup (FR-068)

resources/views/
├── layouts/
│   ├── public.blade.php              # DIUBAH — lang, slot skrip, CSS/JS khusus, bilah persetujuan
│   └── partials/
│       ├── og-meta.blade.php         # DIUBAH — sumber SeoSettings/SocialSettings
│       ├── head-extra.blade.php      # BARU — meta robots, verifikasi situs, meta tambahan
│       └── theme-vars.blade.php      # DIUBAH — sumber AppearanceSettings
├── components/layout/
│   ├── header.blade.php              # DIUBAH — ikon sosial dari pengaturan
│   ├── footer.blade.php              # DIUBAH — kontak, legal, tautan pengaturan cookie
│   ├── social-share.blade.php        # BARU
│   └── cookie-consent.blade.php      # BARU
├── errors/
│   ├── 404.blade.php                 # BARU
│   └── 500.blade.php                 # BARU
└── maintenance.blade.php             # BARU

tests/Feature/
├── Settings/                         # Per halaman pengaturan, termasuk penolakan akses non-super-admin
├── Public/                           # Pembuktian penerapan ke keluaran halaman publik (FR-060)
└── Settings/BrandSettingsMigrationTest.php   # BARU — nilai lama berpindah utuh (FR-068, FR-069)
```

**Structure Decision**: Mengikuti struktur monolit Laravel yang sudah ada tanpa menambah folder dasar baru. Satu kelas settings per halaman admin agar tiap halaman dapat disimpan sendiri tanpa menimpa nilai halaman lain (FR-070), dan agar tiap kelas tetap kecil dan mudah diuji.

## Urutan Pengerjaan

Spec ini besar (75 FR). Urutan berikut menjaga agar setiap tahap meninggalkan repositori dalam keadaan hijau dan dapat dirilis.

| Tahap | Isi | Alasan urutan |
| --- | --- | --- |
| **0. Fondasi** | Lima kelas settings, settings migration beserta `rename`, pembaruan **40 berkas** perujuk `BrandSettings`, penghapusan `BrandSettings` dan halamannya, lima halaman admin kosong | Memblokir seluruh story lain. Tidak menambah fitur apa pun — sukses tahap ini berarti seluruh test yang ada tetap hijau dan tampilan situs tidak berubah sama sekali (FR-069). |
| **1. P1** | US1 identitas & kontak, US2 ikon sosial, US3 slot skrip + CSS/JS khusus | Nilai terbesar: menghapus data klien lain dari situs, memperbaiki ikon bertautan mati, dan menjawab permintaan awal pemasangan kode pelacakan. |
| **2. P2** | US4 SEO lanjutan, US5 robots & sitemap, US6 persetujuan cookie | US6 bergantung pada slot skrip dari US3, karena yang ditahan persetujuan adalah slot tersebut. |
| **3. P3** | US7 mode pemeliharaan, US8 halaman kesalahan, US9 tombol berbagi | Paling jarang dipakai; aman ditunda tanpa menghalangi serah terima klien. |
| **4. Penutup** | Pembaruan dokumentasi deployment dan checklist go-live spec 021 | Diwajibkan Deployment Standards konstitusi; modul tak terdokumentasi dianggap belum selesai. |

Tahap 0 adalah bagian paling berisiko karena menyentuh 40 berkas tanpa memberi fitur baru. Tahap ini harus mendarat sebagai perubahan tersendiri yang terbukti tidak mengubah perilaku, bukan dicampur dengan tahap 1.

## Complexity Tracking

> Diisi karena ada satu keputusan batas yang harus diterima sadar oleh peninjau.

| Violation | Why Needed | Simpler Alternative Rejected Because |
| --- | --- | --- |
| Slot CSS dan JavaScript khusus memberi keleluasaan bebas pada tampilan, yang bersinggungan dengan larangan "primitif penyusun bebas" pada Prinsip III | Klien rutin meminta pemasangan pixel iklan, Tag Manager, dan penyesuaian tampilan kecil. Tanpa slot ini, setiap permintaan semacam itu menjadi pekerjaan pengembangan dan rilis ulang — persis beban yang ingin dihilangkan starter kit ini (Prinsip I). | Menyediakan varian section bernama untuk tiap permintaan ditolak karena permintaan klien di sini bukan soal tata letak melainkan penyisipan alat pihak ketiga, yang tidak mungkin diantisipasi sebagai varian. Batasnya dijaga dengan cara lain: slot ini tidak menyusun tata letak, tidak tersedia bagi admin biasa (FR-045), dan tidak dimuat di panel admin (FR-046). |
| Keluaran kode mentah tanpa escaping pada enam titik di layout publik | FR-044 menuntut isi slot dimuat apa adanya sebagai kode; meng-escape-nya membuat seluruh fitur tidak berfungsi. | Penyaringan atau daftar-putih isi ditolak karena tidak ada cara menyaring kode pihak ketiga sembarang tanpa merusaknya. Pengaman yang dipakai adalah pembatasan peran super admin dan batas ukuran (FR-045, FR-048), dan keputusan ini dinyatakan eksplisit di Assumptions spec agar tidak terbaca sebagai kelalaian. |
