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
