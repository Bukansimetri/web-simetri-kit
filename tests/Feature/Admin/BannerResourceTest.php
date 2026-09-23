<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\BannerResource\Pages\CreateBanner;
use App\Filament\Resources\BannerResource\Pages\EditBanner;
use App\Filament\Resources\BannerResource\Pages\ListBanners;
use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BannerResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_render_list_page(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(ListBanners::class)
            ->assertOk();
    }

    public function test_admin_can_create_banner_without_link_or_period(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())
            ->test(CreateBanner::class)
            ->fillForm([
                'title' => 'Promo Kemerdekaan',
                'image_path' => UploadedFile::fake()->image('banner.jpg', 800, 300),
                'alt_text' => 'Diskon 17% pemasangan',
                'order' => 1,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $banner = Banner::where('title', 'Promo Kemerdekaan')->first();

        $this->assertNotNull($banner);
        $this->assertNull($banner->link_url);
        $this->assertNull($banner->starts_at);
        $this->assertSame('webp', pathinfo($banner->image_path, PATHINFO_EXTENSION));
        Storage::disk('public')->assertExists($banner->image_path);
    }

    public function test_wide_image_is_downscaled_to_1600(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())
            ->test(CreateBanner::class)
            ->fillForm([
                'title' => 'Banner Lebar',
                'image_path' => UploadedFile::fake()->image('wide.jpg', 2400, 900),
                'alt_text' => 'x',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $path = Banner::where('title', 'Banner Lebar')->first()->image_path;
        $info = getimagesizefromstring(Storage::disk('public')->get($path));

        $this->assertSame(1600, $info[0]);
        $this->assertSame(600, $info[1]);
    }

    public function test_required_fields_are_validated(): void
    {
        Livewire::actingAs(User::factory()->create())
            ->test(CreateBanner::class)
            ->fillForm([
                'title' => '',
                'image_path' => null,
                'alt_text' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['title', 'image_path', 'alt_text']);
    }

    public function test_link_url_without_scheme_is_rejected(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())
            ->test(CreateBanner::class)
            ->fillForm([
                'title' => 'Banner',
                'image_path' => UploadedFile::fake()->image('b.jpg', 400, 200),
                'alt_text' => 'x',
                'link_url' => 'contoh.com',
            ])
            ->call('create')
            ->assertHasFormErrors(['link_url']);
    }

    public function test_valid_link_url_is_accepted(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())
            ->test(CreateBanner::class)
            ->fillForm([
                'title' => 'Banner',
                'image_path' => UploadedFile::fake()->image('b.jpg', 400, 200),
                'alt_text' => 'x',
                'link_url' => 'https://x.test/promo',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('banners', ['link_url' => 'https://x.test/promo']);
    }

    public function test_end_date_before_start_date_is_rejected(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())
            ->test(CreateBanner::class)
            ->fillForm([
                'title' => 'Banner',
                'image_path' => UploadedFile::fake()->image('b.jpg', 400, 200),
                'alt_text' => 'x',
                'starts_at' => today()->addDays(5),
                'ends_at' => today()->addDay(),
            ])
            ->call('create')
            ->assertHasFormErrors(['ends_at']);
    }

    public function test_both_dates_blank_is_accepted(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())
            ->test(CreateBanner::class)
            ->fillForm([
                'title' => 'Banner Tanpa Periode',
                'image_path' => UploadedFile::fake()->image('b.jpg', 400, 200),
                'alt_text' => 'x',
                'starts_at' => null,
                'ends_at' => null,
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    public function test_admin_can_edit_banner(): void
    {
        Storage::fake('public');
        $banner = Banner::factory()->create(['order' => 5]);
        Storage::disk('public')->put($banner->image_path, 'fake-bytes');

        Livewire::actingAs(User::factory()->create())
            ->test(EditBanner::class, ['record' => $banner->getRouteKey()])
            ->fillForm(['order' => 2])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(2, $banner->fresh()->order);
    }

    public function test_admin_can_delete_banner(): void
    {
        $banner = Banner::factory()->create();

        Livewire::actingAs(User::factory()->create())
            ->test(ListBanners::class)
            ->callTableAction('delete', $banner);

        $this->assertDatabaseMissing('banners', ['id' => $banner->id]);
    }

    /**
     * T016: label CTA diisi tanpa alamat MUST ditolak, dan sebaliknya (FR-003).
     */
    public function test_cta_label_without_url_is_rejected(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())
            ->test(CreateBanner::class)
            ->fillForm([
                'title' => 'Banner CTA',
                'image_path' => UploadedFile::fake()->image('b.jpg', 400, 200),
                'alt_text' => 'x',
                'cta_primary_label' => 'Konsultasi',
                'cta_primary_url' => '',
            ])
            ->call('create')
            ->assertHasFormErrors(['cta_primary_url']);
    }

    public function test_cta_url_without_label_is_rejected(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())
            ->test(CreateBanner::class)
            ->fillForm([
                'title' => 'Banner CTA',
                'image_path' => UploadedFile::fake()->image('b.jpg', 400, 200),
                'alt_text' => 'x',
                'cta_secondary_label' => '',
                'cta_secondary_url' => '/kontak',
            ])
            ->call('create')
            ->assertHasFormErrors(['cta_secondary_label']);
    }

    public function test_cta_pair_fully_filled_is_accepted(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())
            ->test(CreateBanner::class)
            ->fillForm([
                'title' => 'Banner CTA Lengkap',
                'image_path' => UploadedFile::fake()->image('b.jpg', 400, 200),
                'alt_text' => 'x',
                'cta_primary_label' => 'Konsultasi Gratis',
                'cta_primary_url' => '/kontak',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('banners', ['cta_primary_label' => 'Konsultasi Gratis', 'cta_primary_url' => '/kontak']);
    }

    /**
     * T017: cache `public-page:home` MUST terbuang setelah banner disimpan
     * dan setelah dihapus (FR-018).
     */
    public function test_saving_banner_invalidates_home_cache(): void
    {
        Storage::fake('public');
        Cache::put('public-page:home', 'stale-value', 300);

        Livewire::actingAs(User::factory()->create())
            ->test(CreateBanner::class)
            ->fillForm([
                'title' => 'Banner Cache',
                'image_path' => UploadedFile::fake()->image('b.jpg', 400, 200),
                'alt_text' => 'x',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertFalse(Cache::has('public-page:home'));
    }

    public function test_deleting_banner_invalidates_home_cache(): void
    {
        $banner = Banner::factory()->create();
        Cache::put('public-page:home', 'stale-value', 300);

        Livewire::actingAs(User::factory()->create())
            ->test(ListBanners::class)
            ->callTableAction('delete', $banner);

        $this->assertFalse(Cache::has('public-page:home'));
    }

    /**
     * Reorder drag-and-drop Filament memakai mass-update lewat query
     * builder, bukan Eloquent save() per baris, jadi event `saved` MODEL
     * tidak ikut terpicu — ListBanners::reorderTable() membuang cache
     * secara eksplisit untuk menutup celah ini (FR-018).
     */
    public function test_reordering_banners_invalidates_home_cache(): void
    {
        $first = Banner::factory()->create(['order' => 1]);
        $second = Banner::factory()->create(['order' => 2]);
        Cache::put('public-page:home', 'stale-value', 300);

        Livewire::actingAs(User::factory()->create())
            ->test(ListBanners::class)
            ->call('reorderTable', [$second->id, $first->id]);

        $this->assertFalse(Cache::has('public-page:home'));
        $this->assertSame(1, $second->fresh()->order);
        $this->assertSame(2, $first->fresh()->order);
    }

    /**
     * T040: `overlay_style` dan `text_position` hanya menerima nilai enum.
     */
    public function test_invalid_overlay_style_is_rejected(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())
            ->test(CreateBanner::class)
            ->fillForm([
                'title' => 'Banner Preset',
                'image_path' => UploadedFile::fake()->image('b.jpg', 400, 200),
                'alt_text' => 'x',
                'overlay_style' => 'ungu',
            ])
            ->call('create')
            ->assertHasFormErrors(['overlay_style']);
    }

    public function test_invalid_text_position_is_rejected(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())
            ->test(CreateBanner::class)
            ->fillForm([
                'title' => 'Banner Preset',
                'image_path' => UploadedFile::fake()->image('b.jpg', 400, 200),
                'alt_text' => 'x',
                'text_position' => 'atas',
            ])
            ->call('create')
            ->assertHasFormErrors(['text_position']);
    }

    public function test_valid_preset_values_are_accepted(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())
            ->test(CreateBanner::class)
            ->fillForm([
                'title' => 'Banner Preset Valid',
                'image_path' => UploadedFile::fake()->image('b.jpg', 400, 200),
                'alt_text' => 'x',
                'overlay_style' => 'light',
                'text_position' => 'center',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('banners', ['overlay_style' => 'light', 'text_position' => 'center']);
    }

    public function test_image_up_to_10mb_is_accepted(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())
            ->test(CreateBanner::class)
            ->fillForm([
                'title' => 'Banner Gambar Besar',
                'image_path' => UploadedFile::fake()->image('besar.jpg', 800, 300)->size(9 * 1024),
                'alt_text' => 'x',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('banners', ['title' => 'Banner Gambar Besar']);
    }

    public function test_image_over_10mb_is_rejected(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->create())
            ->test(CreateBanner::class)
            ->fillForm([
                'title' => 'Banner Gambar Terlalu Besar',
                'image_path' => UploadedFile::fake()->image('terlalu-besar.jpg', 800, 300)->size(11 * 1024),
                'alt_text' => 'x',
            ])
            ->call('create')
            ->assertHasFormErrors(['image_path']);

        $this->assertDatabaseMissing('banners', ['title' => 'Banner Gambar Terlalu Besar']);
    }
}
