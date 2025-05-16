<?php

namespace App\Policies;

use App\Models\Diary;
use App\Models\User;
// use Illuminate\Auth\Access\Response; // Không cần thiết nếu chỉ trả về bool
use Illuminate\Auth\Access\HandlesAuthorization;

class DiaryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     * - Admin có thể xem danh sách (controller sẽ lấy tất cả).
     * - Sinh viên có thể xem danh sách (controller của SV sẽ lọc chỉ của họ).
     */
    public function viewAny(User $user): bool // Giữ nguyên
    {
        return $user->hasRole('Admin') || $user->hasRole('SinhVien');
    }

    /**
     * Determine whether the user can view the model.
     * - Admin có thể xem bất kỳ nhật ký nào.
     * - Sinh viên chỉ có thể xem nhật ký của chính mình.
     */
    public function view(User $user, Diary $diary): bool // Giữ nguyên
    {
        if ($user->hasRole('Admin')) {
            return true;
        }
        if ($user->hasRole('SinhVien')) {
            return $user->id === $diary->user_id;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     * - Chỉ Sinh viên mới được tạo nhật ký.
     */
    public function create(User $user): bool // Giữ nguyên
    {
        return $user->hasRole('SinhVien');
    }

    /**
     * Determine whether the user can update the model.
     * - Admin có thể cập nhật BẤT KỲ nhật ký nào (để thêm nhận xét/đánh giá).
     * - Sinh viên chỉ có thể cập nhật nhật ký của chính mình (để sửa nội dung).
     */
    public function update(User $user, Diary $diary): bool // <<< CẬP NHẬT LOGIC Ở ĐÂY
    {
        // Nếu là Admin, cho phép cập nhật (để review)
        if ($user->hasRole('Admin')) {
            return true;
        }
        // Nếu là Sinh viên, chỉ cho phép cập nhật nếu là chủ sở hữu
        if ($user->hasRole('SinhVien')) {
            return $user->id === $diary->user_id;
        }
        // Các vai trò khác không được cập nhật
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     * - Chỉ Sinh viên mới được xóa nhật ký của chính mình.
     * - (Tạm thời chưa cho Admin quyền này)
     */
    public function delete(User $user, Diary $diary): bool // Giữ nguyên
    {
        return $user->hasRole('SinhVien') && $user->id === $diary->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     * (Chỉ cần nếu bạn dùng Soft Deletes)
     */
    // public function restore(User $user, Diary $diary): bool
    // {
    //     // Thường thì giống quyền update hoặc delete
    //     return $user->hasRole('SinhVien') && $user->id === $diary->user_id;
    // }

    /**
     * Determine whether the user can permanently delete the model.
     * (Chỉ cần nếu bạn dùng Soft Deletes)
     */
    // public function forceDelete(User $user, Diary $diary): bool
    // {
    //     // Thường thì giống quyền update hoặc delete, hoặc chỉ Admin cấp cao
    //     return $user->hasRole('SinhVien') && $user->id === $diary->user_id;
    // }
}