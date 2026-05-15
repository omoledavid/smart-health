<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InsuranceProvider extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'address', 'status',
    ];

    public function plans(): HasMany
    {
        return $this->hasMany(InsurancePlan::class);
    }

    public function patientInsurances(): HasMany
    {
        return $this->hasMany(PatientInsurance::class);
    }
}
