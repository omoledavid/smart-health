<?php

namespace App\Http\Middleware;

use App\Models\Message;
use App\Models\MessageThread;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'phone' => $user->phone,
                    'avatar_path' => $user->avatar_path,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'unread_messages' => function () use ($user) {
                if (!$user) return 0;

                $threadQuery = MessageThread::query();
                if ($user->isPatient()) {
                    $threadQuery->where('patient_id', optional($user->patient)->id);
                } elseif ($user->isDoctor()) {
                    $threadQuery->where('doctor_id', optional($user->doctor)->id);
                }

                return Message::whereIn('thread_id', $threadQuery->pluck('id'))
                    ->where('sender_id', '!=', $user->id)
                    ->whereNull('read_at')
                    ->count();
            },
            'notifications' => function () use ($user) {
                if (!$user) return [];

                return $user->unreadNotifications()
                    ->latest()
                    ->limit(10)
                    ->get()
                    ->map(fn ($n) => [
                        'id'         => $n->id,
                        'type'       => $n->data['type'] ?? 'info',
                        'title'      => $n->data['title'] ?? '',
                        'body'       => $n->data['body'] ?? '',
                        'url'        => $n->data['url'] ?? null,
                        'created_at' => $n->created_at->diffForHumans(),
                    ])
                    ->all();
            },
        ];
    }
}
