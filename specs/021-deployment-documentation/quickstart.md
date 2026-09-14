# Quickstart: Deployment Documentation

**Feature**: 021-deployment-documentation

## Untuk developer/ops (memakai dokumen ini)

- Deploy ke VPS baru → `docs/deployment.md` bagian "Deploy ke VPS"
- Deploy ke shared hosting cPanel → `docs/deployment.md` bagian "Deploy ke Shared Hosting cPanel"
- Onboarding klien baru → setelah situs live, ikuti `docs/checklist-ga4-setup.md`
- Sebelum serah terima ke klien → `docs/checklist-go-live.md`

## Verifikasi manual (selama implementasi, per Independent Test di spec.md)

Sebagian besar verifikasi ini butuh VPS/akun hosting sungguhan — tidak sepenuhnya bisa disimulasikan lokal seperti fitur kode. Langkah yang **bisa** diverifikasi tanpa server sungguhan ditandai di bawah.

1. **US1 (VPS)** — *butuh VPS sungguhan, tindak lanjut tim*: sediakan VPS Ubuntu kosong, ikuti `docs/deployment.md` bagian VPS dari nol; verifikasi situs dapat diakses via HTTPS, kirim satu form kontak uji coba dan konfirmasi notifikasi terkirim (membuktikan queue worker + Supervisor berjalan), konfirmasi `activitylog:clean` muncul di log scheduler setelah cron berjalan minimal 1 menit.
2. **US2 (Shared Hosting cPanel)** — *butuh akun hosting sungguhan, tindak lanjut tim*: sediakan akun shared hosting cPanel baru, ikuti `docs/deployment.md` bagian cPanel; verifikasi situs dapat diakses (tanpa index kode ter-expose), kirim form kontak uji coba dan konfirmasi notifikasi terkirim dalam waktu wajar (via Cron Job `queue:work --stop-when-empty`, bukan proses persisten).
3. **US3 (GA4)** — *bisa diverifikasi begitu US1/US2 selesai*: ikuti `docs/checklist-ga4-setup.md` pada instalasi yang sudah live; verifikasi dashboard Google Analytics di admin panel menampilkan data (bukan pesan kosong/error) setelah traffic uji coba masuk.
4. **US4 (Go-live)** — **dapat diverifikasi sekarang, tanpa server**: telaah `docs/checklist-go-live.md` dan konfirmasi setiap butir dapat dijawab ya/tidak tanpa ambiguitas oleh pembaca yang belum terlibat penulisan dokumen — lakukan ini sebagai review internal sebelum dokumen dianggap selesai.
5. **Cross-check kebutuhan `gd`/ekstensi lain**: jalankan `php -m` pada environment PHP 8.3 mana pun yang tersedia dan konfirmasi daftar ekstensi di `docs/deployment.md` bagian Requirement Server sinkron dengan apa yang benar-benar dipakai kode (`grep -rn "extension_loaded\|Storage::disk\|Intervention" app/`), bukan sekadar disalin dari requirement Laravel generik.
