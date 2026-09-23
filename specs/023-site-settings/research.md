# Research: Site Settings

**Feature**: 023-site-settings | **Date**: 2026-09-23 | **Spec**: [spec.md](./spec.md)

Seluruh keputusan di bawah diambil dari pemeriksaan kode dan paket yang benar-benar terpasang di repositori ini, bukan asumsi.

---

## R0. Koreksi cakupan: jenis halaman publik yang benar-benar ada

**Temuan**: Penelusuran `php artisan route:list --except-vendor` menunjukkan situs ini **tidak** memiliki halaman daftar per kategori, halaman tag, halaman hasil pencarian, maupun halaman penulis. Rute publik yang ada hanya: `/`, `/artikel`, `/artikel/{slug}`, `/produk`, `/produk/{slug}`, `/portfolio`, `/portfolio/{slug}`, `/halaman/{slug}`, `/faq`, `/karir`, `/kontak`, `/tentang-kami`, `/robots.txt`, `/sitemap.xml`.

**Decision**: Pola judul per jenis halaman dan pilihan isi peta situs hanya mencakup jenis halaman di atas. FR-026 dan FR-038 sudah dikoreksi di spec.

**Rationale**: Menyediakan pengaturan untuk halaman yang tidak ada berarti membangun abstraksi spekulatif yang dilarang Prinsip V konstitusi, sekaligus membuat admin melihat field yang tidak berpengaruh apa pun — bertentangan langsung dengan FR-059.

**Alternatives considered**: Menyalin seluruh jenis halaman dari produk rujukan (ditolak: tiga di antaranya tidak punya tempat berlabuh di situs ini). Membuat halaman kategori sekalian (ditolak: fitur tersendiri, memperbesar cakupan yang sudah besar).

**Catatan**: `Article` memang memakai `HasTags` dari `spatie/laravel-tags`, tetapi tidak ada rute publik yang menampilkan daftar artikel per tag — jadi tag tetap di luar cakupan.

---

## R1. Penyimpanan pengaturan

**Decision**: Tetap memakai `spatie/laravel-settings` v3.9 yang sudah dipakai, dengan **lima kelas settings** yang memetakan satu-satu ke lima halaman admin: `SiteSettings`, `AppearanceSettings`, `SeoSettings`, `ScriptSettings`, `SocialSettings`.

**Rationale**: Sudah menjadi fondasi tema di project ini dan diwajibkan Prinsip III konstitusi ("Settings-Driven Theming"). Memecah per halaman membuat tiap halaman admin menyimpan sendiri tanpa saling menimpa (FR-070), dan menjaga tiap kelas tetap kecil.

**Alternatives considered**: Satu kelas settings raksasa (ditolak: satu simpan menulis ulang seluruh nilai, dan bertabrakan dengan FR-070). Tabel Eloquent baru (ditolak: menambah infrastruktur tanpa alasan, melanggar Prinsip V).

---

## R2. Migrasi otomatis nilai Brand Settings (FR-063, FR-068)

**Decision**: Memakai settings migration dengan `$this->migrator->rename('brand.x', 'grup_baru.x')` untuk memindahkan nilai lintas grup, lalu menghapus grup `brand` setelah kosong.

**Rationale**: `SettingsMigrator::rename()` (diperiksa di `vendor/spatie/laravel-settings/src/Migrations/SettingsMigrator.php:33`) membuat properti tujuan dengan **membawa payload nilai lama** lalu menghapus properti asal. Ini memenuhi FR-068 tanpa kode migrasi manual, dan bekerja lintas grup karena penanda properti berbentuk `grup.properti`.

**Peta perpindahan**:

| Dari (`brand.*`) | Ke | Alasan |
| --- | --- | --- |
| `app_name` | `site.site_name` | Identitas situs (FR-001) |
| `meta_description` | `seo.default_meta_description` | Default SEO (FR-029 dst.) |
| `og_image_path` | `social.default_share_image_path` | Gambar berbagi default (FR-024) |
| `logo_path` | `appearance.logo_path` | Tampilan (FR-071) |
| `favicon_path` | `appearance.favicon_path` | Tampilan (FR-071) |
| `primary_color` | `appearance.primary_color` | Tampilan (FR-071) |
| `secondary_color` | `appearance.secondary_color` | Tampilan (FR-071) |
| `font_heading` | `appearance.font_heading` | Tampilan (FR-071) |
| `font_body` | `appearance.font_body` | Tampilan (FR-071) |
| `whatsapp_number` | `site.whatsapp_number` | Setelan operasional (FR-072) |
| `contact_notification_email` | `site.contact_notification_email` | Setelan operasional (FR-072) |
| `career_module_enabled` | `site.career_module_enabled` | Sakelar modul (FR-072) |

**Peringatan implementasi**: `rename()` melempar `SettingDoesNotExist` bila properti asal tidak ada. Karena settings migration berjalan berurutan dan migration pembuat `brand.*` sudah ada lebih dulu, instalasi baru maupun lama sama-sama aman. Tetap bungkus dengan pemeriksaan agar migration idempoten bila dijalankan pada basis data yang sudah sebagian berpindah.

