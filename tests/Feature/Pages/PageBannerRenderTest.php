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

    /**
     * @return array<string, array{0: PageBlockType, 1: string, 2: string, 3: string}>
     */
    public static function newlyUnifiedPages(): array
    {
        return [
            'faq' => [PageBlockType::FaqHero, '/faq', 'FAQ', 'images/mockup/artikel-4.jpg'],
            'kontak' => [PageBlockType::ContactHero, '/kontak', 'Kontak', 'images/mockup/home-1.jpg'],
            'tentang-kami' => [PageBlockType::AboutHero, '/tentang-kami', 'Tentang Kami', 'images/mockup/produk-1.jpg'],
        ];
    }

    #[DataProvider('newlyUnifiedPages')]
    public function test_faq_contact_and_about_use_the_shared_hero(PageBlockType $type, string $path, string $breadcrumb, string $defaultImage): void
    {
        $html = $this->get($path)->assertOk()->getContent();

        $this->assertStringContainsString('min-h-[max(45vh,380px)]', $html);
        $this->assertStringContainsString($defaultImage, $html);
        $this->assertMatchesRegularExpression('#Beranda</a>\s*<span[^>]*>/</span>\s*'.preg_quote($breadcrumb, '#').'#', $html);
        $this->assertStringNotContainsString('pt-40 pb-16', $html);
    }

    #[DataProvider('newlyUnifiedPages')]
    public function test_uploaded_image_replaces_default_and_missing_file_falls_back_for_unified_pages(PageBlockType $type, string $path, string $breadcrumb, string $defaultImage): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('page-banners/uji.webp', 'x');

        $this->setBlock($type, ['image_path' => 'page-banners/uji.webp']);
        $this->get($path)->assertOk()
            ->assertSee(Storage::disk('public')->url('page-banners/uji.webp'))
            ->assertDontSee($defaultImage);

        $this->setBlock($type, ['image_path' => 'page-banners/hilang.webp']);
        $this->get($path)->assertOk()->assertSee($defaultImage);
    }

    public function test_contact_page_still_renders_the_contact_form_below_the_banner(): void
    {
        $this->get('/kontak')->assertOk()->assertSee('Pertanyaan Seputar Konsultasi', escape: false);
    }

    public function test_faq_page_keeps_its_search_box_below_the_banner(): void
    {
        $html = $this->get('/faq')->assertOk()->getContent();

        $this->assertStringContainsString('Cari pertanyaan', $html);
        $this->assertLessThan(strpos($html, 'Cari pertanyaan'), strpos($html, 'min-h-[max(45vh,380px)]'));
    }
}
