<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use App\Models\Staff;

class AdminStaffSeeder extends Seeder
{
    public function run()
    {
        // ========== TẠO TÀI KHOẢN ADMIN ==========
        Admin::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password123'),
                'active' => true,
            ]
        );

        // ========== TẠO TÀI KHOẢN NHÂN VIÊN ==========
        Staff::firstOrCreate(
            ['email' => 'nhanvien@example.com'],
            [
                'name' => 'Nhân Viên',
                'password' => Hash::make('password123'),
                'active' => true,
            ]
        );

        $this->command->info('✅ Đã tạo tài khoản:');
        $this->command->info('   - Admin: admin@example.com / password123');
        $this->command->info('   - Nhân viên: nhanvien@example.com / password123');
    }
}
