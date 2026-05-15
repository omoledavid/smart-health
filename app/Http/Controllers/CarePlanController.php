<?php

namespace App\Http\Controllers;

use App\Models\CarePlan;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CarePlanController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $query = CarePlan::with(['patient', 'doctor.user'])
            ->withCount('tasks')
            ->latest();

        if ($user->isPatient()) {
            $query->where('patient_id', optional($user->patient)->id);
        } elseif ($user->isDoctor()) {
            $query->where('doctor_id', optional($user->doctor)->id);
        }

        return Inertia::render('CarePlans/Index', [
            'plans' => $query->paginate(20)->through(fn ($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'patient' => $p->patient?->full_name,
                'doctor' => $p->doctor?->user?->name,
                'status' => $p->status,
                'start_date' => $p->start_date?->toDateString(),
                'review_date' => $p->review_date?->toDateString(),
                'tasks_count' => $p->tasks_count,
            ]),
            'patients' => $user->isAdmin() || $user->isDoctor()
                ? Patient::orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email'])
                : [],
            'doctors' => $user->isAdmin()
                ? Doctor::with('user:id,name')->get()->map(fn ($d) => ['id' => $d->id, 'name' => $d->user?->name])
                : [],
        ]);
    }

    public function show(CarePlan $plan): Response
    {
        $plan->load(['patient', 'doctor.user', 'tasks.assignedTo:id,name', 'tasks.createdBy:id,name']);

        return Inertia::render('CarePlans/Show', ['plan' => $plan]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['nullable', 'exists:doctors,id'],
            'title' => ['required', 'string', 'max:255'],
            'goals' => ['nullable', 'string'],
            'interventions' => ['nullable', 'string'],
            'start_date' => ['nullable', 'date'],
            'review_date' => ['nullable', 'date'],
        ]);

        $plan = CarePlan::create($data + ['status' => 'active']);

        return redirect()->route('care-plans.show', $plan)->with('success', 'Care plan created.');
    }

    public function updateStatus(Request $request, CarePlan $plan)
    {
        $request->validate(['status' => ['required', 'in:active,completed,on_hold']]);
        $plan->update(['status' => $request->status]);

        return back()->with('success', 'Status updated.');
    }

    public function addTask(Request $request, CarePlan $plan)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', 'in:urgent,high,normal,low'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'due_date' => ['nullable', 'date'],
        ]);

        Task::create($data + [
            'care_plan_id' => $plan->id,
            'patient_id' => $plan->patient_id,
            'created_by' => $request->user()->id,
            'status' => 'open',
        ]);

        return back()->with('success', 'Task added.');
    }

    public function completeTask(Task $task)
    {
        $task->update(['status' => 'completed', 'completed_at' => now()]);

        return back()->with('success', 'Task completed.');
    }
}
