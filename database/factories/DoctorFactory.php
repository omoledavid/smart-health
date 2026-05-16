<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Doctor> */
class DoctorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'              => User::factory()->state(['role' => User::ROLE_DOCTOR]),
            'specialization_id'    => Specialization::factory(),
            'license_number'       => strtoupper(fake()->bothify('MD-####??')),
            'bio'                  => fake()->paragraph(),
            'years_experience'     => fake()->numberBetween(1, 30),
            'consultation_fee_cents' => fake()->numberBetween(5000, 30000),
        ];
    }
}
