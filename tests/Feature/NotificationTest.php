<?php

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;

// ── Mark single notification as read ──────────────────────────────────────────

it('user can mark a single notification as read', function () {
    $user = User::factory()->create();

    $notification = DatabaseNotification::create([
        'id'              => \Illuminate\Support\Str::uuid(),
        'type'            => 'App\\Notifications\\AppointmentBookedNotification',
        'notifiable_type' => User::class,
        'notifiable_id'   => $user->id,
        'data'            => json_encode(['title' => 'Test']),
        'read_at'         => null,
    ]);

    $this->actingAs($user)
        ->post(route('notifications.markRead'), ['ids' => [$notification->id]]);

    expect($notification->fresh()->read_at)->not->toBeNull();
});

// ── Mark all notifications as read ────────────────────────────────────────────

it('user can mark all notifications as read', function () {
    $user = User::factory()->create();

    foreach (range(1, 3) as $i) {
        DatabaseNotification::create([
            'id'              => \Illuminate\Support\Str::uuid(),
            'type'            => 'App\\Notifications\\AppointmentBookedNotification',
            'notifiable_type' => User::class,
            'notifiable_id'   => $user->id,
            'data'            => json_encode(['title' => "Notification $i"]),
            'read_at'         => null,
        ]);
    }

    $this->actingAs($user)
        ->post(route('notifications.markAllRead'))
        ->assertSuccessful();

    expect($user->unreadNotifications()->count())->toBe(0);
});

it('marking all read does not affect another user\'s notifications', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    DatabaseNotification::create([
        'id'              => \Illuminate\Support\Str::uuid(),
        'type'            => 'App\\Notifications\\AppointmentBookedNotification',
        'notifiable_type' => User::class,
        'notifiable_id'   => $userB->id,
        'data'            => json_encode(['title' => 'For B']),
        'read_at'         => null,
    ]);

    $this->actingAs($userA)->post(route('notifications.markAllRead'));

    expect($userB->unreadNotifications()->count())->toBe(1);
});

// ── Guest cannot access notification routes ────────────────────────────────────

it('guest cannot mark notifications as read', function () {
    $this->post(route('notifications.markAllRead'))
        ->assertRedirect(route('login'));
});
