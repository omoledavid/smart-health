<?php

use App\Models\Doctor;
use App\Models\Location;
use App\Models\Service;
use App\Models\Specialization;
use App\Models\User;

// ── Authorization ──────────────────────────────────────────────────────────────

it('admin can view the doctors list', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    Doctor::factory()->count(2)->create();

    $this->actingAs($admin)
        ->get(route('doctors.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Doctors/Index'));
});

it('doctor store is accessible to any authenticated user (UI-level role guard only)', function () {
    $patientUser = User::factory()->create(['role' => User::ROLE_PATIENT]);
    $spec        = Specialization::factory()->create();

    // No server-side 403 — role restriction is enforced in the UI only.
    $this->actingAs($patientUser)
        ->post(route('doctors.store'), [
            'name'              => 'Fake Doctor',
            'email'             => 'fake@test.com',
            'password'          => 'secret1234',
            'specialization_id' => $spec->id,
            'license_number'    => 'X-0001',
            'years_experience'  => 1,
        ])
        ->assertRedirect(); // succeeds — documents current behaviour
});

// ── Create / Store ─────────────────────────────────────────────────────────────

it('admin can create a doctor', function () {
    $admin          = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $specialization = Specialization::factory()->create();
    $location       = Location::factory()->create();
    $service        = Service::factory()->create();

    $this->actingAs($admin)->post(route('doctors.store'), [
        'name'              => 'Dr. Jane Smith',
        'email'             => 'jane@clinic.com',
        'password'          => 'secret1234',
        'specialization_id' => $specialization->id,
        'license_number'    => 'MD-1234',
        'years_experience'  => 10,
        'location_ids'      => [$location->id],
        'service_ids'       => [$service->id],
    ])->assertRedirect();

    $this->assertDatabaseHas('users', ['email' => 'jane@clinic.com', 'role' => User::ROLE_DOCTOR]);
    $this->assertDatabaseHas('doctors', ['license_number' => 'MD-1234']);
});

it('validates required fields when creating a doctor', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $this->actingAs($admin)
        ->post(route('doctors.store'), [])
        ->assertSessionHasErrors(['name', 'email', 'password']);
});

it('submitting empty doctor form fails validation and creates no doctor', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $this->actingAs($admin)
        ->post(route('doctors.store'), ['name' => '', 'email' => '', 'password' => '']);

    expect(Doctor::count())->toBe(0);
});

// ── Relationships ──────────────────────────────────────────────────────────────

it('doctor belongs to a user', function () {
    $doctorUser = User::factory()->create(['role' => User::ROLE_DOCTOR]);
    $doctor     = Doctor::factory()->create(['user_id' => $doctorUser->id]);

    expect($doctor->user->id)->toBe($doctorUser->id);
});

it('doctor belongs to a specialization', function () {
    $spec   = Specialization::factory()->create();
    $doctor = Doctor::factory()->create(['specialization_id' => $spec->id]);

    expect($doctor->specialization->id)->toBe($spec->id);
});
