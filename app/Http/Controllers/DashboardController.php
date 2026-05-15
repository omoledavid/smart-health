<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return $this->admin();
        }
        if ($user->isDoctor()) {
            return $this->doctor($user);
        }

        return $this->patient($user);
    }

    private function admin(): Response
    {
        $startOfWeek = now()->subDays(6)->startOfDay();
        $stats = [
            'doctors' => Doctor::count(),
            'patients' => Patient::count(),
            'appointments' => Appointment::count(),
            'revenue_cents' => Invoice::where('status', 'paid')->sum('total_cents'),
        ];

        $monthly = collect(range(0, 11))->map(function ($m) {
            $start = now()->subMonths(11 - $m)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $base = Appointment::whereBetween('scheduled_at', [$start, $end]);

            return [
                'label' => $start->format('M'),
                'completed' => (clone $base)->where('status', Appointment::STATUS_COMPLETED)->count(),
                'ongoing' => (clone $base)->whereIn('status', [
                    Appointment::STATUS_SCHEDULED, Appointment::STATUS_CONFIRMED, Appointment::STATUS_IN_PROGRESS,
                ])->count(),
                'rescheduled' => (clone $base)->where('status', Appointment::STATUS_RESCHEDULED)->count(),
            ];
        });

        $apptCounts = [
            'all' => Appointment::count(),
            'cancelled' => Appointment::where('status', Appointment::STATUS_CANCELLED)->count(),
            'rescheduled' => Appointment::where('status', Appointment::STATUS_RESCHEDULED)->count(),
            'completed' => Appointment::where('status', Appointment::STATUS_COMPLETED)->count(),
        ];

        $upcoming = Appointment::with(['patient', 'doctor.user', 'service'])
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->limit(8)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'patient' => $a->patient?->full_name,
                'doctor' => $a->doctor?->user?->name,
                'service' => $a->service?->name,
                'scheduled_at' => $a->scheduled_at->toIso8601String(),
                'status' => $a->status,
            ]);

        return Inertia::render('Dashboard/Admin', compact('stats', 'monthly', 'apptCounts', 'upcoming'));
    }

    private function doctor($user): Response
    {
        $doctor = $user->doctor;
        $today = Carbon::today();
        $schedule = Appointment::with(['patient', 'service'])
            ->where('doctor_id', $doctor?->id)
            ->whereDate('scheduled_at', $today)
            ->orderBy('scheduled_at')
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'patient' => $a->patient?->full_name,
                'service' => $a->service?->name,
                'scheduled_at' => $a->scheduled_at->toIso8601String(),
                'status' => $a->status,
            ]);

        $stats = [
            'today_appointments' => $schedule->count(),
            'total_patients' => $doctor ? Appointment::where('doctor_id', $doctor->id)->distinct('patient_id')->count('patient_id') : 0,
            'pending_consultations' => $doctor ? \App\Models\Consultation::where('doctor_id', $doctor->id)->where('status', 'draft')->count() : 0,
            'upcoming' => $doctor ? Appointment::where('doctor_id', $doctor->id)->where('scheduled_at', '>=', now())->count() : 0,
        ];

        return Inertia::render('Dashboard/Doctor', compact('stats', 'schedule'));
    }

    private function patient($user): Response
    {
        $patient = $user->patient;
        $upcoming = $patient ? Appointment::with(['doctor.user', 'service'])
            ->where('patient_id', $patient->id)
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'doctor' => $a->doctor?->user?->name,
                'service' => $a->service?->name,
                'scheduled_at' => $a->scheduled_at->toIso8601String(),
                'status' => $a->status,
            ]) : collect();

        $stats = [
            'upcoming' => $upcoming->count(),
            'past' => $patient ? Appointment::where('patient_id', $patient->id)->where('scheduled_at', '<', now())->count() : 0,
            'open_invoices' => $patient ? Invoice::where('patient_id', $patient->id)->whereColumn('paid_cents', '<', 'total_cents')->count() : 0,
            'onboarding_status' => $patient?->onboarding_status,
        ];

        return Inertia::render('Dashboard/Patient', [
            'stats' => $stats,
            'upcoming' => $upcoming,
            'patient' => $patient ? [
                'id' => $patient->id,
                'full_name' => $patient->full_name,
                'mrn' => $patient->mrn,
                'onboarding_step' => $patient->onboarding_step,
            ] : null,
        ]);
    }
}
