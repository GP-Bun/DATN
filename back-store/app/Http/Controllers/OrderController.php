<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

/**
 * OrderController - Quản lý đơn hàng
 * Xử lý danh sách, chi tiết, cập nhật trạng thái, và xóa đơn hàng
 */
class OrderController extends Controller
{
    /**
     * Danh sách đơn hàng - hỗ trợ lọc, tìm kiếm, phân trang
     * Lọc theo: status, search (ID/tên khách), from_date, to_date
     */
    public function index(Request $request)
    {
        $query = Order::with(['user', 'address', 'items', 'payments']);

        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where('id', 'like', "%{$searchTerm}%")
                  ->orWhereHas('user', function ($q) use ($searchTerm) {
                      $q->where('name', 'like', "%{$searchTerm}%");
                  });
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('orders.index', compact('orders'));
    }

    /**
     * Chi tiết một đơn hàng
     */
    public function show($id)
    {
        $order = Order::with(['user', 'address', 'items', 'payments'])->findOrFail($id);
        return view('orders.show', compact('order'));
    }

    /**
     * Cập nhật trạng thái đơn hàng
     * Trạng thái hợp lệ: pending, processing, shipped, delivered, cancelled
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'order_status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        $order = Order::findOrFail($id);
        $order->update(['order_status' => $validated['order_status']]);

        return redirect()->route('orders.show', $id)
                       ->with('success', 'Cập nhật trạng thái thành công!');
    }

    /**
     * Xóa đơn hàng (soft delete)
     * Chỉ xóa được đơn ở trạng thái pending
     */
    public function destroy($id)
    {
        $order = Order::findOrFail($id);

        if ($order->order_status !== 'pending') {
            return redirect()->route('orders.index')
                           ->with('error', 'Chỉ xóa được đơn ở trạng thái chờ xử lý!');
        }

        $order->delete();
        return redirect()->route('orders.index')
                       ->with('success', 'Xóa đơn hàng thành công!');
    }
}
