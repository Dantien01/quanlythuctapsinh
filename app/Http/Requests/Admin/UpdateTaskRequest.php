<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Task;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth; // THÊM DÒNG NÀY

class UpdateTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * @return bool
     */
    public function authorize(): bool
    {
        if (!Auth::check()) { // SỬ DỤNG Auth::check()
            return false;
        }
        $user = Auth::user(); // SỬ DỤNG Auth::user()
        return $user && $user->role && $user->role->name === 'Admin';
    }

    // ... (phần rules() và messages() giữ nguyên như trước)
    public function rules(): array
    {
        return [
            'intern_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereExists(function ($subQuery) {
                        $subQuery->select(\DB::raw(1))
                                 ->from('roles') // Đảm bảo đây là bảng vai trò của bạn
                                 ->whereColumn('roles.id', 'users.role_id') // Đảm bảo users.role_id là cột đúng
                                 ->where('roles.name', 'SinhVien'); // Đảm bảo roles.name là cột tên vai trò
                    })->where('profile_status', 'approved'); // <<< CHỈ SỬA Ở ĐÂY
                })
            ],
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'due_date' => 'required|date',
            'status' => ['required', 'string', Rule::in(array_keys(Task::statuses()))],
            'priority' => ['nullable', 'string', Rule::in(array_keys(Task::priorities()))],
        ];
    }

    public function messages(): array
    {
        return [
            'intern_id.required' => __('Vui lòng chọn sinh viên.'),
            'intern_id.exists' => __('Sinh viên được chọn không hợp lệ hoặc chưa được duyệt.'),
            'title.required' => __('Tiêu đề công việc không được để trống.'),
            'due_date.required' => __('Hạn chót không được để trống.'),
            'status.required' => __('Vui lòng chọn trạng thái công việc.'),
            'status.in' => __('Trạng thái công việc không hợp lệ.'),
            'priority.in' => __('Độ ưu tiên không hợp lệ.'),
        ];
    }
}