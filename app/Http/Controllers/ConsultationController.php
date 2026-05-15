<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConsultationRequest;
use App\Http\Requests\UpdateConsultationRequest;
use App\Models\Consultation;
use Inertia\Inertia;
use Inertia\Response;

class ConsultationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Consultations/Index', [
            'consultations' => Consultation::query()
                ->latest()
                ->get(['id', 'patient_name', 'status', 'created_at']),
        ]);
    }

    public function store(StoreConsultationRequest $request)
    {
        $consultation = Consultation::create($request->validated());

        return redirect()->route('consultations.show', $consultation);
    }

    public function show(Consultation $consultation): Response
    {
        return Inertia::render('Consultations/Show', [
            'consultation' => $consultation->only([
                'id', 'patient_name', 'raw_notes', 'structured_soap', 'status', 'updated_at',
            ]),
        ]);
    }

    public function update(UpdateConsultationRequest $request, Consultation $consultation)
    {
        $consultation->update($request->validated());

        return back();
    }
}
