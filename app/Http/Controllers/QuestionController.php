<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuestionRequest;
use App\Http\Requests\UpdateQuestionRequest;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Services\QuestionAccessService;
use Illuminate\Http\RedirectResponse;
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

        DB::transaction(function () use ($data, $questionBank): void {
            $question = Question::create([
                'question_bank_id' => $questionBank->id,
                'subject_id' => $questionBank->subject_id,
                'created_by' => auth()->id(),
                'type' => $data['type'],
                'question_text' => $data['question_text'],
                'question_text_en' => $data['question_text_en'] ?? null,
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

            if ($data['type'] === Question::TYPE_MULTIPLE_CHOICE) {
                $this->syncMultipleChoiceOptions($question, $data['options'], strtoupper((string) $data['correct_option']));
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

        DB::transaction(function () use ($question, $data): void {
            $question->update([
                'type' => $data['type'],
                'question_text' => $data['question_text'],
                'question_text_en' => $data['question_text_en'] ?? null,
                'explanation' => $data['explanation'] ?? null,
                'explanation_en' => $data['explanation_en'] ?? null,
                'points' => $data['points'],
                'difficulty' => $data['difficulty'],
                'short_answer_key' => $data['type'] === Question::TYPE_SHORT_ANSWER
                    ? $this->normalizeText((string) $data['short_answer_key'])
                    : null,
                'is_active' => (bool) ($data['is_active'] ?? false),
            ]);

            if ($data['type'] === Question::TYPE_MULTIPLE_CHOICE) {
                $this->syncMultipleChoiceOptions($question, $data['options'], strtoupper((string) $data['correct_option']));
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

    private function syncMultipleChoiceOptions(Question $question, array $options, string $correctOption): void
    {
        $question->options()->delete();

        foreach ($options as $option) {
            $key = strtoupper(trim((string) ($option['key'] ?? '')));

            QuestionOption::create([
                'question_id' => $question->id,
                'option_key' => $key,
                'option_text' => trim((string) ($option['text'] ?? '')),
                'option_text_en' => null,
                'is_correct' => $key === $correctOption,
            ]);
        }
    }

    private function normalizeText(string $text): ?string
    {
        $normalized = strtolower(trim(preg_replace('/\s+/u', ' ', $text) ?? ''));

        return $normalized === '' ? null : $normalized;
    }
}

