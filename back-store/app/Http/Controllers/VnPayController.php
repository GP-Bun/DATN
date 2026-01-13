<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class VnPayController extends Controller
{
    /**
     * Tạo URL thanh toán VNPay và redirect sang sandbox
     */
    public function createPayment(Request $request)
    {
        $order = Order::findOrFail($request->order_id);

        $vnp_TmnCode   = env('VNPAY_TMN_CODE');
        $vnp_HashSecret = env('VNPAY_HASH_SECRET');
        $vnp_Url       = env('VNPAY_URL');
        $vnp_Returnurl = route('vnpay.return');

        $inputData = [
            "vnp_Version"    => "2.1.0",
            "vnp_TmnCode"    => $vnp_TmnCode,
            "vnp_Amount"     => $order->final_amount * 100, // VNPay nhân 100
            "vnp_Command"    => "pay",
            "vnp_CreateDate" => date('YmdHis'),
            "vnp_CurrCode"   => "VND",
            "vnp_IpAddr"     => $request->ip(),
            "vnp_Locale"     => "vn",
            "vnp_OrderInfo"  => "Thanh toán đơn hàng #" . $order->id,
            "vnp_ReturnUrl"  => $vnp_Returnurl,
            "vnp_TxnRef"     => $order->id,
        ];

        ksort($inputData);
        $query = http_build_query($inputData);
        $hashdata = hash_hmac('sha512', urldecode($query), $vnp_HashSecret);

        $vnp_Url .= "?" . $query . "&vnp_SecureHash=" . $hashdata;

        return redirect($vnp_Url);
    }

    /**
     * Callback VNPay sau khi người dùng thanh toán xong
     */
    public function vnpayReturn(Request $request)
    {
        $orderId = $request->vnp_TxnRef;
        $order = Order::find($orderId);

        if (!$order) {
            return redirect('/failed')->with('message', 'Đơn hàng không tồn tại');
        }

        // Lấy toàn bộ dữ liệu VNPay
        $vnp_SecureHash = $request->vnp_SecureHash;
        $inputData = $request->except(['vnp_SecureHash', 'vnp_SecureHashType']);
        ksort($inputData);
        $query = http_build_query($inputData);
        $hash = hash_hmac('sha512', urldecode($query), env('VNPAY_HASH_SECRET'));

        // Verify checksum
        if ($hash !== $vnp_SecureHash) {
            $order->update(['payment_status' => 'unpaid']);
            return redirect('/failed')->with('message', 'Dữ liệu thanh toán không hợp lệ');
        }

        // Verify số tiền
        if ($request->vnp_Amount != $order->final_amount * 100) {
            $order->update(['payment_status' => 'unpaid']);
            return redirect('/failed')->with('message', 'Số tiền thanh toán không khớp');
        }

        // Thanh toán thành công
        if ($request->vnp_ResponseCode === "00") {
            $order->update([
                'payment_status' => 'paid',
                'transaction_id' => $request->vnp_TransactionNo,
                'paid_at' => now()
            ]);
            return redirect('/success'); // React route hoặc Blade
        } else {
            $order->update(['payment_status' => 'unpaid']);
            return redirect('/failed'); // React route hoặc Blade
        }
    }
}
