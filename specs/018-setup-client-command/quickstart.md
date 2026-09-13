# Quickstart: Setup Client Command

**Feature**: 018-setup-client-command

## Untuk developer/ops (provisioning klien baru)

```bash
git clone <repo-template> client-acme
cd client-acme
composer install
php artisan app:setup-client "PT Acme Sejahtera"
```

Setelah selesai:
- `.env` sudah dibuat dari `.env.example` dengan `APP_NAME="PT Acme Sejahtera"`.
- `APP_KEY` sudah ter-generate.
- Cache config/route/view/application sudah bersih.

Lanjutkan langkah standar Laravel lain sesuai kebutuhan (mis. `php artisan migrate`, `php artisan db:seed`) — di luar cakupan command ini.

## Menjalankan ulang dengan aman

```bash
php artisan app:setup-client "Nama Baru"
```

Jika `.env`/`APP_KEY` sudah ada, command melewati langkah tersebut (tidak menimpa) dan tetap membersihkan cache — aman dijalankan berkali-kali.

## Menimpa instalasi yang sudah ada secara sengaja

```bash
php artisan app:setup-client "Nama Baru" --force
```

## Verifikasi manual (developer, selama implementasi)

```bash
php artisan test --compact tests/Feature/Console/SetupClientCommandTest.php
```

1. Di direktori tanpa `.env`: jalankan command, konfirmasi `.env` tercipta dengan `APP_NAME` sesuai argumen dan `APP_KEY` terisi.
2. Jalankan lagi tanpa `--force`: konfirmasi `.env`/`APP_KEY` yang sama tidak berubah (bandingkan isi file sebelum/sesudah).
3. Jalankan dengan `--force`: konfirmasi `APP_KEY` berganti nilai.
4. Jalankan dengan argumen nama kosong (`""`): konfirmasi command gagal (exit code bukan 0) tanpa mengubah `.env`.