**Alternatives considered**: Membiarkan `BrandSettings` sebagai lapisan pembaca lama (ditolak: menyisakan dua tempat, melanggar FR-064). Menyalin nilai lewat seeder (ditolak: tidak berjalan otomatis saat pembaruan).

---

## R3. Pembatasan akses super admin (FR-045)

**Decision**: Halaman Scripts & Analytics memakai `public static function canAccess(): bool` dan `shouldRegisterNavigation()` yang memeriksa `auth()->user()?->hasRole('super_admin')`.

**Rationale**: `bezhansalleh/filament-shield` v3.9 sudah terpasang dan `config/filament-shield.php:23` mendefinisikan peran `super_admin`. Pola persis ini sudah dipakai di `app/Providers/Filament/AdminPanelProvider.php:84` untuk membatasi Activity Log, jadi mengikutinya menjaga konsistensi dan tidak menambah mekanisme baru.

**Alternatives considered**: Policy Filament Shield per halaman (ditolak: menambah permission generatif yang harus disinkronkan tiap deploy klien, sementara aturannya cukup satu peran). Middleware rute (ditolak: tidak menyembunyikan item navigasi).

---

## R4. Editor kode untuk skrip dan CSS

**Decision**: Memakai `Textarea` Filament dengan gaya monospace dan `rows` besar, **tanpa menambah paket editor kode**.

**Rationale**: Tidak ada paket editor kode di `composer.json`, dan CLAUDE.md melarang mengubah dependensi tanpa persetujuan; Prinsip V konstitusi juga menuntut disiplin dependensi. Penyorotan sintaks tidak memengaruhi satu pun requirement fungsional — seluruh FR soal skrip berbicara tentang isi yang tersimpan dan posisi pemuatannya.

**Alternatives considered**: Paket seperti editor kode Filament pihak ketiga (ditunda: memerlukan persetujuan pemilik produk dan audit lisensi untuk penjualan ulang komersial). Bila kelak disetujui, penggantinya hanya menyentuh lapisan form, bukan penyimpanan maupun perenderan.

---

## R5. Mode pemeliharaan (FR-010 sampai FR-014)

**Decision**: Middleware sendiri yang dipasang pada grup rute publik, membaca `SiteSettings::maintenance_mode`, mengembalikan view pemeliharaan dengan status **503** dan header `Retry-After`. Panel admin tidak memakai middleware ini, dan pengguna yang terautentikasi dilewatkan.

**Rationale**: `php artisan down` bawaan Laravel tidak dapat dinyalakan dari panel admin oleh admin non-teknis dan mematikan seluruh aplikasi termasuk panel — bertentangan dengan FR-013. Status 503 memenuhi FR-012 karena memberi tahu mesin pencari bahwa kondisi ini sementara.

**Alternatives considered**: `php artisan down` dipanggil dari panel (ditolak: mematikan panel itu sendiri, dan menulis file penanda yang tidak selalu dapat ditulis di shared hosting cPanel — lihat spec 021).

---

## R6. Halaman kesalahan (FR-015 sampai FR-017)

**Decision**: Membuat `resources/views/errors/404.blade.php` dan `resources/views/errors/500.blade.php` yang membaca pesan dari `SiteSettings`, memakai layout publik yang sudah ada.

**Rationale**: Laravel otomatis memakai view pada jalur tersebut tanpa konfigurasi tambahan. Saat ini direktori `resources/views/errors/` belum ada sama sekali, jadi ini murni penambahan tanpa risiko regresi.

**Peringatan implementasi**: View `500` dirender saat aplikasi sedang bermasalah. Pembacaan pengaturan di dalamnya harus tahan gagal — bila basis data tidak dapat dihubungi, halaman tetap harus tampil dengan pesan bawaan, bukan melempar kesalahan kedua.

---

## R7. Penyuntikan skrip dan gaya (FR-041 sampai FR-049)

**Decision**: Menambahkan titik pemuatan di `resources/views/layouts/public.blade.php` memakai `{!! !!}` pada empat posisi, ditambah blok `<style>` untuk CSS khusus di `<head>` dan `<script>` untuk JS khusus sebelum `</body>`. Panel admin tidak disentuh sama sekali (FR-046).

**Rationale**: Isi slot memang kode yang harus dimuat apa adanya (FR-044); meng-escape-nya membuat fitur ini tidak berfungsi. Batas keamanannya adalah pembatasan peran pada R3, bukan penyaringan isi — dan ini dinyatakan eksplisit di Assumptions spec.

**Peringatan implementasi**: `{!! !!}` di sini adalah keputusan sadar, bukan kelalaian. Setiap titik pemuatan wajib diberi komentar yang menyebut FR-044 dan FR-045 agar peninjau kode berikutnya tidak "memperbaikinya" menjadi `{{ }}` dan mematikan fitur.

