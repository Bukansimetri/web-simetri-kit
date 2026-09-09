<?php

namespace Tests\Unit;

use App\Models\Banner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BannerLiveScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_live_scope_includes_and_excludes_correctly(): void
    {
        $noPeriod = Banner::factory()->create();
        $spanning = Banner::factory()->create(['starts_at' => today()->subDay(), 'ends_at' => today()->addDay()]);
        $startsToday = Banner::factory()->create(['starts_at' => today(), 'ends_at' => null]);
        $endsToday = Banner::factory()->create(['starts_at' => null, 'ends_at' => today()]);

        $expired = Banner::factory()->expired()->create();
        $notStarted = Banner::factory()->scheduled()->create();
        $inactive = Banner::factory()->create(['is_active' => false, 'starts_at' => today()->subDay(), 'ends_at' => today()->addDay()]);

        $liveIds = Banner::live()->pluck('id');

        $this->assertEqualsCanonicalizing(
            [$noPeriod->id, $spanning->id, $startsToday->id, $endsToday->id],
            $liveIds->all(),
        );
        $this->assertNotContains($expired->id, $liveIds);
        $this->assertNotContains($notStarted->id, $liveIds);
        $this->assertNotContains($inactive->id, $liveIds);
    }

    public function test_live_scope_orders_by_order_then_id(): void
    {
        $second = Banner::factory()->create(['order' => 2]);
        $first = Banner::factory()->create(['order' => 1]);

        $this->assertSame([$first->id, $second->id], Banner::live()->pluck('id')->all());
    }

    public function test_display_status_matches_live_scope(): void
    {
        $live = Banner::factory()->create(['starts_at' => today(), 'ends_at' => today()]);
        $inactive = Banner::factory()->inactive()->create();
        $scheduled = Banner::factory()->scheduled()->create();
        $expired = Banner::factory()->expired()->create();

        $this->assertSame('live', $live->displayStatus());
        $this->assertSame('inactive', $inactive->displayStatus());
        $this->assertSame('scheduled', $scheduled->displayStatus());
        $this->assertSame('expired', $expired->displayStatus());

        $liveIds = Banner::live()->pluck('id')->all();
        $this->assertContains($live->id, $liveIds);
        $this->assertNotContains($inactive->id, $liveIds);
        $this->assertNotContains($scheduled->id, $liveIds);
        $this->assertNotContains($expired->id, $liveIds);
    }
}
