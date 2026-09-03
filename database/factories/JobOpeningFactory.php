<?php

namespace Database\Factories;

use App\Models\JobOpening;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<JobOpening>
 */
class JobOpeningFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => fake()->jobTitle(),
            'location' => fake()->randomElement(['Jakarta', 'Jakarta / Remote', 'Bandung']),
            'employment_type' => fake()->randomElement(['full-time', 'internship', 'contract']),
            'description' => fake()->paragraph(),
            'is_active' => true,
        ];
    }
}
