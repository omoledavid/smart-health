<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientInsurance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PatientOnboardingController extends Controller
{
    public function show(string $token): Response
    {
        $patient = Patient::where('invite_token', $token)->firstOrFail();

        return Inertia::render('Patients/Onboarding/Wizard', [
            'patient' => $patient,
            'token' => $token,
        ]);
    }

    public function update(Request $request, string $token)
    {
        $patient = Patient::where('invite_token', $token)->firstOrFail();
        $step = (int) $request->input('step', 0);

        $rules = match ($step) {
            0 => [
                'first_name' => ['required', 'string', 'max:80'],
                'last_name' => ['required', 'string', 'max:80'],
                'dob' => ['nullable', 'date'],
                'gender' => ['nullable', 'string'],
            ],
            1 => [
                'email' => ['nullable', 'email'],
                'phone' => ['nullable', 'string', 'max:40'],
                'address' => ['nullable', 'string'],
                'city' => ['nullable', 'string'],
                'state' => ['nullable', 'string'],
                'postal_code' => ['nullable', 'string'],
                'emergency_contact_name' => ['nullable', 'string'],
                'emergency_contact_phone' => ['nullable', 'string'],
            ],
            2 => [
                'insurance_provider' => ['nullable', 'string'],
                'insurance_policy' => ['nullable', 'string'],
                'allergies' => ['nullable', 'string'],
                'medical_history' => ['nullable', 'string'],
            ],
            3 => [
                'consent' => ['accepted'],
            ],
            default => [],
        };

        $data = $request->validate($rules);

        if ($step === 2) {
            if (! empty($data['insurance_provider']) && ! empty($data['insurance_policy'])) {
                PatientInsurance::create([
                    'patient_id' => $patient->id,
                    'provider_name' => $data['insurance_provider'],
                    'policy_number' => $data['insurance_policy'],
                    'holder_name' => $patient->full_name,
                    'is_primary' => true,
                ]);
            }
            $patient->fill([
                'allergies' => $data['allergies'] ?? $patient->allergies,
                'medical_history' => $data['medical_history'] ?? $patient->medical_history,
            ]);
        } else {
            $patient->fill(collect($data)->except(['consent', 'insurance_provider', 'insurance_policy'])->all());
        }

        $patient->onboarding_step = max($patient->onboarding_step, $step + 1);
        $patient->onboarding_status = $step >= 3 ? Patient::ONBOARDING_COMPLETED : Patient::ONBOARDING_IN_PROGRESS;
        if ($step >= 3) {
            $patient->invite_token = null;
        }
        $patient->save();

        return back();
    }
}
