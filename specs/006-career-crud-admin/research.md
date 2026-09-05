# Research: Career/Lowongan Kerja CRUD Admin + Toggle Modul

**Date**: 2026-09-05 | **Feature**: [spec.md](./spec.md)

## 1. Tipe pekerjaan: konstanta PHP tetap, bukan entity/taxonomy baru

**Decision**: Tambahkan `JobOpening::EMPLOYMENT_TYPES` (array asosiatif `value => label`, mis. `'full-time' => 'Full-time'`, `'part-time' => 'Part-time'`, `'contract' => 'Kontrak'`, `'internship' => 'Magang'`) sebagai konstanta di model, dipakai langsung sebagai `options()` untuk `Select::make('employment_type')` di form Filament. Kolom `employment_type` tetap `string` (skema existing tidak berubah) — validasi lewat `Rule::in(array_keys(JobOpening::EMPLOYMENT_TYPES))`.

**Rationale**: Keputusan eksplisit dari klarifikasi (Q1=A). Pola identik `BrandSettings::FONT_OPTIONS` yang sudah ada di codebase ini (dipakai sebagai daftar pilihan tetap untuk `Select::make('font_heading')` di `BrandSettingsPage`) — konsisten dan tidak menambah dependency/tabel baru (Principle V).

**Alternatives considered**: Entity `EmploymentType` terpisah dengan CRUD sendiri (mirip `ArticleCategory`) — ditolak, daftar tipe pekerjaan adalah istilah industri yang stabil dan jarang berubah, CRUD tambahan untuk 4 nilai tetap adalah over-engineering (Principle V, juga eksplisit di constitution: "Avoid speculative abstractions").

## 2. Toggle modul Karir: field boolean baru di `BrandSettings` (spatie/laravel-settings)

**Decision**: Tambahkan property `career_module_enabled` (bool) ke `App\Settings\BrandSettings` lewat migration baru di `database/settings/` (pola `SettingsMigration` yang sudah dipakai untuk `whatsapp_number`, dll.), default `true`. Ditampilkan sebagai `Toggle::make('career_module_enabled')` di `BrandSettingsPage` (halaman Filament yang sudah ada) — bukan halaman Settings baru.

**Rationale**: Keputusan eksplisit dari klarifikasi (Q2=A). `BrandSettings` sudah jadi tempat kanonik untuk semua pengaturan global instalasi (nama app, logo, warna, WhatsApp, email notifikasi) — menambah satu boolean baru di sana jauh lebih sederhana daripada membuat model/tabel/halaman Settings baru untuk kasus satu toggle (Principle V). Pola migration settings (`SettingsMigration` + `$this->migrator->add(...)`) sudah mapan di project ini, tinggal diikuti.

**Alternatives considered**: Halaman Settings modul terpisah (mis. "Module Settings" yang menampung toggle untuk Karir dan modul opsional lain di masa depan) — ditolak untuk v1, karena saat ini hanya SATU modul yang butuh toggle; membuat abstraksi generik "module registry" sebelum ada kebutuhan konkret kedua adalah spekulasi yang dilarang constitution ("build for the modules that exist today, not hypothetical future requirements").

## 3. Penegakan toggle: middleware/guard di route `/karir`, bukan pengecekan manual di tiap tempat

**Decision**: `CareerController@__invoke` memeriksa `app(BrandSettings::class)->career_module_enabled` di awal method dan memanggil `abort(404)` bila `false`, sebelum query `JobOpening` dijalankan. Link "Karir" di `resources/views/components/layout/footer.blade.php` dibungkus `@if (app(\App\Settings\BrandSettings::class)->career_module_enabled)` mengelilingi entri array `footerColumns` terkait (atau di-filter dari array sebelum di-loop).

**Rationale**: Konsisten dengan pola existing di project ini — `ArticleController`/`ProductController` melakukan pengecekan visibilitas langsung di controller (bukan middleware terpisah) karena aturannya spesifik satu route. Menambah middleware generik "module gate" untuk SATU route adalah abstraksi berlebih untuk kasus sesederhana ini (Principle V).

**Alternatives considered**: Middleware `EnsureCareerModuleEnabled` terdaftar di route `/karir` — dipertimbangkan (lebih "clean" secara arsitektur), tapi ditolak karena menambah satu file/lapisan untuk logika satu baris yang sudah pas ditulis langsung di controller, mengikuti prinsip simplicity yang sama dipakai di seluruh fitur-fitur sebelumnya (003, 004, 005) yang tidak memakai middleware kustom untuk logika serupa.

## 4. Admin CRUD tetap dapat diakses terlepas dari status toggle (FR-013)

**Decision**: `JobOpeningResource` (Filament) TIDAK memeriksa `career_module_enabled` sama sekali — resource selalu terdaftar dan dapat diakses oleh siapa pun yang punya akses panel admin, independen dari status toggle publik.

**Rationale**: FR-013 eksplisit meminta ini — admin harus tetap bisa menyiapkan/mengelola konten lowongan kapan saja, termasuk sebelum modul diaktifkan untuk publik. Tidak ada logika tambahan yang diperlukan di sisi admin sama sekali — toggle murni memengaruhi lapisan publik (controller + footer), sesuai cakupan minimal fitur ini.

**Alternatives considered**: Menyembunyikan menu navigasi "Lowongan Kerja" dari sidebar admin saat modul nonaktif — ditolak, bertentangan langsung dengan FR-013 dan edge case yang sudah didokumentasikan di spec ("supaya admin bisa menyiapkan konten karir dulu sebelum modul diaktifkan").
