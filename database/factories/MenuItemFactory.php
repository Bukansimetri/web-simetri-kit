<?php

namespace Database\Factories;

use App\Models\MenuItem;
use App\Models\MenuLocation;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'menu_location_id' => MenuLocation::factory(),
            'parent_id' => null,
            'label' => fake()->words(2, true),
            'link_type' => MenuItem::LINK_TYPE_NONE,
            'linkable_type' => null,
            'linkable_id' => null,
            'external_url' => null,
            'open_in_new_tab' => false,
            'order_column' => 0,
            'is_active' => true,
        ];
    }

    public function external(?string $url = null): static
    {
        return $this->state(fn () => [
            'link_type' => MenuItem::LINK_TYPE_EXTERNAL,
            'external_url' => $url ?? fake()->url(),
        ]);
    }

    public function internal(Model $linkable): static
    {
        return $this->state(fn () => [
            'link_type' => MenuItem::LINK_TYPE_INTERNAL,
            'linkable_type' => $linkable->getMorphClass(),
            'linkable_id' => $linkable->getKey(),
        ]);
    }
}
