# Strategi Versioning Lintas Klien

**Status**: Diputuskan — lihat [ADR di bawah](#keputusan) sebelum membaca prosedur.

Dokumen ini adalah rujukan tunggal untuk cara Web Solarpanel Kit (Simetri) didistribusikan ke setiap instalasi klien, dan cara instalasi klien menerima pembaruan dari Simetri di kemudian hari. Wajib dibaca **sebelum** memotong repositori klien pertama.

## Keputusan

**Strategi yang dipakai: `git clone` + remote `upstream`.**

Setiap klien mendapat repositori Git-nya sendiri, dibuat dengan **meng-clone langsung** repo Simetri (bukan lewat fitur "Use this template" GitHub), lalu remote asal di-rename jadi `upstream` dan repo klien di-push ke remote GitHub baru miliknya sendiri. Karena dibuat lewat `clone`, repo klien punya **histori git bersama** (common ancestor) dengan Simetri sejak awal — ini krusial agar penerimaan pembaruan di kemudian hari berjalan sebagai merge Git yang benar.

**Kenapa bukan fitur "Use this template" GitHub?** Sempat dicoba lebih dulu, tapi verifikasi manual (lihat `specs/020-client-versioning-strategy/research.md` §6) menemukan cacat serius: repo hasil "Use this template" punya histori git yang **independen** (tidak ada common ancestor dengan Simetri). Akibatnya, sinkronisasi pembaruan pertama (`git merge --allow-unrelated-histories`) menandai **SEMUA file yang berbeda isi sebagai konflik** — termasuk file yang hanya diubah di salah satu sisi dan seharusnya bisa masuk otomatis tanpa masalah. Pada repo sungguhan (ratusan file), ini berarti puluhan/ratusan "konflik" palsu yang membingungkan. Mencoba menambalnya dengan flag seperti `-X ours` ternyata lebih berbahaya lagi: flag itu **diam-diam membuang** perubahan sah di sisi yang kalah, walau file tsb tidak pernah disentuh developer. `git clone` biasa tidak punya masalah ini sama sekali — sudah diverifikasi.

**Kenapa bukan Composer package private?** Simetri adalah aplikasi Laravel utuh (routes, migration, Filament Resource, Blade view) — bukan library yang dirancang untuk di-`composer require`. Mengubahnya jadi package berarti merestrukturisasi hampir seluruh aplikasi menjadi `ServiceProvider` yang mem-publish resource ke aplikasi "host", plus butuh infrastruktur registry Composer privat. Kompleksitas ini tidak sepadan dengan manfaatnya dibanding memakai kapabilitas Git yang sudah ada. Detail pertimbangan lengkap: `specs/020-client-versioning-strategy/research.md` §1.

## 1. Membuat repositori klien baru

1. Buat satu repositori GitHub baru yang **kosong** untuk klien tsb — nama mengikuti konvensi `client-<nama-klien>` (mis. `client-acme`), visibilitas **Private** kecuali disepakati lain. **Jangan** centang opsi auto-generate README/`.gitignore`/lisensi saat membuatnya (repo harus benar-benar kosong, karena isi akan datang dari `git push` di langkah 4).
2. Clone Simetri sebagai titik awal:
   ```bash
   git clone https://github.com/Bukansimetri/web-simetri-kit.git client-acme
   cd client-acme
   ```
3. Rename remote asal jadi `upstream` (ini yang dipakai untuk menerima pembaruan di masa depan), lalu tambahkan repo klien yang baru dibuat sebagai `origin`:
   ```bash
   git remote rename origin upstream
   git remote add origin https://github.com/Bukansimetri/client-acme.git
   ```
4. Push ke repo klien:
   ```bash
   git push -u origin main
   ```
5. Lanjutkan setup instalasi standar:
   ```bash
   composer install
   php artisan app:setup-client "Nama Klien Sesungguhnya"
   ```
   (lihat dokumentasi command ini di `specs/018-setup-client-command/quickstart.md`)

**Hasil**: Repo klien berisi seluruh kode Simetri per saat clone dilakukan, dengan histori git **bersama** dengan Simetri (bukan independen) — siap menerima pembaruan lewat prosedur §2 di bawah tanpa risiko konflik palsu.

## 2. Menerapkan pembaruan dari Simetri ke repo klien

Jalankan ini secara berkala (mis. setiap ada rilis/perbaikan penting di Simetri, terutama patch keamanan) pada tiap repo klien yang perlu diperbarui:

1. Pastikan working tree bersih — commit atau stash dulu perubahan yang belum tersimpan:
   ```bash
   git status
   ```
2. Ambil perubahan terbaru dari Simetri:
   ```bash
   git fetch upstream
   ```
3. Gabungkan ke branch klien saat ini:
   ```bash
   git merge upstream/main
   ```
   Tidak perlu flag apa pun (tidak seperti pendekatan template yang gagal — lihat bagian Keputusan di atas). File yang tidak disentuh kedua sisi otomatis aman.
4. **Bila terjadi konflik**: Git akan menandainya di file yang benar-benar bertabrakan (baris yang sama-sama diedit) — selesaikan secara manual (edit file, `git add <file>`, lalu lanjutkan merge). Tidak ada mekanisme auto-resolve; ini disengaja agar kustomisasi klien tidak pernah tertimpa diam-diam.
5. Jalankan langkah pasca-merge sebelum deploy ulang:
   ```bash
   composer install     # bila ada dependency baru
   php artisan migrate  # bila ada migration baru
   php artisan test     # pastikan tidak ada regresi
   ```
6. Commit hasil merge dan push:
   ```bash
   git push origin main
   ```

**Catatan penting**: Setiap repo klien independen. Menerapkan pembaruan ke satu klien **tidak** memengaruhi klien lain atau Simetri itu sendiri — setiap klien boleh berada di versi Simetri yang berbeda-beda, sesuai kebutuhan/jadwal masing-masing.

## 3. Membawa perbaikan dari klien kembali ke Simetri

Kadang perbaikan bug atau peningkatan ditemukan/dibuat dulu di repo klien, bukan di Simetri. Agar klien lain juga mendapat manfaatnya:

1. Di repo klien, identifikasi commit/perubahan yang ingin dibawa kembali ke Simetri.
2. Buat branch baru **dari `main` di repo Simetri** (bukan di repo klien), lalu terapkan perubahan yang sama — bisa dengan mengetik ulang perubahannya, atau memakai `git format-patch` di repo klien dan `git am` di repo Simetri untuk perubahan yang identik persis.
3. Ajukan Pull Request ke `Bukansimetri/web-simetri-kit` seperti kontribusi fitur biasa.

Ini **langkah manual dan sadar** — tidak ada proses otomatis yang mem-forward perubahan klien ke Simetri, supaya kode spesifik satu klien tidak pernah bocor ke kit umum tanpa sengaja.

## Referensi

- Detail pertimbangan, alternatif yang ditolak, dan temuan verifikasi manual: `specs/020-client-versioning-strategy/research.md`
- Kontrak langkah-demi-langkah (versi teknis dari dokumen ini): `specs/020-client-versioning-strategy/contracts/git-workflow-contract.md`
- Setup instalasi klien baru (langkah `.env`/`APP_KEY`/cache): `specs/018-setup-client-command/`
