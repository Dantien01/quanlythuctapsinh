<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Validator; // Thêm Validator

class UserProfileController extends Controller
{
    /**
     * Xem hồ sơ cá nhân của người dùng hiện tại.
     * GET /api/profile
     */
    public function show(Request $request)
    {
        $user = Auth::user();
        // Tùy chọn: Sử dụng API Resource để tùy chỉnh dữ liệu trả về
        // return new UserResource($user);
        return response()->json($user);
    }

    /**
     * Cập nhật hồ sơ cá nhân.
     * POST /api/profile (Sử dụng POST với _method=PUT nếu gửi form-data, hoặc dùng PUT trực tiếp nếu là JSON)
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            // Thêm các trường khác bạn cho phép cập nhật
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048', // Ảnh đại diện
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        if ($request->hasFile('profile_photo')) {
            // Xóa ảnh cũ nếu có
            if ($user->profile_photo_path && Storage::disk('public')->exists($user->profile_photo_path)) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            // Lưu ảnh mới
            $path = $request->file('profile_photo')->store('profile-photos', 'public');
            $validatedData['profile_photo_path'] = $path;
            unset($validatedData['profile_photo']); // Không lưu 'profile_photo' mà là 'profile_photo_path'
        }

        $user->update($validatedData);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => $user->fresh()
        ]);
    }

    /**
     * Đổi mật khẩu.
     * PUT /api/profile/password
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string', function ($attribute, $value, $fail) use ($user) {
                if (!Hash::check($value, $user->password)) {
                    $fail('Mật khẩu hiện tại không đúng.');
                }
            }],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        return response()->json(['message' => 'Password changed successfully.']);
    }
}