<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use App\Models\WaitlistEntry;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WaitlistController extends Controller
{
    public function index(): Response
    {
        $entries = WaitlistEntry::with(['patient', 'doctor.user', 'service'])
            ->where('status', 'waiting')
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 0 WHEN 'high' THEN 1 WHEN 'normal' THEN 2 ELSE 3 END")
            ->orderBy('requested_on')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'patient' => $e->patient?->full_name,
                'patient_id' => $e->patient_id,
                'doctor' => $e->doctor?->user?->name,
                'service' => $e->service?->name,
                'priority' => $e->priority,
                'requested_on' => $e->requested_on?->toDateString(),
                'available_from' => $e->available_from?->toDateString(),
                'notes' => $e->notes,
                'status' => $e->status,
            ]);

        return Inertia::render('Waitlist/Index', [
            'entries' => $entries,
            'patients' => Patient::orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email']),
            'doctors' => Doctor::with('user:id,name')->get()->map(fn ($d) => ['id' => $d->id, 'name' => $d->user?->name]),
            'services' => Service::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['nullable', 'exists:doctors,id'],
            'service_id' => ['nullable', 'exists:services,id'],
            'priority' => ['required', 'in:urgent,high,normal,low'],
            'requested_on' => ['required', 'date'],
            'available_from' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        WaitlistEntry::create($data + ['status' => 'waiting']);

        return back()->with('success', 'Added to waitlist.');
    }

    public function schedule(Request $request, WaitlistEntry $entry)
    {
        $data = $request->validate([
            'appointment_id' => ['required', 'exists:appointments,id'],
        ]);

        $entry->update(['status' => 'scheduled', 'appointment_id' => $data['appointment_id']]);

        return back()->with('success', 'Waitlist entry scheduled.');
    }

    public function cancel(WaitlistEntry $entry)
    {
        $entry->update(['status' => 'cancelled']);

        return back()->with('success', 'Waitlist entry cancelled.');
    }
}
