<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_insurances', function (Blueprint $table) {
            $table->foreignId('insurance_provider_id')->nullable()->after('patient_id')->constrained()->nullOnDelete();
            $table->foreignId('insurance_plan_id')->nullable()->after('insurance_provider_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('patient_insurances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('insurance_plan_id');
            $table->dropConstrainedForeignId('insurance_provider_id');
        });
    }
};
