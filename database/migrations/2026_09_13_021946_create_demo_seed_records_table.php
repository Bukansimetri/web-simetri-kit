<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_seed_records', function (Blueprint $table) {
            $table->id();
            $table->string('seedable_type');
            $table->unsignedBigInteger('seedable_id');
            $table->timestamp('created_at')->nullable();

            $table->unique(['seedable_type', 'seedable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_seed_records');
    }
};
