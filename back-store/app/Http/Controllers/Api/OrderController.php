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
use App\Services\VnPayService;

class OrderController extends Controller
{
    // Người dùng xem danh sách đơn hàng của mình
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

    // Xem chi tiết đơn hàng
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

    //  Admin cập nhật trạng thái đơn hàng
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

    // Admin xóa đơn hàng (soft delete)
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

    // Người dùng tạo đơn hàng
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'address' => 'required|array',
            'address.receiver_name' => 'required|string|max:255',
            'address.receiver_phone' => 'required|string|max:20',
            'address.line1'         => 'required|string|max:255',
            'address.province_id'   => 'required|exists:provinces,id',
            'address.district_id'   => 'required|exists:districts,id',
            'address.ward_id'       => 'required|exists:wards,id',
            'address.zip'           => 'nullable|string|max:20',
            'payment_method'        => 'required|string|in:cod,bank_transfer,vnpay',
            'coupon_code'           => 'nullable|string',
        ]);


        // gọi hàm kiểm tra giỏ hàng
        try {
            $this->validateCartItems($request->items);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

        // Nếu hợp lệ thì mới tạo đơn
        return DB::transaction(function () use ($request) {
            $userId = $request->user()->id;

            // Nếu có address_id thì ưu tiên dùng, nếu không thì tìm hoặc tạo mới (tránh trùng lặp)
            if ($request->filled('address_id')) {
                $addressId = $request->address_id;
                // Kiểm tra xem địa chỉ này có thuộc về user không
                $exists = Address::where('id', $addressId)->where('user_id', $userId)->exists();
                if (!$exists) {
                    throw new \Exception("Địa chỉ không hợp lệ hoặc không thuộc về bạn.");
                }
            } else {
                // Tìm địa chỉ giống hệt để tránh clone quá nhiều (theo yêu cầu user)
                $address = Address::where([
                    'user_id'        => $userId,
                    'receiver_name'  => $request->address['receiver_name'],
                    'receiver_phone' => $request->address['receiver_phone'],
                    'line1'          => $request->address['line1'],
                    'province_id'    => $request->address['province_id'],
                    'district_id'    => $request->address['district_id'],
                    'ward_id'        => $request->address['ward_id'],
                ])->first();

                if (!$address) {
                    $address = Address::create([
                        'user_id'        => $userId,
                        'receiver_name'  => $request->address['receiver_name'],
                        'receiver_phone' => $request->address['receiver_phone'],
                        'line1'          => $request->address['line1'],
                        'province_id'    => $request->address['province_id'],
                        'district_id'    => $request->address['district_id'],
                        'ward_id'        => $request->address['ward_id'],
                        'zip'            => $request->address['zip'] ?? null,
                        'is_saved'       => false, // Không tự động thêm vào danh sách địa chỉ đã lưu
                    ]);
                }
                $addressId = $address->id;
            }

            $order = Order::create([
                'user_id'        => $userId,
                'address_id'     => $addressId,
                'order_status'   => 'pending',
                'payment_status' => 'unpaid',
                'final_amount'   => 0,
                'shipping_cost'  => 0,
                'discount_amount' => 0,
                'payment_method' => $request->payment_method,
            ]);

            $total = 0;

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $price = $product->price;

                if (!empty($item['variant_id'])) {
                    $variant = ProductVariant::findOrFail($item['variant_id']);
                    if ($variant->stock < $item['quantity']) {
                        throw new \Exception("Biến thể {$product->name} không đủ tồn kho");
                    }
                    $variant->decrement('stock', $item['quantity']);
                    $price = $variant->sale_price ?? $variant->original_price;
                } else {
                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Sản phẩm {$product->name} không đủ tồn kho");
                    }
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

            // Xử lý mã giảm giá
            $discount = 0;
            $couponId = null;

            if ($request->coupon_code) {
                $coupon = \App\Models\Coupon::whereRaw('LOWER(code) = ?', [strtolower(trim($request->coupon_code))])->first();

                if ($coupon && $coupon->active) {
                    $now = now();
                    if ((!$coupon->starts_at || $now->gte($coupon->starts_at)) &&
                        (!$coupon->ends_at || $now->lte($coupon->ends_at)) &&
                        (!$coupon->usage_limit || $coupon->used_count < $coupon->usage_limit) &&
                        (!$coupon->min_order_amount || $total >= $coupon->min_order_amount)) {

                        $discount = $coupon->calculateDiscount($total);
                        $couponId = $coupon->id;
                        $coupon->increment('used_count');
                    }
                }
            }

            $finalAmount = max(0, $total - $discount);
            $order->update([
                'coupon_id' => $couponId,
                'discount_amount' => $discount,
                'final_amount' => $finalAmount
            ]);

            Activity::create([
                'user_id'    => $request->user()->id,
                'action'     => 'order',
                'description' => 'Người dùng đã tạo đơn hàng #' . $order->id,
            ]);

            $order = $order->load(['items.product', 'items.variant', 'address', 'coupon']);

            // Format image URLs
            $order->items->each(function ($item) {
                if ($item->product) {
                    if ($item->product->thumbnail) {
                        $item->product->image = url('storage/' . $item->product->thumbnail);
                    } elseif ($item->product->images && is_array($item->product->images) && count($item->product->images) > 0) {
                        $item->product->image = url('storage/' . $item->product->images[0]);
                    }
                }
            });

            if ($order->payment_method === 'cod') {
                return response()->json([
                    'message' => 'Đặt hàng thành công! Thanh toán khi nhận hàng.',
                    'order'   => $order,
                    'data'    => $order
                ]);
            } elseif ($order->payment_method === 'bank_transfer') {
                $bankAccount = "123456789";
                $bankName    = "Vietcombank";
                $accountName = "CONG TY TNHH THUONG MAI";

                $qrDataString = "2|99|{$bankAccount}|{$order->final_amount}|Thanh toan don hang #{$order->id}|{$accountName}";
                $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrDataString);

                $qr_code = [
                    'data' => $qrDataString,
                    'image_url' => $qrCodeUrl,
                    'bank_account' => $bankAccount,
                    'bank_name' => $bankName,
                    'account_name' => $accountName,
                    'amount' => $order->final_amount,
                    'order_id' => $order->id
                ];

                return response()->json([
                    'message'      => 'Vui lòng quét QR để thanh toán',
                    'order'        => $order,
                    'qr_code'      => $qr_code,
                    'data'         => [
                        'order' => $order,
                        'qr_code' => $qr_code
                    ]
                ]);
            } else {
                // vnpay - tạo URL thanh toán
                $ipAddr = $request->header('x-forwarded-for') ?? $request->ip();
                $result = VnPayService::createPaymentUrl(
                    $order->id,
                    $order->final_amount,
                    $ipAddr
                );

                return response()->json([
                    'message' => 'Đang chuyển hướng đến trang thanh toán VNPay...',
                    'order'   => $order,
                    'data'    => $order,
                    'vnpay'   => [
                        'payment_url' => $result['payment_url'],
                        'txn_ref'     => $result['txn_ref'],
                        'amount'      => $result['amount'],
                    ]
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

    // Người dùng hủy đơn hàng
    public function cancel(Order $order, Request $request)
    {
        // Kiểm tra quyền: chỉ chủ đơn hàng mới được hủy
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Không có quyền hủy đơn hàng này'], 403);
        }

        // Chỉ cho phép hủy khi trạng thái là pending
        if ($order->order_status !== 'pending') {
            return response()->json(['message' => 'Chỉ có thể hủy đơn hàng khi đang chờ xử lý'], 403);
        }

        return DB::transaction(function () use ($order) {
            // Hoàn lại số lượng sản phẩm/biến thể
            foreach ($order->items as $item) {
                if ($item->variant_id) {
                    $variant = ProductVariant::find($item->variant_id);
                    if ($variant) {
                        $variant->increment('stock', $item->quantity);
                    }
                } else {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $product->increment('stock', $item->quantity);
                    }
                }
            }

            $order->update([
                'order_status' => 'cancelled'
            ]);

            Activity::create([
                'user_id'    => $order->user_id,
                'action'     => 'cancel_order',
                'description' => 'Người dùng đã hủy đơn hàng #' . $order->id,
            ]);

            return response()->json([
                'message' => 'Đơn hàng đã được hủy thành công và số lượng sản phẩm đã được hoàn lại',
                'order'   => $order
            ]);
        });
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
