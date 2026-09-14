<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();
            $table->string('label');
            $table->string('link_type')->default('none');
            $table->string('linkable_type')->nullable();
            $table->unsignedBigInteger('linkable_id')->nullable();
            $table->string('external_url')->nullable();
            $table->boolean('open_in_new_tab')->default(false);
            $table->integer('order_column')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['menu_location_id', 'parent_id', 'order_column']);
            $table->index(['linkable_type', 'linkable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
