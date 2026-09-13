# Research: Client Versioning Strategy

**Feature**: 020-client-versioning-strategy | **Date**: 2026-09-13

## 1. Template repo + upstream remote vs. Composer package private

**Decision**: **Template repository GitHub + git remote `upstream`**.

**Rationale**:
- Simetri saat ini adalah **aplikasi Laravel utuh** (routes, migrations, Filament Resources, Blade views, config per-instalasi) — bukan library/paket yang dirancang untuk di-`composer require` ke dalam proyek lain. Mengubahnya menjadi Composer package berarti merestrukturisasi hampir seluruh aplikasi menjadi bentuk `ServiceProvider` yang mem-publish resource (migrations, views, config) ke aplikasi "host" — perubahan arsitektur besar yang jauh melampaui scope "tentukan & dokumentasikan strategi" dan melanggar Prinsip V (Simplicity & Dependency Discipline: hindari kompleksitas yang tidak dibutuhkan hari ini).
- Template repository adalah fitur GitHub bawaan (gratis, tanpa setup tambahan) yang persis cocok untuk "banyak repo independen berasal dari satu basis kode" — setiap klien dapat repo baru dengan histori git bersih (tidak membawa pesan commit internal/nama klien lain), sekaligus tetap bisa menerima pembaruan lewat remote `upstream` standar Git.
- Setiap instalasi klien tetap 100% aplikasi Laravel biasa (bisa langsung `composer install && php artisan app:setup-client`, konsisten dengan 018-setup-client-command) — tidak ada lapisan abstraksi baru yang perlu dipelajari developer.

**Alternatives considered**:
- **Composer package private** (mis. lewat Packagist privat atau Satis/repositori Composer sendiri) — ditolak. Cocok untuk mendistribusikan *library* (helper, SDK, paket fungsi), tapi Simetri adalah *aplikasi utuh* yang tiap klien jalankan sebagai deployment sendiri, bukan dependency di dalam aplikasi lain. Memaksakan pola ini butuh: (a) memisahkan seluruh kode jadi package + skeleton aplikasi tipis yang meng-install package tsb, (b) infrastruktur registry privat (biaya & maintenance tambahan), (c) alur update yang lebih rumit bagi developer (`composer update` package lalu publish ulang resource) dibanding `git merge` yang sudah familiar. Tidak ada manfaat tambahan yang sepadan dengan kompleksitas ini pada skala tim/jumlah klien saat ini.
- **Monorepo dengan folder per klien** — tidak dipertimbangkan serius (di luar dua opsi yang disebut AMC-230), tapi disebut untuk kelengkapan: ditolak karena histori git seluruh klien akan tercampur dalam satu repo (masalah kerahasiaan antar-klien) dan ukuran repo terus membengkak.

## 2. Alur pembuatan repositori klien baru

**Decision (direvisi setelah verifikasi manual — lihat §6)**: **`git clone` biasa** dari Simetri (histori penuh ikut terbawa), remote asal di-rename jadi `upstream`, lalu di-push ke repositori GitHub baru yang kosong (dibuat manual, BUKAN lewat tombol "Use this template"):

```bash
git clone https://github.com/Bukansimetri/web-simetri-kit.git client-acme
cd client-acme
git remote rename origin upstream
git remote add origin https://github.com/Bukansimetri/client-acme.git
git push -u origin main
```

**Rationale**: Percobaan awal memakai fitur GitHub **"Use this template"** ternyata punya cacat serius yang baru ketahuan lewat verifikasi manual end-to-end (§6): repo hasil "Use this template" punya *root commit* yang berbeda dari Simetri (histori "unrelated"), sehingga `git merge upstream/main --allow-unrelated-histories` pada sinkronisasi PERTAMA memperlakukan **setiap file yang berbeda isi** sebagai konflik "add/add" — bukan hanya file yang benar-benar bertabrakan. Pada repo sungguhan (ratusan file), ini berarti puluhan/ratusan "konflik" palsu yang membingungkan dan memakan waktu, jauh dari klaim SC-001 (<15 menit). Mencoba menambal dengan flag strategi (`-X ours`/`-X theirs`) justru lebih berbahaya: keduanya bisa **diam-diam membuang** perubahan sah di sisi yang kalah (dibuktikan di §6) — melanggar FR-004 secara langsung. `git clone` biasa mempertahankan histori bersama (common ancestor) dengan Simetri, sehingga `git merge upstream/main` di kemudian hari adalah 3-way merge yang benar: file yang tidak disentuh kedua sisi otomatis aman, dan HANYA baris yang benar-benar bertabrakan yang ditandai konflik — tanpa butuh `--allow-unrelated-histories` sama sekali.

**Alternatives considered**:
- **GitHub "Use this template"** — dicoba lebih dulu, DITOLAK setelah verifikasi manual (§6) karena alasan di atas (histori unrelated → ledakan konflik palsu pada sinkronisasi pertama).
- `git clone` lalu `rm -rf .git && git init` (menghapus histori) — ditolak, ini menghasilkan masalah yang SAMA PERSIS dengan "Use this template" (histori unrelated), hanya dengan langkah manual lebih banyak.

## 3. Alur menerima pembaruan dari Simetri (upstream)

**Decision (direvisi — lihat §2 & §6)**: Remote `upstream` sudah otomatis ada sejak proses clone di §2 (hasil `git remote rename origin upstream`), jadi tidak perlu `git remote add` terpisah. Untuk menerima pembaruan:

```bash
git fetch upstream
git merge upstream/main
```

TIDAK PERNAH butuh `--allow-unrelated-histories` — karena repo klien dibuat lewat `git clone` (§2), histori Simetri dan repo klien selalu punya common ancestor sejak awal. Konflik (bila ada) diselesaikan manual oleh developer seperti konflik merge Git pada umumnya (FR-004) — tidak ada mekanisme auto-resolve.

