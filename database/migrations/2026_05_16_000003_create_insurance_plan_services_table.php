<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_plan_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('insurance_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('adjustment_type', 20)->default('percentage'); // 'percentage' or 'fixed'
            $table->decimal('adjustment_value', 10, 2)->default(0); // % increase or fixed amount in cents
            $table->unsignedInteger('fixed_price')->nullable(); // override price in cents
            $table->timestamps();

            $table->unique(['insurance_plan_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_plan_services');
    }
};
