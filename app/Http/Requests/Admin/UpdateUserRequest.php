<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Import Rule để validate unique email

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Tạm thời để true, chúng ta sẽ dùng Policy ở controller.
     * Hoặc bạn có thể check quyền Admin ở đây: return $this->user()->role->name === 'Admin';
     */
    public function authorize(): bool
    {
        return true; // Tạm thời cho phép
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user')->id; // Lấy ID của user đang được update từ route

        return [
            'name' => ['required', 'string', 'max:255'], // Tên là bắt buộc, dạng chuỗi, tối đa 255 ký tự
            'email' => [
                'required', // Email là bắt buộc
                'string', // Dạng chuỗi
                'lowercase', // Chuyển thành chữ thường
                'email', // Phải là định dạng email hợp lệ
                'max:255', // Tối đa 255 ký tự
                Rule::unique('users')->ignore($userId), // Phải là duy nhất trong bảng users, ngoại trừ chính user đang sửa
            ],
            'role_id' => ['required', 'integer', 'exists:roles,id'], // Role ID là bắt buộc, số nguyên, và phải tồn tại trong bảng roles
            'mssv' => ['nullable', 'string', 'max:255', Rule::unique('users')->ignore($userId)], // MSSV không bắt buộc, nhưng nếu có thì phải là duy nhất (trừ user này)
            'phone' => ['nullable', 'string', 'max:20'], // SĐT không bắt buộc
            // Thêm validation cho school_id, major_id nếu bạn cho phép sửa chúng ở đây
            // 'school_id' => ['nullable', 'integer', 'exists:schools,id'],
            // 'major_id' => ['nullable', 'integer', 'exists:majors,id'],
        ];
    }
}