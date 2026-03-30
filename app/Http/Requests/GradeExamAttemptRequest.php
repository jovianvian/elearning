<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GradeExamAttemptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grades' => ['required', 'array', 'min:1'],
            'grades.*.score' => ['required', 'numeric', 'min:0'],
            'grades.*.teacher_feedback' => ['nullable', 'string'],
        ];
    }
}

