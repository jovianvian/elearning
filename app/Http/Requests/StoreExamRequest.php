<?php

namespace App\Http\Requests;

use App\Models\Exam;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', Rule::exists('courses', 'id')],
            'question_bank_id' => ['nullable', 'integer', Rule::exists('question_banks', 'id')],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'exam_type' => ['required', Rule::in(['objective', 'essay', 'mixed'])],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:300'],
            'shuffle_questions' => ['nullable', 'boolean'],
            'shuffle_options' => ['nullable', 'boolean'],
            'auto_submit' => ['nullable', 'boolean'],
            'show_result_after_submit' => ['nullable', 'boolean'],
            'show_answer_key' => ['nullable', 'boolean'],
            'show_explanation' => ['nullable', 'boolean'],
            'max_attempts' => ['nullable', 'integer', 'min:1', 'max:5'],
            'status' => ['required', Rule::in([
                Exam::STATUS_DRAFT,
                Exam::STATUS_SCHEDULED,
                Exam::STATUS_ACTIVE,
                Exam::STATUS_CLOSED,
                Exam::STATUS_ARCHIVED,
            ])],
            'is_published' => ['nullable', 'boolean'],
            'question_ids' => ['required', 'array', 'min:1'],
            'question_ids.*' => ['required', 'integer', Rule::exists('questions', 'id')],
        ];
    }
}
