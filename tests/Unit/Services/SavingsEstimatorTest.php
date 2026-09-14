<?php

namespace Tests\Unit\Services;

use App\Services\SavingsEstimator;
use App\Settings\CalculatorSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Memverifikasi perhitungan SavingsEstimator memakai asumsi default
 * (tariff Rp1.500/kWh, coverage 70%, eskalasi 3%, faktor investasi 6,8x,
 * 6 jam matahari, proyeksi 25 tahun) — sama dengan yang dulu ada di
 * resources/js/calculator.js sebelum dipindah ke server.
 */
class SavingsEstimatorTest extends TestCase
{
    use RefreshDatabase;

    private function estimator(): SavingsEstimator
    {
        return new SavingsEstimator(app(CalculatorSettings::class));
    }

    public function test_estimate_from_monthly_bill(): void
    {
        $out = $this->estimator()->estimate(SavingsEstimator::METHOD_BILL, 1_000_000, null);

        $this->assertSame(1_000_000, $out['estimated_monthly_bill']);
        $this->assertSame(8_400_000, $out['result']['savings_year1']); // 1jt * 12 * 0.7
        $this->assertSame(57_120_000, $out['result']['estimated_investment']); // 8.4jt * 6.8
        $this->assertGreaterThan(0, $out['result']['breakeven_years']);
        $this->assertCount(25, $out['chart']['points']);
        $this->assertSame(57_120_000, $out['chart']['investment']);
        $this->assertSame(1500, $out['assumptions']['tariff_per_kwh']);
    }

    public function test_estimate_from_appliances_uses_server_wattage_catalog(): void
    {
        // Client hanya kirim key+qty; watt harus diambil dari katalog server,
        // bukan dari input client (tidak ada field watt di sini sama sekali).
        $out = $this->estimator()->estimate(SavingsEstimator::METHOD_APPLIANCE, null, [
            ['key' => 'ac', 'qty' => 1], // 1000W
            ['key' => 'kulkas', 'qty' => 1], // 200W
        ]);

        $this->assertSame(1200, $out['total_watt']);
        $this->assertGreaterThan(0, $out['estimated_monthly_bill']);
        $this->assertCount(2, $out['normalized_appliances']);
    }

    public function test_unknown_appliance_key_is_ignored(): void
    {
        $out = $this->estimator()->estimate(SavingsEstimator::METHOD_APPLIANCE, null, [
            ['key' => 'ac', 'qty' => 1],
            ['key' => 'not_a_real_appliance', 'qty' => 99],
        ]);

        $this->assertSame(1000, $out['total_watt']); // hanya AC yang dihitung
        $this->assertCount(1, $out['normalized_appliances']);
    }

    public function test_zero_appliances_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->estimator()->estimate(SavingsEstimator::METHOD_APPLIANCE, null, [
            ['key' => 'ac', 'qty' => 0],
        ]);
    }

    public function test_invalid_bill_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->estimator()->estimate(SavingsEstimator::METHOD_BILL, 0, null);
    }

    public function test_changing_settings_changes_result(): void
    {
        $settings = app(CalculatorSettings::class);
        $settings->tariff_per_kwh = 2000;
        $settings->save();

        $out = $this->estimator()->estimate(SavingsEstimator::METHOD_BILL, 1_000_000, null);

        $this->assertSame(2000, $out['assumptions']['tariff_per_kwh']);
    }
}
