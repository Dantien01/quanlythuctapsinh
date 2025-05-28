<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateScheduleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()?->hasRole('Admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            // =========================================================================
            // PHẦN CẬP NHẬT - START: TRẢ LẠI TÊN CŨ
            // =========================================================================
            'start_time' => ['required', 'date_format:Y-m-d\TH:i'], // Thời gian tổng thể
            'end_time' => ['required', 'date_format:Y-m-d\TH:i', 'after:start_time'], // Kết thúc tổng thể phải sau bắt đầu tổng thể
            // =========================================================================
            // PHẦN CẬP NHẬT - END
            // =========================================================================

            'slots' => ['nullable', 'array'],
            'slots.*.day_of_week' => ['required_with:slots', 'integer', 'between:1,7'],
            'slots.*.start_time' => ['required_with:slots', 'date_format:H:i'],
            'slots.*.end_time' => ['required_with:slots', 'date_format:H:i', 'after:slots.*.start_time'],
            'slots.*.task_description' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Vui lòng chọn sinh viên.',
            // ... (các message khác) ...
            // =========================================================================
            // PHẦN CẬP NHẬT - START: TRẢ LẠI TÊN CŨ TRONG MESSAGES
            // =========================================================================
            'start_time.required' => 'Thời gian bắt đầu tổng thể không được để trống.',
            'start_time.date_format' => 'Định dạng thời gian bắt đầu tổng thể không hợp lệ.',
            'end_time.required' => 'Thời gian kết thúc tổng thể không được để trống.',
            'end_time.date_format' => 'Định dạng thời gian kết thúc tổng thể không hợp lệ.',
            'end_time.after' => 'Thời gian kết thúc tổng thể phải sau thời gian bắt đầu tổng thể.',
            // =========================================================================
            // PHẦN CẬP NHẬT - END
            // =========================================================================

            'slots.array' => 'Danh sách buổi thực tập không hợp lệ.',
            // ... (các message khác cho slots giữ nguyên) ...
            'slots.*.task_description.max' => 'Mô tả công việc cho buổi thực tập không được vượt quá 500 ký tự.',
        ];
    }
}