<?php

namespace App\Http\Controllers;

use App\Models\Specialization;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SpecializationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Specializations/Index', [
            'specializations' => Specialization::latest()->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        Specialization::create($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]));

        return back();
    }

    public function update(Request $request, Specialization $specialization)
    {
        $specialization->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]));

        return back();
    }

    public function destroy(Specialization $specialization)
    {
        $specialization->delete();

        return back();
    }
}
