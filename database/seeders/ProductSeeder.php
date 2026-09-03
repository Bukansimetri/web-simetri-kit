<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Data contoh (seed) diambil dari mockup Luminous Azure/SUOER
 * (public/mockup-html/produk_suoer_luminous_azure, produk_detail_suoer_header_aligned).
 * Dipertahankan untuk kebutuhan demo/dev saja — CRUD admin (AMC-207,
 * 003-produk-crud-admin) adalah sumber data utama pasca go-live (lihat
 * Assumptions di spec.md fitur tsb). `images` sengaja dikosongkan di sini;
 * gambar diupload lewat panel admin.
 */
class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'SUOER Mono X-Pro 550W',
                'category_name' => 'Residensial',
                'short_description' => 'Panel surya monocrystalline berkinerja tinggi yang dirancang khusus untuk estetika atap rumah modern dengan efisiensi konversi maksimal.',
                'description' => 'Maksimalkan potensi energi matahari dengan panel surya monokristalin efisiensi tinggi. Dirancang untuk memberikan performa optimal bahkan dalam kondisi cahaya rendah, panel ini menawarkan keandalan dan durabilitas untuk kebutuhan energi masa depan Anda.',
                'price' => 2_450_000,
                'strikethrough_price' => 2_800_000,
                'specs' => [
                    'Daya Maksimum (Pmax)' => '550W',
                    'Efisiensi Modul' => '21.3%',
                    'Dimensi' => '2279 x 1134 x 35 mm',
                    'Berat' => '28.6 kg',
                    'Garansi Performa' => '25 Tahun',
                ],
                'features' => [
                    ['icon' => 'shield', 'title' => 'Durabilitas Tinggi', 'description' => 'Tahan cuaca ekstrem dan beban salju/angin tinggi.'],
                    ['icon' => 'bolt', 'title' => 'Efisiensi Maksimal', 'description' => 'Teknologi half-cut cell mengurangi resistensi internal.'],
                    ['icon' => 'verified', 'title' => 'Garansi Panjang', 'description' => 'Jaminan output linear selama 25 tahun pemakaian.'],
                ],
            ],
            [
                'name' => 'SUOER Smart Inverter 5kW',
                'category_name' => 'Komersial & Industri',
                'short_description' => 'Inverter pintar dengan monitoring real-time via aplikasi, cocok untuk instalasi rumah menengah hingga bisnis kecil.',
                'description' => 'Inverter hybrid 5kW dengan efisiensi konversi tinggi dan konektivitas WiFi bawaan untuk monitoring produksi energi secara real-time.',
                'price' => 8_900_000,
                'strikethrough_price' => null,
                'specs' => [
                    'Kapasitas' => '5kW',
                    'Efisiensi Konversi' => '97.5%',
                    'Konektivitas' => 'WiFi & Bluetooth',
                    'Garansi' => '10 Tahun',
                ],
                'features' => [
                    ['icon' => 'monitoring', 'title' => 'Monitoring Real-time', 'description' => 'Pantau produksi energi lewat aplikasi mobile.'],
                    ['icon' => 'bolt', 'title' => 'Efisiensi Tinggi', 'description' => 'Konversi DC ke AC hingga 97.5%.'],
                ],
            ],
            [
                'name' => 'SUOER Pro-Industrial 600W',
                'category_name' => 'Komersial & Industri',
                'short_description' => 'Panel surya kapasitas besar untuk kebutuhan instalasi komersial dan industri skala menengah.',
                'description' => 'Dirancang untuk instalasi atap komersial dan industri dengan kebutuhan daya besar, menawarkan output tinggi per unit panel.',
                'price' => 3_100_000,
                'strikethrough_price' => null,
                'specs' => [
                    'Daya Maksimum (Pmax)' => '600W',
                    'Efisiensi Modul' => '22.1%',
                    'Garansi Performa' => '25 Tahun',
                ],
                'features' => [
                    ['icon' => 'factory', 'title' => 'Skala Industri', 'description' => 'Output tinggi per unit, cocok untuk atap komersial luas.'],
                ],
            ],
            [
                'name' => 'SUOER Aqua-Solar System',
                'category_name' => 'Pompa Air',
                'short_description' => 'Sistem pompa air tenaga surya lengkap untuk irigasi dan kebutuhan air bersih tanpa jaringan listrik PLN.',
                'description' => 'Paket lengkap pompa air tenaga surya — panel, controller, dan pompa submersible — untuk kebutuhan irigasi pertanian atau suplai air bersih di area tanpa listrik PLN.',
                'price' => 12_500_000,
                'strikethrough_price' => null,
                'specs' => [
                    'Kapasitas Pompa' => '2 HP',
                    'Kedalaman Sumur Maks.' => '60 meter',
                    'Garansi' => '5 Tahun',
                ],
                'features' => [
                    ['icon' => 'water_drop', 'title' => 'Tanpa Listrik PLN', 'description' => 'Beroperasi penuh tenaga surya, cocok untuk area terpencil.'],
                ],
            ],
            [
                'name' => 'SUOER PowerBank Home 10kWh',
                'category_name' => 'Residensial',
                'short_description' => 'Baterai penyimpanan energi rumah tangga untuk cadangan listrik saat pemadaman atau malam hari.',
                'description' => 'Sistem baterai lithium 10kWh yang menyimpan kelebihan energi surya di siang hari untuk dipakai malam hari atau saat pemadaman listrik.',
                'price' => 45_000_000,
                'strikethrough_price' => null,
                'specs' => [
                    'Kapasitas' => '10kWh',
                    'Siklus Hidup' => '6000+ siklus',
                    'Garansi' => '10 Tahun',
                ],
                'features' => [
                    ['icon' => 'battery_charging_full', 'title' => 'Cadangan Energi', 'description' => 'Listrik tetap menyala saat pemadaman PLN.'],
                ],
            ],
            [
                'name' => 'Mounting Kit Standar',
                'category_name' => 'Residensial',
                'short_description' => 'Kit pemasangan panel surya universal untuk atap genteng maupun metal, tahan korosi.',
                'description' => 'Kit mounting alumunium anti-karat yang kompatibel dengan mayoritas jenis atap rumah di Indonesia, dilengkapi panduan pemasangan lengkap.',
                'price' => 1_200_000,
                'strikethrough_price' => null,
                'specs' => [
                    'Material' => 'Alumunium anodized',
                    'Kompatibilitas' => 'Atap genteng & metal',
                    'Garansi' => '15 Tahun',
                ],
                'features' => [
                    ['icon' => 'construction', 'title' => 'Mudah Dipasang', 'description' => 'Kompatibel dengan mayoritas jenis atap rumah.'],
                ],
            ],
            [
                'name' => 'SUOER Turnkey Commercial Package',
                'category_name' => 'Komersial & Industri',
                'short_description' => 'Sistem energi surya terintegrasi penuh untuk kebutuhan industri. Termasuk panel efisiensi tinggi, inverter sentral, dan instalasi standar EPC.',
                'description' => 'Paket turnkey lengkap dari desain, pengadaan, hingga instalasi (EPC) untuk kebutuhan energi surya skala industri/komersial besar.',
                'price' => 250_000_000,
                'strikethrough_price' => null,
                'specs' => [
                    'Skala' => 'Industri/Komersial besar',
                    'Cakupan' => 'Desain, pengadaan, instalasi (EPC)',
                    'Garansi Proyek' => '2 Tahun',
                ],
                'features' => [
                    ['icon' => 'handshake', 'title' => 'Solusi Turnkey', 'description' => 'Satu mitra untuk seluruh proses, dari desain hingga instalasi.'],
                ],
            ],
        ];

        foreach ($products as $order => $product) {
            $category = Category::query()->where('name', $product['category_name'])->first();

            // Data literal di atas ditulis asosiatif (label => value) supaya ringkas
            // dibaca; dikonversi ke format list {label, value} yang dipakai kolom
            // `specs` (sesuai output Repeater ProductResource — lihat data-model.md).
            $specs = collect($product['specs'])
                ->map(fn (string $value, string $label) => ['label' => $label, 'value' => $value])
                ->values()
                ->all();

            Product::query()->updateOrCreate(
                ['slug' => Str::slug($product['name'])],
                [
                    'name' => $product['name'],
                    'category_id' => $category?->id,
                    'short_description' => $product['short_description'],
                    'description' => $product['description'],
                    'price' => $product['price'],
                    'strikethrough_price' => $product['strikethrough_price'],
                    'images' => [],
                    'specs' => $specs,
                    'features' => $product['features'],
                    'order' => $order,
                ]
            );
        }
    }
}
