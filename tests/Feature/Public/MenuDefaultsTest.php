<?php

namespace Tests\Feature\Public;

use App\Filament\Resources\MenuItemResource\Pages\ListMenuItems;
use App\Models\MenuItem;
use App\Models\MenuLocation;
use App\Models\User;
use App\Settings\SiteSettings;
use App\Support\Menu\MenuDefaultsInstaller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MenuDefaultsTest extends TestCase
{
    use RefreshDatabase;

    private function navbar(): MenuLocation
    {
        return MenuLocation::factory()->create(['slug' => 'navbar-utama']);
    }

    public function test_installer_creates_default_navbar_items_in_order(): void
    {
        $location = $this->navbar();

        MenuDefaultsInstaller::install();

        $this->assertSame(
            ['Beranda', 'Tentang Kami', 'Produk', 'Artikel', 'Karir', 'Kontak'],
            MenuItem::where('menu_location_id', $location->id)->orderBy('order_column')->pluck('label')->all()
        );
    }

    public function test_installer_creates_default_footer_columns(): void
    {
        $footer = MenuLocation::factory()->create(['slug' => 'footer']);

        MenuDefaultsInstaller::install();

        $columns = MenuItem::where('menu_location_id', $footer->id)->root()->orderBy('order_column')->get();
        $this->assertSame(['Solusi', 'Perusahaan'], $columns->pluck('label')->all());
        $this->assertSame(
            ['Tentang Kami', 'Blog & Artikel', 'Karir', 'FAQ'],
            $columns->last()->children->pluck('label')->all()
        );
    }

    public function test_footer_renders_default_columns_from_the_builder(): void
    {
        MenuLocation::factory()->create(['slug' => 'footer']);
        MenuDefaultsInstaller::install();

        $this->get('/')->assertOk()
            ->assertSeeInOrder(['Solusi', 'Panel Residensial', 'Perusahaan', 'Tentang Kami', 'FAQ'], escape: false);
    }

    public function test_footer_career_link_is_hidden_when_career_module_is_disabled(): void
    {
        MenuLocation::factory()->create(['slug' => 'footer']);
        MenuDefaultsInstaller::install();
        $settings = app(SiteSettings::class);
        $settings->career_module_enabled = false;
        $settings->save();

        $company = collect(MenuItem::treeForLocation('footer'))->firstWhere('label', 'Perusahaan');

        $this->assertNotContains('Karir', collect($company['children'])->pluck('label')->all());
    }

    public function test_installer_is_idempotent(): void
    {
        $location = $this->navbar();

        MenuDefaultsInstaller::install();
        MenuDefaultsInstaller::install();

        $this->assertSame(6, MenuItem::where('menu_location_id', $location->id)->count());
    }

    public function test_installer_keeps_admin_items_after_the_defaults(): void
    {
        $location = $this->navbar();
        $faq = MenuItem::factory()->external('https://example.com/faq')->create(['menu_location_id' => $location->id, 'label' => 'FAQ Kustom']);
        $terms = MenuItem::factory()->create(['menu_location_id' => $location->id, 'label' => 'Syarat']);

        MenuDefaultsInstaller::install();

        $labels = MenuItem::where('menu_location_id', $location->id)->orderBy('order_column')->orderBy('id')->pluck('label')->all();
        $this->assertSame(['FAQ Kustom', 'Syarat'], array_slice($labels, 6));
        $this->assertSame('Beranda', $labels[0]);
    }

    public function test_admin_can_move_a_custom_item_before_default_items(): void
    {
        $location = $this->navbar();
        $custom = MenuItem::factory()->external('https://example.com/promo')->create(['menu_location_id' => $location->id, 'label' => 'Promo']);
        MenuDefaultsInstaller::install();

        $ids = MenuItem::where('menu_location_id', $location->id)->orderBy('order_column')->orderBy('id')->pluck('id')->all();
        $reordered = array_merge([$custom->id], array_values(array_diff($ids, [$custom->id])));

        Livewire::actingAs(User::factory()->create())
            ->test(ListMenuItems::class, ['tableFilters' => ['menu_location_id' => ['value' => $location->id]]])
            ->call('reorderTable', $reordered);

        $this->get('/')->assertOk()->assertSeeInOrder(['Promo', 'Beranda', 'Tentang Kami'], escape: false);
    }

    public function test_route_item_resolves_to_the_page_url(): void
    {
        $item = MenuItem::factory()->route('kontak')->make();

        $this->assertSame(url('/kontak'), $item->resolveUrl());
    }

    public function test_unknown_route_name_resolves_to_null(): void
    {
        $this->assertNull(MenuItem::factory()->route('tidak-ada')->make()->resolveUrl());
    }

    public function test_career_item_is_hidden_when_career_module_is_disabled(): void
    {
        $this->navbar();
        MenuDefaultsInstaller::install();
        $settings = app(SiteSettings::class);
        $settings->career_module_enabled = false;
        $settings->save();

        $labels = collect(MenuItem::treeForLocation('navbar-utama'))->pluck('label')->all();

        $this->assertNotContains('Karir', $labels);
        $this->assertContains('Kontak', $labels);
    }

    public function test_admin_can_create_route_menu_item(): void
    {
        $location = $this->navbar();

        Livewire::actingAs(User::factory()->create())
            ->test(ListMenuItems::class)
            ->mountAction('create')
            ->setActionData([
                'menu_location_id' => $location->id,
                'label' => 'Hubungi',
                'link_type' => MenuItem::LINK_TYPE_ROUTE,
                'route_name' => 'kontak',
            ])
            ->callMountedAction()
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas('menu_items', ['label' => 'Hubungi', 'link_type' => 'route', 'route_name' => 'kontak']);
    }

    public function test_route_name_is_required_for_route_type(): void
    {
        $location = $this->navbar();

        Livewire::actingAs(User::factory()->create())
            ->test(ListMenuItems::class)
            ->mountAction('create')
            ->setActionData(['menu_location_id' => $location->id, 'label' => 'X', 'link_type' => MenuItem::LINK_TYPE_ROUTE])
            ->callMountedAction()
            ->assertHasActionErrors(['route_name']);
    }
}
