# Quickstart: Verifikasi Manual Contact Us Backend

**Feature**: [spec.md](./spec.md) | **Date**: 2026-09-05

## Prasyarat

- `php artisan migrate` sudah dijalankan (tabel `contact_submissions` baru).
- `php artisan queue:work` berjalan (atau `QUEUE_CONNECTION=sync` sementara untuk test lokal) supaya notifikasi email benar-benar terkirim/tercatat.
- `MAIL_MAILER=log` (default `.env.example`) — email notifikasi bisa dicek di `storage/logs/laravel.log` tanpa perlu SMTP sungguhan.

## US1 — Submit form Kontak (P1) 🎯 MVP

1. Buka `/kontak` tanpa login. Kosongkan field wajib, submit — verifikasi pesan validasi muncul tanpa reload halaman, dan tidak ada baris baru di tabel `contact_submissions`.
2. Isi form dengan data valid, submit — verifikasi konfirmasi sukses tampil, dan baris baru muncul di `contact_submissions` (cek lewat panel admin di US2 atau `php artisan tinker`).
3. Submit form yang sama berkali-kali dengan cepat (>5x dalam 1 menit) — verifikasi permintaan ke-6 dst. ditolak (429), bukan tersimpan sebagai submission baru.

## US2 — Admin kelola submission (P1)

1. Login sebagai admin, buka `/admin/contact-submissions`. Verifikasi submission dari US1 muncul dengan nama, kontak, topik, pesan, status "Baru".
2. Ubah status salah satu submission jadi "Sudah Dihubungi", simpan. Reload daftar, verifikasi status tersimpan.
3. Filter daftar berdasarkan status "Baru" — verifikasi hanya submission dengan status tsb yang tampil.
4. Hapus salah satu submission — verifikasi hilang dari daftar.

## US3 — Notifikasi email ke admin (P2)

1. Buka Brand Settings di admin (`/admin/brand-settings-page`), isi "Email Notifikasi Kontak" dengan alamat tes, simpan.
2. Dari sisi pengunjung, submit form Kontak dengan data valid.
3. Cek `storage/logs/laravel.log` (kalau `MAIL_MAILER=log`) atau inbox sungguhan (kalau SMTP dikonfigurasi) — verifikasi email notifikasi masuk berisi ringkasan submission (nama, kontak, topik, pesan) dalam waktu wajar.
4. Kosongkan kembali "Email Notifikasi Kontak", simpan. Submit form Kontak lagi — verifikasi submission tetap tersimpan & konfirmasi sukses tetap tampil, TANPA job notifikasi baru dikirim (edge case field kosong).

## WhatsApp redirect (bagian dari US1, FR-012/FR-013)

1. Di Brand Settings, isi "Nomor WhatsApp Bisnis" (mis. `6281234567890`), simpan.
2. Dari sisi pengunjung, submit form Kontak dengan data valid — verifikasi tab/jendela baru terbuka ke `wa.me` dengan nomor tsb dan pesan sudah terisi otomatis sesuai data form, SEKALIGUS konfirmasi sukses tetap tampil di halaman Kontak.
3. Kosongkan kembali "Nomor WhatsApp Bisnis", simpan. Submit form Kontak lagi — verifikasi TIDAK ada percobaan membuka WhatsApp, konfirmasi sukses tetap tampil normal (FR-013 edge case).
