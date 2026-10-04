<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('section_headings', function (Blueprint $table) {
            $table->id();
            $table->string('section', 64)->unique();
            $table->string('title', 255);
            $table->string('subtitle', 500)->nullable();
            $table->string('eyebrow', 60)->nullable();
            $table->string('featured_image_path')->nullable();
            $table->string('featured_icon', 64)->nullable();
            $table->string('featured_title', 160)->nullable();
            $table->string('featured_description', 500)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_headings');
    }
};
