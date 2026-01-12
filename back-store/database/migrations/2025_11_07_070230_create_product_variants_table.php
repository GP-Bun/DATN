<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();

            // Khóa ngoại tới sản phẩm
            $table->foreignId('product_id')
                ->constrained('products')
                ->onDelete('cascade');

            // Khóa ngoại tới màu
            $table->foreignId('color_id')
                ->constrained('colors')
                ->onDelete('cascade');

            // Khóa ngoại tới size
            $table->foreignId('size_id')
                ->constrained('sizes')
                ->onDelete('cascade');

            // Giá gốc và giá giảm
            $table->decimal('original_price', 15, 2);
            $table->decimal('sale_price', 15, 2)->nullable();

            // Tồn kho và trạng thái
            $table->integer('stock')->default(0);
            $table->tinyInteger('status')->default(1);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
