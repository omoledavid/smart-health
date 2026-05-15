<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CarePlan extends Model
{
    protected $fillable = [
        'patient_id', 'doctor_id', 'title', 'goals',
        'interventions', 'start_date', 'review_date', 'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'review_date' => 'date',
    ];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function doctor(): BelongsTo { return $this->belongsTo(Doctor::class); }
    public function tasks(): HasMany { return $this->hasMany(Task::class); }
}
