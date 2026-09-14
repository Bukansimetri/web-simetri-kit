# Contract: `app:setup-client` CLI Command

**Feature**: 018-setup-client-command

Fitur ini adalah tooling command-line, bukan API/HTTP — kontraknya adalah signature command, argumen/opsi, kode keluar (exit code), dan efek samping pada filesystem/cache.

## Signature

```
php artisan app:setup-client {name : Nama aplikasi untuk klien ini} {--force : Timpa .env dan APP_KEY yang sudah ada}
```

## Input

| Argumen/Opsi | Wajib | Deskripsi |
|---|---|---|
| `name` | Ya | Nama aplikasi klien, dituliskan ke `APP_NAME` di `.env`. Tidak boleh kosong/hanya spasi (FR-002). |
| `--force` | Tidak | Jika diberikan, menimpa `.env` yang sudah ada dari `.env.example` DAN men-generate ulang `APP_KEY` meski sudah ada (FR-006, FR-007). |

## Perilaku & Exit Code

| Kondisi | Perilaku | Exit Code |
|---|---|---|
| `.env` belum ada | Disalin dari `.env.example`, lalu `APP_NAME` di-set ke `name` | — |
| `.env` sudah ada, tanpa `--force` | Seluruh langkah (salin ulang dari template maupun penyetelan `APP_NAME`) dilewati — `.env` yang sudah ada TIDAK diubah sama sekali (US3), pesan info ditampilkan | — |
| `.env` sudah ada, dengan `--force` | `.env` ditimpa ulang dari `.env.example`, lalu `APP_NAME` di-set ke `name` | — |
| `APP_KEY` belum terisi | `key:generate` dijalankan | — |
| `APP_KEY` sudah terisi, tanpa `--force` | Langkah generate key dilewati, pesan info ditampilkan | — |
| `APP_KEY` sudah terisi, dengan `--force` | `key:generate --force` dijalankan | — |
| `.env.example` tidak ditemukan (dan `.env` juga belum ada) | Command berhenti sebelum mengubah apa pun | `1` (gagal) |
| `name` kosong/hanya spasi | Command menolak, tidak ada perubahan apa pun dilakukan | `1` (gagal) |
| Setiap eksekusi yang berhasil sampai akhir | `config:clear`, `route:clear`, `view:clear`, `cache:clear` dijalankan tanpa syarat | `0` (sukses) |

## Output

Command MUST menampilkan, per langkah, salah satu dari: `✔ dibuat`, `- dilewati (sudah ada, gunakan --force untuk menimpa)`, atau `✘ gagal: <alasan>` — sehingga ringkasan akhir (FR-008) dapat dibaca operator tanpa perlu membuka file `.env` secara manual untuk memverifikasi.

## Idempotency

Menjalankan command berkali-kali dengan `name` yang sama dan tanpa `--force` MUST menghasilkan efek akhir yang sama pada `.env`/`APP_KEY` seperti menjalankannya sekali (FR-006, FR-007, SC-003) — hanya cache yang selalu dibersihkan ulang pada setiap eksekusi.