---

## R8. Persetujuan cookie (FR-050 sampai FR-058, FR-073 sampai FR-075)

**Decision**: Alpine.js yang sudah dipakai di project ini (`x-data` pada header) ditambah `localStorage`, tanpa paket baru. Skrip yang terikat kategori dimuat sebagai `<script type="text/plain" data-consent-category="...">` lalu diaktifkan setelah kategori disetujui.

**Rationale**: Pola `type="text/plain"` adalah cara baku menahan eksekusi skrip sampai persetujuan diberikan — peramban tidak menjalankan tipe yang tidak dikenal. `localStorage` sejalan dengan keputusan klarifikasi bahwa persetujuan hanya diingat di perangkat pengunjung (FR-058), sehingga tidak ada data pribadi yang terkumpul.

**Alternatives considered**: Paket cookie consent pihak ketiga (ditolak: menambah dependensi untuk perilaku yang sederhana). Menahan skrip di sisi server berdasarkan cookie (ditolak: membuat setiap halaman punya dua kemungkinan keluaran sehingga merusak cache halaman, sementara keputusan pengunjung tetap harus dibaca di sisi peramban).

---

## R9. Pola judul halaman (FR-025 sampai FR-028)

**Decision**: Satu kelas penyusun judul yang menerima jenis halaman dan nilai isian, mengganti penanda `{page_title}`, `{site_name}`, `{separator}` beserta penanda khusus per jenis halaman, lalu membuang penanda tak dikenal dan merapikan pemisah serta spasi ganda.

**Rationale**: FR-027 mewajibkan penanda tak dikenal tidak bocor ke halaman, dan itu hanya dapat dijamin bila penggantian melewati satu jalur yang sama untuk semua halaman. Pengaturan SEO per konten yang sudah ada tetap menang (FR-028) dengan memeriksa `meta_title` konten lebih dulu sebelum menyusun dari pola.

**Catatan integrasi**: `app/Concerns/HasSeoMetadata.php` sudah menyediakan `meta_title` per konten dari spec 014. Kelas penyusun judul ini duduk **di bawahnya** sebagai lapisan default, bukan menggantikannya.

---

## R10. Aturan perayapan dan peta situs (FR-035 sampai FR-040)

**Decision**: `app/Http/Controllers/Public/SitemapController.php` diubah agar membaca `SeoSettings`. Isi `robots.txt` diambil dari pengaturan dengan penggantian penanda `{site_url}`; peta situs menyaring jenis konten sesuai sakelar dan mengembalikan 404 bila peta situs dimatikan.

**Rationale**: Isi `robots.txt` saat ini berupa string mati di `SitemapController::robots()`, dan daftar jenis konten dirangkai mati di `SitemapController::xml()` — keduanya persis yang harus dipindahkan ke pengaturan.

**Peringatan implementasi**: FR-040 menuntut keselarasan. Bila peta situs dimatikan, baris `Sitemap:` tidak boleh ikut tersaji di `robots.txt` meski admin menuliskannya.

---

## R11. Cache dan kesegeraan perubahan (FR-061)

**Decision**: Tidak menambah lapisan cache baru. `config/settings.php:70` menunjukkan cache settings mati secara bawaan (`SETTINGS_CACHE_ENABLED`, default `false`), dan paket ini membuang cache-nya sendiri saat nilai disimpan.

**Rationale**: FR-061 mewajibkan perubahan langsung terlihat tanpa pembersihan cache manual. Menambah cache sendiri justru menciptakan risiko itu.

**Catatan**: Header dan footer sudah membaca pengaturan dan menu pada setiap render. Fitur ini menambah pembacaan pengaturan, bukan kueri per baris, sehingga SC-009 (waktu muat tidak memburuk) tetap realistis tanpa cache tambahan.

---

## R12. Bahasa dan zona waktu (FR-005)

**Decision**: Nilai bahasa mengisi atribut bahasa dokumen pada layout publik, dan zona waktu menjadi acuan saat menampilkan tanggal di halaman publik. Tidak ada sistem penerjemahan konten.

**Rationale**: Ini batas cakupan yang sudah dinyatakan eksplisit di Assumptions spec. Mengubah nilai locale aplikasi saat runtime tidak menerjemahkan konten apa pun karena seluruh teks situs ditulis langsung dalam bahasa Indonesia.

---

## R13. Tombol berbagi (FR-022 sampai FR-024)

**Decision**: Satu komponen Blade yang menerima judul dan alamat konten, membaca `SocialSettings`, lalu merender hanya platform yang dipilih admin. Dipasang di halaman detail artikel dan produk.

**Rationale**: Komponen tunggal menjaga FR-023 dapat diuji dari satu tempat dan memudahkan pemasangan di halaman lain kelak tanpa menyalin markup.

**Alternatives considered**: Paket berbagi sosial (ditolak: tautan berbagi hanyalah URL berpola tetap, tidak sebanding dengan tambahan dependensi).
