# Research: Dashboard Prospek Admin

**Feature**: [spec.md](./spec.md) | **Plan**: [plan.md](./plan.md) | **Date**: 2026-09-26

Fakta di bawah diambil dari kode pada branch `027-leads-dashboard` (turunan `026-remove-ga-dashboard`, commit `d6ea048`).

## 1. Jenis widget untuk tiap bagian

- **Decision**:
  - Kartu statistik: `Filament\Widgets\StatsOverviewWidget` dengan empat `Stat`. `Stat` sudah mendukung `->url()`, `->chart()` (sparkline), `->description()`, dan `->color()`.
  - Tabel "Perlu ditindaklanjuti": widget Filament biasa (`Filament\Widgets\Widget`) dengan view Blade sendiri yang merender tabel dari koleksi gabungan.
  - Grafik mingguan: `Filament\Widgets\ChartWidget` bertipe `bar` dengan dua dataset.
- **Rationale**: Ketiganya bagian dari Filament 3.3 yang sudah terpasang, termasuk Chart.js yang dibundel Filament; tidak ada dependency baru (Principle V). `TableWidget` tidak dipilih untuk tabel karena ia membutuhkan satu query Eloquent, sedangkan data berasal dari dua tabel dengan id yang bisa bentrok. Menggabungkan 10 baris terbaru dari dua query di PHP jauh lebih sederhana daripada `UNION` Eloquent yang dibungkus `fromSub`, dan tabel ini tidak butuh sorting, pencarian, atau paginasi.
- **Alternatives considered**: `TableWidget` + `UNION` (ditolak: kunci record bentrok antar tabel, URL per baris berbeda resource, fitur tabel Filament tidak dipakai); dua `TableWidget` terpisah per sumber (ditolak: spec meminta satu daftar gabungan, FR-020).

## 2. Zona waktu

- **Decision**: Semua pengelompokan hari/minggu dan batas "30 hari", "90 hari", "12 minggu", serta umur prospek dihitung dengan zona waktu dari `app/Settings/SiteSettings.php` (properti `timezone`, default `Asia/Jakarta` dari migrasi `database/settings/2026_09_23_100000_create_site_settings.php`, bisa diubah admin di Pengaturan Umum).
- **Rationale**: `config('app.timezone')` adalah `UTC`, dan middleware `app/Http/Middleware/SetApplicationTimezone.php` sengaja hanya dipasang di grup `web` publik, tidak di panel admin. Memakai `SiteSettings::timezone` langsung di logika dashboard mengikuti pilihan admin tanpa mengubah perilaku panel lain, dan memenuhi Principle I (tidak hardcode `Asia/Jakarta`).
- **Alternatives considered**: hardcode `Asia/Jakarta` (ditolak: melanggar Principle I untuk klien di zona lain); memasang `SetApplicationTimezone` di panel admin (ditolak: mengubah tampilan tanggal di seluruh panel, di luar scope); `CONVERT_TZ` di SQL (ditolak: butuh tabel zona waktu MySQL terisi, dan test memakai SQLite).

## 3. Cara menghitung data berkala

- **Decision**: Untuk sparkline 30 hari dan grafik 12 minggu, ambil hanya kolom `created_at` dari kedua tabel dalam rentang waktu yang relevan (batas bawah dihitung di zona waktu situs lalu dikonversi ke UTC untuk query), lalu kelompokkan di PHP per tanggal/minggu lokal. Semua slot hari/minggu diisi 0 lebih dulu sehingga slot kosong tetap tampil (FR-032).
- **Rationale**: Portabel antara MySQL (produksi) dan SQLite (test), tidak bergantung pada fungsi tanggal SQL tertentu, dan cukup cepat: 12 minggu data pada skala SC-005 (5.000 prospek total) hanya beberapa ribu timestamp. `calculator_leads` sudah punya indeks `created_at` dan `status`.
- **Alternatives considered**: `GROUP BY DATE(...)` di SQL (ditolak: sintaks beda MySQL/SQLite dan tetap salah zona waktu); paket trend seperti `flowframe/laravel-trend` (ditolak: dependency baru untuk kebutuhan kecil, Principle V).
- **Catatan indeks**: `contact_submissions` belum punya indeks `status`/`created_at`. Pada skala SC-005 hitungan tetap cepat, jadi tidak ditambahkan di fitur ini. Tambahkan bila volume jauh lebih besar.

