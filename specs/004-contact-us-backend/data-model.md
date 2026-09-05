# Data Model: Contact Us Backend

**Date**: 2026-09-05 | **Feature**: [spec.md](./spec.md) | **Research**: [research.md](./research.md)

## Contact Submission (baru)

| Field | Type | Required | Notes |
|---|---|---|---|
| `id` | bigint PK | | |
| `name` | string | Ya | Dari field "Nama Lengkap" di form |
| `phone` | string | Ya | Dari field "No. HP / WhatsApp" |
| `topic` | string, nullable | Tidak | Dari field "Topik Kebutuhan" (`umum`/`residensial`/`komersial`/`pompa`) |
| `message` | text | Ya | Dari field "Pesan Anda" |
| `status` | string (enum: `new`, `contacted`, `closed`), default `new` | | FR-006 — admin ubah manual saat menindaklanjuti |
| `created_at` | timestamp | | Waktu masuk (FR-005) |

**Validasi** (FR-002):
- `name`: required, string, max 255.
- `phone`: required, format nomor wajar (angka/`+`/`-`/spasi, 8–15 karakter — sama dengan validasi client-side yang sudah ada).
- `message`: required, string.
- `topic`: nullable, harus salah satu dari daftar opsi yang ada di form (`umum`, `residensial`, `komersial`, `pompa`) — bukan teks bebas.

**Relasi**: Tidak ada — setiap submission berdiri sendiri (sesuai spec.md Key Entities).

**State**: `status` berubah manual oleh admin (`new` → `contacted` → `closed`), tidak ada transisi otomatis oleh sistem.

## Perluasan `BrandSettings` (existing, dari Epic 2/002)

| Field baru | Type | Required | Notes |
|---|---|---|---|
| `whatsapp_number` | string, nullable | Tidak | Format digit tanpa `+`/spasi (mis. `6281234567890`) dipakai untuk link `wa.me` (FR-012, FR-013). Kosong → sistem lewati langkah buka WhatsApp (edge case). |
| `contact_notification_email` | string, nullable | Tidak | Tujuan notifikasi email submission baru (FR-008, FR-010). Kosong → notifikasi dilewati (fallback wajar, bukan error — konsisten pola FR-013). |

**Validasi**: `whatsapp_number` hanya digit (setelah strip `+`/spasi/`-` di form); `contact_notification_email` format email valid.
