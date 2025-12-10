<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Address;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'address_id'   => 'nullable|exists:addresses,id',
            'full_name'    => 'required_without:address_id|string|max:255',
            'phone'        => 'required_without:address_id|string|max:20',
            'address'      => 'required_without:address_id|string|max:255',
            'city'         => 'required_without:address_id|string|max:255',
            'coupon_code'  => 'nullable|string|exists:coupons,code',
        ]);

        $cart = Cart::where('user_id', $user->id)
            ->with('items.product', 'items.variant')
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Giỏ hàng trống'], 400);
        }

        DB::beginTransaction();
        try {
            // Nếu không có address_id thì tạo mới
            $addressId = $request->address_id ?: Address::create([
                'user_id'        => $user->id,
                'receiver_name'  => $request->full_name,
                'receiver_phone' => $request->phone,
                'line1'          => $request->address,
                'city'           => $request->city,
                'province'       => $request->city,
                'is_default'     => false,
            ])->id;

            $total = 0;
            $shippingCost = 0; // bạn có thể thay đổi logic tính phí ship

            $order = Order::create([
                'user_id'        => $user->id,
                'address_id'     => $addressId,
                'order_status'   => 'pending',
                'payment_status' => 'unpaid',
                'shipping_cost'  => $shippingCost,
                'discount_amount'=> 0,
                'final_amount'   => 0,
            ]);

            foreach ($cart->items as $cartItem) {
                // Trừ tồn kho
                if ($cartItem->variant_id) {
                    $variant = $cartItem->variant;
                    if (!$variant || $variant->stock < $cartItem->quantity) {
                        throw new \Exception("Biến thể {$cartItem->product->name} không đủ tồn kho");
                    }
                    $variant->decrement('stock', $cartItem->quantity);
                    $variant->product->auto_status = $variant->product->variants()->where('stock','>',0)->exists() ? 1 : 2;
                    $variant->product->save();
                } else {
                    $product = $cartItem->product;
                    if (!$product || $product->stock < $cartItem->quantity) {
                        throw new \Exception("Sản phẩm {$product->name} không đủ tồn kho");
                    }
                    $product->decrement('stock', $cartItem->quantity);
                    $product->auto_status = $product->stock > 0 ? 1 : 2;
                    $product->save();
                }

                // Tính tổng tiền từng item
                $lineTotal = $cartItem->price * $cartItem->quantity;

                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $cartItem->product_id,
                    'variant_id'   => $cartItem->variant_id,
                    'product_name' => $cartItem->product->name ?? 'Sản phẩm',
                    'quantity'     => $cartItem->quantity,
                    'price'        => $cartItem->price,
                    'total'        => $lineTotal,
                ]);

                $total += $lineTotal;
            }

            // Áp dụng coupon nếu có
            $discount = 0;
            $couponId = null;

            if ($request->coupon_code) {
                // Tìm coupon (case insensitive)
                $coupon = Coupon::whereRaw('LOWER(code) = ?', [strtolower(trim($request->coupon_code))])->first();
                
                if (!$coupon) {
                    throw new \Exception('Mã giảm giá không tồn tại');
                }

                // Kiểm tra coupon có hợp lệ không
                if (!$coupon->active) {
                    throw new \Exception('Mã giảm giá này hiện không khả dụng');
                }

                // Kiểm tra thời gian hiệu lực
                $now = now();
                if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
                    throw new \Exception('Mã giảm giá chưa có hiệu lực');
                }

                if ($coupon->ends_at && $now->gt($coupon->ends_at)) {
                    throw new \Exception('Mã giảm giá đã hết hạn');
                }

                // Kiểm tra giới hạn sử dụng
                if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
                    throw new \Exception('Mã giảm giá đã hết lượt sử dụng');
                }

                // Kiểm tra điều kiện đơn hàng tối thiểu
                if ($coupon->min_order_amount && $total < $coupon->min_order_amount) {
                    throw new \Exception('Đơn hàng chưa đạt điều kiện áp dụng mã. Đơn hàng tối thiểu: ' . number_format($coupon->min_order_amount, 0, ',', '.') . 'đ');
                }

                // Tính số tiền giảm
                $discount = $coupon->calculateDiscount($total);
                $couponId = $coupon->id;
                
                // Tăng số lần sử dụng
                $coupon->increment('used_count');
            }

            $finalAmount = max(0, $total + $shippingCost - $discount);

            // Cập nhật tổng tiền đơn hàng
            $order->update([
                'coupon_id'       => $couponId,
                'discount_amount' => $discount,
                'final_amount'    => $finalAmount,
            ]);

            // Xóa giỏ hàng
            $cart->items()->delete();

            DB::commit();

            return response()->json([
                'message' => 'Đặt hàng thành công',
                'order'   => $order->load(['items.product','items.variant','address','coupon'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout error: '.$e->getMessage());
            return response()->json([
                'message' => 'Có lỗi xảy ra khi đặt hàng',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
