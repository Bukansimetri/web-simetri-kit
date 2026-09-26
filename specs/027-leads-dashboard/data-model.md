# Data Model: Dashboard Prospek Admin

Fitur ini **tidak menambah tabel, kolom, atau migrasi**. Semua angka diturunkan dari dua model yang sudah ada.

## Sumber data

| Model | Tabel | Kolom yang dipakai | Status "baru" |
|---|---|---|---|
| `App\Models\ContactSubmission` | `contact_submissions` | `id`, `name`, `area` (nullable), `status`, `created_at` | `ContactSubmission::STATUS_NEW` (`new`) |
| `App\Models\CalculatorLead` | `calculator_leads` | `id`, `name`, `area` (nullable), `status`, `estimated_monthly_bill` (integer, Rupiah, nullable), `created_at` | `CalculatorLead::STATUS_NEW` (`new`); won = `CalculatorLead::STATUS_WON` |

Keduanya tidak memakai soft delete, jadi record yang dihapus otomatis tidak terhitung.

## Nilai turunan (dihitung oleh `LeadDashboardMetrics`)

Semua batas waktu dihitung di zona waktu `SiteSettings::timezone` (TZ). "Sekarang" = waktu saat ini di TZ.

| Nama | Definisi | Dipakai di |
|---|---|---|
| `newContactCount` | jumlah `ContactSubmission` dengan `status = new` (semua waktu) | Kartu 1 |
| `newCalculatorCount` | jumlah `CalculatorLead` dengan `status = new` (semua waktu) | Kartu 2 |
| `last30DaysTotal` | jumlah gabungan kedua model dengan `created_at >= awal hari (hari ini − 29)` di TZ | Kartu 3 |
| `last30DaysDaily` | array 30 angka, satu per tanggal lokal dari (hari ini − 29) s.d. hari ini, gabungan kedua model | Sparkline kartu 3 |
| `conversion` | `{won, total, rate}`: lead kalkulator dengan `created_at >= awal hari (hari ini − 89)`; `rate = round(won / total × 100, 1)` atau `null` bila `total = 0` | Kartu 4 |
| `followUpQueue` | maks. 10 item gabungan berstatus `new` dari kedua model, urut `created_at` terbaru | Tabel |
| `weeklySeries` | 12 minggu (Senin–Minggu) berakhir di minggu berjalan; untuk tiap minggu: label tanggal Senin, jumlah `ContactSubmission`, jumlah `CalculatorLead` (semua status) | Grafik |

## Item antrean tindak lanjut

Bentuk data satu baris tabel (bukan model, hanya array/DTO di memori):

| Field | Isi |
|---|---|
| `source` | `contact` atau `calculator` |
| `sourceLabel` | "Pesan Masuk" atau "Lead Kalkulator" |
| `name` | nama prospek |
| `area` | area atau `null` (ditampilkan "-") |
| `createdAt` | waktu masuk, dikonversi ke TZ |
| `ageLabel` | umur relatif berbahasa Indonesia, misal "3 jam lalu" |
| `isOverdue` | `true` bila `sekarang − createdAt > 48 jam` |
| `estimatedMonthlyBill` | integer Rupiah (khusus `calculator`), `null` untuk `contact` atau bila kosong |
| `url` | URL halaman edit record di resource sumbernya |

## Aturan validasi & batas

- Tabel dibatasi 10 item (FR-021): ambil maks. 10 terbaru dari tiap model, gabungkan, urutkan, potong 10.
- Minggu dimulai Senin (FR-031).
- Slot hari/minggu tanpa data bernilai 0 (FR-032).
