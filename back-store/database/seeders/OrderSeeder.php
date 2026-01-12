<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo 5 users
        $users = User::factory(5)->create();

        // Tạo 10 sản phẩm mẫu
        $products = Product::factory(10)->create();

        // Tạo biến thể cho mỗi sản phẩm
        foreach ($products as $product) {
            ProductVariant::factory(2)->create([
                'product_id' => $product->id,
            ]);
        }

        // Tạo 3 đơn hàng cho mỗi user
        foreach ($users as $user) {
            $address = Address::create([
                'user_id' => $user->id,
                'receiver_name' => $user->name,
                'receiver_phone' => '0123456789',
                'line1' => '123 Nguyễn Huệ, Q.1',
                'city' => 'Hồ Chí Minh',
                'province' => 'TP.HCM',
                'zip' => '70000',
                'is_default' => true,
            ]);

            for ($i = 1; $i <= 3; $i++) {
                $itemsTotal = 0;

                $order = Order::create([
                    'user_id' => $user->id,
                    'address_id' => $address->id,
                    'order_status' => ['pending', 'processing', 'shipped'][$i - 1],
                    'payment_status' => ['unpaid', 'paid', 'paid'][$i - 1],
                    'shipping_cost' => 50000,
                    'discount_amount' => $i == 1 ? 0 : 100000 * $i,
                    'final_amount' => 0,
                    'notes' => "Ghi chú cho đơn hàng thứ {$i}",
                ]);

                // Tạo 2-4 items cho đơn hàng
                $itemCount = rand(2, 4);
                for ($j = 1; $j <= $itemCount; $j++) {
                    $product = $products->random();
                    $variant = $product->variants()->inRandomOrder()->first();

                    // Lấy giá từ sale_price nếu có, ngược lại dùng original_price
                    $price = $variant->sale_price ?? $variant->original_price;
                    $quantity = rand(1, 3);

                    OrderItem::create([
                        'order_id'     => $order->id,
                        'product_id'   => $product->id,
                        'variant_id'   => $variant?->id,
                        'product_name' => $product->name,
                        'quantity'     => $quantity,
                        'price'        => $price,
                        'total'        => $price * $quantity,
                    ]);

                    $itemsTotal += $price * $quantity;
                }

                // Cập nhật final_amount
                $order->update([
                    'final_amount' => $itemsTotal + $order->shipping_cost - $order->discount_amount,
                ]);

                // Tạo payments nếu đã thanh toán
                if ($order->payment_status === 'paid') {
                    $paymentCount = rand(1, 2);
                    for ($p = 1; $p <= $paymentCount; $p++) {
                        Payment::create([
                            'order_id' => $order->id,
                            'amount' => $order->final_amount / $paymentCount,
                            'payment_method' => ['credit_card', 'bank_transfer', 'cash'][rand(0, 2)],
                            'payment_status' => 'completed',
                        ]);
                    }
                }
            }
        }
    }
}
