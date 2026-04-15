<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveExamAnswersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers' => ['nullable', 'array'],
            'answers.*.selected_option_id' => ['nullable', 'integer'],
            'answers.*.selected_option_ids' => ['nullable', 'array'],
            'answers.*.selected_option_ids.*' => ['nullable', 'integer'],
            'answers.*.answer_text' => ['nullable', 'string'],
        ];
    }
}
