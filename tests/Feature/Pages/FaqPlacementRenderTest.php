<?php

namespace Tests\Feature\Pages;

use App\Enums\FaqPlacement;
use App\Models\FaqItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqPlacementRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_entries_appear_only_on_their_own_page(): void
    {
        FaqItem::query()->delete();
        FaqItem::factory()->create(['question' => 'Tanya khusus FAQ?']);
        FaqItem::factory()->forPlacement(FaqPlacement::Product)->create(['question' => 'Tanya khusus produk?']);
        FaqItem::factory()->forPlacement(FaqPlacement::Contact)->create(['question' => 'Tanya khusus kontak?']);

        $this->get('/faq')->assertOk()
            ->assertSee('Tanya khusus FAQ?', escape: false)
            ->assertDontSee('Tanya khusus produk?', escape: false)
            ->assertDontSee('Tanya khusus kontak?', escape: false);

        $this->get('/produk')->assertOk()
            ->assertSee('Tanya khusus produk?', escape: false)
            ->assertDontSee('Tanya khusus FAQ?', escape: false);

        $this->get('/kontak')->assertOk()
            ->assertSee('Tanya khusus kontak?', escape: false)
            ->assertDontSee('Tanya khusus produk?', escape: false);
    }

    public function test_inactive_entries_are_hidden_everywhere(): void
    {
        FaqItem::query()->delete();
        FaqItem::factory()->inactive()->create(['question' => 'Nonaktif faq?']);
        FaqItem::factory()->inactive()->forPlacement(FaqPlacement::Product)->create(['question' => 'Nonaktif produk?']);
        FaqItem::factory()->inactive()->forPlacement(FaqPlacement::Contact)->create(['question' => 'Nonaktif kontak?']);

        $this->get('/faq')->assertOk()->assertDontSee('Nonaktif faq?', escape: false);
        $this->get('/produk')->assertOk()->assertDontSee('Nonaktif produk?', escape: false);
        $this->get('/kontak')->assertOk()->assertDontSee('Nonaktif kontak?', escape: false);
    }

    public function test_order_is_followed(): void
    {
        FaqItem::query()->delete();
        FaqItem::factory()->forPlacement(FaqPlacement::Product)->create(['question' => 'Kedua?', 'order' => 2]);
        FaqItem::factory()->forPlacement(FaqPlacement::Product)->create(['question' => 'Pertama?', 'order' => 1]);

        $this->get('/produk')->assertOk()->assertSeeInOrder(['Pertama?', 'Kedua?'], escape: false);
    }

    public function test_product_and_contact_sections_disappear_when_empty(): void
    {
        FaqItem::query()->delete();

        $this->get('/produk')->assertOk()->assertDontSee('Pertanyaan Seputar Produk', escape: false);
        $this->get('/kontak')->assertOk()->assertDontSee('Pertanyaan Seputar Konsultasi', escape: false);
    }

    public function test_faq_page_shows_empty_message_and_no_json_ld_without_entries(): void
    {
        FaqItem::query()->delete();

        $this->get('/faq')->assertOk()
            ->assertSee('Belum ada pertanyaan', escape: false)
            ->assertDontSee('FAQPage', escape: false);
    }

    public function test_json_ld_only_contains_active_faq_page_entries(): void
    {
        FaqItem::query()->delete();
        FaqItem::factory()->create(['question' => 'Masuk skema?']);
        FaqItem::factory()->inactive()->create(['question' => 'Nonaktif skema?']);
        FaqItem::factory()->forPlacement(FaqPlacement::Product)->create(['question' => 'Produk skema?']);

        $html = $this->get('/faq')->assertOk()->getContent();

        $this->assertStringContainsString('Masuk skema?', $html);
        $this->assertStringNotContainsString('Nonaktif skema?', $html);
        $this->assertStringNotContainsString('Produk skema?', $html);
    }

    public function test_default_product_and_contact_faqs_are_present_after_install(): void
    {
        $this->get('/produk')->assertOk()
            ->assertSee('Berapa lama garansi panel?', escape: false)
            ->assertSee('Bagaimana proses instalasinya?', escape: false);

        $this->get('/kontak')->assertOk()
            ->assertSee('Apakah survei lokasi berbayar?', escape: false)
            ->assertSee('Bisa konsultasi tanpa datang ke kantor?', escape: false);
    }
}
