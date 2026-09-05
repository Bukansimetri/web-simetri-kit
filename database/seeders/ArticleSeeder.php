<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Data contoh (seed) — mengikuti struktur artikel di mockup
 * public/mockup-html/artikel_suoer_consistent_header_footer. Sementara,
 * siap digantikan modul CRUD Epic 3 (AMC-213) tanpa mengubah Blade (FR-008).
 *
 * Membutuhkan ArticleCategorySeeder dijalankan lebih dulu (lihat
 * DatabaseSeeder) — kategori dicari berdasarkan nama, bukan dibuat ulang.
 */
class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $articles = [
            [
                'title' => '5 Tanda Rumah Anda Siap Pakai Panel Surya',
                'excerpt' => 'Kenali ciri-ciri hunian yang paling diuntungkan dari instalasi panel surya, mulai dari orientasi atap hingga pola konsumsi listrik.',
                'category' => 'Edukasi',
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Cara Kerja Net Metering PLN untuk Pemilik Panel Surya',
                'excerpt' => 'Pahami skema net metering yang memungkinkan kelebihan listrik dari panel surya Anda diekspor ke jaringan PLN.',
                'category' => 'Edukasi',
                'published_at' => now()->subDays(10),
            ],
            [
                'title' => 'SUOER Selesaikan Instalasi 500 Rumah di Jabodetabek',
                'excerpt' => 'Pencapaian baru SUOER dalam mendukung transisi energi bersih di kawasan perkotaan Jabodetabek.',
                'category' => 'Berita',
                'published_at' => now()->subDays(20),
            ],
        ];

        foreach ($articles as $article) {
            $categoryId = ArticleCategory::query()->where('name', $article['category'])->value('id');

            $paragraphs = collect([$article['excerpt'], ...fake()->paragraphs(4)])
                ->map(fn (string $paragraph) => '<p>'.$paragraph.'</p>')
                ->implode('');

            Article::query()->updateOrCreate(
                ['slug' => Str::slug($article['title'])],
                [
                    'title' => $article['title'],
                    'excerpt' => $article['excerpt'],
                    'content' => $paragraphs,
                    'redaksi' => 'Tim Redaksi SUOER',
                    'article_category_id' => $categoryId,
                    'published_at' => $article['published_at'],
                ]
            );
        }
    }
}
