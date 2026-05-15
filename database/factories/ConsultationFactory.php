<?php

namespace Database\Factories;

use App\Models\Consultation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Consultation>
 */
class ConsultationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_name' => $this->faker->name(),
            'raw_notes' => 'Pt c/o SOB x2d, mild fever 38.1, no chest pain.',
            'structured_soap' => null,
            'status' => Consultation::STATUS_DRAFT,
        ];
    }
}
