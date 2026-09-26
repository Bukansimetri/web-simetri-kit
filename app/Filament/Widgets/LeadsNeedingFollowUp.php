<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\CalculatorLeadResource;
use App\Filament\Resources\ContactSubmissionResource;
use App\Models\CalculatorLead;
use App\Models\ContactSubmission;
use App\Services\LeadDashboardMetrics;
use Carbon\CarbonImmutable;
use Filament\Widgets\Widget;

class LeadsNeedingFollowUp extends Widget
{
    protected static string $view = 'filament.widgets.leads-needing-follow-up';

    protected static bool $isLazy = false;

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return ContactSubmissionResource::canViewAny() && CalculatorLeadResource::canViewAny();
    }

    /**
     * @return array<int, array{source: string, sourceLabel: string, name: string, area: ?string, createdAt: CarbonImmutable, ageLabel: string, isOverdue: bool, estimatedMonthlyBill: ?int, url: string}>
     */
    public function getQueue(): array
    {
        return app(LeadDashboardMetrics::class)->followUpQueue();
    }

    public function getContactListUrl(): string
    {
        return ContactSubmissionResource::getUrl('index', [
            'tableFilters' => ['status' => ['value' => ContactSubmission::STATUS_NEW]],
        ]);
    }

    public function getCalculatorListUrl(): string
    {
        return CalculatorLeadResource::getUrl('index', [
            'tableFilters' => ['status' => ['value' => CalculatorLead::STATUS_NEW]],
        ]);
    }
}
