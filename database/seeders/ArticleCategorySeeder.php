<?php

namespace Database\Seeders;

use App\Models\ArticleCategory;
use Illuminate\Database\Seeder;

/**
 * Kategori artikel kanonik — nama harus sama persis dengan
 * CATEGORY_NAME_MAP di migration
 * 2026_09_05_065726_update_articles_table_for_category_and_image supaya
 * tidak terjadi duplikat kategori saat migrasi data lama.
 */
class ArticleCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Tips',
            'Berita',
            'Edukasi',
        ];

        foreach ($categories as $order => $name) {
            ArticleCategory::query()->updateOrCreate(['name' => $name], ['order' => $order]);
        }
    }
}
