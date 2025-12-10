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
    Schema::table('orders', function (Blueprint $table) {
        // Thêm coupon_id
        if (!Schema::hasColumn('orders', 'coupon_id')) {
            $table->foreignId('coupon_id')->nullable()->constrained('coupons')->onDelete('set null');
        }

        // Sửa final_amount để có default
        $table->decimal('final_amount', 15, 2)->default(0)->change();
    });
}

public function down(): void
{
    Schema::table('orders', function (Blueprint $table) {
        // Xóa coupon_id nếu rollback
        if (Schema::hasColumn('orders', 'coupon_id')) {
            $table->dropConstrainedForeignId('coupon_id');
        }

        // Bỏ default của final_amount
        $table->decimal('final_amount', 15, 2)->change();
    });
}

};
