<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class AnalyticsController extends Controller
{
    public function index(): Response
    {
        $now = now();

        // No-show rate by month (last 6 months)
        $noShowData = Appointment::selectRaw("strftime('%Y-%m', scheduled_at) as month, COUNT(*) as total, SUM(CASE WHEN status = 'no_show' THEN 1 ELSE 0 END) as no_shows")
            ->where('scheduled_at', '>=', $now->copy()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($r) => [
                'month' => $r->month,
                'no_show_rate' => $r->total > 0 ? round($r->no_shows / $r->total * 100, 1) : 0,
                'total' => (int) $r->total,
                'no_shows' => (int) $r->no_shows,
            ]);

        // Doctor utilization: appointments completed vs scheduled (last 30 days)
        $doctorUtilization = Doctor::with('user:id,name')
            ->withCount([
                'appointments as appointments_scheduled' => fn ($q) => $q->whereBetween('scheduled_at', [$now->copy()->subDays(30), $now]),
                'appointments as appointments_completed' => fn ($q) => $q->where('status', 'completed')->whereBetween('scheduled_at', [$now->copy()->subDays(30), $now]),
                'appointments as appointments_no_show' => fn ($q) => $q->where('status', 'no_show')->whereBetween('scheduled_at', [$now->copy()->subDays(30), $now]),
            ])
            ->get()
            ->map(fn ($d) => [
                'doctor' => $d->user?->name,
                'scheduled' => $d->appointments_scheduled,
                'completed' => $d->appointments_completed,
                'no_show' => $d->appointments_no_show,
                'utilization' => $d->appointments_scheduled > 0
                    ? round($d->appointments_completed / $d->appointments_scheduled * 100, 1)
                    : 0,
            ])
            ->sortByDesc('scheduled')
            ->values();

        // Revenue by month (last 6)
        $revenueData = Invoice::selectRaw("strftime('%Y-%m', issued_on) as month, SUM(paid_cents) as revenue, SUM(total_cents) as billed")
            ->whereNotNull('issued_on')
            ->where('issued_on', '>=', $now->copy()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($r) => [
                'month' => $r->month,
                'revenue' => (int) $r->revenue,
                'billed' => (int) $r->billed,
            ]);

        // Appointment status breakdown (all time)
        $statusBreakdown = Appointment::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        // Top services by appointment volume (last 90 days)
        $topServices = Appointment::join('services', 'appointments.service_id', '=', 'services.id')
            ->selectRaw('services.name, COUNT(*) as count')
            ->where('appointments.scheduled_at', '>=', $now->copy()->subDays(90))
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('count')
            ->limit(8)
            ->get();

        // Patient acquisition by month (last 6)
        $patientGrowth = Patient::selectRaw("strftime('%Y-%m', created_at) as month, COUNT(*) as new_patients")
            ->where('created_at', '>=', $now->copy()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return Inertia::render('Analytics/Index', [
            'noShowData' => $noShowData,
            'doctorUtilization' => $doctorUtilization,
            'revenueData' => $revenueData,
            'statusBreakdown' => $statusBreakdown,
            'topServices' => $topServices,
            'patientGrowth' => $patientGrowth,
            'summary' => [
                'total_patients' => Patient::count(),
                'total_doctors' => Doctor::count(),
                'appointments_this_month' => Appointment::whereRaw("strftime('%Y-%m', scheduled_at) = ?", [now()->format('Y-m')])->count(),
                'revenue_this_month' => Invoice::whereRaw("strftime('%Y-%m', issued_on) = ?", [now()->format('Y-m')])->sum('paid_cents'),
                'outstanding_balance' => Invoice::where('status', '!=', 'paid')->sum(DB::raw('total_cents - paid_cents')),
                'no_show_rate_30d' => round(
                    Appointment::where('scheduled_at', '>=', $now->copy()->subDays(30))->count() > 0
                        ? Appointment::where('status', 'no_show')->where('scheduled_at', '>=', $now->copy()->subDays(30))->count()
                            / Appointment::where('scheduled_at', '>=', $now->copy()->subDays(30))->count() * 100
                        : 0,
                    1
                ),
            ],
        ]);
    }
}
