<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CalculatorLeadResource;
use App\Filament\Resources\ContactSubmissionResource;
use App\Models\CalculatorLead;
use App\Models\ContactSubmission;
use App\Services\LeadDashboardMetrics;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LeadStatsOverview extends BaseWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return ContactSubmissionResource::canViewAny() && CalculatorLeadResource::canViewAny();
    }

    protected function getStats(): array
    {
        $metrics = app(LeadDashboardMetrics::class);

        $newContactCount = $metrics->newContactCount();
        $newCalculatorCount = $metrics->newCalculatorCount();
        $last30Days = $metrics->last30Days();
        $conversion = $metrics->conversion();

        return [
            Stat::make('Pesan masuk baru', (string) $newContactCount)
                ->description('Belum dihubungi')
                ->color($newContactCount > 0 ? 'danger' : 'gray')
                ->url(ContactSubmissionResource::getUrl('index', [
                    'tableFilters' => ['status' => ['value' => ContactSubmission::STATUS_NEW]],
                ])),

            Stat::make('Lead kalkulator baru', (string) $newCalculatorCount)
                ->description('Belum di-follow-up')
                ->color($newCalculatorCount > 0 ? 'danger' : 'gray')
                ->url(CalculatorLeadResource::getUrl('index', [
                    'tableFilters' => ['status' => ['value' => CalculatorLead::STATUS_NEW]],
                ])),

            Stat::make('Prospek 30 hari', (string) $last30Days['total'])
                ->description('Pesan masuk + lead kalkulator')
                ->chart($last30Days['daily']),

            Stat::make(
                'Konversi lead kalkulator',
                $conversion['rate'] === null
                    ? 'Belum ada data'
                    : number_format($conversion['rate'], 1, ',', '.').'%'
            )
                ->description("{$conversion['won']} dari {$conversion['total']} lead (90 hari)"),
        ];
    }
}
