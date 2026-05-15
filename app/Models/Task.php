<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'care_plan_id', 'patient_id', 'assigned_to', 'created_by',
        'title', 'description', 'priority', 'status', 'due_date', 'completed_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function carePlan(): BelongsTo { return $this->belongsTo(CarePlan::class); }
    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function assignedTo(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function createdBy(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
