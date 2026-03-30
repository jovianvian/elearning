<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestionImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'question_bank_id' => ['required', 'integer', Rule::exists('question_banks', 'id')],
            'import_type' => ['required', Rule::in(['aiken', 'csv'])],
            'file' => ['required', 'file', 'mimes:txt,csv', 'max:10240'],
        ];
    }
}

