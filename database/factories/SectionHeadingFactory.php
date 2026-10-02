<?php

namespace Database\Factories;

use App\Enums\PageSection;
use App\Models\SectionHeading;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SectionHeading>
 */
class SectionHeadingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'section' => PageSection::WhyChoose,
            'title' => fake()->words(4, true),
            'subtitle' => fake()->sentence(10),
        ];
    }
}
