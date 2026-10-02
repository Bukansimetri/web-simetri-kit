<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('section_items', function (Blueprint $table) {
            $table->id();
            $table->string('section', 64);
            $table->string('icon', 64)->nullable();
            $table->string('title', 120);
            $table->string('description', 500);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_emphasized')->default(false);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->index(['section', 'is_active', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_items');
    }
};
