<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Review;

class ReviewPolicy
{
    /**
     * Quyền cập nhật review
     */
    public function update(User $user, Review $review): bool
    {
        // Chủ review hoặc admin mới được sửa
        return $user->id === $review->user_id || $user->role === 'admin';
    }

    /**
     * Quyền xoá review
     */
    public function delete(User $user, Review $review): bool
    {
        // Chủ review hoặc admin mới được xoá
        return $user->id === $review->user_id || $user->role === 'admin';
    }

    /**
     * Quyền duyệt/ẩn review
     */
    public function toggleStatus(User $user, Review $review): bool
    {
        // Chỉ admin mới có quyền duyệt/ẩn
        return $user->role === 'admin';
    }
}