## 4. Logika bersama

- **Decision**: Satu kelas `app/Services/LeadDashboardMetrics.php` berisi perhitungan yang dipakai widget: jumlah baru per sumber, sparkline 30 hari, konversi 90 hari, 10 prospek terbaru gabungan, dan seri 12 minggu. Widget hanya memanggil kelas ini dan memformat hasilnya.
- **Rationale**: Tiga widget berbagi aturan zona waktu dan definisi "prospek"; menaruhnya di satu tempat membuat angka konsisten dan bisa diuji tanpa merender widget. Mengikuti pola `app/Services/SavingsEstimator.php`.
- **Alternatives considered**: logika langsung di tiap widget (ditolak: aturan zona waktu terduplikasi tiga kali dan sulit diuji).

## 5. Tautan ke daftar terfilter

- **Decision**: Kartu dan tautan tabel memakai `ContactSubmissionResource::getUrl('index', ['tableFilters' => ['status' => ['value' => 'new']]])` dan yang setara untuk `CalculatorLeadResource`. Baris tabel memakai `getUrl('edit', ['record' => $id])` pada resource sumbernya (kedua resource hanya punya halaman `index` dan `edit`).
- **Rationale**: `ListRecords` di Filament 3.3 menyimpan `$tableFilters` sebagai properti `#[Url]`, sehingga filter bisa diisi dari query string. Kedua resource sudah punya `SelectFilter::make('status')`, jadi filter terlihat jelas di UI sebagai "Status: Baru" dan bisa dihapus admin.
- **Alternatives considered**: filter toggle `belum_dihubungi` / `belum_follow_up` (ditolak: nama berbeda per resource; filter status lebih seragam).

## 6. Hak akses (FR-003)

- **Decision**: Setiap widget meng-override `canView()` menjadi `ContactSubmissionResource::canViewAny() && CalculatorLeadResource::canViewAny()`.
- **Rationale**: Mengikuti akses menu sumbernya. Saat ini kedua resource tidak punya policy, sehingga semua pengguna panel (yang punya role, lihat `User::canAccessPanel()`) bisa melihatnya. Jika kelak policy ditambahkan (misal lewat Shield), dashboard otomatis ikut terbatas tanpa perubahan kode.
- **Alternatives considered**: permission widget Shield (ditolak: menambah langkah konfigurasi per role dan bisa tidak sinkron dengan akses menu lead).

## 7. Interaksi dengan Filament Shield

- **Decision**: Judul widget (`getHeading()`) berupa string tetap tanpa query. Widget bawaan `AccountWidget` dan `FilamentInfoWidget` dihapus dari `->widgets([...])` di `app/Providers/Filament/AdminPanelProvider.php`; entri pengecualiannya di `config/filament-shield.php` dibiarkan (tidak berbahaya).
- **Rationale**: Konfigurasi Shield `entities.widgets = true`, dan halaman edit Role memanggil `getHeading()` tiap widget untuk label permission (akar masalah error 500 sebelumnya). Heading tanpa query mencegah regresi; `tests/Feature/Admin/RoleResourceEditTest.php` tetap menjaga halaman itu.

## 8. Konversi (FR-013)

- **Decision**: `won / total` untuk lead kalkulator dengan `created_at` dalam 90 hari terakhir (zona waktu situs), dibulatkan 1 desimal; `null` bila total 0, ditampilkan "Belum ada data". Deskripsi kartu menampilkan angka mentah, misal "1 dari 10 lead (90 hari)".
- **Rationale**: Sesuai spec; menampilkan pembilang/penyebut membuat persentase tidak menyesatkan saat datanya sedikit.
