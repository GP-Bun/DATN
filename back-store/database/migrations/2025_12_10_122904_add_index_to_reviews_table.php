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
        Schema::table('reviews', function (Blueprint $table) {
    $table->index('product_id');
    $table->index('user_id');
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('reviews', function (Blueprint $table) {
        $table->dropIndex('reviews_product_id_index');
        $table->dropIndex('reviews_user_id_index');
    });
}


};
