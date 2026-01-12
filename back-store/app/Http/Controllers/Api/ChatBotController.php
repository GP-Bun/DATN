<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatBotController extends Controller
{
    /**
     * ChatBot đa năng: Ưu tiên AI (Gemini/OpenAI), nếu lỗi/không có key thì dùng FAQ.
     */
    public function chat(Request $request)
    {
        $request->validate(['message' => 'required|string']);
        $userMessage = $request->input('message');
        $loweredMsg = mb_strtolower($userMessage);
        $user = $request->user();

        // 1. Kiểm tra Admin thực sự (Human) có đang chat không
        $adminTakingOver = false;
        $conversation = null;
        $adminId = 1;

        if ($user) {
            $admin = \App\Models\User::where('role', 'admin')->first() ?? \App\Models\User::first();
            $adminId = $admin ? $admin->id : 1;
            
            $conversation = \App\Models\Conversation::firstOrCreate(
                ['user_id' => $user->id, 'admin_id' => $adminId],
                ['last_message_at' => now()]
            );

            // Kiểm tra tin nhắn gần nhất (15 phút) từ phía Admin/Support
            // MÀ KHÔNG PHẢI LÀ BOT (is_bot = 0)
            $recentHumanMessage = $conversation->messages()
                ->where('sender_id', '!=', $user->id) // Tin nhắn không phải của khách
                ->where('is_bot', 0)                  // VÀ là người thật
                ->where('created_at', '>', now()->subMinutes(15))
                ->exists();

            if ($recentHumanMessage) {
                $adminTakingOver = true;
            }
        }

        // Nếu admin đang chat, lưu tin nhắn user và return luôn (không AI)
        if ($adminTakingOver && $conversation) {
            $conversation->messages()->create([
                'sender_id' => $user->id,
                'receiver_id' => $adminId,
                'content' => $userMessage,
                'is_bot' => 0
            ]);
            $conversation->update(['last_message_at' => now()]);
            return response()->json(['reply' => null, 'status' => 'admin_taking_over']);
        }

        // 2. Logic Trả lời AI (khi Admin chưa chiếm quyền)
        $products = \App\Models\Product::where('status', 1)->limit(5)->get(['name', 'price']);
        $productInfo = $products->map(fn($p) => "{$p->name} (" . number_format($p->price) . "đ)")->implode(', ');

        $reply = null;
        $faq = [
            'chào' => 'Chào bạn! 👋 TH Store rất vui được hỗ trợ bạn chọn sneaker.',
            'hi' => 'Hi! 👋 Chào mừng bạn đến với TH Store.',
            'giày' => "Shop đang có sẵn: {$productInfo}. Bạn quan tâm dòng nào?",
            'địa chỉ' => 'Địa chỉ shop: 240 Phú Mỹ, Cầu Giấy, Hà Nội.',
            'ship' => 'Miễn phí ship cho đơn trên 1 triệu đồng bạn nhé!',
        ];

        foreach ($faq as $key => $r) {
            if (str_contains($loweredMsg, $key)) {
                $reply = $r;
                break;
            }
        }

        if (!$reply) {
            $geminiKey = env('GEMINI_API_KEY');
            if ($geminiKey) {
                try {
                    $response = Http::timeout(15)->withoutVerifying()->post(
                        "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$geminiKey}",
                        [
                            'contents' => [
                                ['parts' => [['text' => "Bạn là trợ lý bán giày TH Store. Sản phẩm: {$productInfo}. Hãy trả lời khách: {$userMessage} (ngắn gọn, dưới 50 từ, tiếng Việt)"]]]
                            ]
                        ]
                    );

                    if ($response->successful()) {
                        $resData = $response->json();
                        $reply = $resData['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    }
                } catch (\Exception $e) {}
            }
        }

        if (!$reply) {
            $reply = "Chào bạn, hiện tại tôi đang hỗ trợ các mẫu: {$productInfo}. Bạn cần tư vấn thêm không? ✨";
        }

        // 3. Lưu hội thoại (User & Bot Reply)
        if ($user && $conversation) {
            // Lưu tin user
            $conversation->messages()->create([
                'sender_id' => $user->id,
                'receiver_id' => $adminId,
                'content' => $userMessage,
                'is_bot' => 0
            ]);

            // Bot trả lời (đóng dấu is_bot = 1)
            $replyMessage = $conversation->messages()->create([
                'sender_id' => $adminId,
                'receiver_id' => $user->id,
                'content' => $reply,
                'is_bot' => 1 
            ]);

            $conversation->update(['last_message_at' => now()]);
            
            try {
                broadcast(new \App\Events\MessageSent($replyMessage))->toOthers();
            } catch (\Exception $e) {}
        }

        return response()->json(['reply' => $reply]);
    }
}
