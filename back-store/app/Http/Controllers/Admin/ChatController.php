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
            'content'         => 'nullable|string',
            'file' => 'nullable|file|max:10240',
        ]);

        $conversation = Conversation::findOrFail($request->conversation_id);

        $data = [
            'sender_id'   => session('admin_user_id') ?? 1,
            'receiver_id' => $conversation->user_id,
            'content'     => $request->content,
            'is_bot'      => false,
        ];


        // ✅ XỬ LÝ FILE
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('chat_files', 'public');

            $data['file_path'] = $path;
            $data['file_type'] = str_contains($file->getMimeType(), 'image')
                ? 'image'
                : 'file';
        }

        $message = $conversation->messages()->create($data);

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

    public function markAsRead($id)
    {
        $conversation = Conversation::findOrFail($id);

        $conversation->messages()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $conversation->update(['unread_count' => 0]);

        return response()->json(['status' => 'ok']);
    }

    public function conversations()
    {
        return Conversation::with('user')
            ->orderBy('last_message_at', 'desc')
            ->get();
    }
}
