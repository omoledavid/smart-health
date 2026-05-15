<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Notifications\Notification;

class AppointmentBookedNotification extends Notification
{
    public function __construct(public Appointment $appointment) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $patient = $this->appointment->patient?->full_name ?? 'A patient';
        $date = $this->appointment->scheduled_at->format('M d, Y \a\t g:i A');

        return [
            'type'           => 'appointment_booked',
            'title'          => 'New appointment booked',
            'body'           => "{$patient} booked an appointment on {$date}.",
            'url'            => "/appointments/{$this->appointment->id}",
            'appointment_id' => $this->appointment->id,
        ];
    }
}
