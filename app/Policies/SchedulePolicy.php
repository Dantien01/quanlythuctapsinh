<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
// Bỏ Response nếu không dùng đến return Response::allow() or Response::deny()
// use Illuminate\Auth\Access\Response;

class SchedulePolicy
{
    use HandlesAuthorization;

    

    /**
     * Determine whether the user can view any models.
     * Quyết định xem người dùng có thể xem danh sách lịch trình không.
     * Chỉ Admin mới có quyền xem danh sách tất cả lịch trình.
     */
    public function viewAny(User $user): bool
    {
        // Giả sử bạn đã có hàm hasRole() trong Model User
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can view the model.
     * Quyết định xem người dùng có thể xem chi tiết một lịch trình cụ thể không.
     * Hiện tại, chỉ cho phép Admin xem.
     * (Sau này có thể mở rộng: cho phép sinh viên xem lịch của chính mình)
     */
    public function view(User $user, Schedule $schedule): bool
    {
        // Chỉ Admin mới có quyền xem bất kỳ lịch nào
        return $user->hasRole('Admin');

        // Ví dụ mở rộng sau này:
        // return $user->hasRole('Admin') || $user->id === $schedule->user_id;
    }

    /**
     * Determine whether the user can create models.
     * Quyết định xem người dùng có thể tạo lịch trình mới không.
     * Chỉ Admin mới có quyền tạo.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can update the model.
     * Quyết định xem người dùng có thể cập nhật một lịch trình không.
     * Chỉ Admin mới có quyền cập nhật.
     */
    public function update(User $user, Schedule $schedule): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can delete the model.
     * Quyết định xem người dùng có thể xóa một lịch trình không.
     * Chỉ Admin mới có quyền xóa.
     */
    public function delete(User $user, Schedule $schedule): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can restore the model.
     * (Dành cho Soft Deletes - Hiện tại không cần thiết)
     */
    public function restore(User $user, Schedule $schedule): bool
    {
        // Giữ nguyên là false nếu bạn không dùng Soft Deletes cho Schedule
        // Hoặc nếu có dùng, chỉ cho Admin restore:
        // return $user->hasRole('Admin');
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     * (Dành cho Soft Deletes - Hiện tại không cần thiết)
     */
    public function forceDelete(User $user, Schedule $schedule): bool
    {
         // Giữ nguyên là false nếu bạn không dùng Soft Deletes cho Schedule
         // Hoặc nếu có dùng, chỉ cho Admin force delete:
         // return $user->hasRole('Admin');
        return false;
    }

        /**
     * Determine whether the user can approve a change request.
     */
    public function approveChange(User $user, Schedule $schedule): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can reject a change request.
     */
    public function rejectChange(User $user, Schedule $schedule): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * (Optional) Thêm các quyền khác nếu cần sau này
     * Ví dụ: Quyền duyệt/từ chối yêu cầu thay đổi lịch
     */
    /*
    public function approveChange(User $user, Schedule $schedule): bool
    {
        return $user->hasRole('Admin');
    }

    public function rejectChange(User $user, Schedule $schedule): bool
    {
        return $user->hasRole('Admin');
    }
    */
}