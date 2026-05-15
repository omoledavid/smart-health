<?php

namespace App\Http\Controllers;

use App\Models\InsurancePlan;
use App\Models\InsurancePlanService;
use App\Models\InsuranceProvider;
use App\Models\Service;
use Illuminate\Http\Request;

class InsurancePlanController extends Controller
{
    public function store(Request $request, InsuranceProvider $insuranceProvider)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $insuranceProvider->plans()->create($data);

        return back()->with('success', 'Plan created.');
    }

    public function update(Request $request, InsurancePlan $plan)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $plan->update($data);

        return back()->with('success', 'Plan updated.');
    }

    public function destroy(InsurancePlan $plan)
    {
        $plan->delete();

        return back()->with('success', 'Plan deleted.');
    }

    public function services(InsurancePlan $plan)
    {
        $allServices = Service::where('is_active', true)->orderBy('name')->get(['id', 'name', 'price_cents']);
        $planServices = $plan->planServices()->get()->keyBy('service_id');

        $services = $allServices->map(fn ($s) => [
            'id' => $s->id,
            'name' => $s->name,
            'price_cents' => $s->price_cents,
            'adjustment_type' => $planServices[$s->id]->adjustment_type ?? 'percentage',
            'adjustment_value' => (float) ($planServices[$s->id]->adjustment_value ?? 0),
            'fixed_price' => $planServices[$s->id]->fixed_price ?? null,
            'enabled' => isset($planServices[$s->id]),
        ]);

        return response()->json(['services' => $services]);
    }

    public function syncServices(Request $request, InsurancePlan $plan)
    {
        $data = $request->validate([
            'services' => ['required', 'array'],
            'services.*.service_id' => ['required', 'exists:services,id'],
            'services.*.adjustment_type' => ['required', 'in:percentage,fixed'],
            'services.*.adjustment_value' => ['required', 'numeric', 'min:0'],
            'services.*.fixed_price' => ['nullable', 'integer', 'min:0'],
        ]);

        // Delete existing and re-create
        $plan->planServices()->delete();

        foreach ($data['services'] as $item) {
            InsurancePlanService::create([
                'insurance_plan_id' => $plan->id,
                'service_id' => $item['service_id'],
                'adjustment_type' => $item['adjustment_type'],
                'adjustment_value' => $item['adjustment_value'],
                'fixed_price' => $item['fixed_price'] ?? null,
            ]);
        }

        return back()->with('success', 'Service pricing updated.');
    }
}
