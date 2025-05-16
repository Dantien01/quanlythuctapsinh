<?php

namespace App\Policies;

use App\Models\User;
// use Illuminate\Auth\Access\Response; // Bạn có thể dùng Response nếu muốn trả về thông báo lỗi cụ thể

class UserPolicy
{
    /**
     * Xác định xem người dùng hiện tại có thể xem danh sách người dùng không.
     * Chỉ Admin mới có quyền xem danh sách.
     */
    public function viewAny(User $currentUser): bool
    {
        // Sử dụng optional chaining (?->) để an toàn hơn nếu role có thể null
        return $currentUser->role?->name === 'Admin';
    }

    /**
     * Xác định xem người dùng hiện tại có thể xem chi tiết một người dùng cụ thể không.
     * Chỉ Admin mới có quyền xem chi tiết.
     */
    public function view(User $currentUser, User $user): bool
    {
         return $currentUser->role?->name === 'Admin';
    }

    /**
     * Xác định xem người dùng hiện tại có thể tạo người dùng mới không.
     * Chỉ Admin mới có quyền tạo (nếu chức năng này được sử dụng).
     */
    public function create(User $currentUser): bool
    {
         return $currentUser->role?->name === 'Admin';
    }

    /**
     * Xác định xem người dùng hiện tại có thể cập nhật thông tin một người dùng cụ thể không.
     * Chỉ Admin mới có quyền cập nhật.
     */
    public function update(User $currentUser, User $user): bool
    {
         // Có thể thêm logic không cho Admin sửa thông tin của Admin khác nếu cần
         // Ví dụ: Không cho sửa nếu user đang sửa cũng là Admin và không phải là chính mình
         // if ($user->role?->name === 'Admin' && $currentUser->id !== $user->id) {
         //     return false;
         // }
         return $currentUser->role?->name === 'Admin';
    }

    /**
     * Xác định xem người dùng hiện tại có thể xóa một người dùng cụ thể không.
     * Chỉ Admin mới có quyền xóa và không được tự xóa mình.
     */
    public function delete(User $currentUser, User $user): bool
    {
        // Admin không thể tự xóa chính mình
        if ($currentUser->id === $user->id) {
            return false;
        }

        // Có thể thêm logic không cho Admin xóa Admin khác nếu cần
        // if ($user->role?->name === 'Admin') {
        //     return false;
        // }

        // Chỉ Admin mới có quyền xóa (và đã qua kiểm tra tự xóa)
        return $currentUser->role?->name === 'Admin';
    }

    // ==============================================================
    // ===== THÊM CÁC PHƯƠNG THỨC QUYỀN DUYỆT/TỪ CHỐI HỒ SƠ =====
    // ==============================================================

    /**
     * Xác định xem người dùng hiện tại (Admin) có thể DUYỆT hồ sơ của người dùng khác không.
     * Yêu cầu:
     * 1. Người thực hiện phải là Admin.
     * 2. Người bị tác động phải là SinhVien.
     * 3. Hồ sơ của người bị tác động phải đang ở trạng thái 'pending'.
     */
    public function approve(User $currentUser, User $user): bool
    {
        return $currentUser->role?->name === 'Admin'          // Phải là Admin
            && $user->role?->name === 'SinhVien'            // User bị tác động phải là SinhVien
            && $user->profile_status === 'pending';         // Trạng thái phải là chờ duyệt
    }

     /**
     * Xác định xem người dùng hiện tại (Admin) có thể TỪ CHỐI hồ sơ của người dùng khác không.
     * Yêu cầu:
     * 1. Người thực hiện phải là Admin.
     * 2. Người bị tác động phải là SinhVien.
     * 3. Hồ sơ của người bị tác động phải đang ở trạng thái 'pending'.
     */
    public function reject(User $currentUser, User $user): bool
    {
        return $currentUser->role?->name === 'Admin'          // Phải là Admin
            && $user->role?->name === 'SinhVien'            // User bị tác động phải là SinhVien
            && $user->profile_status === 'pending';         // Trạng thái phải là chờ duyệt
    }


    /*
    // === Các quyền khác nếu bạn sử dụng Soft Deletes ===

    // Xác định xem người dùng hiện tại có thể khôi phục người dùng đã xóa mềm không.
    public function restore(User $currentUser, User $user): bool
    {
        return $currentUser->role?->name === 'Admin';
    }

    // Xác định xem người dùng hiện tại có thể xóa vĩnh viễn người dùng không.
    public function forceDelete(User $currentUser, User $user): bool
    {
        // Thường chỉ Super Admin hoặc có điều kiện rất nghiêm ngặt
        // return $currentUser->isSuperAdmin() && $currentUser->id !== $user->id;
        return false; // Mặc định không cho xóa vĩnh viễn qua giao diện
    }
    */
}