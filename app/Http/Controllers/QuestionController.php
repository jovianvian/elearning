<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Services\QuestionAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuestionController extends Controller
{
    public function __construct(private readonly QuestionAccessService $accessService)
    {
    }

    public function create(QuestionBank $questionBank): View
    {
        abort_unless($this->accessService->canManageBank(auth()->user(), $questionBank), 403);

        return view('questions.create', compact('questionBank'));
    }

    public function store(StoreQuestionRequest $request, QuestionBank $questionBank): RedirectResponse
    {
        abort_unless($this->accessService->canManageBank(auth()->user(), $questionBank), 403);

        $data = $request->validated();
        $imagePath = $this->resolveQuestionImagePath($data['question_image_path'] ?? null, $request->file('question_image_file'));

        DB::transaction(function () use ($data, $questionBank, $imagePath): void {
            $question = Question::create([
                'question_bank_id' => $questionBank->id,
                'subject_id' => $questionBank->subject_id,
                'created_by' => auth()->id(),
                'type' => $data['type'],
                'question_text' => $data['question_text'],
                'question_text_en' => $data['question_text_en'] ?? null,
                'question_image_path' => $imagePath,
                'explanation' => $data['explanation'] ?? null,
                'explanation_en' => $data['explanation_en'] ?? null,
                'points' => $data['points'],
                'difficulty' => $data['difficulty'],
                'import_source' => 'manual',
                'short_answer_key' => $data['type'] === Question::TYPE_SHORT_ANSWER
                    ? $this->normalizeText((string) $data['short_answer_key'])
                    : null,
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]);

            if (in_array($data['type'], [Question::TYPE_MULTIPLE_CHOICE, Question::TYPE_MULTIPLE_RESPONSE], true)) {
                $correctOptionKeys = $data['type'] === Question::TYPE_MULTIPLE_CHOICE
                    ? [strtoupper((string) $data['correct_option'])]
                    : array_values(array_unique(array_map(
                        static fn ($value) => strtoupper(trim((string) $value)),
                        (array) ($data['correct_options'] ?? [])
                    )));
                $this->syncObjectiveOptions($question, $data['options'], $correctOptionKeys);
            }
        });

        return redirect()
            ->route('question-banks.show', $questionBank)
            ->with('success', 'Question created.');
    }

    public function edit(Question $question): View
    {
        $question->load(['bank', 'options']);
        abort_unless($this->accessService->canManageBank(auth()->user(), $question->bank), 403);

        return view('questions.edit', compact('question'));
    }

    public function update(UpdateQuestionRequest $request, Question $question): RedirectResponse
    {
        $question->load(['bank', 'options']);
        abort_unless($this->accessService->canManageBank(auth()->user(), $question->bank), 403);

        $data = $request->validated();
        $imagePath = $this->resolveQuestionImagePath(
            $data['question_image_path'] ?? null,
            $request->file('question_image_file'),
            $question->question_image_path
        );

        DB::transaction(function () use ($question, $data, $imagePath): void {
            $question->update([
                'type' => $data['type'],
                'question_text' => $data['question_text'],
                'question_text_en' => $data['question_text_en'] ?? null,
                'question_image_path' => $imagePath,
                'explanation' => $data['explanation'] ?? null,
                'explanation_en' => $data['explanation_en'] ?? null,
                'points' => $data['points'],
                'difficulty' => $data['difficulty'],
                'short_answer_key' => $data['type'] === Question::TYPE_SHORT_ANSWER
                    ? $this->normalizeText((string) $data['short_answer_key'])
                    : null,
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]);

            if (in_array($data['type'], [Question::TYPE_MULTIPLE_CHOICE, Question::TYPE_MULTIPLE_RESPONSE], true)) {
                $correctOptionKeys = $data['type'] === Question::TYPE_MULTIPLE_CHOICE
                    ? [strtoupper((string) $data['correct_option'])]
                    : array_values(array_unique(array_map(
                        static fn ($value) => strtoupper(trim((string) $value)),
                        (array) ($data['correct_options'] ?? [])
                    )));
                $this->syncObjectiveOptions($question, $data['options'], $correctOptionKeys);
            } else {
                $question->options()->delete();
            }
        });

        return redirect()
            ->route('question-banks.show', $question->question_bank_id)
            ->with('success', 'Question updated.');
    }

    public function destroy(Question $question): RedirectResponse
    {
        $question->load('bank');
        abort_unless($this->accessService->canManageBank(auth()->user(), $question->bank), 403);

        $question->delete();

        return redirect()
            ->route('question-banks.show', $question->question_bank_id)
            ->with('success', 'Question moved to trash.');
    }

    private function syncObjectiveOptions(Question $question, array $options, array $correctOptions): void
    {
        $question->options()->delete();
        $normalizedCorrect = array_values(array_unique(array_map(
            static fn ($value) => strtoupper(trim((string) $value)),
            $correctOptions
        )));

        foreach ($options as $option) {
            $key = strtoupper(trim((string) ($option['key'] ?? '')));

            QuestionOption::create([
                'question_id' => $question->id,
                'option_key' => $key,
                'option_text' => trim((string) ($option['text'] ?? '')),
                'option_text_en' => null,
                'is_correct' => in_array($key, $normalizedCorrect, true),
            ]);
        }
    }

    private function normalizeText(string $text): ?string
    {
        $normalized = strtolower(trim(preg_replace('/\s+/u', ' ', $text) ?? ''));

        return $normalized === '' ? null : $normalized;
    }

    private function resolveQuestionImagePath(?string $rawPath, ?UploadedFile $uploadedFile, ?string $existingPath = null): ?string
    {
        if ($uploadedFile !== null) {
            $stored = $uploadedFile->store('questions', 'public');

            return 'storage/'.$stored;
        }

        $path = trim((string) $rawPath);
        if ($path === '') {
            return $existingPath;
        }

        $normalized = str_replace('\\', '/', $path);
        $normalized = preg_replace('#^(\./)+#', '', $normalized) ?? $normalized;

        return $normalized;
    }
}
