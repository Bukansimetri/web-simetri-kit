<?php

namespace App\Filament\Pages;

use App\Services\Analytics\AnalyticsAvailability;
use BezhanSalleh\FilamentGoogleAnalytics\Widgets\MostVisitedPagesWidget;
use BezhanSalleh\FilamentGoogleAnalytics\Widgets\PageViewsWidget;
use BezhanSalleh\FilamentGoogleAnalytics\Widgets\TopReferrersListWidget;
use BezhanSalleh\FilamentGoogleAnalytics\Widgets\VisitorsWidget;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;

class Dashboard extends BaseDashboard
{
    private const ANALYTICS_WIDGETS = [
        VisitorsWidget::class,
        PageViewsWidget::class,
        MostVisitedPagesWidget::class,
        TopReferrersListWidget::class,
    ];

    public function mount(): void
    {
        if (! app(AnalyticsAvailability::class)->isConfigured()) {
            Notification::make()
                ->warning()
                ->title('Analytics belum dikonfigurasi')
                ->body('Hubungkan kredensial Google Analytics (GA4) di file .env untuk melihat data traffic website di dashboard ini.')
                ->persistent()
                ->send();
        }
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        $widgets = parent::getWidgets();

        if (app(AnalyticsAvailability::class)->isConfigured()) {
            return $widgets;
        }

        return array_values(array_diff($widgets, self::ANALYTICS_WIDGETS));
    }
}
