<?php

use App\Support\PageContent\PageContentInstaller;
use Illuminate\Database\Migrations\Migration;

/**
 * Menanam banner halaman (Produk, Karir, Artikel, FAQ, Kontak, Portfolio) dan judul banner Tentang Kami
 * dengan teks & gambar yang sebelumnya tertulis di Blade, agar tampilan tidak berubah setelah deploy.
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
