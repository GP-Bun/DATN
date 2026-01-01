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
use App\Models\Address;
use App\Http\Resources\OrderResource;

class OrderController extends Controller
{
    // 🟦 Người dùng xem danh sách đơn hàng của mình
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['items.product', 'items.variant', 'address', 'coupon'])
            ->latest()
            ->get();

        // Format image URLs cho products trong order items
        $orders->each(function ($order) {
            $order->items->each(function ($item) {
                if ($item->product) {
                    if ($item->product->thumbnail) {
                        $item->product->image = url('storage/' . $item->product->thumbnail);
                        $item->product->thumbnail_url = url('storage/' . $item->product->thumbnail);
                    } elseif ($item->product->images && is_array($item->product->images) && count($item->product->images) > 0) {
                        $item->product->image = url('storage/' . $item->product->images[0]);
                    }
                }
            });
        });

        return response()->json($orders);
    }

    // 🟩 Xem chi tiết đơn hàng
    public function show(Order $order, Request $request)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Không có quyền truy cập'], 403);
        }

        $order = $order->load(['items.product', 'items.variant', 'address', 'coupon']);

        $order->items->each(function ($item) {
            if ($item->product) {
                if ($item->product->thumbnail) {
                    $item->product->image = url('storage/' . $item->product->thumbnail);
                    $item->product->thumbnail_url = url('storage/' . $item->product->thumbnail);
                } elseif ($item->product->images && is_array($item->product->images) && count($item->product->images) > 0) {
                    $item->product->image = url('storage/' . $item->product->images[0]);
                }
            }
        });

        return response()->json($order);
    }

    // 🟨 Admin cập nhật trạng thái đơn hàng
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

        Activity::create([
            'user_id'    => $order->user_id,
            'action'     => 'update_order_status',
            'description' => 'Admin cập nhật trạng thái đơn hàng #' . $order->id . ' thành ' . $order->order_status,
        ]);

        return response()->json(['message' => 'Cập nhật trạng thái thành công', 'order' => $order]);
    }

    // 🟥 Admin xóa đơn hàng (soft delete)
    public function destroy(Order $order)
    {
        $order->delete();

        Activity::create([
            'user_id'    => $order->user_id,
            'action'     => 'delete_order',
            'description' => 'Admin đã xoá đơn hàng #' . $order->id,
        ]);

        return response()->json(['message' => 'Đơn hàng đã được xóa']);
    }

    // 🟩 Người dùng tạo đơn hàng
    public function store(Request $request)
{
    $request->validate([
        'items' => 'required|array',
        'items.*.product_id' => 'required|exists:products,id',
        'items.*.variant_id' => 'nullable|exists:product_variants,id',
        'items.*.quantity'   => 'required|integer|min:1',
        'address' => 'required|array',
        'payment_method' => 'required|string|in:cod,bank_transfer',
    ]);

    // ✅ gọi hàm kiểm tra giỏ hàng
    try {
        $this->validateCartItems($request->items);
    } catch (\Exception $e) {
        return response()->json(['message' => $e->getMessage()], 400);
    }

    // ✅ Nếu hợp lệ thì mới tạo đơn
    return DB::transaction(function () use ($request) {
        $address = Address::create([
            'user_id'       => $request->user()->id,
            'receiver_name' => $request->address['receiver_name'],
            'receiver_phone'=> $request->address['receiver_phone'],
            'line1'         => $request->address['line1'],
            'city'          => $request->address['city'],
            'province'      => $request->address['province'],
        ]);

        $order = Order::create([
            'user_id'        => $request->user()->id,
            'address_id'     => $address->id,
            'order_status'   => 'pending',
            'payment_status' => 'unpaid',
            'final_amount'   => 0,
            'shipping_cost'  => 0,
            'discount_amount'=> 0,
            'address'        => $request->address,
            'payment_method' => $request->payment_method,
        ]);

        $total = 0;

        foreach ($request->items as $item) {
            $product = Product::findOrFail($item['product_id']);
            $price = $product->price;

            if (!empty($item['variant_id'])) {
                $variant = ProductVariant::findOrFail($item['variant_id']);
                $variant->decrement('stock', $item['quantity']);
                $price = $variant->sale_price ?? $variant->original_price;
            } else {
                $product->decrement('stock', $item['quantity']);
            }

            OrderItem::create([
                'order_id'     => $order->id,
                'product_id'   => $product->id,
                'variant_id'   => $item['variant_id'] ?? null,
                'quantity'     => $item['quantity'],
                'price'        => $price,
                'product_name' => $product->name,
            ]);

            $total += $price * $item['quantity'];
        }

        $order->update(['final_amount' => $total]);

        Activity::create([
            'user_id'    => $request->user()->id,
            'action'     => 'order',
            'description'=> 'Người dùng đã tạo đơn hàng #' . $order->id,
        ]);

        if ($order->payment_method === 'cod') {
            return response()->json([
                'message' => 'Đặt hàng thành công! Thanh toán khi nhận hàng.',
                'order'   => new OrderResource($order->load('items', 'address'))
            ]);
        } else {
            $bankAccount = "123456789";
            $bankName    = "Vietcombank";
            $qrData      = "2|99|{$bankAccount}|{$order->final_amount}|Thanh toan don hang #{$order->id}";

            return response()->json([
                'message'      => 'Vui lòng quét QR để thanh toán',
                'order'        => $order->load('items.product', 'items.variant'),
                'qr_code_data' => $qrData,
                'bank_account' => $bankAccount,
                'bank_name'    => $bankName,
            ]);
        }
    });
}


    // 🟩 Người dùng hoặc webhook xác nhận thanh toán
    public function confirmPayment(Request $request, Order $order)
    {
        $request->validate([
            'transaction_id' => 'required|string',
        ]);

        // ✅ Cập nhật trạng thái thanh toán
        $order->update([
            'payment_status' => 'paid',
            'transaction_id' => $request->transaction_id,
            'paid_at'        => now(),
            'is_verified'    => true,
            'order_status'   => 'processing',
        ]);

        // ✅ Ghi log hoạt động
        Activity::create([
            'user_id'    => $order->user_id,
            'action'     => 'confirm_payment',
            'description' => 'Thanh toán thành công cho đơn hàng #' . $order->id,
        ]);

        return response()->json([
            'message' => 'Thanh toán thành công, đơn hàng đã được xác nhận',
            'order'   => $order->load('items.product', 'items.variant')
        ]);
    }

    private function validateCartItems($items)
{
    foreach ($items as $item) {
        $product = Product::findOrFail($item['product_id']);

        if ($product->status == 0 || $product->status == 2) {
            throw new \Exception("Sản phẩm {$product->name} đã hết hàng hoặc không khả dụng.");
        }

        if (!empty($item['variant_id'])) {
            $variant = ProductVariant::findOrFail($item['variant_id']);
            if ($variant->status == 2 || $variant->stock < $item['quantity']) {
                throw new \Exception("Biến thể {$product->name} không đủ hàng.");
            }
        } else {
            if ($product->stock < $item['quantity']) {
                throw new \Exception("Sản phẩm {$product->name} không đủ hàng.");
            }
        }
    }
}

}
