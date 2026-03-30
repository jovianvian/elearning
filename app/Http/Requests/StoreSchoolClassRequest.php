<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSchoolClassRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:school_classes,name'],
            'code' => ['nullable', 'string', 'max:30', 'unique:school_classes,code'],
            'grade_level' => ['required', 'integer', 'in:7,8,9'],
            'academic_year_id' => ['required', 'integer', Rule::exists('academic_years', 'id')],
            'homeroom_teacher_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
