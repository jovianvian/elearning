<?php

namespace App\Http\Requests;

use App\Models\QuestionBank;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateQuestionBankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'visibility' => ['required', Rule::in([QuestionBank::VISIBILITY_SUBJECT_SHARED, QuestionBank::VISIBILITY_PRIVATE])],
        ];
    }
}

