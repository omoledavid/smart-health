<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Services/Index', [
            'services' => Service::latest()->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        Service::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'price_cents' => ['required', 'integer', 'min:0'],
        ]));

        return back();
    }

    public function update(Request $request, Service $service)
    {
        $service->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'price_cents' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]));

        return back();
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return back();
    }
}
