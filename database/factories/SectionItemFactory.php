<?php

namespace Database\Factories;

use App\Enums\PageSection;
use App\Models\SectionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SectionItem>
 */
class SectionItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'section' => PageSection::WhyChoose,
            'icon' => 'savings',
            'title' => fake()->words(3, true),
            'description' => fake()->sentence(12),
            'is_active' => true,
            'is_emphasized' => false,
            'order' => fake()->numberBetween(10, 100),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }

    public function emphasized(): static
    {
        return $this->state(fn (): array => ['is_emphasized' => true]);
    }

    public function forSection(PageSection $section): static
    {
        return $this->state(fn (): array => [
            'section' => $section,
            'icon' => $section->hasIcon() ? 'savings' : null,
        ]);
    }
}
