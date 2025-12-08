<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string',
            'customer_phone' => 'required|string',
            'customer_address' => 'required|string',
            'items' => 'required|array',
        ]);

        DB::beginTransaction();
        try {

            // Lưu đơn hàng
            $orderId = DB::table('orders')->insertGetId([
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_address' => $request->customer_address,
                'order_status' => 'pending',
                'total_price' => collect($request->items)->sum(function ($i) {
                    return $i['price'] * $i['quantity'];
                }),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Lưu sản phẩm trong đơn hàng
            foreach ($request->items as $item) {
                DB::table('order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'color' => $item['color'] ?? null,
                    'size' => $item['size'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Đặt hàng thành công!',
                'order_id' => $orderId
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Lỗi đặt hàng!',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
