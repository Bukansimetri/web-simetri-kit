<?php

namespace App\Services;

use App\Filament\Resources\CalculatorLeadResource;
use App\Filament\Resources\ContactSubmissionResource;
use App\Models\CalculatorLead;
use App\Models\ContactSubmission;
use App\Settings\SiteSettings;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Perhitungan dashboard prospek admin. Semua batas waktu memakai zona waktu
 * dari `SiteSettings::timezone`, bukan `config('app.timezone')` (UTC) —
 * lihat research.md §2. Query mengambil batas dalam UTC, lalu pengelompokan
 * per tanggal/minggu lokal dilakukan di PHP (research.md §3).
 */
class LeadDashboardMetrics
{
    public function timezone(): string
    {
        return app(SiteSettings::class)->timezone;
    }

    public function now(): CarbonImmutable
    {
        return CarbonImmutable::now($this->timezone());
    }

    public function startOfLocalDaysAgo(int $days): CarbonImmutable
    {
        return $this->now()->subDays($days)->startOfDay()->utc();
    }

    public function newContactCount(): int
    {
        return ContactSubmission::query()->where('status', ContactSubmission::STATUS_NEW)->count();
    }

    public function newCalculatorCount(): int
    {
        return CalculatorLead::query()->where('status', CalculatorLead::STATUS_NEW)->count();
    }

    /**
     * @return array{total: int, daily: array<int, int>}
     */
    public function last30Days(): array
    {
        $since = $this->startOfLocalDaysAgo(29);
        $timezone = $this->timezone();

        $timestamps = [
            ...ContactSubmission::query()->where('created_at', '>=', $since)->pluck('created_at'),
            ...CalculatorLead::query()->where('created_at', '>=', $since)->pluck('created_at'),
        ];

        $daily = array_fill(0, 30, 0);
        $today = $this->now()->startOfDay();

        foreach ($timestamps as $timestamp) {
            $localDate = CarbonImmutable::parse($timestamp)->setTimezone($timezone)->startOfDay();
            $index = 29 - $today->diffInDays($localDate);

            if ($index >= 0 && $index < 30) {
                $daily[$index]++;
            }
        }

        return [
            'total' => count($timestamps),
            'daily' => $daily,
        ];
    }

    /**
     * @return array{won: int, total: int, rate: ?float}
     */
    public function conversion(): array
    {
        $since = $this->startOfLocalDaysAgo(89);

        $total = CalculatorLead::query()->where('created_at', '>=', $since)->count();
        $won = CalculatorLead::query()
            ->where('created_at', '>=', $since)
            ->where('status', CalculatorLead::STATUS_WON)
            ->count();

        return [
            'won' => $won,
            'total' => $total,
            'rate' => $total > 0 ? round($won / $total * 100, 1) : null,
        ];
    }

    /**
     * @return array<int, array{source: string, sourceLabel: string, name: string, area: ?string, createdAt: CarbonImmutable, ageLabel: string, isOverdue: bool, estimatedMonthlyBill: ?int, url: string}>
     */
    public function followUpQueue(int $limit = 10): array
    {
        $now = $this->now();
        $timezone = $this->timezone();

        $contacts = ContactSubmission::query()
            ->where('status', ContactSubmission::STATUS_NEW)
            ->latest('created_at')
            ->limit($limit)
            ->get(['id', 'name', 'area', 'created_at'])
            ->map(function (ContactSubmission $contact) use ($now, $timezone) {
                $createdAt = CarbonImmutable::parse($contact->created_at)->setTimezone($timezone);

                return [
                    'source' => 'contact',
                    'sourceLabel' => 'Pesan Masuk',
                    'name' => $contact->name,
                    'area' => $contact->area,
                    'createdAt' => $createdAt,
                    'ageLabel' => $createdAt->diffForHumans(),
                    'isOverdue' => $now->diffInHours($createdAt, absolute: true) > 48,
                    'estimatedMonthlyBill' => null,
                    'url' => ContactSubmissionResource::getUrl('edit', ['record' => $contact->id]),
                ];
            });

        $calculatorLeads = CalculatorLead::query()
            ->where('status', CalculatorLead::STATUS_NEW)
            ->latest('created_at')
            ->limit($limit)
            ->get(['id', 'name', 'area', 'created_at', 'estimated_monthly_bill'])
            ->map(function (CalculatorLead $lead) use ($now, $timezone) {
                $createdAt = CarbonImmutable::parse($lead->created_at)->setTimezone($timezone);

                return [
                    'source' => 'calculator',
                    'sourceLabel' => 'Lead Kalkulator',
                    'name' => $lead->name,
                    'area' => $lead->area,
                    'createdAt' => $createdAt,
                    'ageLabel' => $createdAt->diffForHumans(),
                    'isOverdue' => $now->diffInHours($createdAt, absolute: true) > 48,
                    'estimatedMonthlyBill' => $lead->estimated_monthly_bill,
                    'url' => CalculatorLeadResource::getUrl('edit', ['record' => $lead->id]),
                ];
            });

        return $contacts->concat($calculatorLeads)
            ->sortByDesc(fn (array $item) => $item['createdAt']->timestamp)
            ->take($limit)
            ->values()
            ->all();
    }

    /**
     * @return array{labels: array<int, string>, contact: array<int, int>, calculator: array<int, int>}
     */
    public function weeklySeries(int $weeks = 12): array
    {
        $timezone = $this->timezone();
        $currentWeekStart = $this->now()->startOfWeek(CarbonImmutable::MONDAY);
        $firstWeekStart = $currentWeekStart->subWeeks($weeks - 1);
        $since = $firstWeekStart->utc();

        $labels = [];
        for ($i = 0; $i < $weeks; $i++) {
            $labels[] = $firstWeekStart->addWeeks($i)->translatedFormat('j M');
        }

        $contact = array_fill(0, $weeks, 0);
        $calculator = array_fill(0, $weeks, 0);

        $this->fillWeeklyCounts(
            ContactSubmission::query()->where('created_at', '>=', $since)->pluck('created_at'),
            $contact,
            $firstWeekStart,
            $timezone,
            $weeks,
        );

        $this->fillWeeklyCounts(
            CalculatorLead::query()->where('created_at', '>=', $since)->pluck('created_at'),
            $calculator,
            $firstWeekStart,
            $timezone,
            $weeks,
        );

        return [
            'labels' => $labels,
            'contact' => $contact,
            'calculator' => $calculator,
        ];
    }

    /**
     * @param  Collection<int, mixed>  $timestamps
     * @param  array<int, int>  $counts
     */
    private function fillWeeklyCounts($timestamps, array &$counts, CarbonImmutable $firstWeekStart, string $timezone, int $weeks): void
    {
        foreach ($timestamps as $timestamp) {
            $weekStart = CarbonImmutable::parse($timestamp)->setTimezone($timezone)->startOfWeek(CarbonImmutable::MONDAY);
            $index = (int) round($firstWeekStart->diffInDays($weekStart, false) / 7);

            if ($index >= 0 && $index < $weeks) {
                $counts[$index]++;
            }
        }
    }
}
