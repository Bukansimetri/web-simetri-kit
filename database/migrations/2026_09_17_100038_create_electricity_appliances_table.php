<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('electricity_appliances', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('icon')->default('bolt');
            $table->unsignedInteger('watt');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed katalog yang sebelumnya hardcode di App\Services\SavingsEstimator
        // supaya kalkulator "Berdasarkan Peralatan" tetap jalan tanpa jeda
        // setelah migrasi ini dijalankan.
        DB::table('electricity_appliances')->insert([
            ['slug' => 'tv', 'name' => 'TV', 'icon' => 'tv', 'watt' => 100, 'order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'kulkas', 'name' => 'Kulkas', 'icon' => 'kitchen', 'watt' => 200, 'order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'ac', 'name' => 'AC', 'icon' => 'ac_unit', 'watt' => 1000, 'order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'pompa', 'name' => 'Pompa Air', 'icon' => 'water_drop', 'watt' => 250, 'order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'pemanas', 'name' => 'Pemanas Air', 'icon' => 'hot_tub', 'watt' => 1500, 'order' => 5, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'kompor', 'name' => 'Kompor Listrik', 'icon' => 'cooking', 'watt' => 2000, 'order' => 6, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('electricity_appliances');
    }
};
