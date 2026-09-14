<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Asumsi default sama persis dengan yang dulu dipakai di
     * resources/js/calculator.js sebelum perhitungan dipindah ke server,
     * supaya angka yang ditampilkan tidak berubah untuk pengunjung yang
     * pertama kali melihatnya setelah deploy.
     */
    public function up(): void
    {
        $this->migrator->add('calculator.tariff_per_kwh', 1500);
        $this->migrator->add('calculator.solar_coverage_percent', 70);
        $this->migrator->add('calculator.tariff_escalation_percent', 3);
        $this->migrator->add('calculator.investment_factor', 6.8);
        $this->migrator->add('calculator.sun_hours_per_day', 6);
        $this->migrator->add('calculator.projection_years', 25);
    }
};
