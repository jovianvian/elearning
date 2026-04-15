<?php

namespace App\Http\Requests;

use App\Models\Exam;
use App\Models\Question;
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
            'exam_type' => ['required', Rule::in([
                Exam::TYPE_OBJECTIVE,
                Exam::TYPE_OBJECTIVE_SINGLE_CHOICE,
                Exam::TYPE_OBJECTIVE_MULTI_RESPONSE,
                Exam::TYPE_OBJECTIVE_SHORT_ANSWER,
                Exam::TYPE_ESSAY,
                Exam::TYPE_MIXED,
            ])],
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
            'required_paid_month' => ['nullable', 'integer', 'between:1,12'],
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

            $examType = (string) $this->input('exam_type', Exam::TYPE_MIXED);
            $questionIds = collect($this->input('question_ids', []))
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->values();

            if ($questionIds->isEmpty()) {
                return;
            }

            $typeByQuestionId = Question::query()
                ->whereIn('id', $questionIds->all())
                ->pluck('type', 'id');

            foreach ($questionIds as $questionId) {
                $questionType = (string) ($typeByQuestionId[$questionId] ?? '');

                if ($examType === Exam::TYPE_OBJECTIVE_SINGLE_CHOICE && $questionType !== Question::TYPE_MULTIPLE_CHOICE) {
                    $validator->errors()->add('question_ids', 'Single-choice exam only allows single-answer multiple choice questions.');
                    return;
                }

                if ($examType === Exam::TYPE_OBJECTIVE_MULTI_RESPONSE && ! in_array($questionType, [Question::TYPE_MULTIPLE_CHOICE, Question::TYPE_MULTIPLE_RESPONSE], true)) {
                    $validator->errors()->add('question_ids', 'Objective multi-response exam only allows single-choice and multi-response objective questions.');
                    return;
                }

                if ($examType === Exam::TYPE_OBJECTIVE_SHORT_ANSWER && $questionType !== Question::TYPE_SHORT_ANSWER) {
                    $validator->errors()->add('question_ids', 'Short-answer exam only allows short-answer questions.');
                    return;
                }

                if ($examType === Exam::TYPE_ESSAY && $questionType !== Question::TYPE_ESSAY) {
                    $validator->errors()->add('question_ids', 'Essay exam only allows essay questions.');
                    return;
                }
            }
        });
    }
}
