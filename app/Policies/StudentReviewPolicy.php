<?php

namespace App\Policies;

use App\Models\StudentReview;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudentReviewPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     * Chỉ Admin mới xem được danh sách nhận xét (trong trang admin).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin'); // Đảm bảo có hàm hasRole() hoặc thay bằng logic check role_id
    }

    /**
     * Determine whether the user can view the model.
     * Tạm thời chưa cần, Admin có thể xem tất cả nếu viewAny là true.
     */
    // public function view(User $user, StudentReview $studentReview): bool
    // {
    //     return $user->hasRole('Admin');
    // }

    /**
     * Determine whether the user can create models.
     * Chỉ Admin mới được tạo nhận xét.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can update the model.
     * Tạm thời không cho phép update.
     */
    // public function update(User $user, StudentReview $studentReview): bool
    // {
    //     return false; // Hoặc return $user->hasRole('Admin') && $user->id === $studentReview->reviewer_id;
    // }

    /**
     * Determine whether the user can delete the model.
     * Tạm thời không cho phép delete.
     */
    // public function delete(User $user, StudentReview $studentReview): bool
    // {
    //      return false; // Hoặc return $user->hasRole('Admin') && $user->id === $studentReview->reviewer_id;
    // }
}