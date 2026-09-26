<?php

namespace Database\Factories;

use App\Models\CalculatorLead;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalculatorLead>
 */
class CalculatorLeadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $monthlyBill = fake()->numberBetween(300_000, 5_000_000);

        return [
            'name' => fake()->name(),
            'phone' => '08'.fake()->numerify('##########'),
            'area' => fake()->city(),
            'category' => fake()->randomElement([CalculatorLead::CATEGORY_RESIDENTIAL, CalculatorLead::CATEGORY_INDUSTRIAL]),
            'method' => CalculatorLead::METHOD_BILL,
            'monthly_bill' => $monthlyBill,
            'estimated_monthly_bill' => $monthlyBill,
            'savings_year1' => fake()->numberBetween(1_000_000, 10_000_000),
            'total_savings_25y' => fake()->numberBetween(50_000_000, 200_000_000),
            'estimated_investment' => fake()->numberBetween(20_000_000, 100_000_000),
            'breakeven_years' => fake()->randomFloat(1, 3, 8),
            'annual_kwh' => fake()->randomFloat(1, 3000, 15000),
            'assumptions' => [],
            'status' => CalculatorLead::STATUS_NEW,
        ];
    }

    public function won(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CalculatorLead::STATUS_WON,
        ]);
    }
}
