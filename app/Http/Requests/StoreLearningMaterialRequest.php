<?php

namespace App\Http\Requests;

use App\Models\LearningMaterial;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLearningMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'content' => ['nullable', 'string'],
            'type' => ['required', Rule::in(LearningMaterial::availableTypes())],
            'external_url' => ['nullable', 'url', 'required_if:type,'.LearningMaterial::TYPE_LINK.','.LearningMaterial::TYPE_VIDEO],
            'upload_file' => ['nullable', 'file', 'max:10240'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $type = (string) $this->input('type');
            $content = trim((string) $this->input('content'));

            if ($type === LearningMaterial::TYPE_TEXT && $content === '') {
                $validator->errors()->add('content', 'Content is required for text materials.');
            }

            if ($type === LearningMaterial::TYPE_FILE && ! $this->hasFile('upload_file')) {
                $validator->errors()->add('upload_file', 'File upload is required for file materials.');
            }
        });
    }
}

