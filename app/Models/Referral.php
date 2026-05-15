<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    protected $fillable = [
        'patient_id', 'referring_doctor_id', 'referred_to', 'specialty',
        'reason', 'urgency', 'status', 'referred_on', 'appointment_date', 'notes',
    ];

    protected $casts = [
        'referred_on' => 'date',
        'appointment_date' => 'date',
    ];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function referringDoctor(): BelongsTo { return $this->belongsTo(Doctor::class, 'referring_doctor_id'); }
}
