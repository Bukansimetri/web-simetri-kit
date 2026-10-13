<?php

namespace App\Support\Menu;

use App\Models\MenuItem;
use App\Models\MenuLocation;

/**
 * Menanam menu bawaan navbar sebagai item Menu Builder agar bisa diurutkan.
 * Idempoten: tidak berbuat apa-apa bila navbar sudah punya item "Halaman
 * Bawaan". Item yang sudah dibuat admin dipertahankan dan diletakkan setelah
 * menu bawaan (admin tinggal menyeretnya ke posisi yang diinginkan).
 */
class MenuDefaultsInstaller
{
    public const NAVBAR_SLUG = 'navbar-utama';

    public static function install(): void
    {
        $location = MenuLocation::query()->where('slug', self::NAVBAR_SLUG)->first();

        if ($location === null) {
            return;
        }

        $alreadyInstalled = MenuItem::query()
            ->where('menu_location_id', $location->id)
            ->where('link_type', MenuItem::LINK_TYPE_ROUTE)
            ->exists();

        if ($alreadyInstalled) {
            return;
        }

        $offset = count(MenuRoutes::NAVBAR_DEFAULTS);

        MenuItem::query()
            ->where('menu_location_id', $location->id)
            ->root()
            ->orderBy('order_column')
            ->orderBy('id')
            ->get()
            ->each(fn (MenuItem $item, int $index) => $item->update(['order_column' => $offset + 1 + $index]));

        foreach (MenuRoutes::NAVBAR_DEFAULTS as $index => $routeName) {
            MenuItem::query()->create([
                'menu_location_id' => $location->id,
                'label' => MenuRoutes::OPTIONS[$routeName],
                'link_type' => MenuItem::LINK_TYPE_ROUTE,
                'route_name' => $routeName,
                'order_column' => $index + 1,
                'is_active' => true,
            ]);
        }
    }
}
