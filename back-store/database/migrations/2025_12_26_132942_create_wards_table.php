<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained('districts')->cascadeOnDelete();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->string('slug')->index();
            $table->string('type')->nullable(); // Phường/Xã/Thị trấn
            $table->timestamps();
            $table->index(['district_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wards');
    }
};
