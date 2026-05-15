<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $existing = collect(DB::select('PRAGMA table_info(patient_insurances)'))->pluck('name');

        if (!$existing->contains('insurance_provider_id')) {
            DB::statement('ALTER TABLE patient_insurances ADD COLUMN insurance_provider_id INTEGER NULL REFERENCES insurance_providers(id) ON DELETE SET NULL');
        }
        if (!$existing->contains('insurance_plan_id')) {
            DB::statement('ALTER TABLE patient_insurances ADD COLUMN insurance_plan_id INTEGER NULL REFERENCES insurance_plans(id) ON DELETE SET NULL');
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('patient_insurances', 'insurance_provider_id')) {
            return;
        }

        try {
            Schema::table('patient_insurances', function ($table) {
                $table->dropColumn(['insurance_provider_id', 'insurance_plan_id']);
            });
        } catch (\Exception) {
            // Older SQLite — skip gracefully.
        }
    }
};
