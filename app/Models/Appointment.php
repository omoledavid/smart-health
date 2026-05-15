<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use HasFactory;

    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_NO_SHOW = 'no_show';
    public const STATUS_RESCHEDULED = 'rescheduled';

    protected $fillable = [
        'patient_id', 'doctor_id', 'location_id', 'service_id', 'rescheduled_from_id',
        'scheduled_at', 'duration_minutes', 'status', 'visit_type', 'reason', 'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function consultation(): HasOne
    {
        return $this->hasOne(Consultation::class);
    }

    public function getEndsAtAttribute()
    {
        return $this->scheduled_at?->copy()->addMinutes((int) $this->duration_minutes);
    }

    public function scopeConflictsWith(Builder $query, int $doctorId, \DateTimeInterface $start, int $durationMinutes, ?int $exceptId = null): Builder
    {
        $end = (clone $start)->modify("+{$durationMinutes} minutes");

        return $query->where('doctor_id', $doctorId)
            ->whereNotIn('status', [self::STATUS_CANCELLED, self::STATUS_NO_SHOW, self::STATUS_RESCHEDULED])
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('scheduled_at', [$start, $end])
                    ->orWhereRaw("datetime(scheduled_at, '+' || duration_minutes || ' minutes') > ? AND scheduled_at < ?", [$start, $end]);
            });
    }
}
