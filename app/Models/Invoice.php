<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;
    protected $fillable = [
        'number', 'patient_id', 'appointment_id',
        'subtotal_cents', 'tax_cents', 'discount_cents', 'total_cents', 'paid_cents',
        'status', 'issued_on', 'due_on', 'notes',
    ];

    protected $casts = [
        'issued_on' => 'date',
        'due_on' => 'date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function claims(): HasMany
    {
        return $this->hasMany(InsuranceClaim::class);
    }

    public function getBalanceCentsAttribute(): int
    {
        return max(0, (int) $this->total_cents - (int) $this->paid_cents);
    }
}
