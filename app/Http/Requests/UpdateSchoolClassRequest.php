<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSchoolClassRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $classId = $this->route('school_class')?->id;

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('school_classes', 'name')->ignore($classId)],
            'code' => ['nullable', 'string', 'max:30', Rule::unique('school_classes', 'code')->ignore($classId)],
            'grade_level' => ['required', 'integer', 'in:7,8,9'],
            'academic_year_id' => ['required', 'integer', Rule::exists('academic_years', 'id')],
            'homeroom_teacher_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
