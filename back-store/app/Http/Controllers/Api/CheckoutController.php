<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Address;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        $user = $request->user();

        // Validate: có thể nhận address_id hoặc dữ liệu địa chỉ trực tiếp
        $request->validate([
            'address_id' => 'nullable|exists:addresses,id',
            // Nếu không có address_id, phải có dữ liệu địa chỉ
            'full_name' => 'required_without:address_id|string|max:255',
            'phone' => 'required_without:address_id|string|max:20',
            'address' => 'required_without:address_id|string|max:255',
            'city' => 'required_without:address_id|string|max:255',
        ]);

        // Lấy giỏ hàng của user
        $cart = Cart::where('user_id', $user->id)->with('items.product', 'items.variant')->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['message' => 'Giỏ hàng trống'], 400);
        }

        DB::beginTransaction();
        try {
            // Xử lý địa chỉ: tạo mới nếu không có address_id
            $addressId = $request->address_id;
            if (!$addressId) {
                $address = Address::create([
                    'user_id' => $user->id,
                    'receiver_name' => $request->full_name,
                    'receiver_phone' => $request->phone,
                    'line1' => $request->address,
                    'city' => $request->city,
                    'province' => $request->city,
                    'is_default' => false,
                ]);
                $addressId = $address->id;
            }

            // Tính tổng tiền
            $total = $cart->items->sum(fn($item) => $item->quantity * $item->price);

            // Tạo đơn hàng
            $order = Order::create([
                'user_id'        => $user->id,
                'address_id'     => $addressId,
                'order_status'   => 'pending',
                'payment_status' => 'unpaid',
                'total_amount'   => $total,
            ]);

            // Tạo order_items từ cart_items với đầy đủ thông tin sản phẩm
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

            // Trả về đơn hàng với đầy đủ thông tin sản phẩm
            return response()->json([
                'message' => 'Đặt hàng thành công',
                'order'   => $order->load(['items.product', 'items.variant', 'address'])
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra khi đặt hàng', 'error' => $e->getMessage()], 500);
        }
    }
}
