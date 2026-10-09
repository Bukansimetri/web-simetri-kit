<?php

namespace Tests\Feature\Pages;

use App\Enums\CtaPlacement;
use App\Models\CallToAction;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\LegacyMarkup;
use Tests\TestCase;

class CallToActionRenderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array<string, string>
     */
    private const FRAGMENT_BY_PLACEMENT = [
        'beranda' => 'home-cta',
        'produk-kalkulator' => 'produk-cta-kalkulator',
        'produk-penutup' => 'produk-cta-penutup',
        'produk-detail' => 'produk-detail-cta',
        'artikel-daftar' => 'artikel-index-cta',
        'artikel-detail' => 'artikel-detail-cta',
        'tentang-kami' => 'tentang-kami-cta',
        'faq' => 'faq-cta',
        'karir' => 'karir-cta',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        LegacyMarkup::seedDeterministicState();
    }

    private function updateCta(CtaPlacement $placement, array $attributes): void
    {
        CallToAction::query()->where('placement', $placement->value)->update($attributes);
    }

    /**
     * @return array<string, array{0: CtaPlacement}>
     */
    public static function placements(): array
    {
        $cases = [];

        foreach (CtaPlacement::cases() as $placement) {
            $cases[$placement->value] = [$placement];
        }

        return $cases;
    }

    #[DataProvider('placements')]
    public function test_editing_one_placement_only_changes_its_own_page(CtaPlacement $placement): void
    {
        $this->updateCta($placement, ['title' => 'Judul CTA Uji']);

        foreach (self::FRAGMENT_BY_PLACEMENT as $placementValue => $fragment) {
            [$path] = LegacyMarkup::FRAGMENTS[$fragment];
            $html = $this->get($path)->assertOk()->getContent();

            if ($placementValue === $placement->value) {
                $this->assertStringContainsString('Judul CTA Uji', $html, "CTA {$placementValue} tidak berubah di {$path}");
            } elseif (! $this->sharesPage($placement, CtaPlacement::from($placementValue))) {
                $this->assertStringNotContainsString('Judul CTA Uji', $html, "CTA {$placementValue} ikut berubah di {$path}");
            }
        }
    }

    private function sharesPage(CtaPlacement $a, CtaPlacement $b): bool
    {
        return LegacyMarkup::FRAGMENTS[self::FRAGMENT_BY_PLACEMENT[$a->value]][0] === LegacyMarkup::FRAGMENTS[self::FRAGMENT_BY_PLACEMENT[$b->value]][0];
    }

    public function test_home_form_button_label_changes_but_link_goes_to_the_calculator(): void
    {
        $this->updateCta(CtaPlacement::Home, ['secondary_label' => 'Minta Penawaran']);

        $fragment = LegacyMarkup::extract($this->get('/')->getContent(), LegacyMarkup::FRAGMENTS['home-cta'][1]);

        $this->assertMatchesRegularExpression('#href="[^"]*/\#kalkulator"[^>]*> Minta Penawaran </a>#', $fragment);
    }

    public function test_product_detail_body_inserts_product_name(): void
    {
        $this->updateCta(CtaPlacement::ProductDetail, ['body' => 'Beli {produk} sekarang']);

        $this->get('/produk/produk-uji')->assertOk()->assertSee('Beli produk uji sekarang', escape: false);
    }

    public function test_product_detail_uses_uploaded_cta_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('cta/hero.webp', 'x');
        $this->updateCta(CtaPlacement::ProductDetail, ['image_path' => 'cta/hero.webp']);

        $this->get('/produk/produk-uji')
            ->assertOk()
            ->assertSee('aspect-video', escape: false)
            ->assertSee(Storage::disk('public')->url('cta/hero.webp'), escape: false);
    }

    public function test_product_detail_ignores_cta_image_missing_from_disk(): void
    {
        Storage::fake('public');
        $this->updateCta(CtaPlacement::ProductDetail, ['image_path' => 'cta/hilang.webp']);

        $this->get('/produk/produk-uji')->assertOk()->assertDontSee('cta/hilang.webp', escape: false);
    }

    public function test_missing_cta_row_falls_back_to_default_text(): void
    {
        CallToAction::query()->where('placement', CtaPlacement::Faq->value)->delete();

        $this->get('/faq')->assertOk()->assertSee('Masih ada pertanyaan lain?', escape: false);
    }

    public function test_empty_body_is_not_rendered(): void
    {
        $this->updateCta(CtaPlacement::Faq, ['body' => null]);
        $this->updateCta(CtaPlacement::ProductClosing, ['body' => null]);

        $faq = LegacyMarkup::extract($this->get('/faq')->getContent(), LegacyMarkup::FRAGMENTS['faq-cta'][1]);
        $this->assertStringNotContainsString('<br>', $faq);

        $product = LegacyMarkup::extract($this->get('/produk')->getContent(), LegacyMarkup::FRAGMENTS['produk-cta-penutup'][1]);
        $this->assertStringNotContainsString('<p', $product);
    }

    public function test_whatsapp_button_falls_back_to_contact_page_without_number(): void
    {
        $site = app(SiteSettings::class);
        $site->whatsapp_number = null;
        $site->save();

        $fragment = LegacyMarkup::extract($this->get('/')->getContent(), LegacyMarkup::FRAGMENTS['home-cta'][1]);

        $this->assertMatchesRegularExpression('#href="[^"]*/kontak"[^>]*>.*?<span>Chat via WhatsApp</span>#', $fragment);
    }

    public function test_cta_text_is_escaped(): void
    {
        $this->updateCta(CtaPlacement::Faq, ['title' => '<b>x</b>']);

        $this->get('/faq')->assertOk()
            ->assertSee('&lt;b&gt;x&lt;/b&gt;', escape: false)
            ->assertDontSee('<b>x</b>', escape: false);
    }

    public function test_changing_site_name_does_not_change_saved_cta_text(): void
    {
        $site = app(SiteSettings::class);
        $site->site_name = 'Merek Lain';
        $site->save();

        $this->get('/tentang-kami')->assertOk()
            ->assertSee('Ingin tahu lebih lanjut tentang SUOER?', escape: false)
            ->assertDontSee('tentang Merek Lain?', escape: false);
    }
}
