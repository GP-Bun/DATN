<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        $conversations = Conversation::with(['user', 'messages'])
            ->orderBy('last_message_at', 'desc')
            ->get();
            
        return view('admin.chat.index', compact('conversations'));
    }

    public function getMessages($id)
    {
        $conversation = Conversation::with('messages')->findOrFail($id);
        return response()->json($conversation->messages);
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'content'         => 'required|string',
        ]);

        $conversation = Conversation::findOrFail($request->conversation_id);
        
        $message = $conversation->messages()->create([
            'sender_id'   => session('admin_user_id') ?? 1, // Sử dụng session thay vì auth()
            'receiver_id' => $conversation->user_id,
            'content'     => $request->content,
            'is_bot'      => false
        ]);

        $conversation->update(['last_message_at' => now()]);

        return response()->json($message);
    }

    public function destroy($id)
    {
        $conversation = Conversation::findOrFail($id);
        
        // Xóa tất cả tin nhắn trong hội thoại trước
        $conversation->messages()->delete();
        
        // Xóa hội thoại
        $conversation->delete();

        return response()->json(['message' => 'Đã xóa hội thoại thành công']);
    }
}
