<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Tạo 5 users trước
        $users = User::factory(5)->create();

        // Tạo 15 đơn hàng (3 đơn cho mỗi user)
        foreach ($users as $user) {
            // Tạo địa chỉ cho user
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

            // Tạo 3 đơn hàng cho user này
            for ($i = 1; $i <= 3; $i++) {
                $itemsTotal = 0;

                $order = Order::create([
                    'user_id' => $user->id,
                    'address_id' => $address->id,
                    'order_status' => ['pending', 'processing', 'shipped'][$i - 1],
                    'payment_status' => ['unpaid', 'paid', 'paid'][$i - 1],
                    'shipping_cost' => 50000,
                    'discount_amount' => $i == 1 ? 0 : 100000 * $i,
                    'final_amount' => 0, // Tạm tính
                    'notes' => "Ghi chú cho đơn hàng thứ {$i}",
                ]);

                // Tạo 2-4 items cho đơn hàng
                $itemCount = rand(2, 4);
                for ($j = 1; $j <= $itemCount; $j++) {
                    $price = 100000 + ($j * 50000);
                    $quantity = rand(1, 3);

                    $item = OrderItem::create([
                        'order_id' => $order->id,
                        'product_name' => "Sản phẩm {$j}",
                        'quantity' => $quantity,
                        'price' => $price,
                    ]);

                    $itemsTotal += $price * $quantity;
                }

                // Cập nhật final_amount
                $order->update([
                    'final_amount' => $itemsTotal + $order->shipping_cost - $order->discount_amount,
                ]);

                // Tạo 1-2 payments nếu đã thanh toán
                $paymentCount = $order->payment_status === 'paid' ? rand(1, 2) : 0;
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
