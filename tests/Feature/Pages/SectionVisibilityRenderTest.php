<?php

namespace Tests\Feature\Pages;

use App\Enums\PublicSection;
use App\Models\ClientLogo;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Support\PageContent\SectionVisibility;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\LegacyMarkup;
use Tests\TestCase;

/**
 * Setiap section yang bisa disembunyikan hilang dari situs saat dimatikan, dan kembali identik saat dinyalakan.
 */
class SectionVisibilityRenderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Penanda teks unik tiap section di halamannya.
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const MARKERS = [
        'HomeWhyChoose' => ['/', 'Mengapa Beralih'],
        'HomeHowItWorks' => ['/', 'Sederhana dan Mulus'],
        'HomeSolutions' => ['/', 'Solusi Untuk Setiap Kebutuhan'],
        'HomeTestimonials' => ['/', 'Partner Kami'],
        'HomeCta' => ['/', 'Siap beralih ke'],
        'AboutWhoWeAre' => ['/tentang-kami', 'Menghadirkan Energi Surya Andal'],
        'AboutVision' => ['/tentang-kami', 'Visi Kami'],
        'AboutMission' => ['/tentang-kami', 'Bagaimana Kami Mewujudkannya'],
        'AboutValues' => ['/tentang-kami', 'Nilai-Nilai Kami'],
        'AboutTrust' => ['/tentang-kami', '5.000+'],
        'AboutTeam' => ['/tentang-kami', 'Orang di balik'],
        'AboutTestimonials' => ['/tentang-kami', 'Partner Kami'],
        'AboutClientLogos' => ['/tentang-kami', 'Dipercaya oleh'],
        'AboutCta' => ['/tentang-kami', 'Ingin tahu lebih lanjut tentang'],
        'CareerValues' => ['/karir', 'Mengapa Bergabung'],
        'CareerProcess' => ['/karir', 'Proses Rekrutmen'],
        'CareerCta' => ['/karir', 'Tidak menemukan posisi'],
        'ProductCtaCalculator' => ['/produk', 'Bingung pilih yang mana'],
        'ProductFaq' => ['/produk', 'Pertanyaan Seputar Produk'],
        'ProductCtaClosing' => ['/produk', 'Belum yakin kapasitas'],
        'ProductDetailCta' => ['/produk/produk-uji', 'Masa Depan Energi Anda'],
        'ArticleCta' => ['/artikel', 'Punya pertanyaan seputar energi surya'],
        'ArticleDetailCta' => ['/artikel/artikel-uji', 'Siap beralih ke energi surya'],
        'FaqCta' => ['/faq', 'Masih ada pertanyaan lain'],
        'ContactFaq' => ['/kontak', 'Pertanyaan Seputar Konsultasi'],
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        LegacyMarkup::seedDeterministicState();

        Testimonial::factory()->count(3)->create();

        $member = TeamMember::factory()->create();
        Storage::disk('public')->put($member->photo_path, 'x');

        $logo = ClientLogo::factory()->create();
        Storage::disk('public')->put($logo->logo_path, 'x');
    }

    /**
     * @return array<string, array{0: PublicSection}>
     */
    public static function sections(): array
    {
        $cases = [];

        foreach (PublicSection::cases() as $section) {
            $cases[$section->value] = [$section];
        }

        return $cases;
    }

    private function body(string $path): string
    {
        return $this->get($path)->assertOk()->getContent();
    }

    public function test_markers_cover_every_section(): void
    {
        $this->assertSame(
            array_map(fn (PublicSection $s) => $s->name, PublicSection::cases()),
            array_keys(self::MARKERS),
        );
    }

    #[DataProvider('sections')]
    public function test_hiding_removes_only_that_section_and_showing_restores_it(PublicSection $section): void
    {
        [$path, $marker] = self::MARKERS[$section->name];
        $before = substr_count($this->body($path), $marker);

        $this->assertGreaterThan(0, $before, "{$section->value} tidak tampil secara bawaan.");

        SectionVisibility::setHidden([$section]);
        $hiddenBody = $this->body($path);

        $this->assertSame(0, substr_count($hiddenBody, $marker), "{$section->value} masih tampil setelah disembunyikan.");

        foreach (self::MARKERS as $name => [$otherPath, $otherMarker]) {
            if ($otherPath === $path && $name !== $section->name && $otherMarker !== $marker) {
                $this->assertStringContainsString($otherMarker, $hiddenBody, "Section {$name} ikut hilang saat {$section->value} disembunyikan.");
            }
        }

        SectionVisibility::setHidden([]);

        $this->assertSame($before, substr_count($this->body($path), $marker));
    }

    public function test_hidden_section_content_is_not_deleted_and_returns_identical(): void
    {
        $xpath = "//section[.//h2[contains(., 'Sederhana dan Mulus')]]";
        $original = LegacyMarkup::extract($this->body('/'), $xpath);

        SectionVisibility::setHidden([PublicSection::HomeHowItWorks]);
        $this->assertStringNotContainsString('Sederhana dan Mulus', $this->body('/'));

        SectionVisibility::setHidden([]);

        $this->assertSame($original, LegacyMarkup::extract($this->body('/'), $xpath));
    }

    public function test_home_and_about_testimonials_are_independent(): void
    {
        SectionVisibility::setHidden([PublicSection::HomeTestimonials]);

        $this->assertStringNotContainsString('Partner Kami', $this->body('/'));
        $this->assertStringContainsString('Partner Kami', $this->body('/tentang-kami'));

        SectionVisibility::setHidden([PublicSection::AboutTestimonials]);

        $this->assertStringContainsString('Partner Kami', $this->body('/'));
        $this->assertStringNotContainsString('Partner Kami', $this->body('/tentang-kami'));
    }

    public function test_all_ctas_can_be_hidden_together_without_affecting_main_content(): void
    {
        SectionVisibility::setHidden([
            PublicSection::HomeCta, PublicSection::AboutCta, PublicSection::CareerCta,
            PublicSection::ProductCtaCalculator, PublicSection::ProductCtaClosing, PublicSection::ProductDetailCta,
            PublicSection::ArticleCta, PublicSection::ArticleDetailCta, PublicSection::FaqCta,
        ]);

        foreach (['/', '/tentang-kami', '/karir', '/produk', '/produk/produk-uji', '/artikel', '/artikel/artikel-uji', '/faq'] as $path) {
            $this->get($path)->assertOk();
        }

        $this->assertStringContainsString('Produk Uji', $this->body('/produk'));
        $this->assertStringContainsString('Artikel Uji', $this->body('/artikel'));
    }

    public function test_main_content_is_never_hidden_even_when_every_section_is_off(): void
    {
        SectionVisibility::setHidden(PublicSection::cases());

        $this->assertStringContainsString('Produk Uji', $this->body('/produk'));
        $this->assertStringContainsString('Artikel Uji', $this->body('/artikel'));
        $this->assertStringContainsString('Hubungi', $this->body('/kontak'));
        $this->assertStringContainsString('Hitung Estimasi Penghematan', $this->body('/'));
        $this->get('/tentang-kami')->assertOk();
        $this->get('/karir')->assertOk();
        $this->get('/faq')->assertOk();
    }

    public function test_a_shown_section_without_content_stays_hidden(): void
    {
        Testimonial::query()->delete();
        ClientLogo::query()->delete();

        $this->assertStringNotContainsString('Partner Kami', $this->body('/'));
        $this->assertStringNotContainsString('Dipercaya oleh', $this->body('/tentang-kami'));
    }
}
