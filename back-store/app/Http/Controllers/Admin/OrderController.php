<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
{
    $query = Order::with('user')->orderBy('created_at', 'desc');

    // Lọc theo tên khách hàng
    if ($request->filled('keyword')) {
        $query->whereHas('user', function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->keyword . '%');
        });
    }

    // Lọc theo trạng thái đơn hàng
    if ($request->filled('status')) {
        $query->where('order_status', $request->status);
    }

    $orders = $query->paginate(10)->appends($request->all());

    return view('admin.orders.index', compact('orders'));
}



    public function show(Order $order)
    {
        $order->load('items.product', 'items.variant', 'user', 'address', 'couponRedemptions.coupon');
        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'order_status' => 'required|in:pending,processing,shipped,delivered,cancelled'
        ]);

        $current = $order->order_status;
        $next = $request->order_status;

        // Định nghĩa luồng trạng thái hợp lệ
        $allowedTransitions = [
            'pending' => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped' => ['delivered'],
            'delivered' => [],
            'cancelled' => [],
        ];

        // Kiểm tra xem chuyển đổi có hợp lệ không 
        if (!in_array($next, $allowedTransitions[$current] ?? [])) {
            return back()->withErrors([
                'order_status' => "Không thể chuyển từ trạng thái '{$current}' sang '{$next}'."
            ]);
        }

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

    public function updatePayment(Request $request, Order $order)
    {
        $request->validate([
            'payment_status' => 'required|in:pending,paid,refunded'
        ]);

        $current = $order->payment_status;
        $next = $request->payment_status;

        $allowedPaymentTransitions = [
            'unpaid' => ['paid', 'cancelled'],
            'paid' => ['refunded'],
            'refunded' => [],
            'cancelled' => [],
        ];

        if (!in_array($next, $allowedPaymentTransitions[$current] ?? [])) {
            return back()->withErrors(['payment_status' => "Không thể chuyển từ trạng thái '{$current}' sang '{$next}'."]);
        }

        $order->update([
            'payment_status' => $request->payment_status,
            'paid_at' => $request->payment_status === 'paid' ? now() : null,
        ]);

        return back()->with('success', 'Cập nhật trạng thái thanh toán thành công!');
    }

    public function search(Request $request)
    {
        $keyword = $request->get('keyword');

        $orders = Order::with('user')
            ->whereHas('user', function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%');
            })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Trả về JSON để frontend xử lý
        return response()->json($orders);
    }
}
