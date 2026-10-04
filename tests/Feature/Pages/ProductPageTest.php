<?php

namespace Tests\Feature\Pages;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\LegacyMarkup;
use Tests\TestCase;

class ProductPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_index_lists_seeded_products(): void
    {
        Product::factory()->create(['name' => 'SUOER Mono X-Pro 550W']);

        $response = $this->get('/produk');

        $response->assertOk();
        $response->assertSee('SUOER Mono X-Pro 550W', escape: false);
        $response->assertSee('Bingung pilih yang mana?', escape: false);
        $response->assertSee('Pertanyaan Seputar Produk', escape: false);
        $response->assertSee('Belum yakin kapasitas yang Anda butuhkan?', escape: false);
    }

    public function test_product_show_displays_detail_and_related_products_from_same_category(): void
    {
        $residensial = Category::factory()->create(['name' => 'Residensial']);
        $komersial = Category::factory()->create(['name' => 'Komersial & Industri']);

        $product = Product::factory()->create([
            'name' => 'Panel Surya Monokristalin 550W',
            'category_id' => $residensial->id,
        ]);
        $related = Product::factory()->create([
            'name' => 'Panel Surya Residensial Lain',
            'category_id' => $residensial->id,
        ]);
        Product::factory()->create([
            'name' => 'Produk Komersial',
            'category_id' => $komersial->id,
        ]);

        $response = $this->get('/produk/'.$product->slug);

        $response->assertOk();
        $response->assertSee($product->name, escape: false);
        $response->assertSee($related->name, escape: false);
        $response->assertDontSee('Produk Komersial', escape: false);
    }

    public function test_product_show_returns_404_for_unknown_slug(): void
    {
        $response = $this->get('/produk/produk-tidak-ada');

        $response->assertNotFound();
    }

    public function test_product_without_images_shows_placeholder(): void
    {
        $product = Product::factory()->create(['name' => 'Produk Tanpa Gambar', 'images' => []]);

        $response = $this->get('/produk/'.$product->slug);

        $response->assertOk();
        $response->assertSee('data-product-image-placeholder', escape: false);
    }

    public function test_index_cards_show_only_image_and_name(): void
    {
        $category = Category::factory()->create(['name' => 'Kategori Rahasia']);
        $product = Product::factory()->create([
            'name' => 'Inverter Uji',
            'slug' => 'inverter-uji',
            'short_description' => 'Deskripsi pendek unik',
            'price' => 1234567,
            'category_id' => $category->id,
        ]);

        $html = $this->get('/produk')->assertOk()->getContent();
        $card = LegacyMarkup::extract($html, "//a[contains(@href, '/produk/inverter-uji')]");

        $this->assertStringContainsString('Inverter Uji', $card);
        $this->assertStringNotContainsString('Deskripsi pendek unik', $card);
        $this->assertStringNotContainsString('1.234.567', $card);
        $this->assertStringNotContainsString('Kategori Rahasia', $card);
        $this->assertStringNotContainsString('Lihat detail', $card);
        $this->assertStringContainsString('text-center', $card);
        $this->get('/produk/'.$product->slug)->assertOk();
    }

    public function test_index_card_without_image_uses_placeholder_with_same_frame(): void
    {
        Product::factory()->create(['name' => 'Tanpa Gambar', 'images' => []]);

        $this->get('/produk')
            ->assertOk()
            ->assertSee('data-product-image-placeholder', escape: false)
            ->assertSee('aspect-video', escape: false);
    }

    public function test_index_category_filter_still_renders_buttons_for_each_category(): void
    {
        $a = Category::factory()->create(['name' => 'Panel']);
        Product::factory()->create(['category_id' => $a->id]);

        $this->get('/produk')->assertOk()->assertSee('Semua')->assertSee('Panel');
    }

    public function test_related_products_on_detail_page_keep_the_full_card(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id]);
        Product::factory()->create([
            'category_id' => $category->id,
            'short_description' => 'Deskripsi terkait unik',
        ]);

        $this->get('/produk/'.$product->slug)
            ->assertOk()
            ->assertSee('Deskripsi terkait unik')
            ->assertSee('Lihat detail');
    }
}
