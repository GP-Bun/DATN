<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();   // Mã hành chính
            $table->string('name');                 // Tên tỉnh/thành
            $table->string('slug')->index();        // Slug để tìm kiếm/URL
            $table->string('type')->nullable();     // Tỉnh/Thành phố
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provinces');
    }
};
