<?php

namespace Database\Factories;

use App\Models\ElectricityAppliance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ElectricityAppliance>
 */
class ElectricityApplianceFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'slug' => str($name)->slug(),
            'name' => ucfirst($name),
            'icon' => 'bolt',
            'watt' => fake()->numberBetween(50, 2000),
            'order' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
