<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Thêm cột role và active vào bảng admins nếu chưa có
        Schema::table('admins', function (Blueprint $table) {
            if (!Schema::hasColumn('admins', 'role')) {
                $table->enum('role', ['super_admin', 'admin'])->default('admin')->after('password');
            }
            if (!Schema::hasColumn('admins', 'active')) {
                $table->boolean('active')->default(true)->after('role');
            }
        });

        // Thêm cột role vào bảng staffs nếu chưa có
        Schema::table('staffs', function (Blueprint $table) {
            if (!Schema::hasColumn('staffs', 'role')) {
                $table->enum('role', ['manager', 'staff'])->default('staff')->after('password');
            }
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            if (Schema::hasColumn('admins', 'role')) {
                $table->dropColumn('role');
            }
            if (Schema::hasColumn('admins', 'active')) {
                $table->dropColumn('active');
            }
        });

        Schema::table('staffs', function (Blueprint $table) {
            if (Schema::hasColumn('staffs', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
