<?php

namespace Tests\Unit;

use App\Models\CustomPage;
use App\Models\MenuItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuItemUrlResolutionTest extends TestCase
{
    use RefreshDatabase;

    public function test_resolves_external_url(): void
    {
        $item = MenuItem::factory()->external('https://example.com')->create();

        $this->assertSame('https://example.com', $item->resolveUrl());
    }

    public function test_resolves_internal_url_via_linkable_model(): void
    {
        $page = CustomPage::factory()->create(['slug' => 'tentang-kami']);
        $item = MenuItem::factory()->internal($page)->create();

        $this->assertSame($page->getPublicUrl(), $item->resolveUrl());
    }

    public function test_internal_url_follows_slug_change(): void
    {
        $page = CustomPage::factory()->create(['slug' => 'lama']);
        $item = MenuItem::factory()->internal($page)->create();

        $page->update(['slug' => 'baru']);

        $this->assertStringContainsString('baru', (string) $item->fresh()->resolveUrl());
    }

    public function test_returns_null_when_internal_target_is_deleted(): void
    {
        $page = CustomPage::factory()->create();
        $item = MenuItem::factory()->internal($page)->create();

        $page->delete();

        $this->assertNull($item->fresh()->resolveUrl());
    }

    public function test_returns_null_for_no_link_type(): void
    {
        $item = MenuItem::factory()->create(['link_type' => MenuItem::LINK_TYPE_NONE]);

        $this->assertNull($item->resolveUrl());
    }
}
