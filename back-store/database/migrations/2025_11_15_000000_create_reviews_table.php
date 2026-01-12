<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('user_name')->nullable(); // Tên người dùng (nếu không đăng nhập)
            $table->string('user_email')->nullable(); // Email người dùng (nếu không đăng nhập)
            $table->tinyInteger('rating')->default(5)->comment('1-5 sao');
            $table->text('comment')->nullable();
            $table->tinyInteger('status')->default(1)->comment('0=Ẩn, 1=Hiển thị');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};

