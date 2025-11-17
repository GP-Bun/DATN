<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    // POST /api/coupons/apply
    public function apply(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string',
            'amount' => 'required|numeric|min:0',
        ]);

        $coupon = Coupon::where('code', $data['code'])->first();
        if (! $coupon) {
            return response()->json(['ok' => false, 'message' => 'Mã không tồn tại'], 404);
        }

        if (! $coupon->isValid()) {
            return response()->json(['ok' => false, 'message' => 'Mã không hợp lệ hoặc đã hết hạn'], 422);
        }

        if ($coupon->min_order_amount && $data['amount'] < $coupon->min_order_amount) {
            return response()->json(['ok' => false, 'message' => 'Đơn hàng chưa đạt điều kiện áp dụng mã'], 422);
        }

        $discount = $coupon->calculateDiscount((float) $data['amount']);

        // Lưu tạm tăng used_count nếu muốn (thông thường tăng khi order hoàn tất) - ở đây không tăng tự động

        return response()->json([
            'ok' => true,
            'discount' => $discount,
            'final_amount' => max(0, round($data['amount'] - $discount, 2)),
            'coupon' => [
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => (float) $coupon->value,
            ]
        ]);
    }
}
