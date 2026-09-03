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
     * database/seeders/CategorySeeder.php, supaya backfill dari data lama
     * (jika ada) tidak membuat kategori duplikat/berbeda nama dengan seeder.
     *
     * @var array<string, string>
     */
    private const CATEGORY_NAME_MAP = [
        'residensial' => 'Residensial',
        'komersial' => 'Komersial & Industri',
        'pompa-air' => 'Pompa Air',
    ];

    public function up(): void
    {
        // 1. Tambah kolom baru. `category_id` sengaja tetap nullable di level DB
        //    (wajib-nya cukup divalidasi Filament, konsisten pola nullable settings
        //    di project ini) — FK + restrictOnDelete tetap jadi safety net FR-004.
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('id')->constrained()->restrictOnDelete();
            $table->json('images')->nullable()->after('price');
        });

        // 2. Backfill Category dari nilai `products.category` (string) yang sudah ada,
        //    lalu isi `category_id`-nya. Aman dijalankan baik di DB kosong (fresh
        //    install, loop di bawah tidak menemukan apa-apa) maupun DB yang sudah
        //    berisi data seed dari 002-theme-branding-system (research.md §3).
        if (Schema::hasColumn('products', 'category')) {
            $existingCategoryValues = DB::table('products')
                ->whereNotNull('category')
                ->distinct()
                ->pluck('category');

            foreach ($existingCategoryValues as $value) {
                $name = self::CATEGORY_NAME_MAP[$value] ?? Str::title(str_replace('-', ' ', $value));

                DB::table('categories')->insertOrIgnore([
                    'name' => $name,
                    'order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $categoryId = DB::table('categories')->where('name', $name)->value('id');

                DB::table('products')->where('category', $value)->update(['category_id' => $categoryId]);
            }
        }

        // 3. Drop kolom lama — `category` (digantikan relasi `category_id`) dan
        //    `image_path` (digantikan `images` json; lihat data-model.md — kolom
        //    ini ternyata tidak pernah benar-benar dirender di frontend publik 002).
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['category', 'image_path']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('category')->nullable()->after('name');
            $table->string('image_path')->nullable()->after('images');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn('images');
        });
    }
};
