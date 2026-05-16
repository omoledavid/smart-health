<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Location> */
class LocationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name'    => fake()->company() . ' Clinic',
            'address' => fake()->streetAddress(),
            'city'    => fake()->city(),
            'phone'   => fake()->phoneNumber(),
            'timezone' => 'America/Los_Angeles',
        ];
    }
}
