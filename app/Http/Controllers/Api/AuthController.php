<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Xử lý yêu cầu đăng nhập của người dùng qua API.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'required|string', // Tên thiết bị để tạo token (vd: "My iPhone 15")
        ]);

        $user = User::where('email', $request->email)->first();

        // Kiểm tra người dùng và mật khẩu
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Thông tin đăng nhập không chính xác.'
            ], 401); // Unauthorized
        }

        // Kiểm tra vai trò - Chỉ cho phép SinhVien đăng nhập qua API này (ví dụ)
        // Bạn có thể bỏ qua hoặc tùy chỉnh kiểm tra vai trò này
        if (!$user->hasRole('SinhVien')) { // Giả sử bạn có phương thức hasRole() trong User model
             return response()->json([
                'message' => 'Tài khoản không được phép đăng nhập qua ứng dụng này.'
            ], 403); // Forbidden
        }

        // Kiểm tra trạng thái hồ sơ (ví dụ: chỉ cho phép 'approved')
        if ($user->profile_status !== 'approved') {
            return response()->json([
                'message' => 'Hồ sơ của bạn chưa được duyệt hoặc đang bị khóa. Vui lòng liên hệ quản trị viên.',
                'profile_status' => $user->profile_status
            ], 403); // Forbidden
        }

        // Xóa các token cũ (nếu muốn mỗi lần đăng nhập chỉ có 1 token active cho 1 device_name)
        // $user->tokens()->where('name', $request->device_name)->delete();

        // Tạo token mới
        $token = $user->createToken($request->device_name)->plainTextToken;

        return response()->json([
            'message' => 'Đăng nhập thành công!',
            'user' => [ // Chỉ trả về các thông tin cần thiết, không trả về toàn bộ object User
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'mssv' => $user->mssv,
                'role' => $user->role->name ?? null, // Giả sử có relationship role
                'profile_photo_url' => $user->profile_photo_url, // Nếu có accessor này
            ],
            'token_type' => 'Bearer',
            'access_token' => $token,
        ], 200);
    }

    /**
     * Xử lý yêu cầu đăng xuất của người dùng qua API.
     * Token hiện tại sẽ bị vô hiệu hóa.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        // Lấy user hiện tại đang xác thực bằng token
        $user = $request->user();

        if ($user) {
            // Xóa token hiện tại đang được sử dụng để xác thực request này
            $user->currentAccessToken()->delete();
            return response()->json(['message' => 'Đăng xuất thành công.'], 200);
        }

        return response()->json(['message' => 'Không có người dùng nào để đăng xuất.'], 401);
    }

    /**
     * (Tùy chọn) Lấy thông tin người dùng hiện tại đang đăng nhập.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        $user = $request->user();
        // Trả về thông tin user đã được lọc, tương tự như khi login
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'mssv' => $user->mssv,
            'role' => $user->role->name ?? null,
            'profile_photo_url' => $user->profile_photo_url,
            'profile_status' => $user->profile_status, // Có thể cần cho mobile app
            // Thêm các thông tin khác nếu mobile app cần
        ]);
    }
}