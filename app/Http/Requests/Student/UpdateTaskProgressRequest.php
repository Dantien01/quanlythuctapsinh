<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use App\Models\TaskProgress;
use Illuminate\Validation\Rule; // <<< THÊM IMPORT NÀY

class UpdateTaskProgressRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\Task $task */
        $task = $this->route('task');
        /** @var \App\Models\TaskProgress $taskProgress */
        $taskProgress = $this->route('taskProgress');

        return $task && $taskProgress &&
               $task->intern_id === Auth::id() &&
               $taskProgress->task_id === $task->id &&
               $taskProgress->user_id === Auth::id();
    }

    public function rules(): array
    {
        // Danh sách các giá trị phần trăm hợp lệ (PHẢI GIỐNG HỆT mảng trong <select> của _form.blade.php)
        $validPercentageValues = [0, 10, 25, 50, 75, 90, 100];

        return [
            'notes' => 'required|string|max:5000',
            'progress_percentage' => [ // Chuyển thành mảng để thêm Rule::in
                'nullable',             // Cho phép null nếu option rỗng được chọn và DB cho phép null
                'integer',              // Vẫn cần thiết để đảm bảo là số nguyên
                Rule::in($validPercentageValues), // Đảm bảo giá trị nằm trong danh sách cho phép
            ],
            'submitted_at' => 'nullable|date|before_or_equal:today',
        ];
    }

    public function messages(): array
    {
        return [
            'notes.required' => 'Vui lòng nhập ghi chú tiến độ.',
            // Cập nhật thông báo cho progress_percentage
            'progress_percentage.integer' => 'Phần trăm hoàn thành không hợp lệ.',
            'progress_percentage.in' => 'Giá trị phần trăm hoàn thành không hợp lệ.', // Thông báo cho Rule::in
            // Bỏ min/max vì Rule::in đã bao gồm việc kiểm tra giá trị
            // 'progress_percentage.min' => 'Phần trăm hoàn thành phải lớn hơn hoặc bằng 0.',
            // 'progress_percentage.max' => 'Phần trăm hoàn thành phải nhỏ hơn hoặc bằng 100.',
            'submitted_at.date' => 'Ngày cập nhật không hợp lệ.',
            'submitted_at.before_or_equal' => 'Ngày cập nhật không được lớn hơn ngày hiện tại.',
        ];
    }
}