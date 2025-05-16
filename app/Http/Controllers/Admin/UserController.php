<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User; // <-- Đảm bảo đã import
use App\Models\Role; // <-- Import Role
use App\Models\School; // <-- Import School
use App\Models\Major; // <-- Import Major
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate; // Sử dụng Gate để authorize
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Schema; // Để kiểm tra cột rejection_reason
use Illuminate\Support\Facades\Log; // Import Log để ghi lỗi
use App\Notifications\ProfileApprovedNotification; // <<< THÊM IMPORT NÀY
use App\Notifications\ProfileRejectedNotification; // <<< THÊM IMPORT NÀY

class UserController extends Controller
{
    /**
     * Hiển thị danh sách người dùng (chủ yếu là sinh viên).
     * Tích hợp lọc theo trạng thái.
     */
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', User::class); // Kiểm tra quyền xem danh sách

        $studentRoleId = Role::where('name', 'SinhVien')->value('id');

        if (!$studentRoleId) {
            Log::error('Vai trò SinhVien không được định nghĩa trong hệ thống.');
            abort(500, 'Lỗi cấu hình hệ thống: Vai trò SinhVien không tồn tại.');
        }

        $query = User::where('role_id', $studentRoleId)
                     ->with(['role', 'school', 'major']);

        // Lọc theo trạng thái hồ sơ
        if ($request->filled('profile_status') && in_array($request->profile_status, ['pending', 'approved', 'rejected'])) {
            $query->where('profile_status', $request->profile_status);
        }
        // (Các logic lọc khác nếu cần)

        $users = $query->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         return abort(404, 'Chức năng tạo người dùng bởi Admin chưa được kích hoạt.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        return abort(404, 'Chức năng tạo người dùng bởi Admin chưa được kích hoạt.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): View
    {
        Gate::authorize('view', $user);
        $user->load(['role', 'school', 'major', 'attendances', 'diaries']);
        return view('admin.users.show', compact('user'));
    }

    /**
     * Hiển thị form sửa thông tin người dùng (sinh viên).
     */
    public function edit(User $user): View
    {
        Gate::authorize('update', $user);
        $roles = Role::orderBy('name')->get();
        $schools = School::orderBy('name')->get();
        $majors = Major::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'roles', 'schools', 'majors'));
    }

    /**
     * Cập nhật thông tin người dùng trong database.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('update', $user);

        // Validate dữ liệu (Đã sửa tên mssv ở lần trước)
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'mssv' => ['nullable', 'string', 'max:50', 'unique:users,mssv,' . $user->id],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'school_id' => ['nullable', 'exists:schools,id'],
            'major_id' => ['nullable', 'exists:majors,id'],
            'role_id' => ['required', 'exists:roles,id'],
            'profile_status' => ['required', 'in:pending,approved,rejected'],
        ]);

        // Cập nhật thông tin user
        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mssv' => $validated['mssv'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'school_id' => $validated['school_id'] ?? null,
            'major_id' => $validated['major_id'] ?? null,
            'role_id' => $validated['role_id'],
            'profile_status' => $validated['profile_status'],
        ]);

        // Cập nhật mật khẩu nếu có
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Cập nhật thông tin người dùng thành công!');
    }

    /**
     * Xóa người dùng khỏi database.
     */
    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'Bạn không thể tự xóa chính mình!');
        }

        try {
            $userName = $user->name;
            $user->delete();
            return redirect()->route('admin.users.index')->with('success', "Đã xóa người dùng '{$userName}' thành công!");
        } catch (\Exception $e) {
            Log::error("Lỗi xóa user ID {$user->id}: " . $e->getMessage());
            return redirect()->route('admin.users.index')->with('error', 'Đã xảy ra lỗi khi xóa người dùng.');
        }
    }

    // ================================================================
    // ===== PHƯƠNG THỨC DUYỆT VÀ TỪ CHỐI HỒ SƠ SINH VIÊN (Đã cập nhật) =====
    // ================================================================

    /**
     * Duyệt hồ sơ sinh viên.
     */
    public function approve(User $user): RedirectResponse
    {
        Gate::authorize('approve', $user);

        if ($user->role?->name === 'SinhVien' && $user->profile_status === 'pending') {
            $user->profile_status = 'approved';
            if (Schema::hasColumn($user->getTable(), 'rejection_reason')) {
                $user->rejection_reason = null;
            }
            $user->save();

            // <<< GỬI THÔNG BÁO DUYỆT >>>
            try {
                $user->notify(new ProfileApprovedNotification());
            } catch (\Exception $e) {
                Log::error("Lỗi gửi notification duyệt hồ sơ cho user {$user->id}: " . $e->getMessage());
            }
            // <<< KẾT THÚC GỬI THÔNG BÁO >>>

            return redirect()->back()->with('success', 'Đã duyệt hồ sơ cho: ' . $user->name);
        }

        return redirect()->back()->with('warning', 'Không thể duyệt hồ sơ này.');
    }

    /**
     * Từ chối hồ sơ sinh viên.
     */
    public function reject(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('reject', $user);

        // Validate lý do nếu form gửi lên có trường này
        // Đảm bảo form từ chối trong view admin/users/index.blade.php có input name="rejection_reason" nếu bạn muốn nhập lý do
        $validated = $request->validate([
             'rejection_reason' => 'nullable|string|max:1000'
        ]);
        $reason = $validated['rejection_reason'] ?? 'Hồ sơ chưa đáp ứng yêu cầu hoặc thiếu thông tin.'; // Lấy lý do hoặc dùng mặc định

        if ($user->role?->name === 'SinhVien' && $user->profile_status === 'pending') {
            $user->profile_status = 'rejected';

            if (Schema::hasColumn($user->getTable(), 'rejection_reason')) {
                $user->rejection_reason = $reason; // Lưu lý do
            }

            $user->save();

            // <<< GỬI THÔNG BÁO TỪ CHỐI >>>
             try {
                // Truyền lý do vào notification
                $user->notify(new ProfileRejectedNotification($reason));
             } catch (\Exception $e) {
                 Log::error("Lỗi gửi notification từ chối hồ sơ cho user {$user->id}: " . $e->getMessage());
             }
            // <<< KẾT THÚC GỬI THÔNG BÁO >>>

            return redirect()->back()->with('success', 'Đã từ chối hồ sơ cho: ' . $user->name);
        }

        return redirect()->back()->with('warning', 'Không thể từ chối hồ sơ này.');
    }
}