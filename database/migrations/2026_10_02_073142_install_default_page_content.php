<?php

use App\Support\PageContent\PageContentInstaller;
use Illuminate\Database\Migrations\Migration;

/**
 * Menanam konten live section & CTA yang sebelumnya tertulis di Blade, agar
 * deploy yang hanya menjalankan `migrate --force` tidak mengubah tampilan.
 * Sengaja memakai kelas aplikasi, bukan seeder (constitution: seeder tidak auto-run di migrasi).
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
