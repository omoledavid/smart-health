<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Location;
use App\Models\Service;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class DoctorController extends Controller
{
    public function index(Request $request): Response
    {
        $q = $request->string('q')->trim();
        $doctors = Doctor::with(['user', 'specialization', 'locations'])
            ->when($q->isNotEmpty(), fn ($query) => $query->whereHas('user', fn ($u) => $u->where('name', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Doctor::count(),
            'specializations' => Specialization::count(),
            'avg_experience' => round(Doctor::avg('years_experience') ?? 0),
            'locations' => Location::count(),
        ];

        return Inertia::render('Doctors/Index', [
            'doctors' => $doctors,
            'filters' => ['q' => (string) $q],
            'stats' => $stats,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Doctors/Create', [
            'specializations' => Specialization::orderBy('name')->get(['id', 'name']),
            'locations' => Location::orderBy('name')->get(['id', 'name']),
            'services' => Service::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:40'],
            'password' => ['required', 'string', 'min:8'],
            'specialization_id' => ['nullable', 'exists:specializations,id'],
            'license_number' => ['nullable', 'string'],
            'bio' => ['nullable', 'string'],
            'years_experience' => ['nullable', 'integer', 'min:0', 'max:80'],
            'consultation_fee_cents' => ['nullable', 'integer', 'min:0'],
            'location_ids' => ['array'],
            'location_ids.*' => ['exists:locations,id'],
            'service_ids' => ['array'],
            'service_ids.*' => ['exists:services,id'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_DOCTOR,
        ]);

        $doctor = Doctor::create([
            'user_id' => $user->id,
            'specialization_id' => $data['specialization_id'] ?? null,
            'license_number' => $data['license_number'] ?? null,
            'bio' => $data['bio'] ?? null,
            'years_experience' => $data['years_experience'] ?? 0,
            'consultation_fee_cents' => $data['consultation_fee_cents'] ?? 0,
        ]);

        $doctor->locations()->sync($data['location_ids'] ?? []);
        $doctor->services()->sync($data['service_ids'] ?? []);

        return redirect()->route('doctors.show', $doctor);
    }

    public function show(Doctor $doctor): Response
    {
        $doctor->load(['user', 'specialization', 'locations', 'services', 'availabilities']);

        return Inertia::render('Doctors/Show', ['doctor' => $doctor]);
    }

    public function destroy(Doctor $doctor)
    {
        $userId = $doctor->user_id;
        $doctor->delete();
        User::where('id', $userId)->delete();

        return redirect()->route('doctors.index');
    }
}
