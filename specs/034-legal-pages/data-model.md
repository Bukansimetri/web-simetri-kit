# Data Model: Halaman Legal yang Dapat Diedit

## custom_pages (ubah)

| Kolom | Tipe | Aturan |
|---|---|---|
| template | string(20), default `standar` | `standar` atau `legal` (enum `CustomPageTemplate`) |
| legal | json, nullable | struktur di bawah; dipertahankan walau template Standar |
| content | longText, **nullable** (sebelumnya wajib) | wajib hanya untuk template Standar (validasi form) |

Baris lama otomatis `template = standar`, `legal = null`.

## Struktur `legal` (JSON)

```json
{
  "hero_image_path": "legal-pages/xxx.webp | null",
  "subtitle": "string | null (maks 500)",
  "intro": "rich text | null",
  "highlight_title": "string | null (maks 160)",
  "highlight_body": "string | null (maks 600)",
  "sections": [
    {
      "label": "string | null (maks 60, mis. 'PENDAHULUAN')",
      "title": "string (wajib, maks 160)",
      "body": "rich text | null",
      "cards": [
        { "icon": "kunci MaterialSymbolsIcons | null", "title": "string (wajib, maks 100)", "text": "string | null (maks 400)" }
      ],
      "note": "string | null (maks 400)"
    }
  ],
  "contact_title": "string | null (maks 120)",
  "contact_text": "string | null (maks 400)",
  "contact_whatsapp_label": "string | null (maks 60)",
  "contact_whatsapp_message": "string | null (maks 300)",
  "contact_email": "email | null",
  "pdf_path": "legal-pages/pdf/xxx.pdf | null",
  "pdf_label": "string | null (maks 80, default 'Unduh Dokumen (PDF)')",
  "cta_title": "string | null (maks 160)",
  "cta_body": "string | null (maks 300)",
  "cta_button_label": "string | null (maks 60)",
  "cta_button_url": "string | null (URL atau path, default /kontak)"
}
```

Penomoran bagian, label "PASAL 0n", dan daftar isi diturunkan dari urutan `sections`; tidak disimpan.

## faq_items (tanpa perubahan skema)

Isi awal tempat `faq`: 5 entri (pertanyaan, jawaban, kategori) dari data contoh FAQ, dipasang bila tempat `faq` kosong.

## Isi awal halaman legal (installer)

| Slug | Judul | Bagian | Kartu |
|---|---|---|---|
| `kebijakan-privasi` | Kebijakan Privasi | 7 | Penggunaan Data (4), Keamanan (2), Hak Pengguna (3) |
| `syarat-ketentuan` | Syarat & Ketentuan | 8 (berlabel PASAL) | Garansi (3) |

Dibuat hanya bila slug belum ada.
