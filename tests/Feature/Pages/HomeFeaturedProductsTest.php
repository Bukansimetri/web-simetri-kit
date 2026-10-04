<?php

namespace Tests\Feature\Pages;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\LegacyMarkup;
use Tests\TestCase;

class HomeFeaturedProductsTest extends TestCase
{
    use RefreshDatabase;

    private function section(): string
    {
        return LegacyMarkup::extract(
            $this->get('/')->assertOk()->getContent(),
            "//section[.//h2[contains(., 'Solusi Untuk Setiap Kebutuhan')]]",
        );
    }

    public function test_without_any_marked_product_the_first_three_by_order_are_shown(): void
    {
        foreach ([4 => 'Keempat', 1 => 'Pertama', 3 => 'Ketiga', 2 => 'Kedua'] as $order => $name) {
            Product::factory()->create(['name' => "Produk {$name}", 'order' => $order]);
        }

        $section = $this->section();

        $this->assertStringContainsString('Produk Pertama', $section);
        $this->assertStringContainsString('Produk Kedua', $section);
        $this->assertStringContainsString('Produk Ketiga', $section);
        $this->assertStringNotContainsString('Produk Keempat', $section);
    }

    public function test_marked_products_replace_the_default_selection_and_follow_order(): void
    {
        Product::factory()->create(['name' => 'Tidak Ditandai A', 'order' => 1]);
        Product::factory()->create(['name' => 'Ditandai Kedua', 'order' => 6, 'show_on_home' => true]);
        Product::factory()->create(['name' => 'Tidak Ditandai B', 'order' => 2]);
        Product::factory()->create(['name' => 'Ditandai Pertama', 'order' => 5, 'show_on_home' => true]);

        $section = $this->section();

        $this->assertStringNotContainsString('Tidak Ditandai', $section);
        $this->assertLessThan(strpos($section, 'Ditandai Kedua'), strpos($section, 'Ditandai Pertama'));
    }

    public function test_deleting_the_only_marked_product_falls_back_to_the_first_three(): void
    {
        $marked = Product::factory()->create(['name' => 'Satu-satunya Ditandai', 'order' => 9, 'show_on_home' => true]);
        Product::factory()->create(['name' => 'Cadangan Satu', 'order' => 1]);
        Product::factory()->create(['name' => 'Cadangan Dua', 'order' => 2]);

        $this->assertStringContainsString('Satu-satunya Ditandai', $this->section());

        $marked->delete();

        $section = $this->section();
        $this->assertStringContainsString('Cadangan Satu', $section);
        $this->assertStringContainsString('Cadangan Dua', $section);
    }

    public function test_second_card_keeps_the_popular_badge_when_at_least_two_products_are_shown(): void
    {
        Product::factory()->count(2)->create();

        $section = $this->section();

        $this->assertSame(1, substr_count($section, 'Terpopuler'));
        $this->assertSame(1, substr_count($section, 'Lihat Detail Produk'));
    }

    public function test_marking_a_product_is_visible_immediately_despite_page_cache(): void
    {
        Product::factory()->create(['name' => 'Awalnya Teratas', 'order' => 1]);
        $other = Product::factory()->create(['name' => 'Baru Ditandai', 'order' => 5]);

        $this->assertStringContainsString('Awalnya Teratas', $this->section());

        $other->update(['show_on_home' => true]);

        $section = $this->section();
        $this->assertStringContainsString('Baru Ditandai', $section);
        $this->assertStringNotContainsString('Awalnya Teratas', $section);
    }
}
