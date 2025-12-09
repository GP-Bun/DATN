<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;

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

        return response()->json(['message' => 'Cập nhật trạng thái thành công', 'order' => $order]);
    }

    // Admin xóa đơn hàng (soft delete)
    public function destroy(Order $order)
    {
        $order->delete();
        return response()->json(['message' => 'Đơn hàng đã được xóa']);
    }
}
