<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_pages', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->after('content');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->string('meta_image_path')->nullable()->after('meta_description');
        });
    }

    public function down(): void
    {
        Schema::table('custom_pages', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'meta_image_path']);
        });
    }
};
