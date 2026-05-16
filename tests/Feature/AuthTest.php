<?php

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;

// ── Login ─────────────────────────────────────────────────────────────────────

it('renders the login page for guests', function () {
    $this->get(route('login'))->assertOk();
});

it('authenticates a user with valid credentials', function () {
    $user = User::factory()->create(['password' => bcrypt('secret')]);

    $this->post(route('login'), ['email' => $user->email, 'password' => 'secret'])
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

it('rejects invalid credentials', function () {
    User::factory()->create(['email' => 'doc@test.com', 'password' => bcrypt('correct')]);

    $this->post(route('login'), ['email' => 'doc@test.com', 'password' => 'wrong'])
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

it('redirects guests away from protected routes', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->get(route('appointments.index'))->assertRedirect(route('login'));
    $this->get(route('patients.index'))->assertRedirect(route('login'));
});

// ── Role helpers ───────────────────────────────────────────────────────────────

it('correctly identifies admin role', function () {
    $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

    expect($admin->isAdmin())->toBeTrue()
        ->and($admin->isDoctor())->toBeFalse()
        ->and($admin->isPatient())->toBeFalse();
});

it('correctly identifies doctor role', function () {
    $doctor = User::factory()->create(['role' => User::ROLE_DOCTOR]);

    expect($doctor->isDoctor())->toBeTrue()
        ->and($doctor->isAdmin())->toBeFalse()
        ->and($doctor->isPatient())->toBeFalse();
});

it('correctly identifies patient role', function () {
    $patient = User::factory()->create(['role' => User::ROLE_PATIENT]);

    expect($patient->isPatient())->toBeTrue()
        ->and($patient->isAdmin())->toBeFalse()
        ->and($patient->isDoctor())->toBeFalse();
});

// ── Logout ─────────────────────────────────────────────────────────────────────

it('logs out the authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('logout'))
        ->assertRedirect();

    $this->assertGuest();
});
