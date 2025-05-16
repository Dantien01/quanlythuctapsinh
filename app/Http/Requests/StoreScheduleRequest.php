<?php

namespace App\Http\Requests;
use Illuminate\Support\Facades\Auth; // Thêm import này ở đầu file nếu chưa có
use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
{
    // Chỉ Admin mới được tạo
    return Auth::check() && Auth::user()?->hasRole('Admin');
}

public function rules(): array
{
    return [
        'user_id' => ['required', 'integer', 'exists:users,id'], // Đảm bảo SV tồn tại
        'title' => ['required', 'string', 'max:255'],
        'description' => ['nullable', 'string'],
        'start_time' => ['required', 'date_format:Y-m-d\TH:i'],
        'end_time' => ['required', 'date_format:Y-m-d\TH:i', 'after:start_time'], // Kết thúc phải sau bắt đầu
         ];
    }
}