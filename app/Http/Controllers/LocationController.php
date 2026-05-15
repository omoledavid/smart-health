<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LocationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Locations/Index', [
            'locations' => Location::latest()->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        Location::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'state' => ['nullable', 'string'],
            'postal_code' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'timezone' => ['nullable', 'string'],
        ]));

        return back();
    }

    public function update(Request $request, Location $location)
    {
        $location->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'state' => ['nullable', 'string'],
            'postal_code' => ['nullable', 'string'],
            'phone' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]));

        return back();
    }

    public function destroy(Location $location)
    {
        $location->delete();

        return back();
    }
}
