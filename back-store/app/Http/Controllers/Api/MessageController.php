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

        $sender = $request->user();
        $receiver = \App\Models\User::find($validated['receiver_id']);

        // Xác định ai là khách, ai là admin để tìm/tạo conversation đúng field
        // Giả định: user_id là khách, admin_id là staff
        if ($sender->role === 'admin') {
            $adminId = $sender->id;
            $customerId = $receiver->id;
        } else {
            $adminId = $receiver->id;
            $customerId = $sender->id;
        }

        // Tìm hoặc tạo hội thoại đúng cặp Khách - Admin
        $conversation = Conversation::firstOrCreate(
            [
                'user_id' => $customerId,
                'admin_id' => $adminId,
            ],
            ['last_message_at' => now()]
        );

        // Tạo tin nhắn gắn vào hội thoại
        $message = $conversation->messages()->create([
            'sender_id'   => $sender->id,
            'receiver_id' => $receiver->id,
            'content'     => $validated['content'],
            'is_bot'      => 0
        ]);

        // Cập nhật hội thoại
        $conversation->update([
            'last_message_at' => now(),
            'unread_count'    => ($sender->id === $customerId) ? ($conversation->unread_count + 1) : $conversation->unread_count,
        ]);

        // Broadcast tin nhắn và cập nhật hội thoại
        try {
            broadcast(new MessageSent($message))->toOthers();
            broadcast(new \App\Events\ConversationUpdated($conversation))->toOthers();
        } catch (\Throwable $e) {}

        return response()->json($message, 201);
    }
}
