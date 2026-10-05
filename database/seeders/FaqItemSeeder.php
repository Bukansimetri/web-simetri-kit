<?php

namespace Database\Seeders;

use App\Support\PageContent\PageContentInstaller;
use Illuminate\Database\Seeder;

/**
 * Menanam FAQ awal (halaman FAQ, Produk, Kontak) lewat installer yang sama dengan migrasi:
 * hanya mengisi tempat yang masih kosong, sehingga aman dijalankan ulang.
 */
class FaqItemSeeder extends Seeder
{
    public function run(): void
    {
        PageContentInstaller::install();
    }
}
