<?php

namespace Tests\Feature\Public;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Category;
use App\Models\FaqItem;
use App\Models\PortfolioCategory;
use App\Models\PortfolioProject;
use App\Models\Product;
use App\Models\TeamMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicPageCachingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    /**
     * Regresi: phpunit.xml memakai CACHE_STORE=array untuk seluruh test
     * lain di file ini, yang TIDAK pernah benar-benar serialize/unserialize
     * (array store menyimpan objek apa adanya di memori) — jadi tidak bisa
     * menangkap bug nyata di store `database` (dipakai produksi/lokal, lihat
     * config/cache.php). Test ini SENGAJA memaksa driver `database` supaya
     * round-trip serialize()/unserialize() sungguhan teruji, termasuk
     * `serializable_classes` allowlist (config/cache.php) yang WAJIB
     * mencakup tiap model yang dibungkus cache — kalau tidak, Laravel diam-
     * diam mengembalikan `__PHP_Incomplete_Class` dan halaman 500.
     */
    public function test_home_page_survives_real_database_cache_serialization_round_trip(): void
    {
        config(['cache.default' => 'database']);

        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id, 'name' => 'Produk Uji Serialisasi']);

        $this->get('/')->assertOk()->assertSee('Produk Uji Serialisasi', escape: false);
        $this->get('/')->assertOk()->assertSee('Produk Uji Serialisasi', escape: false);
    }

    public function test_home_page_serves_stale_data_within_ttl_then_fresh_after_expiry(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'name' => 'Nama Produk Lama']);

        $this->get('/')->assertSee('Nama Produk Lama', escape: false);

        DB::table('products')->where('id', $product->id)->update(['name' => 'Nama Produk Baru']);

        $this->get('/')
            ->assertSee('Nama Produk Lama', escape: false)
            ->assertDontSee('Nama Produk Baru', escape: false);

        Carbon::setTestNow(now()->addMinutes(6));

        $this->get('/')
            ->assertSee('Nama Produk Baru', escape: false)
            ->assertDontSee('Nama Produk Lama', escape: false);
    }

    public function test_produk_index_serves_stale_data_within_ttl_then_fresh_after_expiry(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'name' => 'Produk Index Lama']);

        $this->get('/produk')->assertSee('Produk Index Lama', escape: false);

        DB::table('products')->where('id', $product->id)->update(['name' => 'Produk Index Baru']);

        $this->get('/produk')->assertSee('Produk Index Lama', escape: false);

        Carbon::setTestNow(now()->addMinutes(6));

        $this->get('/produk')->assertSee('Produk Index Baru', escape: false);
    }

    public function test_produk_show_related_products_are_cached_but_main_product_is_always_fresh(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create(['category_id' => $category->id, 'name' => 'Nama Awal']);

        $this->get('/produk/'.$product->slug)->assertSee('Nama Awal', escape: false);

        // Model utama SELALU fresh (route-model binding) — walau di dalam TTL cache.
        DB::table('products')->where('id', $product->id)->update(['name' => 'Nama Sudah Diubah']);
        $this->get('/produk/'.$product->slug)
            ->assertSee('Nama Sudah Diubah', escape: false)
            ->assertDontSee('Nama Awal', escape: false);

        // relatedProducts adalah query turunan — ter-cache.
        $related = Product::factory()->create(['category_id' => $category->id, 'name' => 'Produk Terkait Baru']);
        $this->get('/produk/'.$product->slug)->assertDontSee('Produk Terkait Baru', escape: false);

        Carbon::setTestNow(now()->addMinutes(6));
        $this->get('/produk/'.$product->slug)->assertSee('Produk Terkait Baru', escape: false);
    }

    public function test_artikel_index_serves_stale_data_within_ttl_then_fresh_after_expiry(): void
    {
        $articleCategory = ArticleCategory::factory()->create();
        $article = Article::factory()->create([
            'article_category_id' => $articleCategory->id,
            'title' => 'Judul Artikel Lama',
            'published_at' => now()->subDay(),
        ]);

        $this->get('/artikel')->assertSee('Judul Artikel Lama', escape: false);

        DB::table('articles')->where('id', $article->id)->update(['title' => 'Judul Artikel Baru']);

        $this->get('/artikel')->assertSee('Judul Artikel Lama', escape: false);

        Carbon::setTestNow(now()->addMinutes(6));

        $this->get('/artikel')->assertSee('Judul Artikel Baru', escape: false);
    }

    public function test_faq_page_serves_stale_data_within_ttl_then_fresh_after_expiry(): void
    {
        $item = FaqItem::factory()->create(['question' => 'Pertanyaan Lama?']);

        $this->get('/faq')->assertSee('Pertanyaan Lama?', escape: false);

        DB::table('faq_items')->where('id', $item->id)->update(['question' => 'Pertanyaan Baru?']);

        $this->get('/faq')->assertSee('Pertanyaan Lama?', escape: false);

        Carbon::setTestNow(now()->addMinutes(6));

        $this->get('/faq')->assertSee('Pertanyaan Baru?', escape: false);
    }

    public function test_tentang_kami_serves_stale_data_within_ttl_then_fresh_after_expiry(): void
    {
        $teamMember = TeamMember::factory()->create(['name' => 'Nama Tim Lama']);

        $this->get('/tentang-kami')->assertSee('Nama Tim Lama', escape: false);

        DB::table('team_members')->where('id', $teamMember->id)->update(['name' => 'Nama Tim Baru']);

        $this->get('/tentang-kami')->assertSee('Nama Tim Lama', escape: false);

        Carbon::setTestNow(now()->addMinutes(6));

        $this->get('/tentang-kami')->assertSee('Nama Tim Baru', escape: false);
    }

    public function test_portfolio_index_cache_is_isolated_per_category(): void
    {
        $categoryA = PortfolioCategory::factory()->create(['slug' => 'kategori-a', 'name' => 'Kategori A']);
        $categoryB = PortfolioCategory::factory()->create(['slug' => 'kategori-b', 'name' => 'Kategori B']);

        PortfolioProject::factory()->create([
            'portfolio_category_id' => $categoryA->id,
            'title' => 'Proyek Khusus A',
            'is_active' => true,
        ]);
        PortfolioProject::factory()->create([
            'portfolio_category_id' => $categoryB->id,
            'title' => 'Proyek Khusus B',
            'is_active' => true,
        ]);

        $this->get('/portfolio?kategori=kategori-a')
            ->assertSee('Proyek Khusus A', escape: false)
            ->assertDontSee('Proyek Khusus B', escape: false);

        $this->get('/portfolio?kategori=kategori-b')
            ->assertSee('Proyek Khusus B', escape: false)
            ->assertDontSee('Proyek Khusus A', escape: false);
    }

    public function test_kontak_form_submission_is_never_cached(): void
    {
        $first = $this->postJson('/kontak', [
            'nama' => 'Budi Santoso',
            'phone' => '081234567890',
            'kebutuhan' => 'umum',
            'pesan' => 'Pesan pertama.',
        ]);
        $first->assertCreated();

        $second = $this->postJson('/kontak', [
            'nama' => 'Siti Aminah',
            'phone' => '081298765432',
            'kebutuhan' => 'umum',
            'pesan' => 'Pesan kedua.',
        ]);
        $second->assertCreated();

        $this->assertDatabaseHas('contact_submissions', ['name' => 'Budi Santoso']);
        $this->assertDatabaseHas('contact_submissions', ['name' => 'Siti Aminah']);
    }
}
