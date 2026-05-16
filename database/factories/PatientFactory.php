<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Patient> */
class PatientFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'    => null,
            'mrn'        => 'MRN-' . strtoupper(Str::random(8)),
            'first_name' => fake()->firstName(),
            'last_name'  => fake()->lastName(),
            'email'      => fake()->unique()->safeEmail(),
            'phone'      => fake()->phoneNumber(),
            'dob'        => fake()->date('Y-m-d', '-20 years'),
            'gender'     => fake()->randomElement(['male', 'female', 'other']),
            'onboarding_status' => Patient::ONBOARDING_COMPLETED,
            'onboarding_step'   => 4,
        ];
    }

    public function withUser(): static
    {
        return $this->state(function () {
            $user = User::factory()->create(['role' => User::ROLE_PATIENT]);
            return ['user_id' => $user->id];
        });
    }
}
