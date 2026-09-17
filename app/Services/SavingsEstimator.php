<?php

namespace App\Services;

use App\Models\ElectricityAppliance;
use App\Settings\CalculatorSettings;
use InvalidArgumentException;

/**
 * Satu-satunya tempat perhitungan estimasi hemat listrik dari form
 * "Hitung Estimasi Penghematan" (home). Menggantikan logika yang dulu ada
 * di resources/js/calculator.js (compute()) — sekarang berjalan di server
 * supaya:
 *
 *  - Angka yang tersimpan di database tidak bisa dimanipulasi dari browser.
 *  - Grafik yang ditampilkan ke pengunjung dijamin sama dengan angka yang
 *    tersimpan & dikirim lewat email (server jadi satu-satunya sumber
 *    kebenaran; frontend cuma menggambar ulang titik-titik dari sini).
 *  - Watt tiap peralatan diambil dari katalog server (App\Models\
 *    ElectricityAppliance, dikelola admin di CMS), bukan dari input client,
 *    supaya nilainya tidak bisa dipalsukan.
 */
class SavingsEstimator
{
    public const METHOD_BILL = 'bill';

    public const METHOD_APPLIANCE = 'appliance';

    public function __construct(private readonly CalculatorSettings $settings) {}

    /**
     * @param  string  $method  'bill' | 'appliance'
     * @param  int|null  $monthlyBill  wajib diisi bila $method === 'bill'
     * @param  array<int, array{key: string, qty: int}>|null  $appliances  wajib diisi bila $method === 'appliance'
     * @return array{
     *     normalized_appliances: array<int, array{key: string, label: string, watt: int, qty: int}>,
     *     total_watt: int|null,
     *     estimated_monthly_bill: int,
     *     result: array{savings_year1: int, total_savings_25y: int, estimated_investment: int, breakeven_years: float, annual_kwh: float},
     *     chart: array{investment: int, points: array<int, array{year: int, cumulative_savings: int}>},
     *     assumptions: array<string, int|float>,
     * }
     */
    public function estimate(string $method, ?int $monthlyBill, ?array $appliances): array
    {
        $tariffPerKwh = $this->settings->tariff_per_kwh;
        $coverage = $this->settings->solar_coverage_percent / 100;
        $escalation = 1 + ($this->settings->tariff_escalation_percent / 100);
        $investmentFactor = $this->settings->investment_factor;
        $sunHours = $this->settings->sun_hours_per_day;
        $projectionYears = $this->settings->projection_years;

        $normalizedAppliances = [];
        $totalWatt = null;

        if ($method === self::METHOD_APPLIANCE) {
            $normalizedAppliances = $this->normalizeAppliances($appliances ?? []);
            $totalWatt = array_sum(array_map(fn (array $a) => $a['watt'] * $a['qty'], $normalizedAppliances));

            if ($totalWatt <= 0) {
                throw new InvalidArgumentException('Pilih minimal satu peralatan dengan jumlah lebih dari 0.');
            }

            $monthlyKwh = ($totalWatt / 1000) * $sunHours * 30;
            $effectiveMonthlyBill = (int) round($monthlyKwh * $tariffPerKwh);
        } else {
            if (! $monthlyBill || $monthlyBill <= 0) {
                throw new InvalidArgumentException('Masukkan tagihan listrik bulanan yang valid (lebih dari 0).');
            }

            $effectiveMonthlyBill = $monthlyBill;
        }

        $annualSavingsYear1 = $effectiveMonthlyBill * 12 * $coverage;
        $estimatedInvestment = (int) round($annualSavingsYear1 * $investmentFactor);

        $cumulative = 0.0;
        $breakevenYear = null;
        $points = [];

        for ($year = 1; $year <= $projectionYears; $year++) {
            $cumulative += $annualSavingsYear1 * ($escalation ** ($year - 1));

            if ($breakevenYear === null && $cumulative >= $estimatedInvestment) {
                $breakevenYear = $year;
            }

            $points[] = [
                'year' => $year,
                'cumulative_savings' => (int) round($cumulative),
            ];
        }

        $annualKwh = ($effectiveMonthlyBill / $tariffPerKwh) * 12 * $coverage;

        return [
            'normalized_appliances' => $normalizedAppliances,
            'total_watt' => $totalWatt,
            'estimated_monthly_bill' => $effectiveMonthlyBill,
            'result' => [
                'savings_year1' => (int) round($annualSavingsYear1),
                'total_savings_25y' => (int) round($cumulative),
                'estimated_investment' => $estimatedInvestment,
                'breakeven_years' => (float) ($breakevenYear ?? $projectionYears),
                'annual_kwh' => round($annualKwh, 1),
            ],
            'chart' => [
                'investment' => $estimatedInvestment,
                'points' => $points,
            ],
            'assumptions' => $this->settings->toSnapshot(),
        ];
    }

    /**
     * @param  array<int, array{key: string, qty: int}>  $appliances
     * @return array<int, array{key: string, label: string, watt: int, qty: int}>
     */
    private function normalizeAppliances(array $appliances): array
    {
        $catalog = ElectricityAppliance::activeCatalog()->keyBy('slug');
        $normalized = [];

        foreach ($appliances as $item) {
            $key = $item['key'] ?? null;
            $qty = (int) ($item['qty'] ?? 0);

            if ($qty <= 0 || ! $catalog->has($key)) {
                continue;
            }

            $appliance = $catalog->get($key);

            $normalized[] = [
                'key' => $key,
                'label' => $appliance->name,
                'watt' => $appliance->watt,
                'qty' => $qty,
            ];
        }

        return $normalized;
    }
}
