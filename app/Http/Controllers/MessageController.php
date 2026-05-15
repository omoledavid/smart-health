<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\MessageThread;
use App\Notifications\NewMessageNotification;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MessageController extends Controller
{
    private function threadList(Request $request): array
    {
        $user = $request->user();
        $query = MessageThread::with(['patient', 'doctor.user', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->withCount(['messages as unread_count' => fn ($q) => $q->whereNull('read_at')->where('sender_id', '!=', $user->id)])
            ->latest('last_message_at');

        if ($user->isPatient()) {
            $query->where('patient_id', optional($user->patient)->id);
        } elseif ($user->isDoctor()) {
            $query->where('doctor_id', optional($user->doctor)->id);
        }

        return $query->get()->map(fn ($t) => [
            'id' => $t->id,
            'subject' => $t->subject,
            'patient' => $t->patient?->full_name,
            'patient_initials' => strtoupper(substr($t->patient?->first_name ?? '?', 0, 1) . substr($t->patient?->last_name ?? '', 0, 1)),
            'doctor' => $t->doctor?->user?->name,
            'last_message_at' => $t->last_message_at?->toIso8601String(),
            'last_message' => $t->messages->first()?->body,
            'unread_count' => (int) $t->unread_count,
        ])->all();
    }

    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Messages/Index', [
            'threads' => $this->threadList($request),
            'active_thread' => null,
            'patients' => $user->isAdmin() || $user->isDoctor()
                ? \App\Models\Patient::orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email'])
                : [],
            'doctors' => $user->isAdmin()
                ? \App\Models\Doctor::with('user:id,name')->get()->map(fn ($d) => ['id' => $d->id, 'name' => $d->user?->name])
                : [],
        ]);
    }

    public function show(Request $request, MessageThread $thread): Response
    {
        $user = $request->user();
        $thread->load(['patient', 'doctor.user', 'messages.sender:id,name,role']);

        $thread->messages()
            ->whereNull('read_at')
            ->where('sender_id', '!=', $user->id)
            ->update(['read_at' => now()]);

        return Inertia::render('Messages/Index', [
            'threads' => $this->threadList($request),
            'active_thread' => [
                'id' => $thread->id,
                'subject' => $thread->subject,
                'patient' => $thread->patient?->full_name,
                'patient_initials' => strtoupper(substr($thread->patient?->first_name ?? '?', 0, 1) . substr($thread->patient?->last_name ?? '', 0, 1)),
                'doctor' => $thread->doctor?->user?->name,
                'messages' => $thread->messages->map(fn ($m) => [
                    'id' => $m->id,
                    'sender_id' => $m->sender_id,
                    'sender_name' => $m->sender?->name,
                    'sender_initials' => strtoupper(substr($m->sender?->name ?? '?', 0, 2)),
                    'body' => $m->body,
                    'created_at' => $m->created_at->toIso8601String(),
                    'read_at' => $m->read_at?->toIso8601String(),
                ]),
            ],
            'patients' => $user->isAdmin() || $user->isDoctor()
                ? \App\Models\Patient::orderBy('last_name')->get(['id', 'first_name', 'last_name', 'email'])
                : [],
            'doctors' => $user->isAdmin()
                ? \App\Models\Doctor::with('user:id,name')->get()->map(fn ($d) => ['id' => $d->id, 'name' => $d->user?->name])
                : [],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['nullable', 'exists:doctors,id'],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ]);

        $thread = MessageThread::create([
            'patient_id' => $data['patient_id'],
            'doctor_id' => $data['doctor_id'] ?? null,
            'subject' => $data['subject'] ?? null,
            'last_message_at' => now(),
        ]);

        $message = Message::create([
            'thread_id' => $thread->id,
            'sender_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        $message->load('sender');
        $this->notifyRecipient($request->user()->id, $thread, $message);

        return redirect()->route('messages.show', $thread);
    }

    public function reply(Request $request, MessageThread $thread)
    {
        $data = $request->validate(['body' => ['required', 'string']]);

        $message = Message::create([
            'thread_id' => $thread->id,
            'sender_id' => $request->user()->id,
            'body' => $data['body'],
        ]);
        $thread->update(['last_message_at' => now()]);

        $message->load('sender');
        $this->notifyRecipient($request->user()->id, $thread, $message);

        return back();
    }

    private function notifyRecipient(int $senderId, MessageThread $thread, Message $message): void
    {
        $thread->loadMissing(['patient.user', 'doctor.user']);
        $notification = new NewMessageNotification($message, $thread);

        // Notify the other party in the thread
        $patientUser = $thread->patient?->user;
        $doctorUser  = $thread->doctor?->user;

        if ($patientUser && $patientUser->id !== $senderId) {
            $patientUser->notify($notification);
        }
        if ($doctorUser && $doctorUser->id !== $senderId) {
            $doctorUser->notify($notification);
        }
    }
}
