<?php

namespace Tests\Feature\Dashboard;

use App\Filament\Resources\CalculatorLeadResource;
use App\Filament\Resources\ContactSubmissionResource;
use App\Models\CalculatorLead;
use App\Models\ContactSubmission;
use App\Services\LeadDashboardMetrics;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeadDashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_start_of_local_days_ago_uses_site_timezone(): void
    {
        $settings = app(SiteSettings::class);
        $settings->timezone = 'Asia/Jakarta';
        $settings->save();

        $this->travelTo('2026-09-10 18:30:00 UTC');

        $metrics = app(LeadDashboardMetrics::class);

        $this->assertSame(
            '2026-09-10 17:00:00',
            $metrics->startOfLocalDaysAgo(0)->utc()->format('Y-m-d H:i:s')
        );

        $settings->timezone = 'UTC';
        $settings->save();

        $this->assertSame(
            '2026-09-10 00:00:00',
            $metrics->startOfLocalDaysAgo(0)->utc()->format('Y-m-d H:i:s')
        );
    }

    public function test_new_counts_only_include_new_status(): void
    {
        ContactSubmission::factory()->count(3)->create(['status' => ContactSubmission::STATUS_NEW]);
        ContactSubmission::factory()->count(2)->create(['status' => ContactSubmission::STATUS_CONTACTED]);
        CalculatorLead::factory()->count(4)->create(['status' => CalculatorLead::STATUS_NEW]);
        CalculatorLead::factory()->won()->create();

        $metrics = app(LeadDashboardMetrics::class);

        $this->assertSame(3, $metrics->newContactCount());
        $this->assertSame(4, $metrics->newCalculatorCount());
    }

    public function test_last_30_days_returns_total_and_daily_breakdown_in_site_timezone(): void
    {
        $settings = app(SiteSettings::class);
        $settings->timezone = 'Asia/Jakarta';
        $settings->save();

        $this->travelTo('2026-09-10 12:00:00 UTC');

        // 31 hari lalu (dari "hari ini" WIB) -> tidak dihitung.
        ContactSubmission::factory()->create(['created_at' => now()->subDays(31)]);

        // Masuk pukul 01.00 WIB (18.00 UTC hari sebelumnya) -> harus masuk
        // tanggal lokal yang benar (hari berikutnya di WIB), bukan tanggal UTC.
        $this->travelTo('2026-09-08 18:00:00 UTC');
        ContactSubmission::factory()->create();

        $this->travelTo('2026-09-10 12:00:00 UTC');
        ContactSubmission::factory()->count(2)->create();
        CalculatorLead::factory()->count(3)->create();

        $metrics = app(LeadDashboardMetrics::class);
        $result = $metrics->last30Days();

        $this->assertSame(6, $result['total']);
        $this->assertCount(30, $result['daily']);
        $this->assertSame(0, $result['daily'][0]);
    }

    public function test_conversion_uses_calculator_leads_within_90_days(): void
    {
        $this->travelTo('2026-09-10 12:00:00 UTC');

        CalculatorLead::factory()->count(9)->create();
        CalculatorLead::factory()->won()->create();

        // Lead 91 hari lalu tidak dihitung.
        CalculatorLead::factory()->create(['created_at' => now()->subDays(91)]);

        $metrics = app(LeadDashboardMetrics::class);

        $this->assertSame([
            'won' => 1,
            'total' => 10,
            'rate' => 10.0,
        ], $metrics->conversion());
    }

    public function test_conversion_rate_is_null_without_leads(): void
    {
        $metrics = app(LeadDashboardMetrics::class);

        $this->assertSame([
            'won' => 0,
            'total' => 0,
            'rate' => null,
        ], $metrics->conversion());
    }

    public function test_follow_up_queue_combines_new_leads_up_to_limit(): void
    {
        $this->travelTo('2026-09-10 12:00:00 UTC');

        // 12 prospek "isian" berstatus new tapi jauh lebih lama, harus
        // tersingkir dari 3 prospek baru di bawah saat dibatasi 10 item.
        for ($i = 0; $i < 7; $i++) {
            ContactSubmission::factory()->create(['created_at' => now()->subDays(5)->subMinutes($i)]);
        }
        for ($i = 0; $i < 5; $i++) {
            CalculatorLead::factory()->create(['created_at' => now()->subDays(5)->subMinutes($i)]);
        }

        // Bukan status new -> tidak ikut sama sekali.
        ContactSubmission::factory()->create(['status' => ContactSubmission::STATUS_CONTACTED]);
        CalculatorLead::factory()->won();

        $overdueContact = ContactSubmission::factory()->create(['created_at' => now()->subHours(49)]);
        $freshContact = ContactSubmission::factory()->create(['created_at' => now()->subHours(47)]);
        $calculatorLead = CalculatorLead::factory()->create(['created_at' => now(), 'estimated_monthly_bill' => 1_250_000]);

        $metrics = app(LeadDashboardMetrics::class);
        $queue = $metrics->followUpQueue();

        $this->assertCount(10, $queue);

        $timestamps = array_map(fn (array $item) => $item['createdAt']->timestamp, $queue);
        $sorted = $timestamps;
        rsort($sorted);
        $this->assertSame($sorted, $timestamps);

        $overdueItem = collect($queue)->firstWhere('name', $overdueContact->name);
        $this->assertNotNull($overdueItem);
        $this->assertTrue($overdueItem['isOverdue']);
        $this->assertNull($overdueItem['estimatedMonthlyBill']);
        $this->assertSame(
            ContactSubmissionResource::getUrl('edit', ['record' => $overdueContact->id]),
            $overdueItem['url']
        );

        $freshItem = collect($queue)->firstWhere('name', $freshContact->name);
        $this->assertNotNull($freshItem);
        $this->assertFalse($freshItem['isOverdue']);

        $calculatorItem = collect($queue)->firstWhere('name', $calculatorLead->name);
        $this->assertNotNull($calculatorItem);
        $this->assertSame(1_250_000, $calculatorItem['estimatedMonthlyBill']);
        $this->assertSame(
            CalculatorLeadResource::getUrl('edit', ['record' => $calculatorLead->id]),
            $calculatorItem['url']
        );
    }

    public function test_weekly_series_groups_by_local_monday_start(): void
    {
        $settings = app(SiteSettings::class);
        $settings->timezone = 'Asia/Jakarta';
        $settings->save();

        // "Sekarang" = Senin, 14 September 2026 00:30 WIB -> minggu berjalan
        // dimulai 14 Sep (slot terakhir, index 11).
        $this->travelTo('2026-09-13 17:30:00 UTC');

        // Minggu, 6 September 2026 23:30 WIB (16:30 UTC) -> masuk minggu
        // yang dimulai Senin 31 Agustus (index 9), karena minggu belum
        // berganti (masih hari Minggu lokal).
        ContactSubmission::factory()->create(['created_at' => '2026-09-06 16:30:00 UTC']);

        // Senin, 7 September 2026 00:30 WIB (2026-09-06 17:30 UTC) -> sudah
        // masuk minggu berikutnya, yang dimulai Senin 7 September (index 10).
        ContactSubmission::factory()->create(['created_at' => '2026-09-06 17:30:00 UTC']);
        CalculatorLead::factory()->count(2)->create(['created_at' => '2026-09-06 17:30:00 UTC']);

        // Lebih dari 12 minggu lalu -> tidak dihitung.
        ContactSubmission::factory()->create(['created_at' => now()->subWeeks(13)]);

        $metrics = app(LeadDashboardMetrics::class);
        $series = $metrics->weeklySeries();

        $this->assertCount(12, $series['labels']);
        $this->assertCount(12, $series['contact']);
        $this->assertCount(12, $series['calculator']);

        // Minggu berjalan (index 11) kosong -> bernilai 0, bukan dilewati.
        $this->assertSame(0, $series['contact'][11]);
        $this->assertSame(0, $series['calculator'][11]);

        // Minggu yang dimulai 7 September (index 10) berisi lead Senin 00:30 WIB.
        $this->assertSame(1, $series['contact'][10]);
        $this->assertSame(2, $series['calculator'][10]);

        // Minggu yang dimulai 31 Agustus (index 9) berisi pesan Minggu 23:30 WIB.
        $this->assertSame(1, $series['contact'][9]);
        $this->assertSame(0, $series['calculator'][9]);
    }
}
