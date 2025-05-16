<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage; // <<< THÊM IMPORT STORAGE

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
    
        $user = $request->user();

        // Validate và lấy dữ liệu đã validate (từ ProfileUpdateRequest)
        $validatedData = $request->validated();

        // Cập nhật các trường thông thường
        $user->fill($validatedData);

        // Xử lý nếu email thay đổi và cần xác thực lại
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        // --- XỬ LÝ UPLOAD ẢNH ĐẠI DIỆN ---
        if ($request->hasFile('photo')) {
            // Validate file ảnh (có thể thêm vào ProfileUpdateRequest)
            $request->validate([
                'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:2048'], // Cho phép null, chỉ nhận jpg/png, max 2MB
            ]);

            // Xóa ảnh cũ nếu có
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            // Lưu ảnh mới vào storage/app/public/profile-photos
            // và lấy đường dẫn lưu vào $path
            $path = $request->file('photo')->store('profile-photos', 'public');

            // Cập nhật đường dẫn ảnh mới vào cột profile_photo_path
            $user->profile_photo_path = $path;
        }
        // ---------------------------------

        // Lưu thay đổi vào database
        $user->save();

        // Chuyển hướng về trang edit profile với thông báo thành công
        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
