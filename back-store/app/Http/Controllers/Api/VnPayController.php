<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class VnpayController extends Controller
{
    // Tạo URL thanh toán VNPay
    public function create(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer'
        ]);

        $order = Order::findOrFail($request->order_id);

        // Lấy từ config, không dùng trực tiếp env()
        $vnp_TmnCode = config('vnpay.tmn_code');
        $vnp_HashSecret = config('vnpay.hash_secret');
        $vnp_Url = config('vnpay.url');

        // Kiểm tra và báo lỗi chi tiết
        $missing = [];
        if (empty($vnp_TmnCode)) {
            $missing[] = 'VNPAY_TMN_CODE';
        }
        if (empty($vnp_HashSecret)) {
            $missing[] = 'VNPAY_HASH_SECRET';
        }
        if (empty($vnp_Url)) {
            $missing[] = 'VNPAY_URL';
        }

        if (!empty($missing)) {
            return response()->json([
                'success' => false,
                'message' => 'VNPAY chưa được thiết lập đúng. Vui lòng cấu hình các biến môi trường sau trong file .env: ' . implode(', ', $missing) . '. Xem hướng dẫn tại: https://sandbox.vnpayment.vn/apis/docs/',
                'missing_config' => $missing
            ], 500);
        }

        $vnp_ReturnUrl = url('/api/vnpay/return');
        $vnp_Amount = $order->final_amount * 100;
        $vnp_TxnRef = $order->id;
        $vnp_OrderInfo = "Thanh toán đơn hàng #$order->id";
        $vnp_CreateDate = date('YmdHis');

        $inputData = [
            "vnp_Version" => "2.1.0",
            "vnp_TmnCode" => $vnp_TmnCode,
            "vnp_Amount" => $vnp_Amount,
            "vnp_Command" => "pay",
            "vnp_CreateDate" => $vnp_CreateDate,
            "vnp_CurrCode" => "VND",
            "vnp_IpAddr" => $request->ip() ?: '127.0.0.1',
            "vnp_Locale" => "vn",
            "vnp_OrderInfo" => $vnp_OrderInfo,
            "vnp_ReturnUrl" => $vnp_ReturnUrl,
            "vnp_TxnRef" => $vnp_TxnRef,
        ];

        ksort($inputData);

        $query = [];
        $hashData = "";
        foreach ($inputData as $key => $value) {
            $query[] = urlencode($key) . "=" . urlencode($value);
            $hashData .= $key . "=" . $value . "&";
        }
        $hashData = rtrim($hashData, "&");

        $vnp_SecureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);
        $query[] = "vnp_SecureHash=" . $vnp_SecureHash;

        $vnp_UrlFull = $vnp_Url . "?" . implode('&', $query);

        return response()->json([
            'success' => true,
            'payment_url' => $vnp_UrlFull,
            'order_id' => $order->id
        ]);
    }

    // Callback sau khi thanh toán
    public function return(Request $request)
    {
        $vnp_SecureHash = $request->get('vnp_SecureHash');
        $vnp_HashSecret = config('vnpay.hash_secret');

        $inputData = $request->query();
        unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);
        ksort($inputData);

        $hashData = "";
        foreach ($inputData as $key => $value) {
            $hashData .= $key . "=" . $value . "&";
        }
        $hashData = rtrim($hashData, "&");

        $secureHashCheck = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        $response = [
            'valid' => false,
            'message' => 'Giao dịch không hợp lệ',
            'data' => $inputData
        ];

        if ($secureHashCheck === $vnp_SecureHash) {
            $response['valid'] = true;
            $orderId = $request->get('vnp_TxnRef');
            $order = Order::find($orderId);

            if ($order) {
                if ($request->get('vnp_ResponseCode') == "00") {
                    $order->payment_status = 'paid';
                    $order->save();
                    $response['message'] = 'Thanh toán thành công';
                } else {
                    $order->payment_status = 'failed';
                    $order->save();
                    $response['message'] = 'Thanh toán thất bại hoặc hủy';
                }
            }
        }

        $frontendUrl = "http://localhost:5173/vnpay-return";
        $queryString = http_build_query($request->all());
        return redirect()->away($frontendUrl . '?' . $queryString);
    }
}
