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
                    'exam_start',
                    'exam_submit',
                    'auto_submit',
                    'tab_switch',
                    'visibility_hidden',
                    'visibility_visible',
                    'window_blur',
                    'window_focus',
                    'refresh',
                    'reconnect',
                    'duplicate_session',
                    'multiple_tabs_detected',
                    'heartbeat',
                ]),
            ],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
