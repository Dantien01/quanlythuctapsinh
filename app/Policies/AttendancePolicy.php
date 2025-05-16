<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;
// use Illuminate\Auth\Access\Response; // Không cần nếu chỉ trả về bool
use Illuminate\Auth\Access\HandlesAuthorization;

class AttendancePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Giữ nguyên: Chỉ Admin xem được danh sách trong trang admin
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can view the model.
     * (Thường không cần thiết cho action edit/update nếu đã check quyền update)
     * Giữ nguyên return false nếu bạn không dùng đến action 'show' riêng cho attendance
     */
    public function view(User $user, Attendance $attendance): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create models.
     * (Giữ nguyên return false nếu không muốn Admin tạo điểm danh thủ công)
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     * <<< SỬA ĐỔI CHÍNH Ở ĐÂY >>>
     */
    public function update(User $user, Attendance $attendance): bool
    {
        // Cho phép Admin cập nhật bản ghi điểm danh
        return $user->hasRole('Admin'); // Đảm bảo hàm hasRole('Admin') hoạt động đúng
    }

    /**
     * Determine whether the user can delete the model.
     * Giữ nguyên return $user->hasRole('Admin') nếu bạn muốn Admin có thể xóa.
     * Hoặc đổi thành return false nếu không muốn Admin xóa.
     */
    public function delete(User $user, Attendance $attendance): bool
    {
        // return $user->hasRole('Admin'); // Bật nếu muốn cho phép xóa
        return false; // Giữ nguyên logic cũ của bạn (không cho xóa)
    }

    /**
     * Determine whether the user can restore the model.
     * (Giữ nguyên return false)
     */
    public function restore(User $user, Attendance $attendance): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     * (Giữ nguyên return false)
     */
    public function forceDelete(User $user, Attendance $attendance): bool
    {
        return false;
    }
}