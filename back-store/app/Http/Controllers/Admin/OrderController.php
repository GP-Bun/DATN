<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with('user')->paginate(10);
        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load('items.product', 'items.variant', 'user');
        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'order_status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        // Nếu admin muốn chuyển sang trạng thái "shipped"
        if ($request->order_status === 'shipped') {
            foreach ($order->items as $item) {
                $product = $item->product;

                // Kiểm tra sản phẩm có khả dụng không (dựa trên auto_status)
                if ($product->auto_status != 1) {
                    return redirect()->back()->withErrors([
                        'order_status' => "Sản phẩm {$product->name} không khả dụng."
                    ]);
                }

                if ($item->variant_id) {
                    $variant = $item->variant;

                    if ($variant->stock < $item->quantity) {
                        return redirect()->back()->withErrors([
                            'order_status' => "Biến thể {$product->name} không đủ tồn kho."
                        ]);
                    }

                    // Trừ tồn kho biến thể
                    $variant->stock -= $item->quantity;
                    $variant->save();

                    // Cập nhật auto_status của sản phẩm cha
                    if ($product->variants()->where('stock', '>', 0)->exists()) {
                        $product->auto_status = 1; // còn hàng
                    } else {
                        $product->auto_status = 2; // hết hàng
                    }
                    $product->save();
                } else {
                    if ($product->stock < $item->quantity) {
                        return redirect()->back()->withErrors([
                            'order_status' => "Sản phẩm {$product->name} không đủ tồn kho."
                        ]);
                    }

                    // Trừ tồn kho sản phẩm
                    $product->stock -= $item->quantity;
                    $product->save();

                    // Cập nhật auto_status
                    $product->auto_status = $product->stock > 0 ? 1 : 2;
                    $product->save();
                }
            }
        }

        // Cập nhật trạng thái đơn hàng
        $order->update(['order_status' => $request->order_status]);

        return redirect()->back()->with('success', 'Cập nhật trạng thái thành công!');
    }


    public function destroy(Order $order)
    {
        $order->delete();
        return redirect()->route('admin.orders.index')->with('success', 'Xóa đơn hàng thành công!');
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
