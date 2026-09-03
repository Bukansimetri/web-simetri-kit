<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

/**
 * Kategori produk kanonik — nama harus sama persis dengan
 * CATEGORY_NAME_MAP di migration 2026_09_03_162922_update_products_table_for_category_and_gallery
 * supaya tidak terjadi duplikat kategori saat migrasi data lama.
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Residensial',
            'Komersial & Industri',
            'Pompa Air',
        ];

        foreach ($categories as $order => $name) {
            Category::query()->updateOrCreate(['name' => $name], ['order' => $order]);
        }
    }
}
