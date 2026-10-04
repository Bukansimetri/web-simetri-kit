<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faq_items', function (Blueprint $table) {
            $table->string('placement', 20)->default('faq')->index()->after('category');
            $table->boolean('is_active')->default(true)->after('placement');
        });
    }

    public function down(): void
    {
        Schema::table('faq_items', function (Blueprint $table) {
            $table->dropIndex(['placement']);
            $table->dropColumn(['placement', 'is_active']);
        });
    }
};
