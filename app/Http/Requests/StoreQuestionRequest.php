<?php

namespace App\Http\Requests;

use App\Models\Question;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in([Question::TYPE_MULTIPLE_CHOICE, Question::TYPE_SHORT_ANSWER, Question::TYPE_ESSAY])],
            'question_text' => ['required', 'string'],
            'question_text_en' => ['nullable', 'string'],
            'explanation' => ['nullable', 'string'],
            'explanation_en' => ['nullable', 'string'],
            'points' => ['required', 'numeric', 'min:0.1'],
            'difficulty' => ['required', Rule::in(['easy', 'medium', 'hard'])],
            'is_active' => ['nullable', 'boolean'],
            'short_answer_key' => ['nullable', 'string', 'required_if:type,'.Question::TYPE_SHORT_ANSWER],
            'correct_option' => ['nullable', 'string', 'required_if:type,'.Question::TYPE_MULTIPLE_CHOICE],
            'options' => ['nullable', 'array', 'required_if:type,'.Question::TYPE_MULTIPLE_CHOICE, 'min:2'],
            'options.*.key' => ['nullable', 'string'],
            'options.*.text' => ['nullable', 'string'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $type = $this->input('type');

            if ($type !== Question::TYPE_MULTIPLE_CHOICE) {
                return;
            }

            $options = $this->input('options', []);
            $keys = [];

            foreach ($options as $index => $option) {
                $key = strtoupper(trim((string) ($option['key'] ?? '')));
                $text = trim((string) ($option['text'] ?? ''));

                if (! in_array($key, ['A', 'B', 'C', 'D', 'E'], true)) {
                    $validator->errors()->add("options.{$index}.key", 'Option key must be A, B, C, D, or E.');
                }

                if ($text === '') {
                    $validator->errors()->add("options.{$index}.text", 'Option text is required.');
                }

                if (in_array($key, $keys, true)) {
                    $validator->errors()->add("options.{$index}.key", 'Option keys must be unique.');
                }

                $keys[] = $key;
            }

            $correct = strtoupper(trim((string) $this->input('correct_option')));
            if (! in_array($correct, $keys, true)) {
                $validator->errors()->add('correct_option', 'Correct option must match one of the option keys.');
            }
        });
    }
}

