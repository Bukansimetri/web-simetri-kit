# Checklist: Go-Live

Jalankan checklist ini sebagai gerbang terakhir sebelum menyerahkan instalasi ke klien sebagai situs produksi resmi — berlaku sama baik instalasi di-deploy ke VPS maupun shared hosting cPanel. Setiap butir harus bisa dijawab **Ya** atau **Tidak** berdasarkan kondisi instalasi saat ini — bukan perkiraan atau "sepertinya sudah".

## Blocker — WAJIB "Ya" sebelum go-live

- [ ] **Domain & DNS** — Domain klien sudah mengarah ke server yang benar (A record ke IP VPS, atau domain sudah di-point ke nameserver/hosting cPanel tsb), dan situs dapat diakses lewat domain final (bukan hanya lewat IP/subdomain sementara).
- [ ] **HTTPS aktif** — Situs dapat diakses lewat `https://`, sertifikat valid (tidak ada peringatan browser), dan mekanisme perpanjangan otomatis sudah dikonfirmasi aktif (Certbot auto-renew di VPS, atau AutoSSL di cPanel — lihat `docs/deployment.md`).
- [ ] **Mode produksi aktif** — `.env` berisi `APP_ENV=production` dan `APP_DEBUG=false`. ❌ Bila `APP_DEBUG=true` di produksi, informasi sensitif (stack trace, path server, kredensial di error page) bisa bocor ke publik — ini blocker keras, tidak boleh ditunda.
- [ ] **`APP_URL` sesuai domain final** — Bukan `localhost` atau domain sementara/staging.
- [ ] **Queue & scheduler terverifikasi jalan** — Kirim satu form kontak uji coba dan konfirmasi notifikasi benar-benar terkirim (membuktikan queue worker/Cron Job berfungsi, bukan cuma "sudah dikonfigurasi tapi belum dites").
- [ ] **Backup database terjadwal aktif** — Ada mekanisme backup berkala (mis. cron `mysqldump` di VPS, atau fitur backup otomatis cPanel) yang sudah dikonfirmasi berjalan, bukan hanya direncanakan.
- [ ] **Site Settings sudah diisi milik klien** — Kelima halaman pengaturan (Pengaturan Umum, Tampilan, SEO, Media Sosial, Scripts & Analytics — menu Settings di panel admin) sudah diisi dengan data klien, bukan lagi nilai bawaan kosong. Minimal: nama situs, informasi perusahaan (email/telepon/alamat), dan logo. ❌ Footer dan halaman Kontak akan tampil kosong/tidak lengkap bila bagian ini dilewati — periksa langsung di halaman publik, bukan hanya di form admin.

## Setelah seluruh butir Blocker bercentang

Situs siap diserahkan ke klien sebagai produksi resmi. Dokumentasikan tanggal go-live dan siapa yang menjalankan checklist ini untuk instalasi klien tsb.
