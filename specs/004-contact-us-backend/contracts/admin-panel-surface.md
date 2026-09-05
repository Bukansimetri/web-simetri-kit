# Contract: Admin Panel Surface (Contact Submissions)

**Feature**: [spec.md](../spec.md) | **Date**: 2026-09-05

## Contact Us Resource (User Story 2)

| Aspek | Kontrak |
|---|---|
| Route | `/admin/contact-submissions` (Filament Resource standar: index, edit) |
| Akses | Semua role dengan akses panel admin (FR-011) — tidak dibatasi role tertentu |
| Kolom daftar | Nama, No. HP/WhatsApp, Topik, Status, Waktu masuk |
| Filter | Berdasarkan `status` (FR-006, Acceptance Scenario 3) |
| Ubah status | Bisa diubah lewat form edit atau aksi cepat di tabel; perubahan tersimpan seketika |
| Hapus | Admin bisa hapus submission (FR-007); terhapus permanen, tidak mempengaruhi form publik |

## Brand Settings (perluasan — WhatsApp & Email Notifikasi)

| Aspek | Kontrak |
|---|---|
| Route | Halaman Brand Settings yang sama (`BrandSettingsPage`, sudah diperluas di 002 & 003) |
| Field baru | Nomor WhatsApp Bisnis (text), Email Notifikasi Kontak (text/email) |
| Efek | Begitu `whatsapp_number` diisi, submission BERIKUTNYA (bukan yang lama) menyertakan `whatsapp_url` di response sukses; begitu `contact_notification_email` diisi, submission berikutnya memicu job notifikasi ke alamat tsb |
| Kosong | Field boleh dikosongkan — sistem lewati langkah terkait tanpa error (FR-013, dan setara untuk email) |
