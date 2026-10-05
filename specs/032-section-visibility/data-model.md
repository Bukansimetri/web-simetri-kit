# Data Model: Tampil/Sembunyi Section dari Admin

## SectionVisibilitySettings (baru, Spatie Settings)

| Grup | Properti | Tipe | Bawaan | Aturan |
|---|---|---|---|---|
| `section_visibility` | `hidden` | array<string> | `[]` | Hanya kunci `PublicSection` yang valid. Kunci tak dikenal diabaikan saat dibaca dan dibuang saat disimpan. |

Status sebuah section: **tampil** bila kuncinya tidak ada di `hidden`, **tersembunyi** bila ada. Tidak ada transisi lain.

Settings migration: `$this->migrator->add('section_visibility.hidden', [])`.

## PublicSection (enum, bukan tabel)

25 kunci, lihat [contracts/section-catalog.md](contracts/section-catalog.md). Atribut turunan: halaman induk, label, URL menu isi.

## Tanpa perubahan skema

Isi section (section_items, section_headings, call_to_actions, page_blocks, testimonials, team_members, client_logos, products, faq_items) tidak diubah. Menyembunyikan section tidak menyentuh data ini (FR-008).
