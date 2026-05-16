<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Location;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Appointment> */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'patient_id'       => Patient::factory(),
            'doctor_id'        => Doctor::factory(),
            'location_id'      => Location::factory(),
            'service_id'       => Service::factory(),
            'scheduled_at'     => now()->addDays(fake()->numberBetween(1, 30))->setMinutes(0)->setSeconds(0),
            'duration_minutes' => 30,
            'status'           => Appointment::STATUS_SCHEDULED,
            'visit_type'       => 'in_person',
            'reason'           => fake()->sentence(),
        ];
    }

    public function past(): static
    {
        return $this->state(fn () => [
            'scheduled_at' => now()->subDays(fake()->numberBetween(1, 30)),
            'status'       => Appointment::STATUS_COMPLETED,
        ]);
    }
}
