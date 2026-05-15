<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => User::ROLE_PATIENT,
        ]);

        $parts = explode(' ', $data['name'], 2);
        Patient::create([
            'user_id' => $user->id,
            'mrn' => 'MRN-' . strtoupper(Str::random(8)),
            'first_name' => $parts[0] ?? $data['name'],
            'last_name' => $parts[1] ?? '',
            'email' => $data['email'],
            'onboarding_status' => Patient::ONBOARDING_IN_PROGRESS,
            'onboarding_step' => 0,
        ]);

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
