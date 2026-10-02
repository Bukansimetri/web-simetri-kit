<?php

namespace Database\Seeders;

use App\Support\PageContent\PageContentInstaller;
use Illuminate\Database\Seeder;

/**
 * Konten dasar section & CTA (bukan demo). Aman dijalankan ulang.
 */
class PageContentSeeder extends Seeder
{
    public function run(): void
    {
        PageContentInstaller::install();
    }
}
