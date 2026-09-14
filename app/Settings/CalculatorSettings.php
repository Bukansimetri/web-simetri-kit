<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * Asumsi yang dipakai kalkulator "Hitung Estimasi Penghematan" di home.
 * Bisa diubah admin dari CMS (App\Filament\Pages\CalculatorSettingsPage)
 * tanpa perlu developer/deploy. Nilai yang berlaku SAAT SEBUAH LEAD dihitung
 * disalin ke kolom `assumptions` pada CalculatorLead — mengubah nilai di
 * sini tidak mengubah angka pada lead yang sudah tersimpan sebelumnya.
 *
 * @see \App\Services\SavingsEstimator
 */
class CalculatorSettings extends Settings
{
    public int $tariff_per_kwh;

    public int $solar_coverage_percent;

    public float $tariff_escalation_percent;

    public float $investment_factor;

    public int $sun_hours_per_day;

    public int $projection_years;

    public static function group(): string
    {
        return 'calculator';
    }

    /**
     * Snapshot asumsi dalam bentuk array — dipakai SavingsEstimator untuk
     * disimpan apa adanya ke kolom `assumptions` tiap lead.
     *
     * @return array<string, int|float>
     */
    public function toSnapshot(): array
    {
        return [
            'tariff_per_kwh' => $this->tariff_per_kwh,
            'solar_coverage_percent' => $this->solar_coverage_percent,
            'tariff_escalation_percent' => $this->tariff_escalation_percent,
            'investment_factor' => $this->investment_factor,
            'sun_hours_per_day' => $this->sun_hours_per_day,
            'projection_years' => $this->projection_years,
        ];
    }
}
