<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    public const ONBOARDING_PENDING = 'pending';
    public const ONBOARDING_IN_PROGRESS = 'in_progress';
    public const ONBOARDING_COMPLETED = 'completed';

    protected $fillable = [
        'user_id', 'mrn', 'first_name', 'last_name', 'email', 'phone',
        'dob', 'gender', 'blood_group', 'address', 'city', 'state', 'postal_code',
        'emergency_contact_name', 'emergency_contact_phone',
        'allergies', 'medical_history',
        'onboarding_status', 'onboarding_step', 'invite_token',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    protected $appends = ['full_name'];

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function insurances(): HasMany
    {
        return $this->hasMany(PatientInsurance::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function messageThreads(): HasMany
    {
        return $this->hasMany(MessageThread::class);
    }
}
