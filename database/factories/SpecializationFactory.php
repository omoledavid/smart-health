<?php

namespace Database\Factories;

use App\Models\Specialization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Specialization> */
class SpecializationFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->word() . ' Medicine';
        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
