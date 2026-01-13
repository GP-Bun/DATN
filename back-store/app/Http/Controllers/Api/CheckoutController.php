<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\Address;
use App\Models\Coupon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CheckoutController extends Controller
{
    public function checkout(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'address_id'   => 'nullable|exists:addresses,id',
            'full_name'    => 'required_without:address_id|string|max:255',
            'phone'        => 'required_without:address_id|string|max:20',
            'address'      => 'required_without:address_id|string|max:255',
            'province_id'  => 'required_without:address_id|exists:provinces,id',
            'district_id'  => 'required_without:address_id|exists:districts,id',
            'ward_id'      => 'required_without:address_id|exists:wards,id',
            'city'         => 'nullable|string', // Keep for backward compatibility if needed, but not used in DB
            'province'     => 'nullable|string',
            'payment_method' => 'required|string|in:cod,bank_transfer,vnpay',
            'coupon_code'  => 'nullable|string',
            'cart_item_ids' => 'nullable|array',
            'cart_item_ids.*' => 'integer|exists:cart_items,id',
        ]);

        $cart = Cart::where('user_id', $user->id)
            ->with(['items.product', 'items.variant'])
            ->first();

        if (!$cart) {
            return response()->json(['message' => 'Giỏ hàng không tồn tại'], 400);
        }

        $itemsToProcess = $cart->items;
        if ($request->has('cart_item_ids') && !empty($request->cart_item_ids)) {
            $itemsToProcess = $cart->items->whereIn('id', $request->cart_item_ids);
        }

        if ($itemsToProcess->isEmpty()) {
            return response()->json(['message' => 'Giỏ hàng trống hoặc không có sản phẩm được chọn'], 400);
        }

        // Kiểm tra tất cả items có product không
        foreach ($itemsToProcess as $item) {
            if (!$item->product) {
                return response()->json(['message' => "Sản phẩm trong giỏ hàng không tồn tại"], 400);
            }
        }

        DB::beginTransaction();
        try {
            // Nếu không có address_id thì tìm hoặc tạo mới (tránh trùng lặp)
            try {
                if ($request->filled('address_id')) {
                    $addressId = $request->address_id;
                    // Kiểm tra xem địa chỉ này có thuộc về user không
                    if (!Address::where('id', $addressId)->where('user_id', $user->id)->exists()) {
                        throw new \Exception("Địa chỉ không hợp lệ.");
                    }
                } else {
                    // Check duplicate
                    $existingAddress = Address::where([
                        'user_id'        => $user->id,
                        'receiver_name'  => $request->full_name,
                        'receiver_phone' => $request->phone,
                        'line1'          => $request->address,
                        'province_id'    => $request->province_id,
                        'district_id'    => $request->district_id,
                        'ward_id'        => $request->ward_id,
                    ])->first();

                    if ($existingAddress) {
                        $addressId = $existingAddress->id;
                    } else {
                        $addressId = Address::create([
                            'user_id'        => $user->id,
                            'receiver_name'  => $request->full_name,
                            'receiver_phone' => $request->phone,
                            'line1'          => $request->address,
                            'province_id'    => $request->province_id,
                            'district_id'    => $request->district_id,
                            'ward_id'        => $request->ward_id,
                            'is_default'     => false,
                            'is_saved'       => false, // Không tự động thêm vào danh sách địa chỉ đã lưu
                        ])->id;
                    }
                }
            } catch (\Exception $e) {
                throw new \Exception("Lỗi xử lý địa chỉ: " . $e->getMessage());
            }

            $total = 0;
            $shippingCost = 0; // bạn có thể thay đổi logic tính phí ship

            $order = Order::create([
                'user_id'        => $user->id,
                'address_id'     => $addressId,
                'order_status'   => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $request->payment_method,
                'shipping_cost'  => $shippingCost,
                'discount_amount'=> 0,
                'final_amount'   => 0,
            ]);

            foreach ($itemsToProcess as $cartItem) {
                try {
                    $product = $cartItem->product;
                    if (!$product) {
                        throw new \Exception("Sản phẩm không tồn tại");
                    }

                    // Trừ tồn kho
                    if ($cartItem->variant_id) {
                        $variant = $cartItem->variant;
                        if (!$variant) {
                            throw new \Exception("Biến thể sản phẩm không tồn tại");
                        }
                        if ($variant->stock < $cartItem->quantity) {
                            throw new \Exception("Biến thể {$product->name} không đủ tồn kho (còn {$variant->stock}, cần {$cartItem->quantity})");
                        }
                        $variant->decrement('stock', $cartItem->quantity);
                        // Cập nhật trạng thái sản phẩm
                        if ($product->variants()->where('stock', '>', 0)->exists()) {
                            $product->auto_status = 1;
                        } else {
                            $product->auto_status = 2;
                        }
                        $product->save();
                    } else {
                        if ($product->stock < $cartItem->quantity) {
                            throw new \Exception("Sản phẩm {$product->name} không đủ tồn kho (còn {$product->stock}, cần {$cartItem->quantity})");
                        }
                        $product->decrement('stock', $cartItem->quantity);
                        $product->auto_status = $product->stock > 0 ? 1 : 2;
                        $product->save();
                    }

                    // Tính tổng tiền từng item
                    $lineTotal = $cartItem->price * $cartItem->quantity;

                    // Tạo order item
                    $orderItemData = [
                        'order_id'     => $order->id,
                        'product_name' => $product->name,
                        'quantity'     => $cartItem->quantity,
                        'price'        => $cartItem->price,
                    ];

                    // Chỉ thêm các cột nếu chúng tồn tại trong database
                    if (Schema::hasColumn('order_items', 'product_id')) {
                        $orderItemData['product_id'] = $cartItem->product_id;
                    }
                    
                    if ($cartItem->variant_id && Schema::hasColumn('order_items', 'variant_id')) {
                        $orderItemData['variant_id'] = $cartItem->variant_id;
                    }
                    
                    if (Schema::hasColumn('order_items', 'total')) {
                        $orderItemData['total'] = $lineTotal;
                    }

                    OrderItem::create($orderItemData);

                    $total += $lineTotal;
                } catch (\Exception $e) {
                    $productName = $product ? ($product->name ? $product->name : 'Unknown') : 'Unknown';
                    throw new \Exception("Lỗi xử lý sản phẩm '{$productName}': " . $e->getMessage());
                }
            }

            // Áp dụng coupon nếu có
            $discount = 0;
            $couponId = null;

            if ($request->coupon_code) {
                // Tìm coupon (case insensitive)
                $coupon = Coupon::whereRaw('LOWER(code) = ?', [strtolower(trim($request->coupon_code))])->first();
                
                if (!$coupon) {
                    throw new \Exception('Mã giảm giá không tồn tại');
                }

                // Kiểm tra coupon có hợp lệ không
                if (!$coupon->active) {
                    throw new \Exception('Mã giảm giá này hiện không khả dụng');
                }

                // Kiểm tra thời gian hiệu lực
                $now = now();
                if ($coupon->starts_at && $now->lt($coupon->starts_at)) {
                    throw new \Exception('Mã giảm giá chưa có hiệu lực');
                }

                if ($coupon->ends_at && $now->gt($coupon->ends_at)) {
                    throw new \Exception('Mã giảm giá đã hết hạn');
                }

                // Kiểm tra giới hạn sử dụng
                if ($coupon->usage_limit && $coupon->used_count >= $coupon->usage_limit) {
                    throw new \Exception('Mã giảm giá đã hết lượt sử dụng');
                }

                // Kiểm tra điều kiện đơn hàng tối thiểu
                if ($coupon->min_order_amount && $total < $coupon->min_order_amount) {
                    throw new \Exception('Đơn hàng chưa đạt điều kiện áp dụng mã. Đơn hàng tối thiểu: ' . number_format($coupon->min_order_amount, 0, ',', '.') . 'đ');
                }

                // Tính số tiền giảm
                $discount = $coupon->calculateDiscount($total);
                $couponId = $coupon->id;
                
                // Tăng số lần sử dụng
                $coupon->increment('used_count');
            }

            $finalAmount = max(0, $total + $shippingCost - $discount);

            // Cập nhật tổng tiền đơn hàng
            $order->update([
                'coupon_id'       => $couponId,
                'discount_amount' => $discount,
                'final_amount'    => $finalAmount,
            ]);

            // Xóa các sản phẩm đã chọn khỏi giỏ hàng
            // CHỈ XOÁ NGAY NẾU LÀ COD. Với chuyển khoản, sản phẩm sẽ ở lại giỏ cho đến khi thanh toán thành công 
            // hoặc người dùng chủ động xoá, để tránh mất hàng khi chưa thanh toán xong.
            if ($request->payment_method === 'cod') {
                if ($request->has('cart_item_ids') && !empty($request->cart_item_ids)) {
                    $cart->items()->whereIn('id', $request->cart_item_ids)->delete();
                } else {
                    $cart->items()->delete();
                }
            }

            DB::commit();

            // Load order với relationships
            $order = $order->load(['items.product','items.variant','address','coupon']);
            
            // Format image URLs cho products trong order items
            $order->items->each(function ($item) {
                if ($item->product) {
                    // Chuyển đổi thumbnail thành URL đầy đủ
                    if ($item->product->thumbnail) {
                        $item->product->image = url('storage/' . $item->product->thumbnail);
                        $item->product->thumbnail_url = url('storage/' . $item->product->thumbnail);
                    } elseif ($item->product->images && is_array($item->product->images) && count($item->product->images) > 0) {
                        // Nếu không có thumbnail, lấy ảnh đầu tiên
                        $item->product->image = url('storage/' . $item->product->images[0]);
                    }
                }
            });

            $response = [
                'message' => 'Đặt hàng thành công',
                'order'   => $order
            ];

            // Nếu là chuyển khoản, tạo QR code
            if ($request->payment_method === 'bank_transfer') {
                $bankAccount = "123456789";
                $bankName    = "Vietcombank";
                $accountName = "CONG TY TNHH THUONG MAI";
                
                // Tạo QR code data theo chuẩn VietQR
                $qrData = "2|99|{$bankAccount}|{$finalAmount}|Thanh toan don hang #{$order->id}|{$accountName}";
                
                // Tạo URL QR code image (sử dụng API online)
                $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrData);
                
                $response['qr_code'] = [
                    'data' => $qrData,
                    'image_url' => $qrCodeUrl,
                    'bank_account' => $bankAccount,
                    'bank_name' => $bankName,
                    'account_name' => $accountName,
                    'amount' => $finalAmount,
                    'order_id' => $order->id
                ];
            }

            return response()->json($response);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Lỗi xác thực dữ liệu',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'user_id' => $user ? $user->id : null
            ]);
            return response()->json([
                'message' => $e->getMessage() ?: 'Có lỗi xảy ra khi đặt hàng',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
