<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Admin;
use App\Models\Staff;
use App\Models\User;

class MergeAccounts extends Command
{
    protected $signature = 'merge:accounts';
    protected $description = 'Gộp dữ liệu từ bảng admins và staffs vào bảng users';

    public function handle()
    {
        $this->info('Đang gộp admins...');

        foreach (Admin::all() as $admin) {
            User::updateOrCreate(
                ['email' => $admin->email],
                [
                    'name' => $admin->name,
                    'password' => $admin->password,
                    'role' => 'admin',
                    'active' => $admin->active,
                ]
            );
        }

        $this->info('Đang gộp staffs...');

        foreach (Staff::all() as $staff) {
            User::updateOrCreate(
                ['email' => $staff->email],
                [
                    'name' => $staff->name,
                    'password' => $staff->password,
                    'role' => 'staff',
                    'active' => $staff->active,
                ]
            );
        }

        $this->info('✅ Gộp dữ liệu thành công!');
    }
}
