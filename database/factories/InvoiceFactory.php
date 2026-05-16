<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Invoice> */
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        $total = fake()->numberBetween(5000, 50000);
        return [
            'patient_id'     => Patient::factory(),
            'number'         => 'INV-' . now()->format('Ymd') . '-' . fake()->unique()->numerify('####'),
            'issued_on'      => now()->toDateString(),
            'due_on'         => now()->addDays(30)->toDateString(),
            'subtotal_cents' => $total,
            'total_cents'    => $total,
            'paid_cents'     => 0,
            'status'         => 'draft',
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attrs) => [
            'paid_cents' => $attrs['total_cents'],
            'status'     => 'paid',
        ]);
    }
}
