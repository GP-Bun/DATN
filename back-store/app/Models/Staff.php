<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Staff extends Authenticatable
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'staffs';

    protected $fillable = [
        'name',
        'email',
        'password',
        'active',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Danh sách quyền của nhân viên
     * ✅ Được làm:
     * - Sản phẩm: thêm/sửa thông tin (giá, mô tả, tồn kho)
     * - Đơn hàng: xem, xác nhận, cập nhật trạng thái, ghi chú
     * - Đánh giá: xem và phản hồi
     *
     * 🚫 Không được làm:
     * - Thống kê doanh thu
     * - Danh mục: tạo/xóa
     * - Mã giảm giá: tạo/sửa
     * - Tài khoản: quản lý user/staff/admin
     */
    public function hasPermission(string $permission): bool
    {
        $staffPermissions = [
            // ✅ Sản phẩm - chỉ thêm/sửa, không xóa vĩnh viễn
            'view_products',
            'create_products',
            'edit_products',

            // ✅ Đơn hàng - xem, xác nhận, cập nhật, ghi chú
            'view_orders',
            'update_orders',
            'confirm_orders',

            // ✅ Đánh giá - xem và phản hồi (không xóa)
            'view_reviews',
            'reply_reviews',

            // 🚫 Không có quyền:
            // - view_statistics (thống kê doanh thu)
            // - manage_categories (danh mục)
            // - manage_coupons (mã giảm giá)
            // - manage_users (tài khoản)
            // - delete_products (xóa vĩnh viễn sản phẩm)
            // - delete_reviews (xóa đánh giá)
        ];

        return in_array($permission, $staffPermissions);
    }

    // Lấy loại tài khoản
    public function getAccountType(): string
    {
        return 'staff';
    }
}
