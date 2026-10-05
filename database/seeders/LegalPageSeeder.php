<?php

namespace Database\Seeders;

use App\Support\PageContent\LegalPageInstaller;
use Illuminate\Database\Seeder;

/**
 * Menanam halaman Kebijakan Privasi dan Syarat & Ketentuan bila slug-nya belum ada.
 */
class LegalPageSeeder extends Seeder
{
    public function run(): void
    {
        LegalPageInstaller::install();
    }
}
