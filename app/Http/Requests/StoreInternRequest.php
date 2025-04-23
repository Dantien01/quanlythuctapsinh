<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInternRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->role === 'admin';
    }

    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
            'student_code' => 'required|string|unique:interns,student_code',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'faculty' => 'nullable|string|max:100',
            'major' => 'nullable|string|max:100',
            'academic_year' => 'nullable|string|max:50',
            'skills' => 'nullable|string',
            'cv_path' => 'nullable|string|max:255',
        ];
    }
}