<?php

use App\Models\Doctor;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\Patient;
use App\Models\User;
use App\Notifications\NewMessageNotification;
use Illuminate\Support\Facades\Notification;

// ── Index ──────────────────────────────────────────────────────────────────────

it('authenticated user can view their message threads', function () {
    $patientUser = User::factory()->create(['role' => User::ROLE_PATIENT]);
    Patient::factory()->create(['user_id' => $patientUser->id]);

    $this->actingAs($patientUser)
        ->get(route('messages.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Messages/Index'));
});

// ── Create thread ──────────────────────────────────────────────────────────────

it('patient can start a new message thread', function () {
    Notification::fake();

    $patientUser = User::factory()->create(['role' => User::ROLE_PATIENT]);
    $patient     = Patient::factory()->create(['user_id' => $patientUser->id]);
    $doctorUser  = User::factory()->create(['role' => User::ROLE_DOCTOR]);
    $doctor      = Doctor::factory()->create(['user_id' => $doctorUser->id]);

    $this->actingAs($patientUser)->post(route('messages.store'), [
        'patient_id' => $patient->id,
        'doctor_id'  => $doctor->id,
        'subject'    => 'Question about my prescription',
        'body'       => 'Can I take ibuprofen with my current medication?',
    ])->assertRedirect();

    $this->assertDatabaseHas('message_threads', [
        'patient_id' => $patient->id,
        'doctor_id'  => $doctor->id,
    ]);

    $this->assertDatabaseHas('messages', [
        'body' => 'Can I take ibuprofen with my current medication?',
    ]);
});

// ── Reply ──────────────────────────────────────────────────────────────────────

it('doctor can reply to a thread', function () {
    Notification::fake();

    $doctorUser = User::factory()->create(['role' => User::ROLE_DOCTOR]);
    $doctor     = Doctor::factory()->create(['user_id' => $doctorUser->id]);
    $patient    = Patient::factory()->create();
    $thread     = MessageThread::factory()->create([
        'doctor_id'  => $doctor->id,
        'patient_id' => $patient->id,
    ]);

    $this->actingAs($doctorUser)
        ->post(route('messages.reply', $thread), ['body' => 'Yes, that is safe.'])
        ->assertRedirect();

    $this->assertDatabaseHas('messages', [
        'thread_id' => $thread->id,
        'body'      => 'Yes, that is safe.',
    ]);
});

it('validates that reply body is required', function () {
    $doctorUser = User::factory()->create(['role' => User::ROLE_DOCTOR]);
    $doctor     = Doctor::factory()->create(['user_id' => $doctorUser->id]);
    $thread     = MessageThread::factory()->create(['doctor_id' => $doctor->id]);

    $this->actingAs($doctorUser)
        ->post(route('messages.reply', $thread), ['body' => ''])
        ->assertSessionHasErrors('body');
});

// ── Notifications ──────────────────────────────────────────────────────────────

it('sends NewMessageNotification to the other party when a message is sent', function () {
    Notification::fake();

    $patientUser = User::factory()->create(['role' => User::ROLE_PATIENT]);
    $patient     = Patient::factory()->create(['user_id' => $patientUser->id]);
    $doctorUser  = User::factory()->create(['role' => User::ROLE_DOCTOR]);
    $doctor      = Doctor::factory()->create(['user_id' => $doctorUser->id]);

    $this->actingAs($patientUser)->post(route('messages.store'), [
        'patient_id' => $patient->id,
        'doctor_id'  => $doctor->id,
        'subject'    => 'Test',
        'body'       => 'Hello doctor.',
    ]);

    Notification::assertSentTo($doctorUser, NewMessageNotification::class);
    Notification::assertNotSentTo($patientUser, NewMessageNotification::class);
});

it('notifies patient when doctor replies', function () {
    Notification::fake();

    $doctorUser  = User::factory()->create(['role' => User::ROLE_DOCTOR]);
    $doctor      = Doctor::factory()->create(['user_id' => $doctorUser->id]);
    $patientUser = User::factory()->create(['role' => User::ROLE_PATIENT]);
    $patient     = Patient::factory()->create(['user_id' => $patientUser->id]);
    $thread      = MessageThread::factory()->create([
        'doctor_id'  => $doctor->id,
        'patient_id' => $patient->id,
    ]);

    $this->actingAs($doctorUser)
        ->post(route('messages.reply', $thread), ['body' => 'Your results look fine.']);

    Notification::assertSentTo($patientUser, NewMessageNotification::class);
    Notification::assertNotSentTo($doctorUser, NewMessageNotification::class);
});
