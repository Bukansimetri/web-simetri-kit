<?php

use App\Support\PageContent\PageContentInstaller;
use Illuminate\Database\Migrations\Migration;

/**
 * Menanam FAQ Produk dan Kontak (sebelumnya tertulis di Blade) sebagai data, agar tampilan tidak berubah setelah deploy.
 */
return new class extends Migration
{
    public function up(): void
    {
        PageContentInstaller::install();
    }

    public function down(): void
    {
        //
    }
};
