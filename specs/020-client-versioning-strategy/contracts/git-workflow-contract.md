# Contract: Git Workflow — Client Repository Lifecycle

**Feature**: 020-client-versioning-strategy

Fitur ini bukan API/CLI aplikasi — "kontrak"-nya adalah urutan langkah Git yang harus dihasilkan sama persis oleh siapa pun yang mengikutinya (FR-002, FR-003), dan bentuk akhir dokumen `docs/versioning-strategi-klien.md` yang memuatnya.

## 1. Prasyarat (dilakukan sekali per klien)

- Satu repositori GitHub baru yang **kosong** (tanpa README/`.gitignore`/lisensi bawaan GitHub) sudah dibuat untuk klien tsb sebelum langkah §2 dijalankan. TIDAK memakai fitur "Template repository"/"Use this template" GitHub — lihat research.md #2/#6 untuk alasan penolakannya (histori independen menyebabkan konflik palsu saat sinkronisasi pertama).

## 2. Prosedur: Membuat repositori klien baru (US1)

**Input**: nama klien, URL repo GitHub kosong yang sudah dibuat (§1).

**Langkah**:
```bash
git clone https://github.com/Bukansimetri/web-simetri-kit.git client-acme
cd client-acme
git remote rename origin upstream
git remote add origin https://github.com/Bukansimetri/client-acme.git
git push -u origin main
```
Lanjutkan setup instalasi standar (`composer install`, `php artisan app:setup-client "Nama Klien"`, dst. — lihat 018-setup-client-command).

**Output/garansi**: Repo klien berisi seluruh kode Simetri per saat clone dilakukan, DENGAN histori git bersama (common ancestor) dengan Simetri — inilah yang membuat sinkronisasi pembaruan (§3) selalu jadi 3-way merge yang benar, bukan sekadar "terlihat independen" seperti rencana awal yang gagal diverifikasi (research.md #6). Remote `upstream` sudah otomatis mengarah ke Simetri sejak langkah pertama (US1 acceptance scenario 1–3).

## 3. Prosedur: Menerapkan pembaruan Simetri ke repo klien (US2)

**Prasyarat**: repo klien dibuat lewat §2 (punya remote `upstream`).

**Langkah**:
1. Pastikan working tree klien bersih (`git status`) — commit atau stash perubahan lokal yang belum tersimpan.
2. `git fetch upstream`
3. `git merge upstream/main` — TIDAK PERNAH butuh `--allow-unrelated-histories` (histori sudah bersambung sejak §2; lihat research.md #6 untuk pembuktian kenapa flag ini justru berbahaya bila dipaksakan pada histori independen).
4. Bila muncul konflik, MUST diselesaikan manual (edit file konflik, `git add`, lanjutkan merge) — TIDAK ADA auto-resolve (FR-004). Dengan histori bersama, konflik yang muncul di sini adalah konflik SUNGGUHAN (baris yang sama-sama diedit kedua sisi), bukan noise dari file yang tidak pernah disentuh salah satu pihak.
5. Jalankan `composer install` (bila ada dependency baru), `php artisan migrate` (bila ada migration baru), dan test suite (`php artisan test`) sebelum deploy ulang instalasi klien tsb.
6. Commit hasil merge, push ke repo klien.

**Output/garansi**: Instalasi klien menerima seluruh perbaikan/fitur baru Simetri sejak sinkronisasi terakhir, sementara kustomisasi klien (branding, konten, kode yang diubah khusus klien) tetap ada kecuali secara sadar diselesaikan berbeda saat konflik (US2 acceptance scenario 1–3).

## 4. Prosedur: Membawa perbaikan dari klien kembali ke Simetri (edge case, FR-008)

1. Di repo klien, identifikasi commit/perubahan yang ingin dibawa kembali.
2. Buat branch baru dari `main` DI REPO SIMETRI (bukan repo klien), terapkan perubahan yang sama secara manual (edit ulang atau `git format-patch` + `git am` dari repo klien ke repo Simetri).
3. Ajukan Pull Request ke `Bukansimetri/web-simetri-kit` seperti kontribusi fitur biasa (lihat CLAUDE.md untuk konvensi commit/PR proyek ini).

**Output/garansi**: Perbaikan tersedia bagi seluruh klien lain lewat prosedur §3 di atas pada sinkronisasi mereka berikutnya — TIDAK otomatis, sepenuhnya langkah sadar developer (FR-006).

## Independensi antar instalasi klien

Menjalankan prosedur §3 pada satu repo klien MUST TIDAK memengaruhi repo klien lain maupun Simetri itu sendiri (FR-005, FR-006) — setiap repo klien adalah working tree Git yang sepenuhnya terpisah.
