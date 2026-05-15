<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('specialization_id')->nullable()->constrained()->nullOnDelete();
            $table->string('license_number')->nullable();
            $table->text('bio')->nullable();
            $table->unsignedTinyInteger('years_experience')->default(0);
            $table->unsignedInteger('consultation_fee_cents')->default(0);
            $table->timestamps();
        });

        Schema::create('doctor_location', function (Blueprint $table) {
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->primary(['doctor_id', 'location_id']);
        });

        Schema::create('doctor_service', function (Blueprint $table) {
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->primary(['doctor_id', 'service_id']);
        });

        Schema::create('doctor_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_availabilities');
        Schema::dropIfExists('doctor_service');
        Schema::dropIfExists('doctor_location');
        Schema::dropIfExists('doctors');
    }
};
