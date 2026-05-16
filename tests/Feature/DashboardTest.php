<?php

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\User;

// ── Admin dashboard ────────────────────────────────────────────────────────────

it('renders the admin dashboard with correct stats', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    Doctor::factory()->count(2)->create();
    $patients = Patient::factory()->count(3)->create();
    Invoice::factory()->paid()->count(2)->create([
        'total_cents' => 10000,
        'patient_id'  => $patients->first()->id,
    ]);

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Admin')
            ->has('stats')
            ->where('stats.doctors', 2)
            ->where('stats.patients', 3)
            ->has('monthly')
            ->has('apptCounts')
            ->has('upcoming')
        );
});

it('shows revenue only from paid invoices on admin dashboard', function () {
    $admin   = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $patient = Patient::factory()->create();

    Invoice::factory()->paid()->create(['total_cents' => 20000, 'patient_id' => $patient->id]);
    Invoice::factory()->create(['total_cents' => 5000, 'status' => 'draft', 'patient_id' => $patient->id]); // unpaid

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->where('stats.revenue_cents', 20000)
        );
});

// ── Doctor dashboard ───────────────────────────────────────────────────────────

it('renders the doctor dashboard', function () {
    $doctorUser = User::factory()->create(['role' => User::ROLE_DOCTOR]);
    Doctor::factory()->create(['user_id' => $doctorUser->id]);

    $this->actingAs($doctorUser)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Doctor')
            ->has('stats')
            ->has('schedule')
        );
});

it('only shows today\'s appointments in the doctor schedule', function () {
    $doctorUser = User::factory()->create(['role' => User::ROLE_DOCTOR]);
    $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);
    $patient = Patient::factory()->create();

    // Today's appointment
    Appointment::factory()->create([
        'doctor_id'    => $doctor->id,
        'patient_id'   => $patient->id,
        'scheduled_at' => now()->setTime(10, 0),
        'status'       => Appointment::STATUS_SCHEDULED,
    ]);

    // Tomorrow's appointment — should not appear
    Appointment::factory()->create([
        'doctor_id'    => $doctor->id,
        'patient_id'   => $patient->id,
        'scheduled_at' => now()->addDay()->setTime(10, 0),
        'status'       => Appointment::STATUS_SCHEDULED,
    ]);

    $this->actingAs($doctorUser)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->where('stats.today_appointments', 1)
            ->count('schedule', 1)
        );
});

// ── Patient dashboard ──────────────────────────────────────────────────────────

it('renders the patient dashboard', function () {
    $patientUser = User::factory()->create(['role' => User::ROLE_PATIENT]);
    Patient::factory()->create(['user_id' => $patientUser->id]);

    $this->actingAs($patientUser)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Dashboard/Patient')
            ->has('stats')
            ->has('upcoming')
            ->has('patient')
        );
});

it('counts only the patient\'s own upcoming appointments in stats', function () {
    $patientUser = User::factory()->create(['role' => User::ROLE_PATIENT]);
    $patient = Patient::factory()->create(['user_id' => $patientUser->id]);
    $otherPatient = Patient::factory()->create();

    Appointment::factory()->count(2)->create([
        'patient_id'   => $patient->id,
        'scheduled_at' => now()->addDays(3),
        'status'       => Appointment::STATUS_SCHEDULED,
    ]);

    // Another patient's appointment — must not count
    Appointment::factory()->create([
        'patient_id'   => $otherPatient->id,
        'scheduled_at' => now()->addDays(3),
        'status'       => Appointment::STATUS_SCHEDULED,
    ]);

    $this->actingAs($patientUser)
        ->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->where('stats.upcoming', 2)
        );
});
