<?php

namespace App\Filament\Pages;

use App\Settings\CalculatorSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class CalculatorSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Kalkulator Estimasi';

    protected static ?string $title = 'Pengaturan Kalkulator Estimasi Penghematan';

    protected static string $view = 'filament.pages.calculator-settings-page';

    /**
     * @var array<string, mixed>
     */
    public ?array $data = [];

    public function mount(): void
    {
        $settings = app(CalculatorSettings::class);

        $this->form->fill([
            'tariff_per_kwh' => $settings->tariff_per_kwh,
            'solar_coverage_percent' => $settings->solar_coverage_percent,
            'tariff_escalation_percent' => $settings->tariff_escalation_percent,
            'investment_factor' => $settings->investment_factor,
            'sun_hours_per_day' => $settings->sun_hours_per_day,
            'projection_years' => $settings->projection_years,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Asumsi Perhitungan')
                    ->description(
                        'Dipakai untuk menghitung hasil di form "Hitung Estimasi Penghematan" (home). '
                        .'Mengubah nilai di sini HANYA memengaruhi perhitungan baru — lead yang sudah '
                        .'masuk sebelumnya tetap menyimpan angka yang dulu ditampilkan ke pelanggan.'
                    )
                    ->schema([
                        TextInput::make('tariff_per_kwh')
                            ->label('Tarif Listrik per kWh (Rp)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->prefix('Rp')
                            ->helperText('Tarif rata-rata PLN yang dipakai sebagai dasar hitungan.'),
                        TextInput::make('solar_coverage_percent')
                            ->label('Cakupan Panel Surya (%)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(100)
                            ->suffix('%')
                            ->helperText('Perkiraan porsi konsumsi listrik bulanan yang bisa ditutup oleh panel surya.'),
                        TextInput::make('tariff_escalation_percent')
                            ->label('Eskalasi Tarif Listrik per Tahun (%)')
                            ->numeric()
                            ->required()
                            ->minValue(0)
                            ->suffix('%')
                            ->helperText('Perkiraan kenaikan tarif listrik tiap tahun, dipakai untuk proyeksi hemat jangka panjang.'),
                        TextInput::make('investment_factor')
                            ->label('Faktor Investasi (kelipatan hemat tahun 1)')
                            ->numeric()
                            ->required()
                            ->minValue(0.1)
                            ->step(0.1)
                            ->suffix('×')
                            ->helperText('Perkiraan biaya sistem = faktor ini dikali estimasi hemat tahun pertama.'),
                        TextInput::make('sun_hours_per_day')
                            ->label('Jam Matahari Efektif per Hari')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(24)
                            ->suffix('jam')
                            ->helperText('Dipakai saat menghitung dari daftar peralatan (bukan dari nominal tagihan).'),
                        TextInput::make('projection_years')
                            ->label('Masa Proyeksi (tahun)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(50)
                            ->suffix('tahun')
                            ->helperText('Umur sistem yang dipakai untuk menghitung total hemat & titik balik modal.'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $settings = app(CalculatorSettings::class);
        $settings->tariff_per_kwh = (int) $data['tariff_per_kwh'];
        $settings->solar_coverage_percent = (int) $data['solar_coverage_percent'];
        $settings->tariff_escalation_percent = (float) $data['tariff_escalation_percent'];
        $settings->investment_factor = (float) $data['investment_factor'];
        $settings->sun_hours_per_day = (int) $data['sun_hours_per_day'];
        $settings->projection_years = (int) $data['projection_years'];
        $settings->save();

        Notification::make()
            ->success()
            ->title('Pengaturan kalkulator tersimpan')
            ->send();
    }
}
