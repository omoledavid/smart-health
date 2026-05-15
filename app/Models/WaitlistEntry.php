<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WaitlistEntry extends Model
{
    protected $fillable = [
        'patient_id', 'doctor_id', 'service_id', 'priority',
        'requested_on', 'available_from', 'notes', 'status', 'appointment_id',
    ];

    protected $casts = [
        'requested_on' => 'date',
        'available_from' => 'date',
    ];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function doctor(): BelongsTo { return $this->belongsTo(Doctor::class); }
    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
    public function appointment(): BelongsTo { return $this->belongsTo(Appointment::class); }
}
