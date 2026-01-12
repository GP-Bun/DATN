<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kiểm tra và thêm cột nếu chưa tồn tại
        if (!Schema::hasColumn('order_items', 'product_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->unsignedBigInteger('product_id')->nullable()->after('order_id');
            });
        }
        
        if (!Schema::hasColumn('order_items', 'variant_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->unsignedBigInteger('variant_id')->nullable()->after('product_id');
            });
        }
        
        if (!Schema::hasColumn('order_items', 'total')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->decimal('total', 15, 2)->default(0)->after('price');
            });
        }

        // Thêm foreign key nếu cột tồn tại và chưa có constraint
        try {
            if (Schema::hasColumn('order_items', 'product_id')) {
                Schema::table('order_items', function (Blueprint $table) {
                    $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                });
            }
        } catch (\Exception $e) {
            // Foreign key đã tồn tại, bỏ qua
        }

        try {
            if (Schema::hasColumn('order_items', 'variant_id')) {
                Schema::table('order_items', function (Blueprint $table) {
                    $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('cascade');
                });
            }
        } catch (\Exception $e) {
            // Foreign key đã tồn tại, bỏ qua
        }
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['variant_id']);
            $table->dropColumn(['product_id', 'variant_id', 'total']);
        });
    }
};

