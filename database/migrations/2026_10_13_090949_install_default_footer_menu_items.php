<?php

use App\Support\Menu\MenuDefaultsInstaller;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Idempoten per lokasi: navbar yang sudah terpasang dilewati, footer
     * yang masih berisi item buatan admin tetap dipertahankan.
     */
    public function up(): void
    {
        MenuDefaultsInstaller::install();
    }

    public function down(): void {}
};
