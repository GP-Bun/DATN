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
            'code' => 'required|string|max:50',
            'amount' => 'required|numeric|min:0',
        ]);

        // Tìm coupon theo code (case insensitive)
        $coupon = Coupon::whereRaw('LOWER(code) = ?', [strtolower(trim($data['code']))])->first();

        if (!$coupon) {
            return response()->json([
                'ok' => false,
                'message' => 'Mã giảm giá không tồn tại'
            ], 404);
        }

        // Kiểm tra coupon có active không
        if (!$coupon->active) {
            return response()->json([
                'ok' => false,
                'message' => 'Mã giảm giá này hiện không khả dụng'
            ], 422);
        }

        // Kiểm tra thời gian hiệu lực
        $now = now();
        if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
            return response()->json([
                'ok' => false,
                'message' => 'Mã giảm giá chưa có hiệu lực. Thời gian bắt đầu: ' . $coupon->starts_at->format('d/m/Y H:i')
            ], 422);
        }

        if ($coupon->ends_at && $now->gt($coupon->ends_at)) {
            return response()->json([
                'ok' => false,
                'message' => 'Mã giảm giá đã hết hạn. Thời gian kết thúc: ' . $coupon->ends_at->format('d/m/Y H:i')
            ], 422);
        }

        // Kiểm tra giới hạn sử dụng
        if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
            return response()->json([
                'ok' => false,
                'message' => 'Mã giảm giá đã hết lượt sử dụng'
            ], 422);
        }

        // Kiểm tra điều kiện đơn hàng tối thiểu
        $orderAmount = (float) $data['amount'];
        if ($coupon->min_order_amount && $orderAmount < $coupon->min_order_amount) {
            return response()->json([
                'ok' => false,
                'message' => 'Đơn hàng chưa đạt điều kiện. Đơn hàng tối thiểu: ' . number_format($coupon->min_order_amount, 0, ',', '.') . 'đ'
            ], 422);
        }

        // Tính số tiền giảm
        $discount = $coupon->calculateDiscount($orderAmount);
        $finalAmount = max(0, round($orderAmount - $discount, 2));

        return response()->json([
            'ok' => true,
            'message' => 'Áp dụng mã giảm giá thành công!',
            'discount' => round($discount, 2),
            'final_amount' => $finalAmount,
            'coupon' => [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => (float) $coupon->value,
                'min_order_amount' => $coupon->min_order_amount ? (float) $coupon->min_order_amount : null,
                'max_discount' => $coupon->max_discount ? (float) $coupon->max_discount : null,
            ]
        ]);
    }

    // GET /api/coupons/available - Lấy danh sách voucher có sẵn
    // GET /api/coupons/available - Lấy tất cả voucher kèm trạng thái
    public function available(Request $request)
    {
        $orderAmount = $request->get('amount', 0);
        $now = now();

        $coupons = Coupon::orderBy('created_at', 'desc')
            ->get()
            ->map(function ($coupon) use ($orderAmount, $now) {
                // Tính discount ước tính
                $estimatedDiscount = $coupon->calculateDiscount($orderAmount);
                $isApplicable = $coupon->isValid($orderAmount);

                // Xác định trạng thái
                $status = 'active';
                if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
                    $status = 'upcoming';
                }
                if ($coupon->ends_at && $now->gt($coupon->ends_at)) {
                    $status = 'expired';
                }
                if (!$coupon->active) {
                    $status = 'inactive';
                }

                return [
                    'id' => $coupon->id,
                    'code' => $coupon->code,
                    'type' => $coupon->type,
                    'value' => (float) $coupon->value,
                    'min_order_amount' => $coupon->min_order_amount ? (float) $coupon->min_order_amount : null,
                    'max_discount' => $coupon->max_discount ? (float) $coupon->max_discount : null,
                    'estimated_discount' => round($estimatedDiscount, 2),
                    'is_applicable' => $isApplicable,
                    'status' => $status,
                    'description' => $this->getCouponDescription($coupon, $orderAmount),
                ];
            })
            ->values();

        return response()->json([
            'coupons' => $coupons,
            'count' => $coupons->count(),
        ]);
    }
    // Helper function để tạo mô tả voucher
    private function getCouponDescription($coupon, $orderAmount)
    {
        $desc = '';

        if ($coupon->type === 'percent') {
            $desc = "Giảm {$coupon->value}%";
            if ($coupon->max_discount) {
                $desc .= " (tối đa " . number_format($coupon->max_discount, 0, ',', '.') . "đ)";
            }
        } else {
            $desc = "Giảm " . number_format($coupon->value, 0, ',', '.') . "đ";
        }

        if ($coupon->min_order_amount) {
            $desc .= " - Áp dụng cho đơn từ " . number_format($coupon->min_order_amount, 0, ',', '.') . "đ";
        }

        if (!$coupon->isValid($orderAmount) && $coupon->min_order_amount && $orderAmount < $coupon->min_order_amount) {
            $desc .= " (Cần thêm " . number_format($coupon->min_order_amount - $orderAmount, 0, ',', '.') . "đ)";
        }

        // Nếu chưa tới thời gian bắt đầu
        $now = now();
        if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
            $desc .= " (Kích hoạt từ " . $coupon->starts_at->format('d/m/Y H:i') . ")";
        }

        // Nếu đã hết hạn
        if ($coupon->ends_at && $now->gt($coupon->ends_at)) {
            $desc .= " (Đã hết hạn)";
        }

        return $desc;
    }
}
