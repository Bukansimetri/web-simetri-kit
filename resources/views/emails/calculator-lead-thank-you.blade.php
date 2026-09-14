@component('mail::message')
# Terima kasih, {{ $lead->name }}

Terima kasih sudah menghitung estimasi penghematan di **{{ $brandName }}**. Berikut hasilnya — kami kirimkan supaya mudah Anda buka lagi kapan pun dibutuhkan.

## Perkiraan penghematan Anda

@component('mail::table')
| | |
| --- | ---: |
| Hemat di tahun pertama | **Rp {{ number_format($lead->savings_year1, 0, ',', '.') }}** |
| Total hemat {{ (int) ($lead->assumptions['projection_years'] ?? 25) }} tahun | **Rp {{ number_format($lead->total_savings_25y, 0, ',', '.') }}** |
| Perkiraan balik modal | **{{ rtrim(rtrim(number_format($lead->breakeven_years, 1), '0'), '.') }} tahun** |
| Listrik yang dihasilkan | **{{ number_format($lead->annual_kwh, 1, ',', '.') }} kWh per tahun** |
@endcomponent

**Dihitung dari data Anda:** {{ $inputSummary }}

Angka di atas adalah estimasi awal dengan asumsi umum (tarif sekitar Rp {{ number_format($lead->assumptions['tariff_per_kwh'] ?? 1500, 0, ',', '.') }}/kWh dan panel menutup sekitar {{ $lead->assumptions['solar_coverage_percent'] ?? 70 }}% konsumsi listrik). Hasil sebenarnya bergantung pada luas dan arah atap, bayangan di sekitar rumah, serta pola pemakaian harian Anda — dan biasanya baru bisa dipastikan setelah kami melihat langsung kondisi lokasi.

**Langkah berikutnya.** Tim kami akan menghubungi Anda dalam 1×24 jam kerja untuk menawarkan survei lokasi gratis, tanpa biaya dan tanpa kewajiban apa pun. Kalau ingin lebih cepat, balas email ini atau chat kami langsung.

@if ($whatsappUrl)
@component('mail::button', ['url' => $whatsappUrl])
Chat WhatsApp
@endcomponent
@endif

Salam,<br>
Tim {{ $brandName }}

---

<small>Email ini dikirim karena nomor dan email Anda diisikan pada kalkulator estimasi hemat di situs kami. Bila ini bukan Anda, cukup abaikan email ini.</small>
@endcomponent
