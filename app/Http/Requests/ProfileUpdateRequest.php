<?php

namespace App\Http\Requests; // Namespace mặc định của Breeze

use App\Models\User; // Import Model User
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Import Rule

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Lấy ID của user đang thực hiện request (chính là user đang đăng nhập)
        $userId = $this->user()->id;

        return [
            // --- Rules mặc định của Breeze (Giữ lại) ---
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($userId) // Kiểm tra email duy nhất, trừ chính user này
            ],

            // --- THÊM RULES CHO TRƯỜNG SINH VIÊN ---
            'mssv' => [
                'nullable', // Cho phép không nhập
                'string',
                'max:20', // Độ dài tối đa cho MSSV
                Rule::unique(User::class)->ignore($userId) // MSSV cũng phải duy nhất (trừ chính user này)
            ],
            'phone' => [
                'nullable', // Cho phép không nhập
                'string',
                'max:20', // Độ dài tối đa cho SĐT
                // Có thể thêm regex kiểm tra định dạng SĐT nếu muốn: 'regex:/^([0-9\s\-\+\(\)]*)$/'
            ],
            // Bạn KHÔNG nên cho phép sửa school_id, major_id, profile_status ở đây
            // vì những thông tin này thường do Admin quản lý hoặc chỉ nhập 1 lần khi đăng ký.
        ];
    }
}