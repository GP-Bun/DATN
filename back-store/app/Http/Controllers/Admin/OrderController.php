<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(){
        $orders = Order::with('user')->paginate(10);
        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order){
        $order->load('items.product','items.variant','user');
        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order){
        $request->validate([
            'order_status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        $order->update(['order_status' => $request->order_status]);
        return redirect()->back()->with('success','Cập nhật trạng thái thành công!');
    }

    public function destroy(Order $order){
        $order->delete();
        return redirect()->route('admin.orders.index')->with('success','Xóa đơn hàng thành công!');
    }

    public function applyCoupon(Request $request)
{
    $orderAmount = $request->order_amount;
    $code = $request->coupon_code;

    $coupon = Coupon::where('code', $code)->first();

    if (!$coupon || !$coupon->isValid($orderAmount)) {
        return back()->withErrors(['coupon_code' => 'Voucher không hợp lệ hoặc đã hết hạn.']);
    }

    $discount = $coupon->calculateDiscount($orderAmount);

    // Cập nhật số lần sử dụng
    $coupon->increment('used_count');

    $finalAmount = $orderAmount - $discount;

    return back()->with('success', "Áp dụng voucher thành công! Giảm giá: {$discount}, Tổng thanh toán: {$finalAmount}");
}

}
