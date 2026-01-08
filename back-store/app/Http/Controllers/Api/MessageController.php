<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Events\ConversationUpdated;

class MessageController extends Controller
{
    // Lấy lịch sử chat giữa user hiện tại và admin (hoặc tất cả hội thoại liên quan)
    public function index(Request $request)
    {
        $messages = Message::where(function ($q) use ($request) {
            $q->where('sender_id', $request->user()->id)
                ->orWhere('receiver_id', $request->user()->id);
        })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'content'     => 'required|string',
        ]);

        // Tìm hoặc tạo hội thoại giữa user hiện tại và admin
        $conversation = Conversation::firstOrCreate(
            [
                'user_id' => $request->user()->id,
                'admin_id' => $validated['receiver_id'],
            ],
            ['last_message_at' => now()]
        );

        // Tạo tin nhắn gắn vào hội thoại
        $message = $conversation->messages()->create([
            'sender_id'   => $request->user()->id,
            'receiver_id' => $validated['receiver_id'],
            'content'     => $validated['content'],
        ]);

        // Cập nhật hội thoại
        $conversation->update([
            'last_message_at' => now(),
            'unread_count'    => $conversation->unread_count + 1,
        ]);

        // Broadcast tin nhắn và cập nhật hội thoại
        broadcast(new MessageSent($message))->toOthers();
        broadcast(new ConversationUpdated($conversation))->toOthers();

        return response()->json($message, 201);
    }
}
