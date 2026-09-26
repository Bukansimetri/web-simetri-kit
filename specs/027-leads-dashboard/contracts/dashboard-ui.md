# Contract: Tampilan Dashboard Prospek

Kontrak tampilan halaman awal panel admin (`/admin`). Test feature memeriksa label dan perilaku di sini.

## Urutan widget

| Urutan | Widget | Lebar |
|---|---|---|
| 1 | Kartu statistik prospek (4 kartu) | penuh |
| 2 | Tabel "Perlu ditindaklanjuti" | penuh |
| 3 | Grafik "Prospek per minggu" | penuh |

Widget `AccountWidget` dan `FilamentInfoWidget` tidak tampil (FR-001).

## 1. Kartu statistik

| Kartu | Nilai | Deskripsi | Klik ke |
|---|---|---|---|
| **Pesan masuk baru** | `newContactCount` | "Belum dihubungi" | Daftar Pesan Masuk, `tableFilters[status][value]=new` |
| **Lead kalkulator baru** | `newCalculatorCount` | "Belum di-follow-up" | Daftar Lead Kalkulator, `tableFilters[status][value]=new` |
| **Prospek 30 hari** | `last30DaysTotal` | "Pesan masuk + lead kalkulator" + sparkline `last30DaysDaily` | tidak ada |
| **Konversi lead kalkulator** | `rate` diformat `12,5%`, atau "Belum ada data" | "{won} dari {total} lead (90 hari)" | tidak ada |

Kartu 1 dan 2 berwarna `danger` bila nilainya > 0, selain itu abu-abu.

## 2. Tabel "Perlu ditindaklanjuti"

Kolom: **Nama**, **Sumber**, **Area**, **Masuk**, **Estimasi Tagihan**.

- Sumber tampil sebagai badge: "Pesan Masuk" / "Lead Kalkulator".
- Area kosong tampil "-".
- Masuk: `ageLabel`; bila `isOverdue`, tampil badge merah "Terlambat" di sebelahnya.
- Estimasi Tagihan: format `Rp 1.250.000`; "-" untuk pesan masuk atau bila kosong.
- Seluruh baris dapat diklik ke `url`.
- Kosong: teks "Semua prospek sudah ditindaklanjuti".
- Footer: dua tautan "Lihat semua pesan masuk baru" dan "Lihat semua lead kalkulator baru" (URL sama dengan kartu 1 dan 2).

## 3. Grafik "Prospek per minggu"

- Tipe: bar, dua dataset: "Pesan Masuk" dan "Lead Kalkulator".
- 12 label sumbu X, format tanggal Senin, misal `7 Sep`.
- Deskripsi widget: "12 minggu terakhir".

## Akses

Semua widget di atas hanya dirender bila `ContactSubmissionResource::canViewAny()` **dan** `CalculatorLeadResource::canViewAny()` bernilai `true` (FR-003). Jika tidak, dashboard tampil tanpa widget dan tanpa error.
