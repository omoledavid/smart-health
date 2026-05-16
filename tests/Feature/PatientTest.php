<?php

use App\Models\Patient;
use App\Models\User;

// ── Authorization ──────────────────────────────────────────────────────────────

it('admin can access the patients list', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    Patient::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('patients.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Patients/Index'));
});

it('doctor can access the patients list', function () {
    $doctorUser = User::factory()->create(['role' => User::ROLE_DOCTOR]);

    $this->actingAs($doctorUser)
        ->get(route('patients.index'))
        ->assertOk();
});

it('any authenticated user can reach the patients list endpoint', function () {
    $patientUser = User::factory()->create(['role' => User::ROLE_PATIENT]);

    // The route has no role-based guard; access control is UI-level only.
    $this->actingAs($patientUser)
        ->get(route('patients.index'))
        ->assertOk();
});

// ── Create / Store ─────────────────────────────────────────────────────────────

it('admin can create a patient', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $this->actingAs($admin)->post(route('patients.store'), [
        'first_name' => 'Alice',
        'last_name'  => 'Smith',
        'email'      => 'alice@test.com',
        'dob'        => '1990-05-10',
        'gender'     => 'female',
    ])->assertRedirect();

    $this->assertDatabaseHas('patients', ['email' => 'alice@test.com']);
});

it('validates required fields when creating a patient', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $this->actingAs($admin)
        ->post(route('patients.store'), [])
        ->assertSessionHasErrors(['first_name', 'last_name']);
});

// ── Show ───────────────────────────────────────────────────────────────────────

it('admin can view a patient profile', function () {
    $admin   = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $patient = Patient::factory()->create();

    $this->actingAs($admin)
        ->get(route('patients.show', $patient))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Patients/Show'));
});

// ── Full name accessor ─────────────────────────────────────────────────────────

it('full_name accessor concatenates first and last name', function () {
    $patient = Patient::factory()->make(['first_name' => 'John', 'last_name' => 'Doe']);

    expect($patient->full_name)->toBe('John Doe');
});

it('full_name handles a missing last name gracefully', function () {
    $patient = Patient::factory()->make(['first_name' => 'Mononym', 'last_name' => '']);

    expect($patient->full_name)->toBe('Mononym');
});
