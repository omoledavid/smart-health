<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ExportConsultationController;
use App\Http\Controllers\GenerateSoapController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\CarePlanController;
use App\Http\Controllers\InsuranceClaimController;
use App\Http\Controllers\InsurancePlanController;
use App\Http\Controllers\InsuranceProviderController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\WaitlistController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientOnboardingController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SpecializationController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

// Guest auth
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
});

// Patient onboarding (public via token)
Route::get('onboarding/{token}', [PatientOnboardingController::class, 'show'])->name('onboarding.show');
Route::post('onboarding/{token}', [PatientOnboardingController::class, 'update'])->name('onboarding.update');

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Patients — admin + doctor (role-scoped in controller)
    Route::resource('patients', PatientController::class);

    // Doctors — admin only (enforced loosely by UI/role; expand with policies as needed)
    Route::resource('doctors', DoctorController::class)->except(['edit', 'update']);

    // Appointments — all roles, scoped in controller
    Route::get('appointments/calendar', [AppointmentController::class, 'calendar'])->name('appointments.calendar');
    Route::resource('appointments', AppointmentController::class)->except(['edit', 'update']);
    Route::patch('appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.status');
    Route::post('appointments/{appointment}/start-consultation', [AppointmentController::class, 'startConsultation'])->name('appointments.start-consultation');

    // Admin-only directories
    Route::resource('locations', LocationController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('services', ServiceController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::resource('specializations', SpecializationController::class)->only(['index', 'store', 'update', 'destroy']);

    // Billing
    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::post('invoices/{invoice}/payments', [InvoiceController::class, 'recordPayment'])->name('invoices.payments');

    // Insurance providers & plans
    Route::resource('insurance-providers', InsuranceProviderController::class)->except(['create', 'edit']);
    Route::post('insurance-providers/{insurance_provider}/plans', [InsurancePlanController::class, 'store'])->name('insurance-plans.store');
    Route::patch('insurance-plans/{plan}', [InsurancePlanController::class, 'update'])->name('insurance-plans.update');
    Route::delete('insurance-plans/{plan}', [InsurancePlanController::class, 'destroy'])->name('insurance-plans.destroy');
    Route::get('insurance-plans/{plan}/services', [InsurancePlanController::class, 'services'])->name('insurance-plans.services');
    Route::post('insurance-plans/{plan}/services', [InsurancePlanController::class, 'syncServices'])->name('insurance-plans.sync-services');

    // Insurance claims
    Route::get('claims', [InsuranceClaimController::class, 'index'])->name('claims.index');
    Route::post('claims', [InsuranceClaimController::class, 'store'])->name('claims.store');
    Route::get('claims/{claim}', [InsuranceClaimController::class, 'show'])->name('claims.show');
    Route::patch('claims/{claim}/status', [InsuranceClaimController::class, 'updateStatus'])->name('claims.status');

    // Messaging
    Route::get('messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('messages/{thread}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('messages/{thread}/reply', [MessageController::class, 'reply'])->name('messages.reply');

    // Waitlist
    Route::get('waitlist', [WaitlistController::class, 'index'])->name('waitlist.index');
    Route::post('waitlist', [WaitlistController::class, 'store'])->name('waitlist.store');
    Route::patch('waitlist/{entry}/schedule', [WaitlistController::class, 'schedule'])->name('waitlist.schedule');
    Route::patch('waitlist/{entry}/cancel', [WaitlistController::class, 'cancel'])->name('waitlist.cancel');

    // Care plans
    Route::get('care-plans', [CarePlanController::class, 'index'])->name('care-plans.index');
    Route::post('care-plans', [CarePlanController::class, 'store'])->name('care-plans.store');
    Route::get('care-plans/{plan}', [CarePlanController::class, 'show'])->name('care-plans.show');
    Route::patch('care-plans/{plan}/status', [CarePlanController::class, 'updateStatus'])->name('care-plans.status');
    Route::post('care-plans/{plan}/tasks', [CarePlanController::class, 'addTask'])->name('care-plans.tasks.store');
    Route::patch('tasks/{task}/complete', [CarePlanController::class, 'completeTask'])->name('tasks.complete');

    // Referrals
    Route::get('referrals', [ReferralController::class, 'index'])->name('referrals.index');
    Route::post('referrals', [ReferralController::class, 'store'])->name('referrals.store');
    Route::patch('referrals/{referral}/status', [ReferralController::class, 'updateStatus'])->name('referrals.status');

    // Analytics (admin only)
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

    // Consultations (existing, preserved)
    Route::resource('consultations', ConsultationController::class)->only(['index', 'store', 'show', 'update']);
    Route::post('consultations/{consultation}/generate', GenerateSoapController::class)->name('consultations.generate');
    Route::get('consultations/{consultation}/export', ExportConsultationController::class)->name('consultations.export');
});
