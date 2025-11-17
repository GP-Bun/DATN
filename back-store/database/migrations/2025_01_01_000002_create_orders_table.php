<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tạo bảng đơn hàng
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('address_id')->constrained()->onDelete('cascade');
            $table->string('order_status')->default('pending'); // Trạng thái: pending, processing, shipped, delivered, cancelled
            $table->string('payment_status')->default('unpaid'); // Trạng thái thanh toán: unpaid, paid, refunded
            $table->decimal('shipping_cost', 12, 2)->default(0); // Phí vận chuyển
            $table->decimal('discount_amount', 12, 2)->default(0); // Số tiền giảm
            $table->decimal('final_amount', 12, 2); // Tổng tiền cuối cùng
            $table->text('notes')->nullable(); // Ghi chú
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
