<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tạm tắt foreign key check để tránh lỗi 1824
        Schema::disableForeignKeyConstraints();

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->string('line1');
            $table->string('city');
            $table->string('province');
            $table->string('zip')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // Bật lại foreign key check
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
