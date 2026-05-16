<?php

use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;

// ── Index – role scoping ───────────────────────────────────────────────────────

it('admin sees all invoices', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    Invoice::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('invoices.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Invoices/Index')
            ->where('invoices.total', 3)
        );
});

it('patient only sees their own invoices', function () {
    $patientUser = User::factory()->create(['role' => User::ROLE_PATIENT]);
    $patient     = Patient::factory()->create(['user_id' => $patientUser->id]);

    Invoice::factory()->count(2)->create(['patient_id' => $patient->id]);
    Invoice::factory()->count(3)->create(); // other patients

    $this->actingAs($patientUser)
        ->get(route('invoices.index'))
        ->assertInertia(fn ($page) => $page->where('invoices.total', 2));
});

// ── Stats scoping ──────────────────────────────────────────────────────────────

it('patient stats only reflect their own invoices', function () {
    $patientUser = User::factory()->create(['role' => User::ROLE_PATIENT]);
    $patient     = Patient::factory()->create(['user_id' => $patientUser->id]);

    Invoice::factory()->paid()->create(['patient_id' => $patient->id, 'total_cents' => 15000]);
    Invoice::factory()->create(['patient_id' => $patient->id, 'total_cents' => 5000, 'status' => 'draft']);
    Invoice::factory()->paid()->count(5)->create(); // other patients — must be excluded

    $this->actingAs($patientUser)
        ->get(route('invoices.index'))
        ->assertInertia(fn ($page) => $page
            ->where('stats.total', 2)
            ->where('stats.paid_cents', 15000)
        );
});

it('admin stats aggregate across all patients', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    Invoice::factory()->paid()->count(2)->create(['total_cents' => 10000]);
    Invoice::factory()->create(['total_cents' => 3000, 'status' => 'draft']);

    $this->actingAs($admin)
        ->get(route('invoices.index'))
        ->assertInertia(fn ($page) => $page
            ->where('stats.total', 3)
            ->where('stats.paid_cents', 20000)
        );
});

// ── Create / Store ─────────────────────────────────────────────────────────────

it('admin can create an invoice', function () {
    $admin   = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $patient = Patient::factory()->create();
    $service = Service::factory()->create(['price_cents' => 10000]);

    $this->actingAs($admin)->post(route('invoices.store'), [
        'patient_id' => $patient->id,
        'issued_on'  => now()->toDateString(),
        'items'      => [
            [
                'description'     => $service->name,
                'service_id'      => $service->id,
                'quantity'        => 1,
                'unit_price_cents' => 10000,
            ],
        ],
    ])->assertRedirect();

    $this->assertDatabaseHas('invoices', ['patient_id' => $patient->id]);
    $this->assertDatabaseHas('invoice_items', ['unit_price_cents' => 10000]);
});

it('validates required invoice fields', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $this->actingAs($admin)
        ->post(route('invoices.store'), [])
        ->assertSessionHasErrors(['patient_id', 'issued_on', 'items']);
});

// ── Payment recording ──────────────────────────────────────────────────────────

it('recording a payment increments paid_cents', function () {
    $admin   = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $invoice = Invoice::factory()->create(['total_cents' => 10000, 'paid_cents' => 0]);

    $this->actingAs($admin)->post(route('invoices.payments', $invoice), [
        'amount_cents' => 5000,
        'method'       => 'cash',
        'paid_on'      => now()->toDateString(),
    ])->assertRedirect();

    expect($invoice->fresh()->paid_cents)->toBe(5000);
});

it('marks invoice as paid when full amount is recorded', function () {
    $admin   = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $invoice = Invoice::factory()->create(['total_cents' => 10000, 'paid_cents' => 0]);

    $this->actingAs($admin)->post(route('invoices.payments', $invoice), [
        'amount_cents' => 10000,
        'method'       => 'card',
        'paid_on'      => now()->toDateString(),
    ]);

    expect($invoice->fresh()->status)->toBe('paid');
});

it('does not mark invoice as paid when partial payment is recorded', function () {
    $admin   = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $invoice = Invoice::factory()->create(['total_cents' => 10000, 'paid_cents' => 0]);

    $this->actingAs($admin)->post(route('invoices.payments', $invoice), [
        'amount_cents' => 4999,
        'method'       => 'cash',
        'paid_on'      => now()->toDateString(),
    ]);

    expect($invoice->fresh()->status)->not->toBe('paid');
});
