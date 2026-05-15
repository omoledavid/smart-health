<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Consultation;
use App\Models\Doctor;
use App\Models\Location;
use App\Models\Patient;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AppointmentController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = Appointment::with(['patient', 'doctor.user', 'service', 'location'])
            ->orderByDesc('scheduled_at');

        if ($user->isDoctor()) {
            $query->where('doctor_id', optional($user->doctor)->id);
        } elseif ($user->isPatient()) {
            $query->where('patient_id', optional($user->patient)->id);
        }

        $status = $request->string('status')->trim();
        if ($status->isNotEmpty()) {
            $query->where('status', (string) $status);
        }

        return Inertia::render('Appointments/Index', [
            'appointments' => $query->paginate(20)->withQueryString(),
            'filters' => ['status' => (string) $status],
        ]);
    }

    public function calendar(Request $request): Response
    {
        $user = $request->user();
        $month = $request->string('month')->toString() ?: now()->format('Y-m');
        [$y, $m] = explode('-', $month);
        $start = now()->setDate((int) $y, (int) $m, 1)->startOfMonth()->startOfWeek();
        $end = now()->setDate((int) $y, (int) $m, 1)->endOfMonth()->endOfWeek();

        $query = Appointment::with(['patient', 'doctor.user', 'service'])
            ->whereBetween('scheduled_at', [$start, $end]);

        if ($user->isDoctor()) {
            $query->where('doctor_id', optional($user->doctor)->id);
        } elseif ($user->isPatient()) {
            $query->where('patient_id', optional($user->patient)->id);
        }

        return Inertia::render('Appointments/Calendar', [
            'month' => $month,
            'appointments' => $query->get()->map(fn ($a) => [
                'id' => $a->id,
                'title' => ($a->patient?->full_name ?? 'Patient') . ' · ' . ($a->service?->name ?? 'Visit'),
                'doctor' => $a->doctor?->user?->name,
                'start' => $a->scheduled_at->toIso8601String(),
                'end' => $a->ends_at?->toIso8601String(),
                'status' => $a->status,
            ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Appointments/Create', [
            'patients' => Patient::orderBy('last_name')->get(['id', 'first_name', 'last_name', 'mrn', 'email']),
            'doctors' => Doctor::with('user:id,name')->get()->map(fn ($d) => ['id' => $d->id, 'name' => $d->user?->name]),
            'locations' => Location::orderBy('name')->get(['id', 'name']),
            'services' => Service::orderBy('name')->get(['id', 'name', 'duration_minutes']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['required', 'exists:doctors,id'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'service_id' => ['nullable', 'exists:services,id'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'visit_type' => ['required', 'in:in_person,telehealth'],
            'reason' => ['nullable', 'string'],
        ]);

        $start = new \DateTimeImmutable($data['scheduled_at']);
        if (Appointment::conflictsWith((int) $data['doctor_id'], $start, (int) $data['duration_minutes'])->exists()) {
            throw ValidationException::withMessages([
                'scheduled_at' => 'This time conflicts with another appointment for this doctor.',
            ]);
        }

        $appointment = Appointment::create($data + ['status' => Appointment::STATUS_SCHEDULED]);

        return redirect()->route('appointments.show', $appointment);
    }

    public function show(Appointment $appointment): Response
    {
        $appointment->load(['patient', 'doctor.user', 'service', 'location', 'consultation']);

        return Inertia::render('Appointments/Show', ['appointment' => $appointment]);
    }

    public function startConsultation(Appointment $appointment)
    {
        $appointment->loadMissing(['patient', 'consultation']);

        // Mark as in-progress
        $appointment->update(['status' => Appointment::STATUS_IN_PROGRESS]);

        // Reuse existing consultation or create a fresh one
        $consultation = $appointment->consultation ?? Consultation::create([
            'appointment_id' => $appointment->id,
            'patient_id'     => $appointment->patient_id,
            'patient_name'   => $appointment->patient?->full_name ?? 'Unknown',
            'raw_notes'      => '',
            'status'         => Consultation::STATUS_DRAFT,
        ]);

        return redirect()->route('consultations.show', $consultation);
    }

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $data = $request->validate([
            'status' => ['required', 'in:scheduled,confirmed,in_progress,completed,cancelled,no_show'],
        ]);
        $appointment->update(['status' => $data['status']]);

        return back();
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()->route('appointments.index');
    }
}
