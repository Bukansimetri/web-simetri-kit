<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_topics', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Opsi yang sebelumnya hardcode di form Kontak, supaya dropdown tetap
        // identik setelah migrasi dijalankan.
        DB::table('contact_topics')->insert([
            ['slug' => 'umum', 'name' => 'Konsultasi Umum', 'order' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'residensial', 'name' => 'Residensial', 'order' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'komersial', 'name' => 'Komersial & Industri', 'order' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'pompa', 'name' => 'Pompa Air Tenaga Surya', 'order' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_topics');
    }
};
