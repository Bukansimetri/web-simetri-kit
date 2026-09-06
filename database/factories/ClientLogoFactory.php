<?php

namespace Database\Factories;

use App\Models\ClientLogo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClientLogo>
 */
class ClientLogoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'logo_path' => 'client-logos/'.fake()->uuid().'.webp',
            'link_url' => null,
            'order' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
