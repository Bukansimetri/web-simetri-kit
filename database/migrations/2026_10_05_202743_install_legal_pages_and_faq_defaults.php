<?php

use App\Support\PageContent\LegalPageInstaller;
use App\Support\PageContent\PageContentInstaller;
use Illuminate\Database\Migrations\Migration;

/**
 * Menanam halaman Kebijakan Privasi, Syarat & Ketentuan, dan FAQ awal agar tautan footer langsung berisi setelah deploy.
 */
return new class extends Migration
{
    public function up(): void
    {
        PageContentInstaller::install();
        LegalPageInstaller::install();
    }

    public function down(): void
    {
        //
    }
};
