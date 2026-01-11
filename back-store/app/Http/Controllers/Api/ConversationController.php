<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Conversation;
use App\Events\ConversationUpdated;

class ConversationController extends Controller
{
    // Khách xem danh sách hội thoại của mình
    public function index(Request $request)
    {
        $conversations = Conversation::where('user_id', $request->user()->id)
            ->with('messages')
            ->orderBy('last_message_at','desc')
            ->get();

        return response()->json($conversations);
    }

    // Admin xem tất cả hội thoại
    public function adminIndex()
    {
        $conversations = Conversation::with(['user', 'messages'])
            ->orderBy('last_message_at','desc')
            ->get();

        return response()->json($conversations);
    }

    // Admin đánh dấu hội thoại đã đọc
    public function markAsRead($id)
    {
        $conversation = Conversation::findOrFail($id);
        $conversation->update(['unread_count'=>0]);
        $conversation->messages()->whereNull('read_at')->update(['read_at'=>now()]);

        broadcast(new ConversationUpdated($conversation))->toOthers();

        return response()->json(['message'=>'Marked as read']);
    }
}
