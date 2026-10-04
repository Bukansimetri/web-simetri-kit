<?php

namespace Tests\Support;

use App\Models\Article;
use App\Models\CallToAction;
use App\Models\Product;
use App\Models\SectionHeading;
use App\Models\SectionItem;
use App\Settings\SiteSettings;
use App\Support\PageContent\PageContentInstaller;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\Assert;

/**
 * Ekstraksi fragmen HTML section/CTA untuk membandingkan markup lama vs baru.
 * Catatan: libxml membuang atribut Alpine (`@click`, `:class`), jadi atribut itu tidak ikut terbandingkan.
 */
class LegacyMarkup
{
    /**
     * @var array<string, array{0: string, 1: string}>
     */
    public const FRAGMENTS = [
        'home-why-choose' => ['/', "//section[.//h2[contains(., 'Mengapa Beralih')]]"],
        'home-how-it-works' => ['/', "//section[.//h2[contains(., 'Sederhana dan Mulus')]]"],
        'home-cta' => ['/', "//section[.//h2[contains(., 'Siap beralih ke')]]"],
        'karir-values' => ['/karir', "//section[.//h2[contains(., 'Mengapa Bergabung')]]"],
        'karir-recruitment' => ['/karir', "//section[.//h2[contains(., 'Proses Rekrutmen')]]"],
        'karir-cta' => ['/karir', "//section[.//h2[contains(., 'Tidak menemukan posisi')]]"],
        'produk-cta-kalkulator' => ['/produk', "//section[.//h2[contains(., 'Bingung pilih')]]"],
        'produk-cta-penutup' => ['/produk', "//section[.//h2[contains(., 'Belum yakin kapasitas')]]"],
        'produk-detail-cta' => ['/produk/produk-uji', "//section[.//h2[contains(., 'Masa Depan Energi Anda')]]"],
        'artikel-index-cta' => ['/artikel', "//section[.//h2[contains(., 'Punya pertanyaan seputar')]]"],
        'artikel-detail-cta' => ['/artikel/artikel-uji', "//section[.//h2[contains(., 'Siap beralih ke energi surya')]]"],
        'tentang-kami-cta' => ['/tentang-kami', "//section[.//h2[contains(., 'Ingin tahu lebih lanjut')]]"],
        'faq-cta' => ['/faq', "//section[.//h2[contains(., 'Masih ada pertanyaan lain')]]"],
        'tentang-kami-hero' => ['/tentang-kami', "//section[.//h1[contains(., 'Lebih Dekat')]]"],
        'tentang-kami-siapa-kami' => ['/tentang-kami', "//section[.//div[contains(@class, '-rotate-3')]]"],
        'tentang-kami-visi' => ['/tentang-kami', "//section[.//p[contains(@class, 'tracking-[0.3em]')]]"],
        'tentang-kami-misi' => ['/tentang-kami', "//section[.//span[contains(., 'check_circle')]]"],
        'tentang-kami-nilai' => ['/tentang-kami', "//section[.//div[contains(@class, 'md:col-span-3') and .//img]]"],
        'tentang-kami-trust' => ['/tentang-kami', "//section[contains(@class, 'border-y')]"],
        'kontak-info' => ['/kontak', "//div[contains(@class, 'lg:w-2/5') and contains(@class, 'bg-primary')]"],
        'produk-hero' => ['/produk', "//section[.//h1[contains(., 'Katalog Produk')]]"],
        'karir-hero' => ['/karir', "//section[.//h1[contains(., 'Revolusi Energi')]]"],
        'faq-hero' => ['/faq', "//section[.//h1[contains(., 'Pertanyaan Umum')]]"],
        'kontak-hero' => ['/kontak', "//section[.//h1[contains(., 'Rumah Hemat Energi')]]"],
    ];

    /**
     * Fragmen yang desainnya sengaja diubah (spec 030-client-design-update): tetap bisa diekstrak
     * untuk test render, tetapi tidak lagi dibandingkan dengan fixture HTML lama.
     *
     * @var list<string>
     */
    public const REDESIGNED = ['home-why-choose', 'home-how-it-works', 'faq-hero', 'kontak-hero', 'tentang-kami-hero'];

    public static function fixturePath(string $name): string
    {
        return base_path("tests/Fixtures/legacy-page-content/{$name}.html");
    }

    public static function extract(string $html, string $xpath): string
    {
        $previous = libxml_use_internal_errors(true);

        $document = new DOMDocument;
        $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $nodes = (new DOMXPath($document))->query($xpath);

        if ($nodes === false || $nodes->length === 0) {
            Assert::fail("Fragmen tidak ditemukan untuk XPath: {$xpath}");
        }

        return self::normalize($document->saveHTML($nodes->item($nodes->length - 1)));
    }

    public static function normalize(string $html): string
    {
        $collapsed = preg_replace('/\s+/u', ' ', $html);

        return trim(preg_replace('/>\s+</u', '><', $collapsed));
    }

    public static function seedDeterministicState(): void
    {
        $site = app(SiteSettings::class);
        $site->site_name = 'SUOER';
        $site->career_module_enabled = true;
        $site->save();

        CallToAction::query()->delete();
        SectionItem::query()->delete();
        SectionHeading::query()->delete();
        PageContentInstaller::install();

        Product::factory()->create([
            'name' => 'Produk Uji',
            'slug' => 'produk-uji',
            'images' => [],
        ]);

        Article::factory()->create([
            'title' => 'Artikel Uji',
            'slug' => 'artikel-uji',
            'image_path' => null,
            'published_at' => now()->subDay(),
        ]);
    }
}
