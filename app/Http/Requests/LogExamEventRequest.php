<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LogExamEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_type' => [
                'required',
                Rule::in([
                    'visibility_hidden',
                    'visibility_visible',
                    'window_blur',
                    'window_focus',
                    'refresh',
                    'reconnect',
                    'duplicate_session',
                ]),
            ],
        ];
    }
}

