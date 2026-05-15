<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Referral;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReferralController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = Referral::with(['patient', 'referringDoctor.user'])->latest('referred_on');

        if ($user->isPatient()) {
            $query->where('patient_id', optional($user->patient)->id);
        } elseif ($user->isDoctor()) {
            $query->where('referring_doctor_id', optional($user->doctor)->id);
        }

        return Inertia::render('Referrals/Index', [
            'referrals' => $query->paginate(20)->through(fn ($r) => [
                'id' => $r->id,
                'patient' => $r->patient?->full_name,
                'patient_id' => $r->patient_id,
                'referring_doctor' => $r->referringDoctor?->user?->name,
                'referred_to' => $r->referred_to,
                'specialty' => $r->specialty,
                'reason' => $r->reason,
                'urgency' => $r->urgency,
                'status' => $r->status,
                'referred_on' => $r->referred_on?->toDateString(),
                'appointment_date' => $r->appointment_date?->toDateString(),
            ]),
            'patients' => Patient::orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email']),
            'doctors' => Doctor::with('user:id,name')->get()->map(fn ($d) => ['id' => $d->id, 'name' => $d->user?->name]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'referring_doctor_id' => ['nullable', 'exists:doctors,id'],
            'referred_to' => ['required', 'string', 'max:255'],
            'specialty' => ['nullable', 'string', 'max:100'],
            'reason' => ['required', 'string'],
            'urgency' => ['required', 'in:routine,urgent,emergency'],
            'referred_on' => ['required', 'date'],
            'appointment_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        Referral::create($data + ['status' => 'pending']);

        return back()->with('success', 'Referral created.');
    }

    public function updateStatus(Request $request, Referral $referral)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,accepted,completed,declined'],
            'appointment_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $referral->update(array_filter($data, fn ($v) => $v !== null));

        return back()->with('success', 'Referral updated.');
    }
}
