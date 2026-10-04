<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('call_to_actions', function (Blueprint $table) {
            $table->id();
            $table->string('placement', 64)->unique();
            $table->string('title', 80);
            $table->string('body', 300)->nullable();
            $table->string('primary_label', 40);
            $table->string('secondary_label', 40)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('call_to_actions');
    }
};
