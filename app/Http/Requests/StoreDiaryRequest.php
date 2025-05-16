<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDiaryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
        public function authorize(): bool
    {
        // Đã kiểm tra quyền ở controller, ở đây chỉ cần return true
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'diary_date' => 'required|date',
            'content' => 'required|string',
        ];
    }
}
