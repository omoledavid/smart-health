<?php

namespace App\Http\Controllers;

use App\Models\InsuranceProvider;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InsuranceProviderController extends Controller
{
    public function index(): Response
    {
        $providers = InsuranceProvider::withCount('plans')
            ->latest()
            ->paginate(20);

        return Inertia::render('InsuranceProviders/Index', [
            'providers' => $providers,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        InsuranceProvider::create($data);

        return back()->with('success', 'Insurance provider created.');
    }

    public function show(InsuranceProvider $insuranceProvider): Response
    {
        $insuranceProvider->load(['plans' => fn ($q) => $q->withCount('patientInsurances')]);

        return Inertia::render('InsuranceProviders/Show', [
            'provider' => $insuranceProvider,
        ]);
    }

    public function update(Request $request, InsuranceProvider $insuranceProvider)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'address' => ['nullable', 'string'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $insuranceProvider->update($data);

        return back()->with('success', 'Provider updated.');
    }

    public function destroy(InsuranceProvider $insuranceProvider)
    {
        $insuranceProvider->delete();

        return redirect()->route('insurance-providers.index')->with('success', 'Provider deleted.');
    }
}
