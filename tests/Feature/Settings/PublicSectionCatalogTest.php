<?php

namespace Tests\Feature\Settings;

use App\Enums\CtaPlacement;
use App\Enums\FaqPlacement;
use App\Enums\PageBlockType;
use App\Enums\PageSection;
use App\Enums\PublicSection;
use Tests\TestCase;

class PublicSectionCatalogTest extends TestCase
{
    public function test_catalog_has_exactly_25_sections_with_unique_keys(): void
    {
        $this->assertCount(25, PublicSection::cases());
        $this->assertCount(25, array_unique(array_map(fn (PublicSection $s) => $s->value, PublicSection::cases())));
    }

    public function test_every_cta_placement_maps_to_a_distinct_section(): void
    {
        $mapped = array_map(fn (CtaPlacement $p) => PublicSection::fromCta($p)->value, CtaPlacement::cases());

        $this->assertCount(9, array_unique($mapped));
        $this->assertSame('beranda.cta', PublicSection::fromCta(CtaPlacement::Home)->value);
        $this->assertSame('artikel-detail.cta', PublicSection::fromCta(CtaPlacement::ArticleDetail)->value);
    }

    public function test_every_page_section_maps_and_keeps_its_key(): void
    {
        foreach (PageSection::cases() as $section) {
            $this->assertSame($section->value, PublicSection::fromPageSection($section)->value);
        }
    }

    public function test_faq_and_page_block_mappings(): void
    {
        $this->assertSame(PublicSection::ProductFaq, PublicSection::forFaqPlacement(FaqPlacement::Product));
        $this->assertSame(PublicSection::ContactFaq, PublicSection::forFaqPlacement(FaqPlacement::Contact));
        $this->assertNull(PublicSection::forFaqPlacement(FaqPlacement::Faq));
        $this->assertSame(PublicSection::AboutWhoWeAre, PublicSection::forPageBlock(PageBlockType::AboutWhoWeAre));
        $this->assertSame(PublicSection::AboutVision, PublicSection::forPageBlock(PageBlockType::AboutVision));
        $this->assertNull(PublicSection::forPageBlock(PageBlockType::AboutHero));
    }

    public function test_pages_cover_every_section_exactly_once_in_catalog_order(): void
    {
        $all = [];

        foreach (PublicSection::pages() as $page) {
            foreach (PublicSection::forPage($page) as $section) {
                $this->assertSame($page, $section->page());
                $all[] = $section->value;
            }
        }

        $this->assertCount(25, $all);
        $this->assertCount(25, array_unique($all));
        $this->assertSame(['Beranda', 'Tentang Kami', 'Karir', 'Produk', 'Detail Produk', 'Artikel', 'Detail Artikel', 'FAQ', 'Kontak'], PublicSection::pages());
    }

    public function test_per_page_counts_match_the_contract(): void
    {
        $counts = array_map(fn (string $page) => count(PublicSection::forPage($page)), PublicSection::pages());

        $this->assertSame([5, 9, 3, 3, 1, 1, 1, 1, 1], $counts);
    }

    public function test_every_section_has_a_content_menu_url_and_labels(): void
    {
        foreach (PublicSection::cases() as $section) {
            $this->assertNotEmpty($section->contentUrl(), $section->value);
            $this->assertNotEmpty($section->label());
            $this->assertStringStartsWith($section->page(), $section->fullLabel());
        }
    }

    public function test_faq_sections_link_to_the_matching_placement_filter(): void
    {
        $this->assertStringContainsString('produk', urldecode(PublicSection::ProductFaq->contentUrl()));
        $this->assertStringContainsString('kontak', urldecode(PublicSection::ContactFaq->contentUrl()));
    }
}
