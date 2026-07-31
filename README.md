# Simetri — Laravel + Filament Company Profile Starter Kit

Simetri adalah starter kit **company profile** berbasis **Laravel + Filament** yang dirancang untuk dipakai ulang (reusable) di berbagai proyek klien. Tujuannya mempercepat pembuatan website company profile dengan modul konten yang sudah jadi, admin panel yang di-white-label per klien, dan sistem tema yang bisa disesuaikan tanpa perlu full page builder.

Referensi awal dikembangkan dari [`superduper-filament-starter-kit`](https://github.com), lalu di-upgrade dengan modul dan tooling tambahan.

## Fitur Utama (Rencana)

### Konten Company Profile
- **Services** — CRUD layanan/produk (icon, deskripsi, gambar)
- **Portfolio/Project Showcase** — gallery, kategori, tautan proyek
- **Team Members** — foto, jabatan, bio singkat, social link
- **Testimonials** — nama, perusahaan, foto, rating
- **Client Logos** — logo strip perusahaan partner/klien
- **Career/Lowongan Kerja** — opsional, toggle aktif per klien
- **Blog/Artikel** — kategori, tag, featured image, draft/publish
- **Banner Management** — gambar, link, urutan, periode tayang
- **Menu Builder** — navigasi navbar/footer dinamis, multi-lokasi
- **Contact Us** — form + notifikasi email/WA + resource di admin panel
- **Custom Page** — halaman statis (About Us, TnC, Privacy Policy) dengan rich text editor + slug

### Admin Panel & Autentikasi
- Filament v3 sebagai panel admin dasar
- Filament Shield untuk role & permission (Super Admin, Editor, Viewer)
- White-labeling panel (nama, logo, favicon, brand color)
- Dark/light mode switching + halaman profile admin (Filament Breezy)
- Activity log & audit trail admin (spatie/laravel-activitylog + filament-logger)
- Media manager berbasis folder (tomatophp/filament-media-manager)
- Widget Google Analytics di dashboard admin

### Theme & Branding System
- Settings-driven theme (warna, font, logo) via Spatie Settings
- CSS variable-based theming (Tailwind config dinamis)
- Varian komponen Blade per section (hero, about, dll) yang bisa dipilih per klien
- Live preview theme di admin panel (nice-to-have)

### SEO & Performance
- SEO meta global & per halaman/konten (meta title, description, OG image)
- Sitemap.xml & robots.txt otomatis
- Optimasi performa (caching, lazy load gambar, asset bundling)

### Deployment & Client Setup Tooling
- Artisan command `app:setup-client` untuk generate `.env`, set nama app, generate key, clear cache
- Seeder dummy content demo (services, team, testimonials, portfolio) untuk showcase ke calon klien
- Strategi git/versioning lintas klien (template repo + upstream remote atau composer package private)
- Dokumentasi deployment lengkap (requirement server, langkah deploy, checklist go-live)

## Tech Stack

- **Backend**: Laravel 11
- **Admin Panel**: Filament v3 + Filament Shield, Filament Breezy
- **Package pendukung**: Spatie Media Library, Spatie Settings, Spatie Tags, Spatie Activity Log
- **Frontend**: Tailwind CSS (dengan CSS variable-based theming)

## Roadmap (Epics)

| Epic | Deskripsi |
|------|-----------|
| 1. Setup Fondasi Proyek | Inisialisasi project Laravel + Filament: repo, environment, dependensi wajib |
| 2. Autentikasi, Otorisasi & White-labeling Panel | Role & permission dasar + white-labeling admin panel |
| 3. Modul Konten Company Profile | Services, Portfolio, Team, Testimonials, Client Logos, Career, Blog |
| 4. Theme & Branding System | Settings-driven theme + varian section Blade per klien |
| 5. SEO & Performance | SEO meta, sitemap otomatis, optimasi performa frontend |
| 6. Deployment & Client Setup Tooling | Installer/seeder demo, strategi versioning lintas klien, dokumentasi deploy |
| 7. QA, Dokumentasi & Rilis | Testing menyeluruh, dokumentasi teknis & user manual, rilis v1.0 |

Progres detail tugas dapat dilihat di [Jira board "Simetri" (SIM)](https://murdiantokops.atlassian.net/jira/software/projects/SIM/boards/34/backlog).

## License

Proyek ini dibangun di atas [Laravel](https://laravel.com), yang open-sourced di bawah [MIT license](https://opensource.org/licenses/MIT).
