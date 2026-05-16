<?php

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Location;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Notifications\AppointmentBookedNotification;
use Illuminate\Support\Facades\Notification;

// ── Index ──────────────────────────────────────────────────────────────────────

it('admin can see all appointments', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
    Appointment::factory()->count(3)->create();

    $this->actingAs($admin)
        ->get(route('appointments.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Appointments/Index'));
});

it('doctor only sees their own appointments', function () {
    $doctorUser = User::factory()->create(['role' => User::ROLE_DOCTOR]);
    $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

    Appointment::factory()->create(['doctor_id' => $doctor->id]);
    Appointment::factory()->count(2)->create(); // other doctors

    $this->actingAs($doctorUser)
        ->get(route('appointments.index'))
        ->assertInertia(fn ($page) => $page->where('appointments.total', 1));
});

it('patient only sees their own appointments', function () {
    $patientUser = User::factory()->create(['role' => User::ROLE_PATIENT]);
    $patient = Patient::factory()->create(['user_id' => $patientUser->id]);

    Appointment::factory()->create(['patient_id' => $patient->id]);
    Appointment::factory()->count(2)->create(); // other patients

    $this->actingAs($patientUser)
        ->get(route('appointments.index'))
        ->assertInertia(fn ($page) => $page->where('appointments.total', 1));
});

// ── Create / Store ─────────────────────────────────────────────────────────────

it('admin can create an appointment', function () {
    Notification::fake();

    $admin    = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $patient  = Patient::factory()->create();
    $doctor   = Doctor::factory()->create();
    $location = Location::factory()->create();
    $service  = Service::factory()->create(['duration_minutes' => 30]);

    $this->actingAs($admin)->post(route('appointments.store'), [
        'patient_id'       => $patient->id,
        'doctor_id'        => $doctor->id,
        'location_id'      => $location->id,
        'service_id'       => $service->id,
        'scheduled_at'     => now()->addDay()->toDateTimeString(),
        'duration_minutes' => 30,
        'visit_type'       => 'in_person',
    ])->assertRedirect();

    $this->assertDatabaseHas('appointments', [
        'patient_id' => $patient->id,
        'doctor_id'  => $doctor->id,
        'status'     => Appointment::STATUS_SCHEDULED,
    ]);
});

it('validates required fields on appointment store', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    $this->actingAs($admin)
        ->post(route('appointments.store'), [])
        ->assertSessionHasErrors(['patient_id', 'doctor_id', 'scheduled_at', 'duration_minutes', 'visit_type']);
});

// ── Conflict detection ─────────────────────────────────────────────────────────

it('blocks storing an appointment that conflicts with an existing one', function () {
    $admin   = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $patient = Patient::factory()->create();
    $doctor  = Doctor::factory()->create();

    $start = now()->addDay()->setTime(10, 0)->setSeconds(0);

    Appointment::factory()->create([
        'doctor_id'        => $doctor->id,
        'scheduled_at'     => $start,
        'duration_minutes' => 60,
        'status'           => Appointment::STATUS_SCHEDULED,
    ]);

    $this->actingAs($admin)->post(route('appointments.store'), [
        'patient_id'       => $patient->id,
        'doctor_id'        => $doctor->id,
        'scheduled_at'     => $start->toDateTimeString(),
        'duration_minutes' => 30,
        'visit_type'       => 'in_person',
    ])->assertSessionHasErrors('scheduled_at');
});

it('allows an appointment for the same doctor outside the conflict window', function () {
    Notification::fake();

    $admin   = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $patient = Patient::factory()->create();
    $doctor  = Doctor::factory()->create();
    $start   = now()->addDay()->setTime(10, 0)->setSeconds(0);

    Appointment::factory()->create([
        'doctor_id'        => $doctor->id,
        'scheduled_at'     => $start,
        'duration_minutes' => 30,
        'status'           => Appointment::STATUS_SCHEDULED,
    ]);

    $this->actingAs($admin)->post(route('appointments.store'), [
        'patient_id'       => $patient->id,
        'doctor_id'        => $doctor->id,
        'scheduled_at'     => $start->copy()->addHour()->toDateTimeString(),
        'duration_minutes' => 30,
        'visit_type'       => 'in_person',
    ])->assertRedirect();

    expect(Appointment::where('doctor_id', $doctor->id)->count())->toBe(2);
});

it('conflict scope ignores cancelled and no-show appointments', function () {
    Notification::fake();

    $admin   = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $patient = Patient::factory()->create();
    $doctor  = Doctor::factory()->create();
    $start   = now()->addDay()->setTime(14, 0)->setSeconds(0);

    Appointment::factory()->create([
        'doctor_id'        => $doctor->id,
        'scheduled_at'     => $start,
        'duration_minutes' => 60,
        'status'           => Appointment::STATUS_CANCELLED,
    ]);

    $this->actingAs($admin)->post(route('appointments.store'), [
        'patient_id'       => $patient->id,
        'doctor_id'        => $doctor->id,
        'scheduled_at'     => $start->toDateTimeString(),
        'duration_minutes' => 30,
        'visit_type'       => 'telehealth',
    ])->assertRedirect();
});

// ── Status update ──────────────────────────────────────────────────────────────

