<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMajorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->role->name === 'Admin';
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $majorId = $this->route('major')->id; // Lấy ID của major đang sửa

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // Tên ngành phải duy nhất trong cùng trường, trừ chính nó
                Rule::unique('majors')->where(function ($query) {
                    return $query->where('school_id', $this->input('school_id'));
                })->ignore($majorId) // Bỏ qua chính major này khi kiểm tra unique
            ],
            'school_id' => ['required', 'integer', 'exists:schools,id'],
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
            'name.unique' => 'Tên chuyên ngành này đã tồn tại trong trường đã chọn.',
            'school_id.required' => 'Bạn phải chọn trường học.',
            'school_id.exists' => 'Trường học được chọn không hợp lệ.',
        ];
    }
}