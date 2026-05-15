<?php

namespace App\Http\Controllers;

use App\Models\InsuranceProvider;
use App\Models\Patient;
use App\Models\PatientInsurance;
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
        return Inertia::render('Patients/Create', [
            'insuranceProviders' => InsuranceProvider::where('status', 'active')
                ->with(['plans' => fn ($q) => $q->where('status', 'active')])
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
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
            'insurance_provider_id' => ['nullable', 'exists:insurance_providers,id'],
            'insurance_plan_id' => ['nullable', 'exists:insurance_plans,id'],
            'policy_number' => ['nullable', 'string', 'max:100'],
            'group_number' => ['nullable', 'string', 'max:100'],
        ]);

        $insuranceData = collect($data)->only(['insurance_provider_id', 'insurance_plan_id', 'policy_number', 'group_number'])->filter()->all();
        $patientData = collect($data)->except(['insurance_provider_id', 'insurance_plan_id', 'policy_number', 'group_number'])->all();

        $patientData['mrn'] = 'MRN-' . strtoupper(Str::random(8));
        $patientData['onboarding_status'] = Patient::ONBOARDING_COMPLETED;
        $patientData['onboarding_step'] = 4;
        $patient = Patient::create($patientData);

        if (! empty($insuranceData['insurance_provider_id'])) {
            $provider = InsuranceProvider::find($insuranceData['insurance_provider_id']);
            PatientInsurance::create([
                'patient_id' => $patient->id,
                'insurance_provider_id' => $insuranceData['insurance_provider_id'],
                'insurance_plan_id' => $insuranceData['insurance_plan_id'] ?? null,
                'provider_name' => $provider->name,
                'policy_number' => $insuranceData['policy_number'] ?? '',
                'group_number' => $insuranceData['group_number'] ?? null,
                'is_primary' => true,
            ]);
        }

        return redirect()->route('patients.show', $patient);
    }

    public function edit(Patient $patient): Response
    {
        $patient->load('insurances');

        return Inertia::render('Patients/Edit', [
            'patient' => $patient,
            'insuranceProviders' => InsuranceProvider::where('status', 'active')
                ->with(['plans' => fn ($q) => $q->where('status', 'active')])
                ->orderBy('name')
                ->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Patient $patient)
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
            'insurance_provider_id' => ['nullable', 'exists:insurance_providers,id'],
            'insurance_plan_id' => ['nullable', 'exists:insurance_plans,id'],
            'policy_number' => ['nullable', 'string', 'max:100'],
            'group_number' => ['nullable', 'string', 'max:100'],
        ]);

        $insuranceData = collect($data)->only(['insurance_provider_id', 'insurance_plan_id', 'policy_number', 'group_number'])->all();
        $patientData = collect($data)->except(['insurance_provider_id', 'insurance_plan_id', 'policy_number', 'group_number'])->all();

        $patient->update($patientData);

        // Update or create primary insurance
        if (! empty($insuranceData['insurance_provider_id'])) {
            $provider = InsuranceProvider::find($insuranceData['insurance_provider_id']);
            $patient->insurances()->updateOrCreate(
                ['is_primary' => true],
                [
                    'insurance_provider_id' => $insuranceData['insurance_provider_id'],
                    'insurance_plan_id' => $insuranceData['insurance_plan_id'] ?? null,
                    'provider_name' => $provider->name,
                    'policy_number' => $insuranceData['policy_number'] ?? '',
                    'group_number' => $insuranceData['group_number'] ?? null,
                ]
            );
        } else {
            // Remove primary insurance if provider cleared
            $patient->insurances()->where('is_primary', true)->delete();
        }

        return redirect()->route('patients.show', $patient)->with('success', 'Patient updated.');
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
