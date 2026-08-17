# Quickstart: Rapikan Epic 2 - Auth, White-labeling & Audit Trail

**Feature**: [spec.md](./spec.md) | **Date**: 2026-08-16

## Setup dependency baru

```bash
composer require spatie/laravel-activitylog:^4.12 rmsramos/activitylog:^2.0
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-config"
php artisan migrate
```

## Verifikasi widget GA (User Story 1)

```bash
php artisan route:list --path=admin | grep -i analytics
```

Pastikan `config/analytics.php` sudah terisi (`ANALYTICS_PROPERTY_ID` dan file service account di `storage/app/analytics/service-account-credentials.json`). Buka `/admin` — widget Visitors/Pageviews/Top Pages/Top Referrers harus tampil. Kosongkan `ANALYTICS_PROPERTY_ID` untuk menguji FR-002 (pesan informatif, bukan error mentah).

## Verifikasi white-labeling (User Story 2)

Buka halaman Settings baru di admin panel, isi nama brand, upload logo/favicon, pilih warna primer, simpan. Reload `/admin` dan verifikasi sidebar/judul/favicon/warna berubah sesuai input, tanpa restart server atau `php artisan config:clear`.

## Verifikasi activity log (User Story 3)

```bash
php artisan tinker --execute 'auth()->loginUsingId(1); \App\Models\User::first()->update(["name" => "Test Audit"]);'
```

Login sebagai Super Admin, buka halaman activity log, cari entri terbaru dan pastikan nilai before/after "Test Audit" tercatat. Login sebagai role non-Super Admin dan pastikan halaman tersebut tidak bisa diakses.

## Menjalankan test

```bash
php artisan test --compact --filter=GoogleAnalyticsWidgetTest
php artisan test --compact --filter=BrandSettingsTest
php artisan test --compact --filter=ActivityLogAccessTest
```
