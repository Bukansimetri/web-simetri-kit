<?php

namespace Tests\Feature\Pages;

use App\Enums\PageBlockType;
use App\Models\PageBlock;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PageBannerRenderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $site = app(SiteSettings::class);
        $site->career_module_enabled = true;
        $site->save();
    }

    private function setBlock(PageBlockType $type, array $data): void
    {
        $block = PageBlock::query()->where('block', $type->value)->firstOrFail();
        $block->update(['data' => [...$block->data, ...$data]]);
    }

    /**
     * @return array<string, array{0: PageBlockType, 1: string}>
     */
    public static function pages(): array
    {
        return [
            'produk' => [PageBlockType::ProductsHero, '/produk'],
            'tentang-kami' => [PageBlockType::AboutHero, '/tentang-kami'],
            'karir' => [PageBlockType::CareerHero, '/karir'],
            'artikel' => [PageBlockType::ArticlesHero, '/artikel'],
            'portfolio' => [PageBlockType::PortfolioHero, '/portfolio'],
            'faq' => [PageBlockType::FaqHero, '/faq'],
            'kontak' => [PageBlockType::ContactHero, '/kontak'],
        ];
    }

    #[DataProvider('pages')]
    public function test_banner_shows_edited_title_and_subtitle(PageBlockType $type, string $path): void
    {
        $this->setBlock($type, ['title' => 'Judul Banner <Editan>', 'subtitle' => 'Subjudul banner editan.']);

        $this->get($path)
            ->assertOk()
            ->assertSee('Judul Banner &lt;Editan&gt;', escape: false)
            ->assertSee('Subjudul banner editan.');
    }

    #[DataProvider('pages')]
    public function test_empty_subtitle_is_not_rendered(PageBlockType $type, string $path): void
    {
        $original = PageBlock::query()->where('block', $type->value)->firstOrFail()->data['subtitle'];

        $this->setBlock($type, ['subtitle' => null]);

        $body = Str::after($this->get($path)->assertOk()->getContent(), '<body');

        $this->assertStringNotContainsString(e($original), $body);
    }

    public function test_uploaded_banner_image_replaces_default_and_missing_file_falls_back(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('page-banners/produk.webp', 'x');

        $this->setBlock(PageBlockType::ProductsHero, ['image_path' => 'page-banners/produk.webp']);
        $this->setBlock(PageBlockType::CareerHero, ['image_path' => 'page-banners/tidak-ada.webp']);

        $this->get('/produk')->assertOk()
            ->assertSee(Storage::disk('public')->url('page-banners/produk.webp'))
            ->assertDontSee('images/mockup/produk-1.jpg');

        $this->get('/karir')->assertOk()->assertSee('images/mockup/home-3.jpg');
    }

    public function test_articles_and_portfolio_banners_use_hero_with_breadcrumb_and_default_images(): void
    {
        $this->get('/artikel')->assertOk()
            ->assertSee('Wawasan & Artikel')
            ->assertSee('images/mockup/artikel-3.jpg')
            ->assertSeeInOrder(['Beranda', 'Artikel'], escape: false);

        $this->get('/portfolio')->assertOk()
            ->assertSee('Portofolio Proyek')
            ->assertSee('images/mockup/artikel-1.jpg')
            ->assertSeeInOrder(['Beranda', 'Portofolio'], escape: false);
    }

    public function test_uploaded_image_replaces_default_on_articles_and_portfolio_and_missing_file_falls_back(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('page-banners/artikel.webp', 'x');

        $this->setBlock(PageBlockType::ArticlesHero, ['image_path' => 'page-banners/artikel.webp']);
        $this->setBlock(PageBlockType::PortfolioHero, ['image_path' => 'page-banners/hilang.webp']);

        $this->get('/artikel')->assertOk()
            ->assertSee(Storage::disk('public')->url('page-banners/artikel.webp'))
            ->assertDontSee('images/mockup/artikel-3.jpg');

        $this->get('/portfolio')->assertOk()->assertSee('images/mockup/artikel-1.jpg');
    }
}
