<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Service> */
class ServiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'             => fake()->unique()->words(3, true),
            'description'      => fake()->sentence(),
            'duration_minutes' => fake()->randomElement([15, 30, 45, 60]),
            'price_cents'      => fake()->numberBetween(5000, 50000),
        ];
    }
}
