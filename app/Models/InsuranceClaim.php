<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsuranceClaim extends Model
{
    protected $fillable = [
        'invoice_id', 'patient_insurance_id', 'claim_number',
        'claimed_cents', 'approved_cents', 'status',
        'submitted_on', 'decided_on', 'notes',
    ];

    protected $casts = [
        'submitted_on' => 'date',
        'decided_on' => 'date',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function insurance(): BelongsTo
    {
        return $this->belongsTo(PatientInsurance::class, 'patient_insurance_id');
    }
}
