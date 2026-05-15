<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsurancePlan extends Model
{
    protected $fillable = [
        'insurance_provider_id', 'name', 'description', 'status',
    ];

    public function provider(): BelongsTo
    {
        return $this->belongsTo(InsuranceProvider::class, 'insurance_provider_id');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'insurance_plan_services')
            ->withPivot(['adjustment_type', 'adjustment_value', 'fixed_price'])
            ->withTimestamps();
    }

    public function planServices(): HasMany
    {
        return $this->hasMany(InsurancePlanService::class);
    }

    public function patientInsurances(): HasMany
    {
        return $this->hasMany(PatientInsurance::class);
    }

    /**
     * Calculate the insured price for a given service.
     */
    public function priceForService(int $serviceId, int $basePriceCents): int
    {
        $pivot = $this->planServices()->where('service_id', $serviceId)->first();

        if (! $pivot) {
            return $basePriceCents;
        }

        if ($pivot->fixed_price !== null) {
            return (int) $pivot->fixed_price;
        }

        if ($pivot->adjustment_type === 'percentage') {
            return (int) round($basePriceCents * (1 + $pivot->adjustment_value / 100));
        }

        // fixed addition
        return $basePriceCents + (int) round($pivot->adjustment_value);
    }
}
