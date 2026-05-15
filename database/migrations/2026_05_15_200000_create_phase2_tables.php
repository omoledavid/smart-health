<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Waitlist
        Schema::create('waitlist_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('priority', 20)->default('normal'); // urgent, high, normal, low
            $table->date('requested_on');
            $table->date('available_from')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('waiting'); // waiting, scheduled, cancelled
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        // Care plans
        Schema::create('care_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('goals')->nullable();
            $table->text('interventions')->nullable();
            $table->date('start_date')->nullable();
            $table->date('review_date')->nullable();
            $table->string('status', 20)->default('active'); // active, completed, on_hold
            $table->timestamps();
        });

        // Tasks
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('care_plan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('patient_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('priority', 20)->default('normal'); // urgent, high, normal, low
            $table->string('status', 20)->default('open'); // open, in_progress, completed, cancelled
            $table->date('due_date')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
        });

        // Referrals
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('referring_doctor_id')->nullable()->constrained('doctors')->nullOnDelete();
            $table->string('referred_to'); // specialist name/org
            $table->string('specialty')->nullable();
            $table->string('reason');
            $table->string('urgency', 20)->default('routine'); // routine, urgent, emergency
            $table->string('status', 20)->default('pending'); // pending, accepted, completed, declined
            $table->date('referred_on');
            $table->date('appointment_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('care_plans');
        Schema::dropIfExists('waitlist_entries');
    }
};
