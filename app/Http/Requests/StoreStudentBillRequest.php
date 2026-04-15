<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentBillRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'academic_year_id' => ['required', 'integer', 'exists:academic_years,id'],
            'semester_id' => ['required', 'integer', 'exists:semesters,id'],
            'monthly_amount' => ['required', 'numeric', 'min:1'],
        ];
    }
}

