<?php

namespace Database\Factories;

use App\Enums\PageBlockType;
use App\Models\PageBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PageBlock>
 */
class PageBlockFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'block' => PageBlockType::AboutVision,
            'data' => [
                'eyebrow' => fake()->words(2, true),
                'heading' => fake()->sentence(8),
                'subtext' => fake()->sentence(10),
            ],
        ];
    }
}