it('admin can update appointment status', function () {
    $admin       = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $appointment = Appointment::factory()->create(['status' => Appointment::STATUS_SCHEDULED]);

    $this->actingAs($admin)
        ->patch(route('appointments.status', $appointment), ['status' => 'confirmed'])
        ->assertRedirect();

    expect($appointment->fresh()->status)->toBe(Appointment::STATUS_CONFIRMED);
});

it('rejects an invalid status value', function () {
    $admin       = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $appointment = Appointment::factory()->create();

    $this->actingAs($admin)
        ->patch(route('appointments.status', $appointment), ['status' => 'invalid_status'])
        ->assertSessionHasErrors('status');
});

// ── Patient self-booking ───────────────────────────────────────────────────────

it('patient can book an appointment', function () {
    Notification::fake();

    $patientUser = User::factory()->create(['role' => User::ROLE_PATIENT]);
    $patient     = Patient::factory()->create(['user_id' => $patientUser->id]);
    $doctor      = Doctor::factory()->create();
    $location    = Location::factory()->create();
    $service     = Service::factory()->create(['duration_minutes' => 30]);

    $this->actingAs($patientUser)->post(route('appointments.storeBooking'), [
        'doctor_id'        => $doctor->id,
        'location_id'      => $location->id,
        'service_id'       => $service->id,
        'scheduled_at'     => now()->addDays(2)->toDateTimeString(),
        'duration_minutes' => 30,
        'visit_type'       => 'in_person',
        'reason'           => 'Routine checkup',
    ])->assertRedirect();

    $this->assertDatabaseHas('appointments', [
        'patient_id' => $patient->id,
        'doctor_id'  => $doctor->id,
        'status'     => Appointment::STATUS_SCHEDULED,
    ]);
});

it('rejects patient booking with a past scheduled_at', function () {
    $patientUser = User::factory()->create(['role' => User::ROLE_PATIENT]);
    Patient::factory()->create(['user_id' => $patientUser->id]);
    $doctor = Doctor::factory()->create();

    $this->actingAs($patientUser)->post(route('appointments.storeBooking'), [
        'doctor_id'        => $doctor->id,
        'scheduled_at'     => now()->subDay()->toDateTimeString(),
        'duration_minutes' => 30,
        'visit_type'       => 'in_person',
    ])->assertSessionHasErrors('scheduled_at');
});

it('detects conflict on patient self-booking', function () {
    $patientUser = User::factory()->create(['role' => User::ROLE_PATIENT]);
    Patient::factory()->create(['user_id' => $patientUser->id]);
    $doctor = Doctor::factory()->create();
    $start  = now()->addDays(3)->setTime(9, 0)->setSeconds(0);

    Appointment::factory()->create([
        'doctor_id'        => $doctor->id,
        'scheduled_at'     => $start,
        'duration_minutes' => 60,
        'status'           => Appointment::STATUS_SCHEDULED,
    ]);

    $this->actingAs($patientUser)->post(route('appointments.storeBooking'), [
        'doctor_id'        => $doctor->id,
        'scheduled_at'     => $start->toDateTimeString(),
        'duration_minutes' => 30,
        'visit_type'       => 'in_person',
    ])->assertSessionHasErrors('scheduled_at');
});

// ── Notifications on booking ───────────────────────────────────────────────────

it('sends AppointmentBookedNotification to doctor and admin when admin creates an appointment', function () {
    Notification::fake();

    $admin      = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $adminTwo   = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $doctorUser = User::factory()->create(['role' => User::ROLE_DOCTOR]);
    $doctor     = Doctor::factory()->create(['user_id' => $doctorUser->id]);
    $patient    = Patient::factory()->create();

    $this->actingAs($admin)->post(route('appointments.store'), [
        'patient_id'       => $patient->id,
        'doctor_id'        => $doctor->id,
        'scheduled_at'     => now()->addDay()->toDateTimeString(),
        'duration_minutes' => 30,
        'visit_type'       => 'in_person',
    ]);

    Notification::assertSentTo($doctorUser, AppointmentBookedNotification::class);
    Notification::assertSentTo($adminTwo, AppointmentBookedNotification::class);
});

it('sends notification to doctor and admins when patient books', function () {
    Notification::fake();

    $adminUser   = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $patientUser = User::factory()->create(['role' => User::ROLE_PATIENT]);
    $patient     = Patient::factory()->create(['user_id' => $patientUser->id]);
    $doctorUser  = User::factory()->create(['role' => User::ROLE_DOCTOR]);
    $doctor      = Doctor::factory()->create(['user_id' => $doctorUser->id]);

    $this->actingAs($patientUser)->post(route('appointments.storeBooking'), [
        'doctor_id'        => $doctor->id,
        'scheduled_at'     => now()->addDays(2)->toDateTimeString(),
        'duration_minutes' => 30,
        'visit_type'       => 'telehealth',
    ]);

    Notification::assertSentTo($doctorUser, AppointmentBookedNotification::class);
    Notification::assertSentTo($adminUser, AppointmentBookedNotification::class);
});

// ── Destroy ────────────────────────────────────────────────────────────────────

it('admin can delete an appointment', function () {
    $admin       = User::factory()->create(['role' => User::ROLE_ADMIN]);
    $appointment = Appointment::factory()->create();

    $this->actingAs($admin)
        ->delete(route('appointments.destroy', $appointment))
        ->assertRedirect(route('appointments.index'));

    $this->assertDatabaseMissing('appointments', ['id' => $appointment->id]);
});
