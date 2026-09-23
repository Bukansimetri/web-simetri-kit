<?php

namespace Database\Seeders;

use App\Models\DemoSeedRecord;
use App\Models\MenuItem;
use App\Models\MenuLocation;
use App\Settings\SiteSettings;
use Illuminate\Database\Seeder;

/**
 * Item menu contoh untuk showcase ke calon klien. Sejak header/footer publik
 * memakai Menu Builder sebagai satu-satunya sumber navbar/footer ketika
 * lokasinya terisi (spec 017-menu-builder), seeder ini menyeed ULANG menu
 * utama (Beranda, Produk, dst.) sebagai MenuItem sungguhan — bukan lagi
 * menu statis di kode — ditambah satu item/grup contoh untuk menunjukkan
 * admin bisa menambah link (termasuk sub-menu bertingkat) tanpa sentuh kode.
 *
 * Dipanggil HANYA lewat `demo:seed` (AMC-229, spec 019-demo-content-seeder)
 * — tidak lagi lewat DatabaseSeeder/app:setup-client (FR-003). Dilewati bila
 * sudah pernah di-seed sebelumnya (research.md #4).
 */
class MenuItemDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (DemoSeedRecord::alreadySeeded(MenuItem::class)) {
            return;
        }

        $careerEnabled = app(SiteSettings::class)->career_module_enabled;

        $navbar = MenuLocation::firstOrCreate(
            ['slug' => 'navbar-utama'],
            ['name' => 'Navbar Utama', 'description' => 'Tampil di header seluruh halaman publik']
        );

        $footer = MenuLocation::firstOrCreate(
            ['slug' => 'footer'],
            ['name' => 'Footer', 'description' => 'Tampil di footer seluruh halaman publik']
        );

        $order = 1;
        $this->createItem($navbar, 'Beranda', url('/'), $order++);
        $this->createItem($navbar, 'Tentang Kami', url('/tentang-kami'), $order++);
        $this->createItem($navbar, 'Produk', url('/produk'), $order++);
        $this->createItem($navbar, 'Artikel', url('/artikel'), $order++);
        if ($careerEnabled) {
            $this->createItem($navbar, 'Karir', url('/karir'), $order++);
        }
        $this->createItem($navbar, 'Kontak', url('/kontak'), $order++);
        $this->createItem($navbar, 'Portofolio', url('/portfolio'), $order);

        $solusi = $this->createItem($footer, 'Solusi', null, 1, MenuItem::LINK_TYPE_NONE);
        $this->createItem($footer, 'Panel Residensial', url('/produk'), 1, parentId: $solusi->id);
        $this->createItem($footer, 'B2B & Industri', url('/produk'), 2, parentId: $solusi->id);
        $this->createItem($footer, 'Pompa Air Surya', url('/produk'), 3, parentId: $solusi->id);
        $this->createItem($footer, 'Net Metering PLN', url('/produk'), 4, parentId: $solusi->id);

        $perusahaan = $this->createItem($footer, 'Perusahaan', null, 2, MenuItem::LINK_TYPE_NONE);
        $order = 1;
        $this->createItem($footer, 'Tentang Kami', url('/tentang-kami'), $order++, parentId: $perusahaan->id);
        $this->createItem($footer, 'Blog & Artikel', url('/artikel'), $order++, parentId: $perusahaan->id);
        if ($careerEnabled) {
            $this->createItem($footer, 'Karir', url('/karir'), $order++, parentId: $perusahaan->id);
        }
        $this->createItem($footer, 'FAQ', url('/faq'), $order, parentId: $perusahaan->id);

        $sumberDaya = $this->createItem($footer, 'Sumber Daya', null, 3, MenuItem::LINK_TYPE_NONE);
        $this->createItem($footer, 'Portofolio', url('/portfolio'), 1, parentId: $sumberDaya->id);
    }

    private function createItem(
        MenuLocation $location,
        string $label,
        ?string $externalUrl,
        int $order,
        string $linkType = MenuItem::LINK_TYPE_EXTERNAL,
        ?int $parentId = null,
    ): MenuItem {
        $item = MenuItem::create([
            'menu_location_id' => $location->id,
            'parent_id' => $parentId,
            'label' => $label,
            'link_type' => $linkType,
            'external_url' => $externalUrl,
            'order_column' => $order,
            'is_active' => true,
        ]);

        DemoSeedRecord::recordFor($item);

        return $item;
    }
}
