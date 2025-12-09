<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
        ]);

        $user = $request->user();

        // Lấy giỏ hàng của user
        $cart = Cart::where('user_id', $user->id)->with('items.product', 'items.variant')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Giỏ hàng trống'], 400);
        }

        DB::beginTransaction();
        try {
            // Tính tổng tiền
            $total = $cart->items->sum(fn($item) => $item->quantity * $item->price);

            // Tạo đơn hàng
            $order = Order::create([
                'user_id'        => $user->id,
                'address_id'     => $request->address_id,
                'order_status'   => 'pending',
                'payment_status' => 'unpaid',
                'total_amount'   => $total,
            ]);

            // Tạo order_items từ cart_items
            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id'     => $order->id,
                    'product_id'   => $cartItem->product_id,
                    'variant_id'   => $cartItem->variant_id,
                    'product_name' => $cartItem->product->name ?? 'Sản phẩm',
                    'quantity'     => $cartItem->quantity,
                    'price'        => $cartItem->price,
                ]);
            }

            // Xóa giỏ hàng sau khi checkout
            $cart->items()->delete();

            DB::commit();

            return response()->json([
                'message' => 'Đặt hàng thành công',
                'order'   => $order->load('items', 'address')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra khi đặt hàng', 'error' => $e->getMessage()], 500);
        }
    }
}
