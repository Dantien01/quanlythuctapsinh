<?php

namespace App\Http\Controllers\Auth; // Namespace cho controller Auth

use App\Http\Controllers\Controller; // Kế thừa Controller cơ bản
use App\Models\User;                 // Model User
use App\Models\Role;                 // Model Role (Thêm cái này)
use Illuminate\Auth\Events\Registered; // Sự kiện khi đăng ký
use Illuminate\Http\RedirectResponse; // Kiểu trả về cho redirect
use Illuminate\Http\Request;         // Lớp Request để lấy dữ liệu form
use Illuminate\Support\Facades\Auth; // Facade Auth (Thực ra không cần dùng trong store mới)
use Illuminate\Support\Facades\Hash; // Facade Hash để mã hóa mật khẩu
use Illuminate\Validation\Rules;     // Lớp Rules để validate password
use Illuminate\View\View;            // Kiểu trả về cho view (phương thức create)

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     * Hàm này hiển thị form đăng ký
     */
    public function create(): View
    {
        // Bạn có thể truyền danh sách Schools, Majors ra đây nếu muốn chọn ngay lúc đăng ký
        // $schools = \App\Models\School::orderBy('name')->get();
        // $majors = \App\Models\Major::orderBy('name')->get();
        // return view('auth.register', compact('schools', 'majors'));

        // Hoặc chỉ trả về view mặc định
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     * Hàm này xử lý khi người dùng nhấn nút đăng ký
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // --- ĐOẠN CODE NÀY GIỐNG NHƯ HƯỚNG DẪN TRƯỚC ---
        // Validate dữ liệu từ form đăng ký
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
             // Thêm validation cho các trường SV khác nếu có trong form register
             // 'mssv' => ['required', 'string', 'max:20', 'unique:'.User::class],
             // 'phone' => ['nullable', 'string', 'max:20'],
             // 'school_id' => ['required', 'integer', 'exists:schools,id'],
             // 'major_id' => ['required', 'integer', 'exists:majors,id'],
        ]);

        // Tìm Role 'SinhVien'
        $studentRole = Role::where('name', 'SinhVien')->first();

        if (!$studentRole) {
            // Log::error('Không tìm thấy vai trò SinhVien khi đăng ký.'); // Nên ghi log
            return back()->withInput()->with('error', 'Đã có lỗi xảy ra trong quá trình đăng ký.');
        }

        // Tạo user mới
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $studentRole->id,
            'profile_status' => 'pending',
             // 'mssv' => $request->mssv, // Nếu có
             // 'phone' => $request->phone, // Nếu có
             // 'school_id' => $request->school_id, // Nếu có
             // 'major_id' => $request->major_id, // Nếu có
        ]);

        // Bắn sự kiện Registered
        event(new Registered($user));

        // Chuyển hướng về trang login với thông báo
        return redirect()->route('login')->with('status', 'Đăng ký thành công! Hồ sơ của bạn đang chờ phê duyệt bởi quản trị viên.');
        // --- KẾT THÚC ĐOẠN CODE GIỐNG HƯỚNG DẪN TRƯỚC ---
    }
}