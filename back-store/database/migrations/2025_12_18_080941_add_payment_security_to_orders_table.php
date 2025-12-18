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
        $table->string('payment_method')->default('cod')->after('payment_status');
        $table->string('transaction_id')->nullable()->after('payment_method');
        $table->timestamp('paid_at')->nullable()->after('transaction_id');
        $table->boolean('is_verified')->default(false)->after('paid_at');
    });
}

public function down(): void
{
    Schema::table('orders', function (Blueprint $table) {
        $table->dropColumn(['payment_method', 'transaction_id', 'paid_at', 'is_verified']);
    });
}

};
