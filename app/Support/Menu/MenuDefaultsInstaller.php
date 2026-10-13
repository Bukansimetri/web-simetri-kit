<?php

namespace App\Support\Menu;

use App\Models\MenuItem;
use App\Models\MenuLocation;

/**
 * Menanam menu bawaan navbar dan footer sebagai item Menu Builder agar bisa
 * diurutkan. Idempoten per lokasi: tidak berbuat apa-apa bila lokasi itu
 * sudah punya item "Halaman Bawaan". Item yang sudah dibuat admin
 * dipertahankan dan diletakkan setelah menu bawaan (admin tinggal
 * menyeretnya ke posisi yang diinginkan).
 */
class MenuDefaultsInstaller
{
    public const NAVBAR_SLUG = 'navbar-utama';

    public const FOOTER_SLUG = 'footer';

    public static function install(): void
    {
        $navbar = array_map(
            fn (string $routeName): array => ['label' => MenuRoutes::OPTIONS[$routeName], 'route' => $routeName],
            MenuRoutes::NAVBAR_DEFAULTS
        );

        self::installFor(self::NAVBAR_SLUG, $navbar);
        self::installFor(self::FOOTER_SLUG, MenuRoutes::FOOTER_DEFAULTS);
    }

    /**
     * @param  array<int, array{label: string, route?: string, children?: array<int, array{label: string, route: string}>}>  $defaults
     */
    private static function installFor(string $slug, array $defaults): void
    {
        $location = MenuLocation::query()->where('slug', $slug)->first();

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

        $offset = count($defaults);

        MenuItem::query()
            ->where('menu_location_id', $location->id)
            ->root()
            ->orderBy('order_column')
            ->orderBy('id')
            ->get()
            ->each(fn (MenuItem $item, int $index) => $item->update(['order_column' => $offset + 1 + $index]));

        foreach ($defaults as $index => $default) {
            $item = self::create($location, $default['label'], $default['route'] ?? null, $index + 1);

            foreach ($default['children'] ?? [] as $childIndex => $child) {
                self::create($location, $child['label'], $child['route'], $childIndex + 1, $item->id);
            }
        }
    }

    private static function create(MenuLocation $location, string $label, ?string $routeName, int $order, ?int $parentId = null): MenuItem
    {
        return MenuItem::query()->create([
            'menu_location_id' => $location->id,
            'parent_id' => $parentId,
            'label' => $label,
            'link_type' => $routeName === null ? MenuItem::LINK_TYPE_NONE : MenuItem::LINK_TYPE_ROUTE,
            'route_name' => $routeName,
            'order_column' => $order,
            'is_active' => true,
        ]);
    }
}
