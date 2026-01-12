<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            // Thêm cột liên kết hành chính nếu chưa có
            if (!Schema::hasColumn('addresses', 'province_id')) {
                $table->unsignedBigInteger('province_id')->nullable()->index();
            }
            if (!Schema::hasColumn('addresses', 'district_id')) {
                $table->unsignedBigInteger('district_id')->nullable()->index();
            }
            if (!Schema::hasColumn('addresses', 'ward_id')) {
                $table->unsignedBigInteger('ward_id')->nullable()->index();
            }

            // Thêm foreign key với tên rõ ràng
            $table->foreign('province_id', 'fk_addresses_province_id')
                  ->references('id')->on('provinces')
                  ->onDelete('set null');

            $table->foreign('district_id', 'fk_addresses_district_id')
                  ->references('id')->on('districts')
                  ->onDelete('set null');

            $table->foreign('ward_id', 'fk_addresses_ward_id')
                  ->references('id')->on('wards')
                  ->onDelete('set null');

            // Thêm mã hành chính để đối soát nếu chưa có
            if (!Schema::hasColumn('addresses', 'province_code')) {
                $table->string('province_code', 10)->nullable()->index();
            }
            if (!Schema::hasColumn('addresses', 'district_code')) {
                $table->string('district_code', 10)->nullable()->index();
            }
            if (!Schema::hasColumn('addresses', 'ward_code')) {
                $table->string('ward_code', 10)->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            // Xoá foreign key theo tên đã đặt
            $table->dropForeign('fk_addresses_province_id');
            $table->dropForeign('fk_addresses_district_id');
            $table->dropForeign('fk_addresses_ward_id');

            // Xoá cột mã hành chính
            $table->dropColumn(['province_code','district_code','ward_code']);
        });
    }
};
