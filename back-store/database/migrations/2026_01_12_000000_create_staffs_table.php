<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Chỉ tạo bảng nếu chưa tồn tại
        if (!Schema::hasTable('staffs')) {
            Schema::create('staffs', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email', 191)->unique();
                $table->string('password');
                $table->boolean('active')->default(true);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('staffs');
    }
};
