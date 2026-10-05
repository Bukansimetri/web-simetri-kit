# Quickstart: Verifikasi Spec 033

## Persiapan

```bash
npm run build
```

## Pemeriksaan Manual

1. Buka beranda, kalkulator, pilih metode **Berdasarkan Peralatan**.
2. Bandingkan stepper semua kartu: ukurannya sama (80 × 32 px) dan menempel di kanan kartu, termasuk pada "Mesin Cuci 2 Tabung" dan "Kompor Listrik".
3. Nama alat lebih kecil dan tidak tebal; watt lebih samar dan tetap terbaca.
4. Ubah nama satu peralatan di admin menjadi sangat panjang (±40 karakter): nama dipotong di dua baris dan stepper tidak berubah.
5. Tekan tambah/kurang dan ketik angka langsung: jumlah berubah, tidak bisa di bawah nol, hasil perhitungan direset.
6. Ulangi di lebar 360 px: satu kolom, kartu bertinggi sama, tanpa gulir horizontal, tombol mudah disentuh.

## Pengujian Otomatis

```bash
php artisan test --compact --filter=CalculatorApplianceCardsTest
vendor/bin/pint --dirty --format agent
```
