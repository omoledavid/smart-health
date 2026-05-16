<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = Invoice::with('patient')->latest();
        if ($user->isPatient()) {
            $query->where('patient_id', optional($user->patient)->id);
        }

        $statsBase = Invoice::query();
        if ($user->isPatient()) {
            $statsBase->where('patient_id', optional($user->patient)->id);
        }

        $stats = [
            'total'         => (clone $statsBase)->count(),
            'paid_cents'    => (clone $statsBase)->where('status', 'paid')->sum('total_cents'),
            'pending_cents' => (clone $statsBase)->where('status', '!=', 'paid')->sum('total_cents'),
            'overdue'       => (clone $statsBase)->whereNotNull('due_on')
                ->where('due_on', '<', now())
                ->where('status', '!=', 'paid')
                ->count(),
        ];

        return Inertia::render('Invoices/Index', [
            'invoices' => $query->paginate(20),
            'stats' => $stats,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Invoices/Create', [
            'patients' => Patient::orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email']),
            'services' => Service::orderBy('name')->get(['id', 'name', 'price_cents']),
            'appointments' => Appointment::with('patient')
                ->where('status', 'completed')
                ->whereDoesntHave('invoice')
                ->latest('scheduled_at')
                ->get(['id', 'patient_id', 'scheduled_at']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'appointment_id' => ['nullable', 'exists:appointments,id'],
            'issued_on' => ['required', 'date'],
            'due_on' => ['nullable', 'date', 'after_or_equal:issued_on'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string'],
            'items.*.service_id' => ['nullable', 'exists:services,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price_cents' => ['required', 'integer', 'min:0'],
        ]);

        $subtotal = collect($data['items'])->sum(fn ($i) => $i['quantity'] * $i['unit_price_cents']);
        $number = 'INV-' . now()->format('Ymd') . '-' . str_pad(Invoice::count() + 1, 4, '0', STR_PAD_LEFT);

        $invoice = Invoice::create([
            'number' => $number,
            'patient_id' => $data['patient_id'],
            'appointment_id' => $data['appointment_id'] ?? null,
            'issued_on' => $data['issued_on'],
            'due_on' => $data['due_on'] ?? null,
            'notes' => $data['notes'] ?? null,
            'subtotal_cents' => $subtotal,
            'total_cents' => $subtotal,
            'status' => 'draft',
        ]);

        foreach ($data['items'] as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'service_id' => $item['service_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price_cents' => $item['unit_price_cents'],
                'total_cents' => $item['quantity'] * $item['unit_price_cents'],
            ]);
        }

        return redirect()->route('invoices.show', $invoice)->with('success', 'Invoice created.');
    }

    public function show(Invoice $invoice): Response
    {
        $invoice->load(['patient', 'items.service', 'payments', 'claims.insurance']);

        return Inertia::render('Invoices/Show', ['invoice' => $invoice]);
    }

    public function recordPayment(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'amount_cents' => ['required', 'integer', 'min:1'],
            'method' => ['required', 'string'],
            'reference' => ['nullable', 'string'],
            'paid_on' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        Payment::create(['invoice_id' => $invoice->id] + $data);
        $invoice->increment('paid_cents', (int) $data['amount_cents']);
        if ($invoice->paid_cents >= $invoice->total_cents) {
            $invoice->update(['status' => 'paid']);
        }

        return back()->with('success', 'Payment recorded.');
    }
}
