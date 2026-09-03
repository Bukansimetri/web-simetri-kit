<?php

namespace Tests\Feature\Pages;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
