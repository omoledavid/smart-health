<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('mrn', 30)->unique();
            $table->string('first_name', 80);
            $table->string('last_name', 80);
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();
            $table->date('dob')->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('blood_group', 10)->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state', 60)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 40)->nullable();
            $table->text('allergies')->nullable();
            $table->text('medical_history')->nullable();
            $table->string('onboarding_status', 20)->default('pending');
            $table->unsignedTinyInteger('onboarding_step')->default(0);
            $table->string('invite_token', 64)->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
