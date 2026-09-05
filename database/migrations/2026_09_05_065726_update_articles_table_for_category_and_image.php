<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Nama kategori kanonik dipetakan sama persis dengan yang dipakai
     * database/seeders/ArticleCategorySeeder.php, supaya backfill dari data
     * lama (jika ada) tidak membuat kategori duplikat/berbeda nama dengan
     * seeder — pola identik migration Produk (research.md §1).
     *
     * @var array<string, string>
     */
    private const CATEGORY_NAME_MAP = [
        'tips' => 'Tips',
        'berita' => 'Berita',
        'edukasi' => 'Edukasi',
    ];

    public function up(): void
    {
        // 1. Tambah kolom baru. `article_category_id` sementara nullable supaya
        //    backfill di langkah 2 bisa mengisinya dulu sebelum di-NOT-NULL-kan.
        Schema::table('articles', function (Blueprint $table) {
            $table->foreignId('article_category_id')->nullable()->after('id')->constrained()->restrictOnDelete();
            $table->string('redaksi')->nullable()->after('content');
        });

        // 2. Backfill ArticleCategory dari nilai `articles.category` (string) yang
        //    sudah ada, lalu isi `article_category_id`-nya (research.md §1, pola
        //    sama seperti migration Produk 003-produk-crud-admin).
        if (Schema::hasColumn('articles', 'category')) {
            $existingCategoryValues = DB::table('articles')
                ->whereNotNull('category')
                ->distinct()
                ->pluck('category');

            foreach ($existingCategoryValues as $value) {
                $name = self::CATEGORY_NAME_MAP[$value] ?? Str::title(str_replace('-', ' ', $value));

                DB::table('article_categories')->insertOrIgnore([
                    'name' => $name,
                    'order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $categoryId = DB::table('article_categories')->where('name', $name)->value('id');

                DB::table('articles')->where('category', $value)->update(['article_category_id' => $categoryId]);
            }
        }

        // 3. Artikel tanpa kategori sama sekali (mis. instalasi kosong tanpa seed)
        //    tidak perlu ditangani di sini — `article_category_id` wajib divalidasi
        //    di form Filament (FR-004), bukan di-enforce lewat DB NOT NULL, supaya
        //    konsisten dengan pola nullable-di-DB/wajib-di-app yang dipakai project
        //    ini (lihat kolom serupa di Produk).

        // 4. Drop kolom lama `category` (digantikan relasi `article_category_id`),
        //    dan jadikan `image_path` nullable (FR-013 opsional; digunakan sebagai
        //    penampung path hasil konversi WebP di FR-021).
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('category');
            $table->string('image_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('category')->nullable()->after('title');
            $table->string('image_path')->nullable(false)->change();
        });

        Schema::table('articles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('article_category_id');
            $table->dropColumn('redaksi');
        });
    }
};
