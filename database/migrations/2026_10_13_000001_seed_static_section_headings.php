<?php

use App\Support\PageContent\PageContentInstaller;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        PageContentInstaller::install();
    }

    public function down(): void
    {
        // Seeded heading rows are retained so admin edits are not deleted on rollback.
    }
};
