<?php

namespace Tests\Feature\Admin;

use App\Enums\PageBlockType;
use App\Filament\Clusters\BannerCluster;
use App\Filament\Resources\BannerResource;
use App\Filament\Resources\PageBannerResource;
use App\Filament\Resources\PageBannerResource\Pages\EditPageBanner;
use App\Filament\Resources\PageBannerResource\Pages\ListPageBanners;
use App\Models\PageBlock;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PageBannerResourceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create();
    }

    private function block(PageBlockType $type): PageBlock
    {
        return PageBlock::query()->where('block', $type->value)->firstOrFail();
    }

    private function edit(PageBlockType $type)
    {
        return Livewire::actingAs($this->admin())->test(EditPageBanner::class, ['record' => $this->block($type)->getRouteKey()]);
    }

    public function test_every_page_banner_is_listed_in_page_order_and_cannot_be_created_or_deleted(): void
    {
        $expected = array_map(fn (PageBlockType $type): PageBlock => $this->block($type), PageBlockType::pageBanners());

        Livewire::actingAs($this->admin())
            ->test(ListPageBanners::class)
            ->assertOk()
            ->assertCanSeeTableRecords($expected, inOrder: true)
            ->assertCanNotSeeTableRecords([$this->block(PageBlockType::AboutVision), $this->block(PageBlockType::ContactInfo)])
            ->assertCountTableRecords(count(PageBlockType::pageBanners()))
            ->assertTableActionDoesNotExist('delete');

        $this->assertFalse(PageBannerResource::canCreate());
        $this->assertFalse(PageBannerResource::canDelete($this->block(PageBlockType::ProductsHero)));
        $this->assertArrayNotHasKey('create', PageBannerResource::getPages());
    }

    public function test_both_banner_lists_are_tabs_of_one_banner_menu(): void
    {
        $this->assertSame(BannerCluster::class, BannerResource::getCluster());
        $this->assertSame(BannerCluster::class, PageBannerResource::getCluster());
        $this->assertSame('Banner', BannerCluster::getNavigationLabel());
        $this->assertSame('Beranda', BannerCluster::getNavigationGroup());

        $user = User::factory()->create();
        $user->assignRole(Role::create(['name' => 'super_admin']));

        $this->actingAs($user)
            ->get(PageBannerResource::getUrl('index'))
            ->assertOk()
            ->assertSeeInOrder(['Slider Beranda', 'Banner Halaman'])
            ->assertSee(BannerResource::getUrl('index'), escape: false);

        $this->actingAs($user)
            ->get(BannerCluster::getUrl())
            ->assertRedirect(BannerResource::getUrl('index'));
    }

    public function test_only_pages_with_image_banners_get_an_image_field(): void
    {
        foreach ([PageBlockType::ProductsHero, PageBlockType::CareerHero, PageBlockType::AboutHero, PageBlockType::ArticlesHero, PageBlockType::PortfolioHero] as $type) {
            $this->edit($type)
                ->assertFormFieldExists('data.image_path')
                ->assertFormFieldExists('data.title')
                ->assertFormFieldExists('data.subtitle');
        }

        foreach ([PageBlockType::FaqHero, PageBlockType::ContactHero] as $type) {
            $this->edit($type)
                ->assertFormFieldDoesNotExist('data.image_path')
                ->assertFormFieldExists('data.title')
                ->assertFormFieldExists('data.subtitle');
        }
    }

    public function test_form_is_filled_with_current_banner_content(): void
    {
        $this->edit(PageBlockType::FaqHero)
            ->assertFormSet([
                'data.title' => 'Pertanyaan Umum',
                'data.subtitle' => 'Temukan jawaban cepat seputar layanan, instalasi, dan produk panel surya kami.',
            ]);
    }

    public function test_admin_can_edit_title_and_clear_subtitle(): void
    {
        $this->edit(PageBlockType::ContactHero)
            ->fillForm(['data' => ['title' => 'Hubungi Kami', 'subtitle' => '']])
            ->call('save')
            ->assertHasNoFormErrors();

        $data = $this->block(PageBlockType::ContactHero)->data;
        $this->assertSame('Hubungi Kami', $data['title']);
        $this->assertEmpty($data['subtitle']);
    }

    public function test_title_is_required_and_lengths_are_limited(): void
    {
        $this->edit(PageBlockType::ArticlesHero)
            ->fillForm(['data' => ['title' => '', 'subtitle' => 'x']])
            ->call('save')
            ->assertHasFormErrors(['data.title' => 'required']);

        $this->edit(PageBlockType::ArticlesHero)
            ->fillForm(['data' => ['title' => str_repeat('a', 161), 'subtitle' => str_repeat('b', 501)]])
            ->call('save')
            ->assertHasFormErrors(['data.title' => 'max', 'data.subtitle' => 'max']);
    }

    public function test_admin_can_edit_articles_and_portfolio_hero_text(): void
    {
        $this->edit(PageBlockType::ArticlesHero)
            ->fillForm(['data' => ['title' => 'Berita Surya', 'subtitle' => 'Subjudul baru.']])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->edit(PageBlockType::PortfolioHero)
            ->fillForm(['data' => ['title' => 'Karya Kami', 'subtitle' => 'Subjudul portofolio.']])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Berita Surya', $this->block(PageBlockType::ArticlesHero)->data['title']);
        $this->assertSame('Karya Kami', $this->block(PageBlockType::PortfolioHero)->data['title']);
    }

    public function test_uploaded_banner_image_is_stored_as_webp(): void
    {
        Storage::fake('public');

        $this->edit(PageBlockType::ProductsHero)
            ->fillForm(['data' => ['image_path' => UploadedFile::fake()->image('hero.jpg', 1200, 600), 'title' => 'Katalog Produk']])
            ->call('save')
            ->assertHasNoFormErrors();

        $path = $this->block(PageBlockType::ProductsHero)->data['image_path'];

        $this->assertNotEmpty($path);
        $this->assertSame('webp', pathinfo($path, PATHINFO_EXTENSION));
        Storage::disk('public')->assertExists($path);
    }

    public function test_non_banner_blocks_are_not_editable_from_page_banners(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->edit(PageBlockType::ContactInfo);
    }
}
