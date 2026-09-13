# Quickstart: Client Versioning Strategy

**Feature**: 020-client-versioning-strategy

## Untuk developer/ops (memakai strategi ini)

Panduan lengkap ada di [`docs/versioning-strategi-klien.md`](../../docs/versioning-strategi-klien.md) (dibuat sebagai bagian implementasi fitur ini). Ringkasan cepat:

**Klien baru**: buat repo GitHub kosong untuk klien → `git clone` Simetri → `git remote rename origin upstream` → `git remote add origin <url-repo-klien>` → `git push -u origin main`.

**Terima pembaruan**: `git fetch upstream` → `git merge upstream/main` (tanpa flag khusus) → selesaikan konflik bila ada → test → push.

> Catatan: rencana awal memakai fitur GitHub "Use this template" **ditolak** setelah verifikasi manual (lihat research.md #6) — pendekatan itu menghasilkan histori git independen yang membuat SEMUA file berbeda ditandai konflik palsu pada sinkronisasi pertama. `git clone` biasa (mempertahankan histori bersama) adalah pendekatan yang benar dan sudah diverifikasi.

## Verifikasi manual (selama implementasi, per Independent Test di spec.md)

Verifikasi berikut sudah dijalankan sebagai simulasi lokal (dua repo Git kecil berperan sebagai Simetri & klien) tanpa perlu akses GitHub, dan hasilnya sudah dituangkan ke `research.md` #6 serta memperbaiki keputusan strategi:

1. **US1 (disederhanakan)**: `git clone` dari Simetri, `git remote rename origin upstream`. Dikonfirmasi: histori git klien punya common ancestor dengan Simetri (bukan histori independen).
2. **US2, tanpa konflik**: commit satu perubahan di file berbeda pada masing-masing sisi (Simetri & klien), lalu `git fetch upstream` + `git merge upstream/main`. Dikonfirmasi: kedua perubahan sama-sama masuk tanpa satu pun file ditandai konflik palsu (berbeda dari pendekatan "Use this template" yang gagal di sini).
3. **US2, dengan konflik sungguhan**: edit BARIS YANG SAMA di file yang sama pada kedua sisi, lalu merge. Dikonfirmasi: Git menandai konflik dengan jelas dan menghentikan merge sampai diselesaikan manual (`git add` setelah edit) — tidak ada auto-resolve yang menimpa salah satu sisi.

**Masih perlu dilakukan oleh tim** (di luar jangkauan environment otomatis ini — butuh akses GitHub UI/CLI):
4. **US1, end-to-end sungguhan**: praktikkan §2 kontrak dengan repo GitHub klien uji coba yang sesungguhnya (bukan simulasi lokal), lalu `composer install` + `php artisan app:setup-client "Klien Uji"`; hapus repo uji coba setelah selesai.
5. **US3**: minta satu anggota tim yang belum pernah membuat repo klien untuk membaca hanya `docs/versioning-strategi-klien.md`, lalu mempraktikkan langkah 4 di atas tanpa bantuan tambahan; catat keberhasilan dan waktu yang dibutuhkan (SC-003).
