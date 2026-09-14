<?php

namespace Tests\Feature\Public;

use App\Models\CustomPage;
use App\Models\MenuItem;
use App\Models\MenuLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuRenderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_navbar_menu_item_is_rendered_on_homepage(): void
    {
        $location = MenuLocation::factory()->create(['slug' => 'navbar-utama']);
        MenuItem::factory()->external('https://wa.me/6281234567890')->create([
            'menu_location_id' => $location->id,
            'label' => 'Chat WhatsApp',
        ]);

        $this->get('/')->assertOk()->assertSee('Chat WhatsApp', escape: false)
            ->assertSee('https://wa.me/6281234567890', escape: false);
    }

    public function test_inactive_navbar_menu_item_is_not_rendered(): void
    {
        $location = MenuLocation::factory()->create(['slug' => 'navbar-utama']);
        MenuItem::factory()->external('https://example.com/rahasia')->create([
            'menu_location_id' => $location->id,
            'label' => 'Item Nonaktif',
            'is_active' => false,
        ]);

        $this->get('/')->assertOk()->assertDontSee('Item Nonaktif', escape: false);
    }

    public function test_footer_menu_column_renders_from_footer_location(): void
    {
        $location = MenuLocation::factory()->create(['slug' => 'footer']);
        $parent = MenuItem::factory()->create([
            'menu_location_id' => $location->id,
            'label' => 'Sumber Daya',
            'link_type' => MenuItem::LINK_TYPE_NONE,
        ]);
        MenuItem::factory()->external('https://example.com/panduan')->create([
            'menu_location_id' => $location->id,
            'parent_id' => $parent->id,
            'label' => 'Panduan Instalasi',
        ]);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Sumber Daya', escape: false)
            ->assertSee('Panduan Instalasi', escape: false)
            ->assertSee('https://example.com/panduan', escape: false);
    }

    public function test_item_assigned_only_to_footer_does_not_appear_in_navbar(): void
    {
        $navbar = MenuLocation::factory()->create(['slug' => 'navbar-utama']);
        $footer = MenuLocation::factory()->create(['slug' => 'footer']);

        MenuItem::factory()->external('https://example.com/footer-only')->create([
            'menu_location_id' => $footer->id,
            'label' => 'Hanya Footer',
        ]);

        $content = $this->get('/')->assertOk()->getContent();

        $footerStart = strpos($content, '<footer');
        $this->assertNotFalse($footerStart);
        $this->assertStringNotContainsString('Hanya Footer', substr($content, 0, $footerStart));
        $this->assertStringContainsString('Hanya Footer', substr($content, $footerStart));
    }

    public function test_menu_item_pointing_to_deleted_internal_page_renders_without_href(): void
    {
        $location = MenuLocation::factory()->create(['slug' => 'navbar-utama']);
        $page = CustomPage::factory()->create();
        $item = MenuItem::factory()->internal($page)->create([
            'menu_location_id' => $location->id,
            'label' => 'Halaman Terhapus',
        ]);

        $page->delete();

        $response = $this->get('/');

        $response->assertOk()->assertSee('Halaman Terhapus', escape: false);
        $this->assertNull($item->fresh()->resolveUrl());
    }
}
