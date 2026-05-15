<?php

namespace App\Http\Controllers;

use App\Models\InsuranceClaim;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InsuranceClaimController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = InsuranceClaim::with(['invoice.patient', 'insurance'])
            ->latest();

        if ($user->isPatient()) {
            $query->whereHas('invoice', fn ($q) => $q->where('patient_id', optional($user->patient)->id));
        }

        $stats = [
            'total' => InsuranceClaim::count(),
            'approved_cents' => InsuranceClaim::whereIn('status', ['approved', 'partially_approved'])->sum('approved_cents'),
            'pending' => InsuranceClaim::whereIn('status', ['submitted', 'under_review'])->count(),
            'denied' => InsuranceClaim::where('status', 'denied')->count(),
        ];

        return Inertia::render('Claims/Index', [
            'claims' => $query->paginate(20)->through(fn ($c) => [
                'id' => $c->id,
                'claim_number' => $c->claim_number,
                'patient' => $c->invoice?->patient?->full_name,
                'invoice_number' => $c->invoice?->number,
                'invoice_id' => $c->invoice_id,
                'provider' => $c->insurance?->provider_name,
                'claimed_cents' => $c->claimed_cents,
                'approved_cents' => $c->approved_cents,
                'status' => $c->status,
                'submitted_on' => $c->submitted_on?->toDateString(),
                'decided_on' => $c->decided_on?->toDateString(),
            ]),
            'stats' => $stats,
        ]);
    }

    public function show(InsuranceClaim $claim): Response
    {
        $claim->load(['invoice.patient', 'invoice.items', 'invoice.payments', 'insurance']);

        return Inertia::render('Claims/Show', ['claim' => $claim]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'invoice_id' => ['required', 'exists:invoices,id'],
            'patient_insurance_id' => ['required', 'exists:patient_insurances,id'],
            'claim_number' => ['nullable', 'string', 'max:100'],
            'claimed_cents' => ['required', 'integer', 'min:1'],
            'submitted_on' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $claim = InsuranceClaim::create($data + ['status' => 'submitted']);

        return redirect()->route('claims.show', $claim)->with('success', 'Claim submitted.');
    }

    public function updateStatus(Request $request, InsuranceClaim $claim)
    {
        $data = $request->validate([
            'status' => ['required', 'in:submitted,under_review,approved,partially_approved,denied,appealed'],
            'approved_cents' => ['nullable', 'integer', 'min:0'],
            'decided_on' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $claim->update(array_filter($data, fn ($v) => $v !== null));

        if (in_array($data['status'], ['approved', 'partially_approved']) && isset($data['approved_cents'])) {
            $claim->invoice->increment('paid_cents', (int) $data['approved_cents']);
            if ($claim->invoice->paid_cents >= $claim->invoice->total_cents) {
                $claim->invoice->update(['status' => 'paid']);
            }
        }

        return back()->with('success', 'Claim status updated.');
    }
}
