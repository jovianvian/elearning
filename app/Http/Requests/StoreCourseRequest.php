<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCourseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')],
            'class_id' => ['required', 'integer', Rule::exists('school_classes', 'id')],
            'academic_year_id' => ['required', 'integer', Rule::exists('academic_years', 'id')],
            'semester_id' => ['required', 'integer', Rule::exists('semesters', 'id')],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'teacher_ids' => ['required', 'array', 'min:1'],
            'teacher_ids.*' => ['required', 'integer', Rule::exists('users', 'id')],
            'main_teacher_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
        ];
    }
}
