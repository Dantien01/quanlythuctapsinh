<?php

namespace App\Policies;

use App\Models\School; // Import Model School
use App\Models\User;   // Import Model User
use Illuminate\Auth\Access\HandlesAuthorization; // Sử dụng trait này

class SchoolPolicy
{
    use HandlesAuthorization; // Sử dụng trait

    // Ai được xem danh sách trường?
    public function viewAny(User $user): bool
    {
        return $user->role->name === 'Admin';
    }

    // Ai được xem chi tiết một trường? (Tạm thời giống viewAny)
    public function view(User $user, School $school): bool
    {
        return $user->role->name === 'Admin';
    }

    // Ai được tạo trường mới?
    public function create(User $user): bool
    {
        return $user->role->name === 'Admin';
    }

    // Ai được cập nhật trường?
    public function update(User $user, School $school): bool
    {
        return $user->role->name === 'Admin';
    }

    // Ai được xóa trường?
    public function delete(User $user, School $school): bool
    {
        return $user->role->name === 'Admin';
    }

    // Các phương thức khác như restore, forceDelete (nếu dùng Soft Deletes)
}