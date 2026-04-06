<?php

namespace App\Http\Requests;

use App\Models\Exam;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
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
            'scoring_mode' => ['nullable', Rule::in(['auto', 'manual'])],
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
            'target_score' => ['nullable', 'numeric', 'min:1', 'max:1000'],
            'objective_weight_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'essay_weight_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
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
            'question_points' => ['nullable', 'array'],
            'question_points.*' => ['nullable', 'numeric', 'min:0.1'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $examType = (string) $this->input('exam_type', 'mixed');
            $objectiveWeight = (float) $this->input('objective_weight_percent', 60);
            $essayWeight = (float) $this->input('essay_weight_percent', 40);

            if ($examType === 'mixed' && abs(($objectiveWeight + $essayWeight) - 100.0) > 0.0001) {
                $validator->errors()->add(
                    'objective_weight_percent',
                    'Objective and essay weights must total exactly 100 for mixed exam type.'
                );
            }

            if ((string) $this->input('scoring_mode', 'auto') === 'manual') {
                $questionIds = collect($this->input('question_ids', []))
                    ->map(fn ($id) => (int) $id)
                    ->filter()
                    ->unique()
                    ->values();
                $points = collect($this->input('question_points', []));

                foreach ($questionIds as $questionId) {
                    $point = $points->get((string) $questionId, $points->get($questionId));
                    if ($point === null || $point === '' || ! is_numeric($point) || (float) $point <= 0) {
                        $validator->errors()->add(
                            "question_points.{$questionId}",
                            "Point for selected question {$questionId} is required and must be greater than zero."
                        );
                    }
                }
            }
        });
    }
}
