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

        // 1. Lấy dữ liệu sản phẩm thực tế
        $products = \App\Models\Product::where('status', 1)->limit(5)->get(['name', 'price']);
        $productInfo = $products->map(fn($p) => "{$p->name} (" . number_format($p->price) . "đ)")->implode(', ');

        // 2. Logic FAQ (Trả lời ngay lập tức các câu cơ bản)
        $faq = [
            'chào' => 'Chào bạn! 👋 TH Store rất vui được hỗ trợ bạn chọn sneaker.',
            'hi' => 'Hi! 👋 Chào mừng bạn đến với TH Store.',
            'giày' => "Shop đang có sẵn: {$productInfo}. Bạn quan tâm dòng nào?",
            'địa chỉ' => 'Địa chỉ shop: 240 Phú Mỹ, Cầu Giấy, Hà Nội.',
            'ship' => 'Miễn phí ship cho đơn trên 1 triệu đồng bạn nhé!',
        ];

        foreach ($faq as $key => $reply) {
            if (str_contains($loweredMsg, $key)) {
                return response()->json(['reply' => $reply]);
            }
        }

        // 3. Ưu tiên sử dụng Gemini (Vì nó miễn phí và thông minh)
        $geminiKey = env('GEMINI_API_KEY');
        if ($geminiKey) {
            try {
                $response = Http::timeout(15)->withoutVerifying()->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$geminiKey}",
                    [
                        'contents' => [
                            ['parts' => [['text' => "Bạn là trợ lý bán giày TH Store. Sản phẩm: {$productInfo}. Hãy trả lời khách: {$userMessage} (ngắn gọn, tiếng Việt)"]]]
                        ]
                    ]
                );

                if ($response->successful()) {
                    $resData = $response->json();
                    $reply = $resData['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if ($reply) return response()->json(['reply' => $reply]);
                }
            } catch (\Exception $e) {}
        }

        // 4. Fallback: Nếu không khớp FAQ và AI lỗi/không có key
        return response()->json([
            'reply' => "Chào bạn, hiện tại tôi đang có sẵn các mẫu: {$productInfo}. Bạn cần tư vấn thêm về size hay mẫu nào không? ✨"
        ]);
    }
}
