# Quickstart: Memverifikasi Dashboard Prospek

## 1. Test otomatis

```bash
php artisan test --compact tests/Feature/Dashboard
php artisan test --compact tests/Feature/Admin/RoleResourceEditTest.php
```

## 2. Data contoh di lokal

Gunakan factory lewat tinker (hanya di lokal):

```bash
php artisan tinker --execute '
\App\Models\ContactSubmission::factory()->count(3)->create(["status" => "new"]);
\App\Models\ContactSubmission::factory()->create(["status" => "new", "created_at" => now()->subDays(3)]);
\App\Models\CalculatorLead::factory()->count(4)->create(["status" => "new"]);
\App\Models\CalculatorLead::factory()->create(["status" => "won", "created_at" => now()->subDays(10)]);
'
```

## 3. Cek manual

1. Login ke `/admin`. Tidak ada widget info akun atau info Filament.
2. Kartu: Pesan masuk baru = 4, Lead kalkulator baru = 4, Prospek 30 hari = 9, Konversi = 20% ("1 dari 5 lead (90 hari)").
3. Klik kartu "Pesan masuk baru": daftar Pesan Masuk terbuka dengan filter Status: Baru dan 4 baris.
4. Tabel "Perlu ditindaklanjuti": 8 baris, pesan berumur 3 hari berlabel "Terlambat", lead kalkulator menampilkan estimasi tagihan.
5. Klik satu baris: halaman edit prospek tersebut terbuka.
6. Grafik: 12 batang minggu, dua warna, minggu berjalan berisi data di atas.
7. Ubah **Pengaturan Situs → Pengaturan Umum → Zona Waktu**, muat ulang: batas hari/minggu mengikuti zona baru.
8. Buka **Sistem → Roles → edit role apa pun**: halaman terbuka tanpa error.
