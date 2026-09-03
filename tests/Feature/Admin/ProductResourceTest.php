<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProductResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_product_and_it_appears_on_public_page(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateProduct::class)
            ->fillForm([
                'name' => 'Panel Surya Tes 400W',
                'category_id' => $category->id,
                'short_description' => 'Deskripsi singkat.',
                'description' => 'Deskripsi lengkap.',
                'price' => 1500000,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::where('name', 'Panel Surya Tes 400W')->first();

        $this->assertNotNull($product);
        $this->assertSame('panel-surya-tes-400w', $product->slug);

        $this->get('/produk')->assertOk()->assertSee('Panel Surya Tes 400W', escape: false);
    }

    public function test_admin_can_edit_product_and_change_is_reflected_publicly(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create(['name' => 'Nama Lama', 'category_id' => $category->id]);

        Livewire::actingAs($user)
            ->test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->fillForm(['name' => 'Nama Baru'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Nama Baru', $product->fresh()->name);
        $this->get('/produk/'.$product->fresh()->slug)->assertOk()->assertSee('Nama Baru', escape: false);
    }

    public function test_duplicate_slug_is_rejected(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        Product::factory()->create(['slug' => 'produk-a', 'category_id' => $category->id]);

        Livewire::actingAs($user)
            ->test(CreateProduct::class)
            ->fillForm([
                'name' => 'Produk B',
                'slug' => 'produk-a',
                'category_id' => $category->id,
                'short_description' => 'x',
                'description' => 'x',
                'price' => 1000,
            ])
            ->call('create')
            ->assertHasFormErrors(['slug']);
    }

    public function test_required_fields_are_validated(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(CreateProduct::class)
            ->fillForm([
                'name' => '',
                'category_id' => null,
                'price' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'category_id', 'price']);
    }

    public function test_admin_can_upload_multiple_images_and_first_becomes_cover(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $images = [
            UploadedFile::fake()->image('satu.jpg'),
            UploadedFile::fake()->image('dua.jpg'),
            UploadedFile::fake()->image('tiga.jpg'),
        ];

        Livewire::actingAs($user)
            ->test(CreateProduct::class)
            ->fillForm([
                'name' => 'Produk Galeri',
                'category_id' => $category->id,
                'short_description' => 'x',
                'description' => 'x',
                'price' => 1000,
                'images' => $images,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $product = Product::where('name', 'Produk Galeri')->first();

        $this->assertCount(3, $product->images);

        $response = $this->get('/produk/'.$product->slug);
        $response->assertOk();
        $response->assertSee($product->coverImageUrl(), escape: false);
    }
}
