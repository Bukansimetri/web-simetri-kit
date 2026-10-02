<?php

namespace Tests\Feature\Pages;

use App\Enums\PageBlockType;
use App\Models\PageBlock;
use App\Settings\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\LegacyMarkup;
use Tests\TestCase;

class PageBlockRenderTest extends TestCase
{
    use RefreshDatabase;

    private function setBlock(PageBlockType $type, array $data): void
    {
        $block = PageBlock::query()->where('block', $type->value)->firstOrFail();
        $block->update(['data' => [...$block->data, ...$data]]);
    }

    private function fragment(string $name): string
    {
        [$path, $xpath] = LegacyMarkup::FRAGMENTS[$name];

        return LegacyMarkup::extract($this->get($path)->assertOk()->getContent(), $xpath);
    }

    public function test_hero_who_we_are_and_vision_show_block_content(): void
    {
        $this->setBlock(PageBlockType::AboutHero, ['subtitle' => 'Subjudul hero editan.']);
        $this->setBlock(PageBlockType::AboutWhoWeAre, ['badge_text' => 'Badge Editan', 'heading' => 'Judul Siapa Kami Editan']);
        $this->setBlock(PageBlockType::AboutVision, ['heading' => 'Visi editan.']);

        $this->assertStringContainsString('Subjudul hero editan.', $this->fragment('tentang-kami-hero'));
        $this->assertStringContainsString('Badge Editan', $this->fragment('tentang-kami-siapa-kami'));
        $this->assertStringContainsString('Judul Siapa Kami Editan', $this->fragment('tentang-kami-siapa-kami'));
        $this->assertStringContainsString('Visi editan.', $this->fragment('tentang-kami-visi'));
    }

    public function test_empty_images_fall_back_to_default_assets(): void
    {
        $this->setBlock(PageBlockType::AboutHero, ['image_path' => null]);
        $this->setBlock(PageBlockType::AboutWhoWeAre, ['image_path' => 'about-page/tidak-ada.webp']);

        $this->assertStringContainsString('images/mockup/produk-1.jpg', $this->fragment('tentang-kami-hero'));
        $this->assertStringContainsString('images/mockup/tentang-kami-2.jpg', $this->fragment('tentang-kami-siapa-kami'));
    }

    public function test_rich_text_is_sanitized(): void
    {
        $this->setBlock(PageBlockType::AboutWhoWeAre, [
            'body' => '<strong>Tebal</strong><script>alert(1)</script><img src=x onerror=alert(2)>',
            'quote' => '<em>Miring</em><script>alert(3)</script>',
        ]);

        $fragment = $this->fragment('tentang-kami-siapa-kami');

        $this->assertStringContainsString('<strong>Tebal</strong>', $fragment);
        $this->assertStringContainsString('<em>Miring</em>', $fragment);
        $this->assertStringNotContainsString('<script', $fragment);
        $this->assertStringNotContainsString('onerror', $fragment);
    }

    public function test_contact_info_shows_label_and_hours_and_hides_empty_hours(): void
    {
        $this->setBlock(PageBlockType::ContactInfo, ['whatsapp_label' => 'Tanya Kami', 'operating_hours' => 'Setiap Hari, 08:00 - 20:00']);

        $fragment = $this->fragment('kontak-info');
        $this->assertStringContainsString('Tanya Kami', $fragment);
        $this->assertStringContainsString('Setiap Hari, 08:00 - 20:00', $fragment);

        $this->setBlock(PageBlockType::ContactInfo, ['operating_hours' => '']);
        $this->assertStringNotContainsString('text-sm text-primary-fixed-dim mt-1', $this->fragment('kontak-info'));
    }

    public function test_whatsapp_message_is_used_by_contact_home_and_cta_band(): void
    {
        $site = app(SiteSettings::class);
        $site->whatsapp_number = '6281234567890';
        $site->save();

        $this->setBlock(PageBlockType::ContactInfo, ['whatsapp_message' => 'Halo pesan khusus admin']);
        $encoded = rawurlencode('Halo pesan khusus admin');

        foreach (['/kontak', '/', '/tentang-kami'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();

            $this->assertStringContainsString($encoded, $html, "Pesan WhatsApp tidak dipakai di {$path}");
        }
    }

    public function test_missing_block_row_falls_back_to_defaults(): void
    {
        PageBlock::query()->where('block', PageBlockType::AboutVision->value)->delete();
        PageBlock::query()->where('block', PageBlockType::ContactInfo->value)->delete();

        $this->assertStringContainsString('Visi Kami', $this->fragment('tentang-kami-visi'));
        $this->assertStringContainsString('Chat via WhatsApp', $this->fragment('kontak-info'));
    }
}
