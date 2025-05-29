<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException; // Mặc dù không dùng trực tiếp, giữ lại nếu có ý định throw

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
            'device_name' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Thông tin đăng nhập không chính xác.'
            ], 401);
        }

        // =====================================================================
        // === TẠM THỜI COMMENT OUT ĐỂ LẤY TOKEN ADMIN CHO MỤC ĐÍCH TEST ===
        // ===   SAU KHI TEST XONG, HÃY UNCOMMENT LẠI NẾU CẦN THIẾT    ===
        // =====================================================================
        /*
        if (!$user->hasRole('SinhVien')) {
             return response()->json([
                'message' => 'Tài khoản không được phép đăng nhập qua ứng dụng này.'
            ], 403);
        }
        */
        // =====================================================================

        // Kiểm tra trạng thái hồ sơ (ví dụ: chỉ cho phép 'approved' cho SinhVien)
        // Nếu Admin không cần check profile_status, bạn có thể đặt điều kiện này
        if ($user->hasRole('SinhVien') && $user->profile_status !== 'approved') {
            return response()->json([
                'message' => 'Hồ sơ của bạn chưa được duyệt hoặc đang bị khóa. Vui lòng liên hệ quản trị viên.',
                'profile_status' => $user->profile_status
            ], 403);
        }

        // Xóa các token cũ (nếu muốn mỗi lần đăng nhập chỉ có 1 token active cho 1 device_name)
        // Cân nhắc việc này, có thể gây phiền toái nếu người dùng đăng nhập trên nhiều thiết bị
        // với cùng device_name (mặc dù device_name nên là duy nhất cho mỗi lần tạo token)
        // $user->tokens()->where('name', $request->device_name)->delete();

        // Tạo token mới
        $token = $user->createToken($request->device_name)->plainTextToken;

        // Sử dụng UserResource để chuẩn hóa output (khuyến nghị)
        // Giả sử bạn đã có App\Http\Resources\UserResource
        // Nếu chưa có, bạn có thể trả về mảng trực tiếp như code gốc của bạn
        $userResource = new \App\Http\Resources\UserResource($user->loadMissing('role'));


        return response()->json([
            'message' => 'Đăng nhập thành công!',
            // 'user' => $userResource, // Nếu dùng UserResource
            'user' => [ // Giữ lại cấu trúc gốc của bạn nếu chưa có UserResource
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'mssv' => $user->mssv,
                'role' => $user->role->name ?? null,
                'profile_photo_url' => $user->profile_photo_url,
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
        $user = $request->user();

        if ($user) {
            $user->currentAccessToken()->delete();
            return response()->json(['message' => 'Đăng xuất thành công.'], 200);
        }
        // Dòng này gần như không bao giờ đạt được nếu middleware auth:sanctum hoạt động đúng
        return response()->json(['message' => 'Không có người dùng nào để đăng xuất hoặc token không hợp lệ.'], 401);
    }

    /**
     * Lấy thông tin người dùng hiện tại đang đăng nhập.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        $user = $request->user();
        // Sử dụng UserResource để chuẩn hóa output
        // $userResource = new \App\Http\Resources\UserResource($user->loadMissing('role'));
        // return response()->json($userResource);

        // Hoặc giữ lại cấu trúc gốc của bạn nếu chưa có UserResource
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'mssv' => $user->mssv,
            'role' => $user->role->name ?? null,
            'profile_photo_url' => $user->profile_photo_url,
            'profile_status' => $user->profile_status,
        ]);
    }
}