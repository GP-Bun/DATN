<?php

namespace App\Events;

use App\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConversationUpdated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $conversation;

    public function __construct(Conversation $conversation)
    {
        $this->conversation = $conversation->load('user');
    }

    public function broadcastOn()
    {
        return new PrivateChannel('admin.conversations');
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->conversation->id,
            'user_id' => $this->conversation->user_id,
            'last_message_at' => $this->conversation->last_message_at,
            'unread_count' => $this->conversation->unread_count,
            'user' => [
                'id' => $this->conversation->user->id,
                'name' => $this->conversation->user->name,
            ],
        ];
    }
}