**Rationale**: `git fetch`/`merge` adalah kapabilitas Git standar tanpa tooling tambahan. Karena histori selalu tersambung sejak repo klien dibuat (§2), setiap merge — termasuk yang pertama — adalah 3-way merge yang benar: file yang tidak disentuh kedua sisi tidak pernah ditandai konflik (dibuktikan §6), berbeda dengan pendekatan "Use this template" yang sempat dicoba lebih dulu.

**Alternatives considered**:
- `git subtree`/`git submodule` — ditolak, kedua pendekatan ini menambah kompleksitas mental model yang signifikan (terutama submodule, yang menyimpan Simetri sebagai referensi commit terpisah, bukan bagian penuh dari kode klien) untuk manfaat yang tidak dibutuhkan di sini — klien butuh SALINAN PENUH yang bisa dikustomisasi bebas, bukan referensi ke Simetri.
- `git cherry-pick` per-commit alih-alih merge — ditolak sebagai default (boleh dipakai kasus per kasus bila developer hanya ingin satu perbaikan spesifik), karena merge biasa lebih sederhana untuk kasus umum "ambil semua pembaruan sejak terakhir sync".

## 4. Membawa perbaikan dari level klien kembali ke Simetri

**Decision**: Didokumentasikan sebagai langkah manual: developer membuat branch/patch dari perubahan di repo klien, lalu mengajukan Pull Request-nya secara terpisah ke repo Simetri (`Bukansimetri/web-simetri-kit`) — bukan proses otomatis.

**Rationale**: FR-008 hanya meminta panduan tersedia, bukan otomasi. Karena histori git klien dan Simetri terpisah sejak awal (lihat #2), tidak ada cara otomatis membawa satu commit spesifik dari repo klien ke Simetri selain `cherry-pick` manual (menyalin patch/diff) — ini didokumentasikan sebagai proses sadar, konsisten dengan FR-006 (perubahan klien tidak boleh memengaruhi Simetri kecuali lewat langkah eksplisit).

**Alternatives considered**:
- Bot otomatis yang mendeteksi & mem-forward perubahan — ditolak, jauh melampaui scope (dan kompleksitas) dari sekadar "menentukan & mendokumentasikan strategi".

## 5. Lokasi dokumentasi

**Decision**: `docs/versioning-strategi-klien.md` (direktori `docs/` baru di root repo Simetri), dengan tautan singkat ditambahkan ke `README.md` (FR-007 — mudah ditemukan).

**Rationale**: Repo ini belum punya direktori `docs/`; `specs/` sudah dipakai spec-kit khusus untuk artefak per-fitur (spec/plan/tasks), bukan dokumentasi rujukan jangka panjang yang perlu terus dibaca tim ("bagaimana cara membuat klien baru" bukan sesuatu yang selesai setelah fitur ini "implemented" — ia terus dipakai). `docs/` adalah lokasi konvensional di banyak proyek untuk dokumentasi semacam ini, dan juga akan menjadi rumah alami bagi dokumentasi deployment (AMC-231) di masa depan.

**Alternatives considered**:
- Menulis langsung sebagai bagian panjang di `README.md` — ditolak, akan membuat README terlalu panjang untuk fitur yang detailnya lebih relevan untuk developer/ops yang sedang aktif provisioning klien, bukan pembaca README pertama kali.
- Menyimpan hanya di `specs/020-client-versioning-strategy/` — ditolak, direktori `specs/` secara konvensi kit ini adalah riwayat proses spec-kit per fitur (lihat 15+ folder feature lain di sana), bukan tempat tim mencari panduan operasional sehari-hari; FR-007 mensyaratkan "mudah ditemukan", bukan "ada di suatu tempat di riwayat spec".

## 6. Verifikasi manual yang mengubah keputusan (T005/T008/T009)

Saat menjalankan verifikasi manual end-to-end (simulasi lokal: dua repo Git kecil berperan sebagai Simetri & klien, tanpa perlu akses GitHub), ditemukan bahwa rencana awal ("Use this template" → histori independen → merge pertama pakai `--allow-unrelated-histories`) menghasilkan **konflik pada SEMUA file yang berbeda isi antara kedua sisi**, bukan hanya file yang benar-benar bertabrakan — termasuk file yang HANYA diubah di salah satu sisi (mis. file yang cuma diedit di Simetri, tidak disentuh klien sama sekali, tetap muncul sebagai konflik "add/add"). Ini karena `--allow-unrelated-histories` membuat Git tidak punya *merge base* sama sekali, sehingga tidak bisa membedakan "file identik yang tidak berubah" dari "file yang sengaja diedit dua sisi berbeda".

Mencoba menambal dengan `-X ours` (percobaan lanjutan) memang menghilangkan konflik secara otomatis, TAPI diam-diam **membuang isi perbaikan upstream** pada file yang tidak pernah disentuh klien sama sekali — persis skenario yang FR-004 larang ("konflik ... MUST mewajibkan penyelesaian ... secara sadar", bukan ditimpa otomatis).

**Tindakan**: Keputusan §2 dan §3 di atas direvisi dari "Use this template" ke `git clone` biasa (mempertahankan histori bersama). Diverifikasi ulang: pendekatan ini menghasilkan merge bersih untuk file yang tidak bertabrakan (termasuk yang hanya diubah salah satu sisi), DAN tetap menampilkan konflik yang jelas & wajib diselesaikan manual untuk baris yang benar-benar diedit kedua sisi pada file yang sama. Kedua perilaku ini sesuai FR-004/FR-005/FR-006 dan SC-002.

## Outstanding NEEDS CLARIFICATION

Tidak ada.
