<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CalculatorLeadResource;
use App\Filament\Resources\ContactSubmissionResource;
use App\Services\LeadDashboardMetrics;
use Filament\Widgets\ChartWidget;

class WeeklyLeadsChart extends ChartWidget
{
    protected static ?string $heading = 'Prospek per minggu';

    protected static ?string $description = '12 minggu terakhir';

    protected static bool $isLazy = false;

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return ContactSubmissionResource::canViewAny() && CalculatorLeadResource::canViewAny();
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $series = app(LeadDashboardMetrics::class)->weeklySeries();

        return [
            'datasets' => [
                [
                    'label' => 'Pesan Masuk',
                    'data' => $series['contact'],
                ],
                [
                    'label' => 'Lead Kalkulator',
                    'data' => $series['calculator'],
                ],
            ],
            'labels' => $series['labels'],
        ];
    }
}
