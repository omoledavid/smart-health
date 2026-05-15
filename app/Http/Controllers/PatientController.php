<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PatientController extends Controller
{
    public function index(Request $request): Response
    {
        $q = $request->string('q')->trim();
        $patients = Patient::query()
            ->when($q->isNotEmpty(), function ($query) use ($q) {
                $query->where(function ($s) use ($q) {
                    $s->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('mrn', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Patient::count(),
            'new_this_month' => Patient::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'male' => Patient::where('gender', 'male')->count(),
            'female' => Patient::where('gender', 'female')->count(),
            'active' => Patient::where('onboarding_status', Patient::ONBOARDING_COMPLETED)->count(),
        ];

        return Inertia::render('Patients/Index', [
            'patients' => $patients,
            'filters' => ['q' => (string) $q],
            'stats' => $stats,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Patients/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'string', 'max:40'],
            'dob' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:20'],
            'blood_group' => ['nullable', 'string', 'max:10'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'state' => ['nullable', 'string'],
        ]);
        $data['mrn'] = 'MRN-' . strtoupper(Str::random(8));
        $data['onboarding_status'] = Patient::ONBOARDING_COMPLETED;
        $data['onboarding_step'] = 4;
        $patient = Patient::create($data);

        return redirect()->route('patients.show', $patient);
    }

    public function show(Patient $patient): Response
    {
        $patient->load(['insurances', 'appointments.doctor.user', 'appointments.service', 'consultations']);

        return Inertia::render('Patients/Show', ['patient' => $patient]);
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();

        return redirect()->route('patients.index');
    }
}
