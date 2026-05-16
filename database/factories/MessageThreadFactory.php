<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\MessageThread;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MessageThread> */
class MessageThreadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'doctor_id'  => Doctor::factory(),
            'subject'    => fake()->sentence(4),
        ];
    }
}
