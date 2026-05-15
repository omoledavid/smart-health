<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientInsurance extends Model
{
    protected $fillable = [
        'patient_id', 'provider_name', 'policy_number', 'group_number',
        'holder_name', 'holder_relationship', 'effective_from', 'effective_to', 'is_primary',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_primary' => 'boolean',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }
}
