<?php

namespace Database\Factories;

use App\Enums\CtaPlacement;
use App\Models\CallToAction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CallToAction>
 */
class CallToActionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'placement' => CtaPlacement::Faq,
            'title' => fake()->words(5, true),
            'body' => fake()->sentence(10),
            'primary_label' => fake()->words(2, true),
            'secondary_label' => null,
        ];
    }
}
