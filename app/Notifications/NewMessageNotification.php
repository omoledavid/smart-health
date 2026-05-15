<?php

namespace App\Notifications;

use App\Models\Message;
use App\Models\MessageThread;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class NewMessageNotification extends Notification
{
    public function __construct(
        public Message $message,
        public MessageThread $thread,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $sender = $this->message->sender?->name ?? 'Someone';

        return [
            'type'      => 'new_message',
            'title'     => 'New message from ' . $sender,
            'body'      => Str::limit($this->message->body, 80),
            'url'       => "/messages/{$this->thread->id}",
            'thread_id' => $this->thread->id,
        ];
    }
}
