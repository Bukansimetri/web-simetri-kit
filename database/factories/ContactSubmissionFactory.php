<?php

namespace Database\Factories;

use App\Models\ContactSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactSubmission>
 */
class ContactSubmissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => '08'.fake()->numerify('##########'),
            'topic' => fake()->randomElement(['umum', 'residensial', 'komersial', 'pompa']),
            'message' => fake()->paragraph(),
            'status' => ContactSubmission::STATUS_NEW,
        ];
    }
}
