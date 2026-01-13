<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'product_id')) {
                $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null')->after('id');
            }
            if (!Schema::hasColumn('order_items', 'product_variant_id')) {
                $table->foreignId('product_variant_id')->nullable()->constrained('product_variants')->onDelete('set null')->after('product_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'product_variant_id')) {
                $table->dropColumn('product_variant_id');
            }
            if (Schema::hasColumn('order_items', 'product_id')) {
                $table->dropColumn('product_id');
            }
        });
    }
};
