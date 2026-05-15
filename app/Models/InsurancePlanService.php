<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsurancePlanService extends Model
{
    protected $table = 'insurance_plan_services';

    protected $fillable = [
        'insurance_plan_id', 'service_id', 'adjustment_type', 'adjustment_value', 'fixed_price',
    ];

    protected $casts = [
        'adjustment_value' => 'decimal:2',
        'fixed_price' => 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(InsurancePlan::class, 'insurance_plan_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Calculate the final price given a base service price.
     */
    public function calculatePrice(int $basePriceCents): int
    {
        if ($this->fixed_price !== null) {
            return $this->fixed_price;
        }

        if ($this->adjustment_type === 'percentage') {
            return (int) round($basePriceCents * (1 + $this->adjustment_value / 100));
        }

        return $basePriceCents + (int) round($this->adjustment_value);
    }
}
