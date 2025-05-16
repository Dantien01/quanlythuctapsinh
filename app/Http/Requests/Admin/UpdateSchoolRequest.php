<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule; // Import Rule

class UpdateSchoolRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Chỉ Admin mới được sửa School
        return $this->user()->role->name === 'Admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $schoolId = $this->route('school')->id; // Lấy ID của school đang được update từ route

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('schools')->ignore($schoolId) // Tên phải duy nhất, trừ chính school này
            ],
            'address' => ['nullable', 'string', 'max:255'],
        ];
    }
}