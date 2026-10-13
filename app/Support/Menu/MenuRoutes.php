<?php

namespace App\Support\Menu;

use App\Settings\SiteSettings;
use Illuminate\Support\Facades\Route;

/**
 * Halaman bawaan situs yang boleh dijadikan tujuan item menu (tipe tautan
 * "Halaman Bawaan"), sehingga menu bawaan bisa diurutkan seperti item lain.
 */
class MenuRoutes
{
    /**
     * Urutan di sini menjadi urutan menu bawaan navbar.
     *
     * @var array<string, string>
     */
    public const OPTIONS = [
        'home' => 'Beranda',
        'tentang-kami' => 'Tentang Kami',
        'produk.index' => 'Produk',
        'artikel.index' => 'Artikel',
        'portfolio.index' => 'Portfolio',
        'karir' => 'Karir',
        'kontak' => 'Kontak',
        'faq' => 'FAQ',
    ];

    /**
     * Item menu bawaan navbar yang ditanam saat lokasi menu masih kosong
     * (sama dengan menu statis sebelum Menu Builder dipakai).
     *
     * @var array<int, string>
     */
    public const NAVBAR_DEFAULTS = ['home', 'tentang-kami', 'produk.index', 'artikel.index', 'karir', 'kontak'];

    /**
     * Kolom footer bawaan: judul kolom (item induk tanpa tautan) beserta
     * sub-itemnya, sama dengan kolom statis sebelum Menu Builder dipakai.
     *
     * @var array<int, array{label: string, children: array<int, array{label: string, route: string}>}>
     */
    public const FOOTER_DEFAULTS = [
        [
            'label' => 'Solusi',
            'children' => [
                ['label' => 'Panel Residensial', 'route' => 'produk.index'],
                ['label' => 'B2B & Industri', 'route' => 'produk.index'],
                ['label' => 'Pompa Air Surya', 'route' => 'produk.index'],
                ['label' => 'Net Metering PLN', 'route' => 'produk.index'],
            ],
        ],
        [
            'label' => 'Perusahaan',
            'children' => [
                ['label' => 'Tentang Kami', 'route' => 'tentang-kami'],
                ['label' => 'Blog & Artikel', 'route' => 'artikel.index'],
                ['label' => 'Karir', 'route' => 'karir'],
                ['label' => 'FAQ', 'route' => 'faq'],
            ],
        ],
    ];

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return self::OPTIONS;
    }

    public static function url(?string $routeName): ?string
    {
        if ($routeName === null || ! array_key_exists($routeName, self::OPTIONS) || ! Route::has($routeName)) {
            return null;
        }

        return route($routeName);
    }

    /**
     * Halaman Karir ikut tersembunyi bila modul karir dimatikan di Pengaturan Umum.
     */
    public static function isHidden(?string $routeName): bool
    {
        return $routeName === 'karir' && ! app(SiteSettings::class)->career_module_enabled;
    }
}
