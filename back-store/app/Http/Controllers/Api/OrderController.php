<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use App\Models\Activity;

class OrderController extends Controller
{
    // Người dùng xem danh sách đơn hàng của mình
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('items')
            ->latest()
            ->get();

        return response()->json($orders);
    }

    // Xem chi tiết đơn hàng
    public function show(Order $order, Request $request)
    {
        // Chỉ cho phép user xem đơn hàng của chính mình
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Không có quyền truy cập'], 403);
        }

        return response()->json($order->load('items'));
    }

    // Admin cập nhật trạng thái đơn hàng
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'order_status' => 'required|string',
            'payment_status' => 'nullable|string'
        ]);

        $order->update([
            'order_status' => $request->order_status,
            'payment_status' => $request->payment_status ?? $order->payment_status,
        ]);

         // ✅ Ghi log hoạt động
        Activity::create([
            'user_id'    => $order->user_id,
            'action'     => 'update_order_status',
            'description'=> 'Admin cập nhật trạng thái đơn hàng #' . $order->id . ' thành ' . $order->order_status,
        ]);

        return response()->json(['message' => 'Cập nhật trạng thái thành công', 'order' => $order]);
    }

    // Admin xóa đơn hàng (soft delete)
    public function destroy(Order $order)
    {
        $order->delete();

        // ✅ Ghi log hoạt động
        Activity::create([
            'user_id'    => $order->user_id,
            'action'     => 'delete_order',
            'description'=> 'Admin đã xoá đơn hàng #' . $order->id,
        ]);

        return response()->json(['message' => 'Đơn hàng đã được xóa']);
    }

    // Người dùng tạo đơn hàng
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'address' => 'required|array',
        ]);

        return DB::transaction(function () use ($request) {
            $order = Order::create([
                'user_id' => $request->user()->id,
                'order_status' => 'pending',
                'payment_status' => 'unpaid',
                'total_amount' => 0,
                'address' => $request->address,
            ]);

            $total = 0;

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                // ✅ Kiểm tra trạng thái sản phẩm
                if ($product->status != 1) {
                    throw new \Exception("Sản phẩm {$product->name} không khả dụng");
                }

                // Nếu có variant thì kiểm tra tồn kho variant
                if (!empty($item['variant_id'])) {
                    $variant = ProductVariant::findOrFail($item['variant_id']);

                    if ($variant->stock < $item['quantity']) {
                        throw new \Exception("Sản phẩm {$product->name} - biến thể không đủ hàng");
                    }

                    // Trừ tồn kho
                    $variant->stock -= $item['quantity'];
                    $variant->save();

                    $price = $variant->sale_price ?? $variant->original_price;
                } else {
                    // Nếu không có variant thì kiểm tra tồn kho sản phẩm
                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Sản phẩm {$product->name} không đủ hàng");
                    }

                    $product->stock -= $item['quantity'];
                    $product->save();

                    $price = $product->price;
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'variant_id' => $item['variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $price,
                ]);

                $total += $price * $item['quantity'];
            }

            $order->update(['total_amount' => $total]);

            // ✅ Ghi log hoạt động
            Activity::create([
                'user_id'    => $request->user()->id,
                'action'     => 'order',
                'description'=> 'Người dùng đã tạo đơn hàng #' . $order->id,
            ]);

            return response()->json([
                'message' => 'Đặt hàng thành công',
                'order' => $order->load('items.product', 'items.variant')
            ]);
        });
    }
}
