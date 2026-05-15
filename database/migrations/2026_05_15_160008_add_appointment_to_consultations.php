<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->foreignId('appointment_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->nullable()->after('appointment_id')->constrained()->nullOnDelete();
            $table->foreignId('doctor_id')->nullable()->after('patient_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
            $table->dropForeign(['patient_id']);
            $table->dropForeign(['doctor_id']);
            $table->dropColumn(['appointment_id', 'patient_id', 'doctor_id']);
        });
    }
};
