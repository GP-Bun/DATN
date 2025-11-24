<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('type'); // percent or fixed
            $table->decimal('value', 15, 2);
            $table->decimal('min_order_amount', 15, 2)->nullable();
            $table->decimal('max_discount', 15, 2)->nullable();
            $table->timestamp('starts_at')->nullable(); // ← đổi tên và kiểu timestamp
            $table->timestamp('ends_at')->nullable();   // ← đổi tên và kiểu timestamp
            $table->integer('usage_limit')->nullable();
            $table->integer('used_count')->default(0); // thêm nếu chưa có
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
