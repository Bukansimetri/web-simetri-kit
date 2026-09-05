# Contract: POST /kontak (Submit Endpoint)

**Feature**: [spec.md](../spec.md) | **Date**: 2026-09-05

Endpoint internal yang dipanggil `fetch()` dari `kontak.blade.php` (bukan API publik terdokumentasi) — kontrak di sini untuk sinkronisasi frontend/backend dan dasar feature test.

## Request

`POST /kontak`

| Field | Type | Required |
|---|---|---|
| `name` | string | Ya |
| `phone` | string | Ya |
| `topic` | string (`umum`\|`residensial`\|`komersial`\|`pompa`) | Tidak |
| `message` | string | Ya |

Header `Accept: application/json` (supaya error validasi Laravel dikembalikan sebagai JSON 422, bukan redirect/HTML).

## Response — Sukses (201)

```json
{
  "message": "Pesan Anda telah kami terima.",
  "whatsapp_url": "https://wa.me/6281234567890?text=..." // atau null bila whatsapp_number belum dikonfigurasi
}
```

| Aspek | Kontrak |
|---|---|
| Status code | `201 Created` |
| Efek | Submission tersimpan (FR-001); job notifikasi email ke-queue (FR-008, tidak menunggu hasil kirim — FR-009) |
| `whatsapp_url` | `wa.me` link berisi nomor bisnis + pesan pre-filled (nama, topik, pesan) bila `whatsapp_number` terisi di Brand Settings; `null` bila belum dikonfigurasi (FR-013) |

## Response — Validasi Gagal (422)

Format standar Laravel validation error (`{"message": ..., "errors": {"field": ["pesan error"]}}`) — field `name`/`phone`/`message` kosong atau `phone` format tidak valid MUST menghasilkan ini, MUST NOT menyimpan apa pun (FR-002).

## Response — Rate Limited (429)

Melebihi batas throttle (research.md §4) MUST mengembalikan `429 Too Many Requests`, bukan menyimpan submission tambahan (edge case spam).

## Kontrak tambahan

| Aspek | Kontrak |
|---|---|
| Kegagalan notifikasi email | MUST NOT mengubah response endpoint ini — 201 tetap dikembalikan meski job notifikasi nantinya gagal saat diproses queue (FR-009) |
| CSRF | Endpoint MUST tetap terlindungi CSRF standar Laravel (form menyertakan token, bukan dikecualikan) |
