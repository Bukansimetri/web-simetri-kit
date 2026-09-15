<?php

namespace Tests\Feature\Database;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use Tests\TestCase;

/**
 * T056 (hasil /speckit-analyze, temuan C1): memverifikasi migration
 * `add_hero_fields_to_banners_table` membackfill HANYA banner ber-`order`
 * terkecil, dan hanya bila `heading` masih kosong (FR-016, SC-008,
 * research.md R10).
 *
 * RefreshDatabase sudah menjalankan migration ini pada tabel kosong
 * (no-op) sebelum tiap test. Untuk menguji logika backfill pada tabel
 * yang SUDAH berisi baris, test ini memanggil ulang metode privat
 * `backfillHeroContent()` lewat Reflection pada data yang disiapkan
 * manual -- tanpa mengulang up()/down() skema, yang akan menambah/
 * menghapus kolom berulang dan tidak merepresentasikan skenario nyata
 * (migration hanya berjalan sekali per basis data).
 */
class BannerHeroBackfillTest extends TestCase
{
    use RefreshDatabase;

    private function runBackfill(): void
    {
        $migration = require database_path('migrations/2026_09_14_000000_add_hero_fields_to_banners_table.php');

        $method = (new ReflectionClass($migration))->getMethod('backfillHeroContent');
        $method->setAccessible(true);
        $method->invoke($migration);
    }

    public function test_backfill_fills_only_the_smallest_order_banner(): void
    {
        $secondId = DB::table('banners')->insertGetId([
            'title' => 'Banner Kedua', 'image_path' => 'banners/b.webp', 'alt_text' => 'x',
            'order' => 5, 'is_active' => true, 'overlay_style' => 'dark', 'text_position' => 'left',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $firstId = DB::table('banners')->insertGetId([
            'title' => 'Banner Pertama', 'image_path' => 'banners/a.webp', 'alt_text' => 'x',
            'order' => 1, 'is_active' => true, 'overlay_style' => 'dark', 'text_position' => 'left',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->runBackfill();

        $first = DB::table('banners')->find($firstId);
        $second = DB::table('banners')->find($secondId);

        $this->assertSame('Nyalakan Rumah & Bisnis Anda dengan Energi Matahari', $first->heading);
        $this->assertNotNull($first->cta_primary_url);
        $this->assertNull($second->heading);
    }

    public function test_backfill_breaks_ties_by_smallest_id(): void
    {
        $firstId = DB::table('banners')->insertGetId([
            'title' => 'Dibuat Duluan', 'image_path' => 'banners/a.webp', 'alt_text' => 'x',
            'order' => 3, 'is_active' => true, 'overlay_style' => 'dark', 'text_position' => 'left',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $secondId = DB::table('banners')->insertGetId([
            'title' => 'Dibuat Belakangan', 'image_path' => 'banners/b.webp', 'alt_text' => 'x',
            'order' => 3, 'is_active' => true, 'overlay_style' => 'dark', 'text_position' => 'left',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->runBackfill();

        $first = DB::table('banners')->find($firstId);
        $second = DB::table('banners')->find($secondId);

        $this->assertNotNull($first->heading);
        $this->assertNull($second->heading);
    }

    public function test_backfill_does_nothing_when_table_is_empty(): void
    {
        $this->runBackfill();

        $this->assertSame(0, DB::table('banners')->count());
    }

    public function test_backfill_does_not_overwrite_an_already_filled_heading(): void
    {
        $id = DB::table('banners')->insertGetId([
            'title' => 'Banner Manual', 'image_path' => 'banners/c.webp', 'alt_text' => 'x',
            'order' => 0, 'is_active' => true, 'heading' => 'Sudah Diisi Admin',
            'overlay_style' => 'dark', 'text_position' => 'left',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->runBackfill();

        $banner = DB::table('banners')->find($id);
        $this->assertSame('Sudah Diisi Admin', $banner->heading);
    }

    public function test_backfill_preserves_pre_existing_columns(): void
    {
        $id = DB::table('banners')->insertGetId([
            'title' => 'Banner Lama', 'image_path' => 'banners/old.webp', 'alt_text' => 'Alt Lama',
            'link_url' => 'https://lama.test', 'order' => 0, 'is_active' => false,
            'overlay_style' => 'dark', 'text_position' => 'left',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->runBackfill();

        $banner = DB::table('banners')->find($id);
        $this->assertSame('Banner Lama', $banner->title);
        $this->assertSame('https://lama.test', $banner->link_url);
        $this->assertSame(0, (int) $banner->is_active);
    }
}
