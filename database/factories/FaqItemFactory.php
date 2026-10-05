<?php

namespace Database\Factories;

use App\Enums\FaqPlacement;
use App\Models\FaqItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FaqItem>
 */
class FaqItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'question' => fake()->sentence().'?',
            'answer' => fake()->paragraph(),
            'category' => fake()->randomElement(['Umum', 'Pembayaran', 'Instalasi']),
            'placement' => FaqPlacement::Faq,
            'is_active' => true,
            'order' => 0,
        ];
    }

    public function forPlacement(FaqPlacement $placement): static
    {
        return $this->state(fn (array $attributes) => ['placement' => $placement, 'category' => null]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
