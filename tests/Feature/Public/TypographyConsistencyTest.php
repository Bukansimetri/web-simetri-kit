<?php

namespace Tests\Feature\Public;

use App\Models\Article;
use App\Models\JobOpening;
use App\Models\PortfolioCategory;
use App\Models\PortfolioProject;
use App\Models\Product;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Judul dan teks tombol CTA di situs publik seragam bold (700) — tidak ada extrabold/black (spec 031 US5).
 */
class TypographyConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $site = app(SiteSettings::class);
        $site->career_module_enabled = true;
        $site->company_email = 'halo@contoh.test';
        $site->save();

        $product = Product::factory()->create(['slug' => 'produk-uji']);
        Product::factory()->count(2)->create(['category_id' => $product->category_id]);
        Article::factory()->create(['slug' => 'artikel-uji'])->attachTag('Surya');
        $category = PortfolioCategory::factory()->create();
        PortfolioProject::factory()->create(['portfolio_category_id' => $category->id, 'slug' => 'proyek-uji']);
        Testimonial::factory()->count(3)->create();
        $member = TeamMember::factory()->create();
        Storage::disk('public')->put($member->photo_path, 'x');
        JobOpening::factory()->create();
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function pages(): array
    {
        return array_combine(
            ['/', '/tentang-kami', '/produk', '/produk/produk-uji', '/artikel', '/artikel/artikel-uji', '/portfolio', '/portfolio/proyek-uji', '/karir', '/faq', '/kontak'],
            array_map(fn (string $path): array => [$path], ['/', '/tentang-kami', '/produk', '/produk/produk-uji', '/artikel', '/artikel/artikel-uji', '/portfolio', '/portfolio/proyek-uji', '/karir', '/faq', '/kontak']),
        );
    }

    #[DataProvider('pages')]
    public function test_no_element_is_bolder_than_bold(string $path): void
    {
        $html = $this->get($path)->assertOk()->getContent();

        $this->assertDoesNotMatchRegularExpression('/font-(extrabold|black)/', $html, "{$path} masih memakai bobot di atas 700.");
    }

    #[DataProvider('pages')]
    public function test_every_filled_cta_button_is_bold(string $path): void
    {
        $html = $this->get($path)->assertOk()->getContent();

        preg_match_all('/<(?:a|button)\b[^>]*class="([^"]*\bbtn-fill\b[^"]*)"/', $html, $matches);

        foreach ($matches[1] as $classes) {
            $this->assertContains('font-bold', preg_split('/\s+/', trim($classes)), "Tombol CTA di {$path} tidak bold: {$classes}");
        }
    }

    public function test_headline_tokens_are_700(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        foreach (['headline-xl', 'headline-lg', 'headline-lg-mobile'] as $token) {
            $this->assertStringContainsString("--text-{$token}--font-weight: 700;", $css);
        }
    }

    public function test_all_headings_and_cta_buttons_are_forced_to_700_by_the_stylesheet(): void
    {
        $css = file_get_contents(resource_path('css/app.css'));

        $this->assertMatchesRegularExpression('/h1,\s*h2,\s*h3,\s*h4,\s*h5,\s*h6,\s*\.btn-fill\s*\{\s*font-weight:\s*700;\s*\}/', $css);
    }

    public function test_similar_headings_across_pages_share_the_same_weight(): void
    {
        $home = $this->get('/')->assertOk()->getContent();
        $about = $this->get('/tentang-kami')->assertOk()->getContent();

        preg_match_all('/<h2 class="([^"]*)"/', $home.$about, $matches);

        foreach ($matches[1] as $classes) {
            $this->assertDoesNotMatchRegularExpression('/font-(thin|light|normal|medium|semibold)\b/', $classes, "Judul section tidak bold: {$classes}");
        }
    }
}
