<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Use PRAGMA table_info (supported on all SQLite versions) to check
        // existing columns, then add via raw ALTER TABLE to avoid triggering
        // pragma_table_xinfo which requires SQLite >= 3.26.0.
        $existing = collect(DB::select('PRAGMA table_info(consultations)'))->pluck('name');

        if (!$existing->contains('appointment_id')) {
            DB::statement('ALTER TABLE consultations ADD COLUMN appointment_id INTEGER NULL REFERENCES appointments(id) ON DELETE SET NULL');
        }
        if (!$existing->contains('patient_id')) {
            DB::statement('ALTER TABLE consultations ADD COLUMN patient_id INTEGER NULL REFERENCES patients(id) ON DELETE SET NULL');
        }
        if (!$existing->contains('doctor_id')) {
            DB::statement('ALTER TABLE consultations ADD COLUMN doctor_id INTEGER NULL REFERENCES doctors(id) ON DELETE SET NULL');
        }
    }

    public function down(): void
    {
        // DROP COLUMN requires SQLite >= 3.35.0; skip gracefully on older versions.
        if (!Schema::hasColumn('consultations', 'appointment_id')) {
            return;
        }

        try {
            Schema::table('consultations', function ($table) {
                $table->dropColumn(['appointment_id', 'patient_id', 'doctor_id']);
            });
        } catch (\Exception) {
            // Older SQLite — columns remain but migration is rolled back in the
            // migrations table, so a fresh migrate will re-add them correctly.
        }
    }
};
